<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolmartFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_only_exposes_public_and_member_actions(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
        $response->assertDontSee('Katalog Belanja Anggota');
        $response->assertDontSee('Siap Berbelanja di Polmart?');
        $response->assertDontSee('Panel Admin & Pengurus');
        $response->assertDontSee('Dashboard Admin');
    }

    public function test_role_based_access_control_and_navigation_isolation(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $admin = User::factory()->admin()->create();

        // Member ditolak (403) mengakses seluruh rute admin
        $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.manual-orders.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.order-imports.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.pickup.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.transactions.index'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.reports.index'))->assertForbidden();

        // Admin ditolak (403) mengakses rute anggota
        $this->actingAs($admin)->get(route('anggota.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('anggota.katalog'))->assertForbidden();
        $this->actingAs($admin)->get(route('anggota.keranjang'))->assertForbidden();
        $this->actingAs($admin)->get(route('anggota.pesanan'))->assertForbidden();

        // Redirect /dashboard sesuai role
        $this->actingAs($member)->get('/dashboard')->assertRedirect(route('anggota.dashboard'));
        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));

        // Navigasi terpisah per role
        $memberDash = $this->actingAs($member)->get(route('anggota.dashboard'));
        $memberDash->assertOk();
        $memberDash->assertSee('Katalog Belanja');
        $memberDash->assertDontSee('Master Barang');
        $memberDash->assertDontSee('Validasi Loket Ambil');

        $adminDash = $this->actingAs($admin)->get(route('admin.dashboard'));
        $adminDash->assertOk();
        $adminDash->assertSee('Master Barang');
        $adminDash->assertSee('Barang Terlaris');
        $adminDash->assertSee('Stok Terbanyak');
        $adminDash->assertDontSee('Keranjang Belanja');
    }

    public function test_member_checkout_qris_callback_and_admin_pickup_flow(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        $product = Product::create([
            'sku' => 'ATK-001',
            'name' => 'Buku Tulis Campus',
            'category' => 'Alat Tulis',
            'price' => 5000,
            'stock' => 20,
            'is_active' => true,
        ]);

        // Tambah ke keranjang & checkout
        $this->actingAs($member)
            ->post(route('anggota.keranjang.add'), [
                'product_id' => $product->id,
                'qty' => 3,
            ])
            ->assertRedirect();

        $checkoutRes = $this->actingAs($member)
            ->post(route('anggota.checkout.store'));

        $order = Order::firstOrFail();
        $checkoutRes->assertRedirect(route('anggota.pembayaran', ['order_code' => $order->order_code]));

        $this->assertSame('MENUNGGU_BAYAR', $order->status);
        $this->assertSame(17, $product->fresh()->stock);

        // Simulasi QRIS callback (FR-07)
        $callbackRes = $this->postJson(route('api.qris.simulate-callback'), [
            'order_code' => $order->order_code,
        ]);

        $callbackRes->assertOk()
            ->assertJson([
                'success' => true,
                'order_code' => $order->order_code,
                'status' => 'SIAP_DIAMBIL',
            ]);

        $order->refresh();
        $this->assertSame('SIAP_DIAMBIL', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $order->pickup_code);

        // Polling status endpoint
        $this->getJson(route('api.qris.status', ['order_code' => $order->order_code]))
            ->assertOk()
            ->assertJson([
                'status' => 'SIAP_DIAMBIL',
                'pickup_code' => $order->pickup_code,
            ]);

        // Validasi loket oleh admin menggunakan kode 4-digit
        $this->actingAs($admin)
            ->post(route('admin.pickup.verify-code'), [
                'pickup_code' => $order->pickup_code,
            ])
            ->assertRedirect();

        $this->assertSame('SELESAI', $order->fresh()->status);

        // Dashboard Analitik & Laporan (FR-11, FR-12)
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Buku Tulis Campus');

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['period' => 'monthly']))
            ->assertOk()
            ->assertSee($order->order_code);
    }

    public function test_expired_pending_order_is_cancelled_and_stock_returned(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $product = Product::create([
            'sku' => 'MNM-001',
            'name' => 'Air Mineral 600ml',
            'category' => 'Minuman',
            'price' => 3500,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($member)->post(route('anggota.keranjang.add'), [
            'product_id' => $product->id,
            'qty' => 4,
        ]);

        $this->actingAs($member)->post(route('anggota.checkout.store'));

        $order = Order::firstOrFail();
        $this->assertSame(6, $product->fresh()->stock);

        // Simulasikan waktu bayar 15 menit sudah terlewat
        $order->update(['expires_at' => now()->subMinute()]);

        $this->artisan('orders:cancel-expired')->assertExitCode(0);

        $this->assertSame('DIBATALKAN', $order->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_database_seeder_populates_expected_tables(): void
    {
        $this->seed(\Database\Seeders\SupermarketSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@polmart.test',
            'role' => 'admin',
            'member_code' => 'ADM-001',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'anggota@polmart.test',
            'role' => 'member',
            'member_code' => '224443001',
        ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'MNM-001',
        ]);

        $this->assertGreaterThanOrEqual(18, Order::count());
        $this->assertDatabaseHas('orders', ['source' => 'web']);
        $this->assertDatabaseHas('orders', ['source' => 'manual_admin']);
        $this->assertDatabaseHas('orders', ['source' => 'import_excel']);
        $this->assertDatabaseHas('orders', ['status' => 'MENUNGGU_BAYAR']);
        $this->assertDatabaseHas('orders', ['status' => 'SIAP_DIAMBIL']);
    }

    public function test_admin_dashboard_integrated_sales_analytics_and_period_filters(): void
    {
        $this->seed(\Database\Seeders\SupermarketSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();

        // 1. Default Bulanan
        $resMonthly = $this->actingAs($admin)->get(route('admin.dashboard'));
        $resMonthly->assertOk();
        $resMonthly->assertSee('Analisis Penjualan & Laporan Berkala Koperasi');
        $resMonthly->assertSee('Total Omzet');
        $resMonthly->assertSee('Jumlah Transaksi');
        $resMonthly->assertSee('Produk Terjual');
        $resMonthly->assertSee('Rata-rata Nilai Transaksi');
        $resMonthly->assertSee('Grafik Tren Penjualan');
        $resMonthly->assertSee('Distribusi Sumber Transaksi');
        $resMonthly->assertSee('Portal Anggota (QRIS)');
        $resMonthly->assertSee('Kasir Walk-in');
        $resMonthly->assertSee('Impor Excel Historis');
        $resMonthly->assertSee('Export Excel (.xlsx)');

        // 2. Filter Harian
        $resDaily = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2026-11-17',
        ]));
        $resDaily->assertOk();
        $resDaily->assertSee('Harian: 17 November 2026');
        $resDaily->assertSee('Per Jam');

        // 3. Filter Mingguan
        $resWeekly = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'weekly',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-17',
        ]));
        $resWeekly->assertOk();
        $resWeekly->assertSee('Mingguan:');
        $resWeekly->assertSee('Per Hari');

        // 4. Filter Tahunan
        $resYearly = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'yearly',
            'year' => 2026,
        ]));
        $resYearly->assertOk();
        $resYearly->assertSee('Tahunan: 2026');
        $resYearly->assertSee('Per Bulan');
    }

    public function test_admin_reports_export_excel_with_informative_filenames(): void
    {
        $this->seed(\Database\Seeders\SupermarketSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();

        // 1. Export Harian
        $resDaily = $this->actingAs($admin)->get(route('admin.reports.export', [
            'period' => 'daily',
            'date' => '2026-11-18',
        ]));
        $resDaily->assertOk();
        $this->assertStringContainsString('Laporan_Penjualan_Harian_2026-11-18.xlsx', (string) $resDaily->headers->get('content-disposition'));

        // 2. Export Bulanan
        $resMonthly = $this->actingAs($admin)->get(route('admin.reports.export', [
            'period' => 'monthly',
            'month' => 11,
            'year' => 2026,
        ]));
        $resMonthly->assertOk();
        $this->assertStringContainsString('Laporan_Penjualan_Bulanan_2026-11.xlsx', (string) $resMonthly->headers->get('content-disposition'));

        // 3. Export Tahunan
        $resYearly = $this->actingAs($admin)->get(route('admin.reports.export', [
            'period' => 'yearly',
            'year' => 2026,
        ]));
        $resYearly->assertOk();
        $this->assertStringContainsString('Laporan_Penjualan_Tahunan_2026.xlsx', (string) $resYearly->headers->get('content-disposition'));

        // 4. Export Mingguan
        $resWeekly = $this->actingAs($admin)->get(route('admin.reports.export', [
            'period' => 'weekly',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-16',
        ]));
        $resWeekly->assertOk();
        $this->assertStringContainsString('Laporan_Penjualan_Mingguan_2026-11-10_sd_2026-11-16.xlsx', (string) $resWeekly->headers->get('content-disposition'));
    }

    public function test_admin_reports_export_includes_summary_report_sheet_with_accurate_metrics(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Export ketika tidak ada transaksi (empty state) menghasilkan HTTP 200 valid
        $resEmpty = $this->actingAs($admin)->get(route('admin.reports.export', [
            'period' => 'daily',
            'date' => '2020-01-01',
        ]));
        $resEmpty->assertOk();

        // 2. Verifikasi struktur sheets PeriodicReportExport
        $exportEmpty = new \App\Exports\PeriodicReportExport(collect(), collect(), [
            'total_omzet' => 0.0,
            'total_transactions' => 0,
            'total_items_sold' => 0,
            'avg_transaction' => 0.0,
        ], 'Harian: 01 Januari 2020', [
            'web' => 0,
            'manual_admin' => 0,
            'import_excel' => 0,
            'other' => 0,
        ]);

        $sheets = $exportEmpty->sheets();
        $this->assertCount(3, $sheets);
        $this->assertInstanceOf(\App\Exports\Sheets\SummaryReportSheet::class, $sheets[0]);
        $this->assertInstanceOf(\App\Exports\Sheets\TransactionsReportSheet::class, $sheets[1]);
        $this->assertInstanceOf(\App\Exports\Sheets\TopProductsReportSheet::class, $sheets[2]);

        $this->assertSame('Ringkasan Eksekutif', $sheets[0]->title());
        $summaryData = $sheets[0]->array();
        $this->assertContains(['Total Omzet Penjualan (Rp)', 'Rp 0'], $summaryData);
        $this->assertContains(['Jumlah Transaksi Sukses', '0 Pesanan'], $summaryData);
        $this->assertContains(['Total Produk Terjual (Qty)', '0 Pcs'], $summaryData);
        $this->assertContains(['Rata-rata Nilai Transaksi (Rp)', 'Rp 0'], $summaryData);
        $this->assertContains(['Kriteria Data Transaksi', 'Hanya transaksi berstatus SELESAI'], $summaryData);

        // 3. Verifikasi dengan data terisi
        $exportFilled = new \App\Exports\PeriodicReportExport(collect(), collect(), [
            'total_omzet' => 150000.0,
            'total_transactions' => 3,
            'total_items_sold' => 7,
            'avg_transaction' => 50000.0,
        ], 'Bulanan: November 2026', [
            'web' => 2,
            'manual_admin' => 1,
            'import_excel' => 0,
            'other' => 0,
        ]);

        $filledSheetData = $exportFilled->sheets()[0]->array();
        $this->assertContains(['Periode Laporan', 'Bulanan: November 2026'], $filledSheetData);
        $this->assertContains(['Total Omzet Penjualan (Rp)', 'Rp 150.000'], $filledSheetData);
        $this->assertContains(['Jumlah Transaksi Sukses', '3 Pesanan'], $filledSheetData);
        $this->assertContains(['Total Produk Terjual (Qty)', '7 Pcs'], $filledSheetData);
        $this->assertContains(['Rata-rata Nilai Transaksi (Rp)', 'Rp 50.000'], $filledSheetData);
        $this->assertContains(['Distribusi: Portal Anggota (QRIS)', '2 Transaksi (66.7%)'], $filledSheetData);
        $this->assertContains(['Distribusi: Kasir Walk-in', '1 Transaksi (33.3%)'], $filledSheetData);
    }

    public function test_admin_dashboard_and_reports_handle_invalid_date_inputs_gracefully(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Invalid date string fallback
        $resInvalidDate = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => 'bukan-tanggal-valid',
        ]));
        $resInvalidDate->assertOk();

        // 2. Tanggal kalender tidak valid (misal 31 Februari) harus ditolak dan menggunakan fallback tanggal valid
        $resInvalidCalendarDate = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2026-02-31',
        ]));
        $resInvalidCalendarDate->assertOk();
        $resInvalidCalendarDate->assertDontSee('31 Februari 2026');

        // 3. Input non-string / array (misal date[]=...) tidak boleh memicu TypeError 500
        $resArrayDate = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => ['2026-01-01', 'hack'],
        ]));
        $resArrayDate->assertOk();

        // 4. Rentang mingguan lebih dari 7 hari dibatasi otomatis maksimal 7 hari
        $resInvalidWeekly = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'weekly',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-01', // Rentang 2 bulan
        ]));
        $resInvalidWeekly->assertOk();
        $resInvalidWeekly->assertSee('Mingguan: 01/01/2026 - 07/01/2026');

        // 5. Rentang mingguan dengan input array atau kalender tidak valid
        $resArrayWeekly = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'weekly',
            'start_date' => ['2026-01-01'],
            'end_date' => '2026-04-31', // 31 April tidak ada
        ]));
        $resArrayWeekly->assertOk();

        // 6. Bulan & tahun di luar batas normal
        $resInvalidMonthYear = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'monthly',
            'month' => 999,
            'year' => -10,
        ]));
        $resInvalidMonthYear->assertOk();
    }

    public function test_admin_dashboard_sales_statistics_calculation_with_controlled_data(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $p1 = Product::create([
            'sku' => 'QC-TEST-001',
            'name' => 'Barang QC 1',
            'category' => 'Kategori QC',
            'price' => 25000,
            'stock' => 100,
            'is_active' => true,
        ]);

        $p2 = Product::create([
            'sku' => 'QC-TEST-002',
            'name' => 'Barang QC 2',
            'category' => 'Kategori QC',
            'price' => 25000,
            'stock' => 100,
            'is_active' => true,
        ]);

        // Order 1: SELESAI, total 50.000, qty 2
        $order1 = Order::create([
            'order_code' => 'ORD-QC-001',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 50000,
            'transaction_date' => '2026-06-15 10:00:00',
            'paid_at' => '2026-06-15 10:00:00',
        ]);
        $order1->items()->create([
            'product_id' => $p1->id,
            'qty' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
        ]);

        // Order 2: SELESAI, total 75.000, qty 3
        $order2 = Order::create([
            'order_code' => 'ORD-QC-002',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'manual_admin',
            'status' => 'SELESAI',
            'total_amount' => 75000,
            'transaction_date' => '2026-06-15 14:00:00',
            'paid_at' => '2026-06-15 14:00:00',
        ]);
        $order2->items()->create([
            'product_id' => $p2->id,
            'qty' => 3,
            'unit_price' => 25000,
            'subtotal' => 75000,
        ]);

        // Order 3: MENUNGGU_BAYAR (tidak boleh masuk hitungan statistik penjualan)
        $order3 = Order::create([
            'order_code' => 'ORD-QC-003',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'MENUNGGU_BAYAR',
            'total_amount' => 100000,
            'transaction_date' => '2026-06-15 16:00:00',
        ]);
        $order3->items()->create([
            'product_id' => $p1->id,
            'qty' => 4,
            'unit_price' => 25000,
            'subtotal' => 100000,
        ]);

        // Request dashboard harian pada tanggal terkontrol
        $res = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2026-06-15',
        ]));

        $res->assertOk();
        // Total Omzet: 50.000 + 75.000 = 125.000
        $res->assertSee('Rp 125.000');
        // Total Transaksi: 2
        $res->assertSee('2 transaksi selesai');
        // Produk Terjual: 2 + 3 = 5
        $res->assertSee('5');
        // Rata-rata Nilai Transaksi: 125.000 / 2 = 62.500
        $res->assertSee('Rp 62.500');
        // Produk terlaris
        $res->assertSee('Barang QC 2');
        $res->assertSee('Barang QC 1');
    }

    public function test_admin_reports_chart_empty_state_when_no_transactions(): void
    {
        $admin = User::factory()->admin()->create();

        // Periode di masa depan yang tidak memiliki transaksi sama sekali
        $res = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2035-01-01',
        ]));

        $res->assertOk();
        $res->assertViewHas('chartData');

        /** @var array<string, mixed> $chartData */
        $chartData = $res->viewData('chartData');
        $this->assertFalse($chartData['has_data']);
        $this->assertSame('daily', $chartData['period']);
        $this->assertSame('Jam', $chartData['unit_name']);
        $this->assertCount(24, $chartData['series']);

        // Pastikan pesan empty state muncul di antarmuka
        $res->assertSee('Tidak ada transaksi SELESAI pada periode ini.');
    }

    public function test_admin_reports_chart_aggregation_daily_weekly_monthly_yearly_and_status_isolation(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $product = Product::create([
            'sku' => 'CHART-TEST-001',
            'name' => 'Barang Chart Test',
            'category' => 'Kategori Chart',
            'price' => 20000,
            'stock' => 100,
            'is_active' => true,
        ]);

        // 1. Transaksi SELESAI Jam 09:00 (Omzet: 60.000)
        Order::create([
            'order_code' => 'ORD-CHART-001',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 60000,
            'transaction_date' => '2026-08-10 09:30:00',
            'paid_at' => '2026-08-10 09:30:00',
        ]);

        // 2. Transaksi SELESAI Jam 14:00 (Omzet: 40.000)
        Order::create([
            'order_code' => 'ORD-CHART-002',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'manual_admin',
            'status' => 'SELESAI',
            'total_amount' => 40000,
            'transaction_date' => '2026-08-10 14:15:00',
            'paid_at' => '2026-08-10 14:15:00',
        ]);

        // 3. Transaksi SELESAI di hari lain pada minggu yang sama (2026-08-12, Omzet: 100.000)
        Order::create([
            'order_code' => 'ORD-CHART-003',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'import_excel',
            'status' => 'SELESAI',
            'total_amount' => 100000,
            'transaction_date' => '2026-08-12 11:00:00',
            'paid_at' => '2026-08-12 11:00:00',
        ]);

        // 4. Transaksi BATAL Jam 10:00 (200.000) -> TIDAK BOLEH MASUK GRAFIK
        Order::create([
            'order_code' => 'ORD-CHART-004',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'BATAL',
            'total_amount' => 200000,
            'transaction_date' => '2026-08-10 10:00:00',
        ]);

        // 5. Transaksi DIPROSES Jam 11:00 (300.000) -> TIDAK BOLEH MASUK GRAFIK
        Order::create([
            'order_code' => 'ORD-CHART-005',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'DIPROSES',
            'total_amount' => 300000,
            'transaction_date' => '2026-08-10 11:00:00',
        ]);

        // --- UJI PERIODE HARIAN ---
        $resDaily = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-08-10',
        ]));
        $resDaily->assertOk();
        $dailyChart = $resDaily->viewData('chartData');
        $this->assertTrue($dailyChart['has_data']);
        $this->assertSame('Jam', $dailyChart['unit_name']);

        // Cari titik jam 09 dan 14
        $seriesByLabel = collect($dailyChart['series'])->keyBy('short_label');
        $this->assertEquals(60000, $seriesByLabel['09']['omzet']);
        $this->assertEquals(1, $seriesByLabel['09']['count']);
        $this->assertEquals(40000, $seriesByLabel['14']['omzet']);
        $this->assertEquals(1, $seriesByLabel['14']['count']);
        // Jam 10 dan 11 harus 0 karena bukan SELESAI
        $this->assertEquals(0, $seriesByLabel['10']['omzet']);
        $this->assertEquals(0, $seriesByLabel['11']['omzet']);

        // UI Assertion Harian
        $resDaily->assertSee('Grafik Tren Penjualan (Per Jam)');
        $resDaily->assertSee('Omzet (Rp)');
        $resDaily->assertSee('Jumlah Transaksi');

        // --- UJI PERIODE MINGGUAN ---
        $resWeekly = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'weekly',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-16',
        ]));
        $resWeekly->assertOk();
        $weeklyChart = $resWeekly->viewData('chartData');
        $this->assertTrue($weeklyChart['has_data']);
        $this->assertSame('Hari', $weeklyChart['unit_name']);
        $this->assertCount(7, $weeklyChart['series']);
        $totalWeeklyChartOmzet = collect($weeklyChart['series'])->sum('omzet');
        $this->assertEquals(200000, $totalWeeklyChartOmzet); // 60k + 40k + 100k

        // --- UJI PERIODE BULANAN ---
        $resMonthly = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'monthly',
            'month' => '8',
            'year' => '2026',
        ]));
        $resMonthly->assertOk();
        $monthlyChart = $resMonthly->viewData('chartData');
        $this->assertTrue($monthlyChart['has_data']);
        $this->assertSame('Tanggal', $monthlyChart['unit_name']);
        $this->assertCount(31, $monthlyChart['series']); // Agustus ada 31 hari
        $totalMonthlyChartOmzet = collect($monthlyChart['series'])->sum('omzet');
        $this->assertEquals(200000, $totalMonthlyChartOmzet);

        // --- UJI PERIODE TAHUNAN ---
        $resYearly = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'yearly',
            'year' => '2026',
        ]));
        $resYearly->assertOk();
        $yearlyChart = $resYearly->viewData('chartData');
        $this->assertTrue($yearlyChart['has_data']);
        $this->assertSame('Bulan', $yearlyChart['unit_name']);
        $this->assertCount(12, $yearlyChart['series']);
        $totalYearlyChartOmzet = collect($yearlyChart['series'])->sum('omzet');
        $this->assertEquals(200000, $totalYearlyChartOmzet);
        $totalYearlyChartCount = collect($yearlyChart['series'])->sum('count');
        $this->assertEquals(3, $totalYearlyChartCount);
    }
}

