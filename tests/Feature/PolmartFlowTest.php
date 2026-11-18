<?php

namespace Tests\Feature;

use App\Imports\OrderExcelChunkImport;
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
        $response->assertSee('Katalog Belanja Anggota');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
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

    public function test_admin_can_store_and_update_product_cost_price(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Simpan produk dengan cost_price
        $resStore = $this->actingAs($admin)->post(route('admin.products.store'), [
            'sku' => 'TEST-HPP-001',
            'name' => 'Barang HPP Test',
            'category' => 'Testing',
            'cost_price' => 15000,
            'price' => 20000,
            'stock' => 50,
        ]);

        $resStore->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-HPP-001',
            'cost_price' => 15000.00,
            'price' => 20000.00,
        ]);

        $product = Product::where('sku', 'TEST-HPP-001')->firstOrFail();

        // 2. Perbarui cost_price produk
        $resUpdate = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'sku' => 'TEST-HPP-001',
            'name' => 'Barang HPP Test Updated',
            'category' => 'Testing',
            'cost_price' => 17500,
            'price' => 23000,
            'is_active' => true,
        ]);

        $resUpdate->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'cost_price' => 17500.00,
            'price' => 23000.00,
        ]);
    }

    public function test_validation_rejects_negative_cost_price(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Store dengan cost_price negatif
        $resStore = $this->actingAs($admin)->post(route('admin.products.store'), [
            'sku' => 'TEST-NEG-001',
            'name' => 'Barang Negatif',
            'category' => 'Testing',
            'cost_price' => -5000,
            'price' => 20000,
            'stock' => 10,
        ]);
        $resStore->assertSessionHasErrors(['cost_price']);
        $this->assertDatabaseMissing('products', ['sku' => 'TEST-NEG-001']);

        // 2. Update dengan cost_price negatif
        $product = Product::create([
            'sku' => 'TEST-NEG-002',
            'name' => 'Barang Valid Awal',
            'category' => 'Testing',
            'cost_price' => 10000,
            'price' => 15000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $resUpdate = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'sku' => 'TEST-NEG-002',
            'name' => 'Barang Valid Awal',
            'category' => 'Testing',
            'cost_price' => -100,
            'price' => 15000,
            'is_active' => true,
        ]);
        $resUpdate->assertSessionHasErrors(['cost_price']);
        $this->assertEquals(10000.00, $product->fresh()->cost_price);
    }

    public function test_member_checkout_snapshots_cost_price_and_subtotal_cost(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $product = Product::create([
            'sku' => 'CO-HPP-001',
            'name' => 'Barang Checkout HPP',
            'category' => 'Testing',
            'cost_price' => 12000,
            'price' => 18000,
            'stock' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($member)
            ->withSession(['polmart_cart' => [$product->id => 3]])
            ->post(route('anggota.checkout.store'))
            ->assertRedirect();

        $order = Order::where('user_id', $member->id)->latest()->firstOrFail();
        $this->assertCount(1, $order->items);

        $item = $order->items->first();
        $this->assertEquals($product->id, $item->product_id);
        $this->assertEquals(3, $item->qty);
        $this->assertEquals(18000.00, $item->unit_price);
        $this->assertEquals(54000.00, $item->subtotal);
        // Verifikasi Snapshot HPP
        $this->assertEquals(12000.00, $item->cost_price);
        $this->assertEquals(36000.00, $item->subtotal_cost); // 3 * 12000
    }

    public function test_admin_manual_order_snapshots_cost_price_and_subtotal_cost(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create([
            'sku' => 'POS-HPP-001',
            'name' => 'Barang Kasir HPP',
            'category' => 'Testing',
            'cost_price' => 7000,
            'price' => 10000,
            'stock' => 30,
            'is_active' => true,
        ]);

        $res = $this->actingAs($admin)->post(route('admin.manual-orders.store'), [
            'customer_name' => 'Pembeli Walk-in QC',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 4,
                ],
            ],
        ]);

        $res->assertRedirect();

        $order = Order::where('customer_name', 'Pembeli Walk-in QC')->latest()->firstOrFail();
        $this->assertCount(1, $order->items);

        $item = $order->items->first();
        $this->assertEquals(4, $item->qty);
        $this->assertEquals(10000.00, $item->unit_price);
        $this->assertEquals(40000.00, $item->subtotal);
        // Verifikasi Snapshot HPP
        $this->assertEquals(7000.00, $item->cost_price);
        $this->assertEquals(28000.00, $item->subtotal_cost); // 4 * 7000
    }

    public function test_excel_import_without_hpp_stores_zero_cost_price(): void
    {
        $product = Product::create([
            'sku' => 'IMP-NOHPP-01',
            'name' => 'Barang Impor Tanpa HPP',
            'category' => 'Testing',
            'cost_price' => 8500, // Master produk punya HPP, tapi impor historis TIDAK boleh mengambilnya
            'price' => 12000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $importer = new OrderExcelChunkImport(commit: true);
        $importer->collection(collect([
            collect([
                'tanggal_transaksi' => '2026-09-10 10:00:00',
                'kode_pesanan_lama' => 'OLD-NOHPP-001',
                'nama_pemesan' => 'Customer Historis A',
                'kode_barang' => 'IMP-NOHPP-01',
                'nama_barang' => 'Barang Impor Tanpa HPP',
                'qty' => 5,
                'harga_satuan' => 12000,
            ]),
        ]));

        $order = Order::where('order_code', 'OLD-NOHPP-001')->firstOrFail();
        $item = $order->items->first();

        $this->assertNotNull($item);
        $this->assertEquals(12000.00, $item->unit_price);
        $this->assertEquals(60000.00, $item->subtotal);
        // Harus bernilai 0 karena tidak ada kolom HPP di Excel (tidak mengambil cost_price aktif produk)
        $this->assertEquals(0.00, $item->cost_price);
        $this->assertEquals(0.00, $item->subtotal_cost);
    }

    public function test_excel_import_with_hpp_stores_cost_price_from_file(): void
    {
        $product = Product::create([
            'sku' => 'IMP-WITHHPP-01',
            'name' => 'Barang Impor Dengan HPP',
            'category' => 'Testing',
            'cost_price' => 5000, // Master produk punya HPP 5.000
            'price' => 10000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $importer = new OrderExcelChunkImport(commit: true);
        $importer->collection(collect([
            collect([
                'tanggal_transaksi' => '2026-09-15 14:00:00',
                'kode_pesanan_lama' => 'OLD-WITHHPP-001',
                'nama_pemesan' => 'Customer Historis B',
                'kode_barang' => 'IMP-WITHHPP-01',
                'nama_barang' => 'Barang Impor Dengan HPP',
                'qty' => 4,
                'harga_satuan' => 10000,
                'cost_price' => 7500, // Nilai HPP eksplisit dari file Excel
            ]),
        ]));

        $order = Order::where('order_code', 'OLD-WITHHPP-001')->firstOrFail();
        $item = $order->items->first();

        $this->assertNotNull($item);
        $this->assertEquals(10000.00, $item->unit_price);
        $this->assertEquals(40000.00, $item->subtotal);
        // Menggunakan nilai HPP dari file Excel (7.500), bukan dari master produk (5.000)
        $this->assertEquals(7500.00, $item->cost_price);
        $this->assertEquals(30000.00, $item->subtotal_cost); // 4 * 7500
    }

    public function test_updating_product_cost_price_does_not_mutate_existing_order_items_snapshot(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        // 1. Buat produk awal dengan harga modal 10.000 dan harga jual 15.000
        $product = Product::create([
            'sku' => 'IMMUTABLE-001',
            'name' => 'Barang Uji Immutabilitas',
            'category' => 'Testing',
            'cost_price' => 10000,
            'price' => 15000,
            'stock' => 50,
            'is_active' => true,
        ]);

        // 2. Member melakukan transaksi 2 pcs saat harga modal masih 10.000
        $this->actingAs($member)
            ->withSession(['polmart_cart' => [$product->id => 2]])
            ->post(route('anggota.checkout.store'));

        $order = Order::where('user_id', $member->id)->latest()->firstOrFail();
        $item = $order->items->first();

        $this->assertEquals(10000.00, $item->cost_price);
        $this->assertEquals(20000.00, $item->subtotal_cost);

        // 3. Admin kemudian menaikkan harga modal produk menjadi 14.000
        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'sku' => 'IMMUTABLE-001',
            'name' => 'Barang Uji Immutabilitas',
            'category' => 'Testing',
            'cost_price' => 14000,
            'price' => 18000,
            'is_active' => true,
        ])->assertRedirect();

        $this->assertEquals(14000.00, $product->fresh()->cost_price);

        // 4. Pastikan snapshot pada pesanan lama TETAP 10.000 dan TIDAK TERUBAH menjadi 14.000
        $itemRefreshed = $item->fresh();
        $this->assertEquals(10000.00, $itemRefreshed->cost_price);
        $this->assertEquals(20000.00, $itemRefreshed->subtotal_cost);
    }

    public function test_admin_reports_calculates_omzet_hpp_and_gross_profit_only_for_completed_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        $p1 = Product::create([
            'sku' => 'FIN-001',
            'name' => 'Produk Finansial 1',
            'category' => 'Testing',
            'cost_price' => 12000,
            'price' => 20000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $p2 = Product::create([
            'sku' => 'FIN-002',
            'name' => 'Produk Finansial 2',
            'category' => 'Testing',
            'cost_price' => 35000,
            'price' => 50000,
            'stock' => 50,
            'is_active' => true,
        ]);

        // 1. Order 1: SELESAI pada 2026-07-15 10:00
        // Item 1: 2 pcs * 20.000 = 40.000 (HPP: 2 * 12.000 = 24.000)
        // Item 2: 1 pcs * 50.000 = 50.000 (HPP: 1 * 35.000 = 35.000)
        // Total Omzet: 90.000, Total HPP: 59.000, Laba Kotor: 31.000
        $order1 = Order::create([
            'order_code' => 'ORD-FIN-001',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 90000,
            'transaction_date' => '2026-07-15 10:00:00',
            'paid_at' => '2026-07-15 10:00:00',
        ]);
        $order1->items()->create([
            'product_id' => $p1->id,
            'qty' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
            'cost_price' => 12000,
            'subtotal_cost' => 24000,
        ]);
        $order1->items()->create([
            'product_id' => $p2->id,
            'qty' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
            'cost_price' => 35000,
            'subtotal_cost' => 35000,
        ]);

        // 2. Transaksi NON-SELESAI pada tanggal yang sama: TIDAK BOLEH IKUT DIHITUNG
        // a. SIAP_DIAMBIL
        $order2 = Order::create([
            'order_code' => 'ORD-FIN-002',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SIAP_DIAMBIL',
            'total_amount' => 100000,
            'transaction_date' => '2026-07-15 11:00:00',
        ]);
        $order2->items()->create([
            'product_id' => $p2->id,
            'qty' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
            'cost_price' => 35000,
            'subtotal_cost' => 70000,
        ]);

        // b. MENUNGGU_BAYAR
        $order3 = Order::create([
            'order_code' => 'ORD-FIN-003',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'MENUNGGU_BAYAR',
            'total_amount' => 80000,
            'transaction_date' => '2026-07-15 12:00:00',
        ]);
        $order3->items()->create([
            'product_id' => $p1->id,
            'qty' => 4,
            'unit_price' => 20000,
            'subtotal' => 80000,
            'cost_price' => 12000,
            'subtotal_cost' => 48000,
        ]);

        // c. BATAL
        $order4 = Order::create([
            'order_code' => 'ORD-FIN-004',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'BATAL',
            'total_amount' => 60000,
            'transaction_date' => '2026-07-15 13:00:00',
        ]);
        $order4->items()->create([
            'product_id' => $p1->id,
            'qty' => 3,
            'unit_price' => 20000,
            'subtotal' => 60000,
            'cost_price' => 12000,
            'subtotal_cost' => 36000,
        ]);

        // Request laporan harian 2026-07-15
        $res = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-07-15',
        ]));

        $res->assertOk();
        $summary = $res->viewData('summary');

        // Verifikasi metrik: hanya transaksi SELESAI yang dihitung
        $this->assertEquals(90000.0, $summary['total_omzet']);
        $this->assertEquals(59000.0, $summary['total_hpp']);
        $this->assertEquals(31000.0, $summary['gross_profit']);
        $this->assertEquals(1, $summary['total_transactions']);
        $this->assertEquals(3, $summary['total_items_sold']);
        $this->assertFalse($summary['has_unrecorded_hpp']);
        $this->assertEquals(0, $summary['unrecorded_orders_count']);
        $this->assertEquals(0, $summary['unrecorded_items_count']);
        $this->assertEquals(100.0, $summary['completeness_pct']);

        // Verifikasi tampilan blade
        $res->assertSee('Ringkasan Finansial: Omzet, HPP &amp; Laba Kotor', false);
        $res->assertSee('Rp 90.000');
        $res->assertSee('Rp 59.000');
        $res->assertSee('Rp 31.000');
        $res->assertSee('Data HPP Lengkap (100%)');
    }

    public function test_admin_reports_flags_unrecorded_hpp_and_marks_gross_profit_as_provisional(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        $product = Product::create([
            'sku' => 'HIST-NOHPP-001',
            'name' => 'Barang Historis Tanpa HPP',
            'category' => 'Testing',
            'cost_price' => 0,
            'price' => 25000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Transaksi SELESAI dengan HPP bernilai 0 (belum tercatat)
        $order = Order::create([
            'order_code' => 'ORD-NOHPP-001',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'import_excel',
            'status' => 'SELESAI',
            'total_amount' => 50000,
            'transaction_date' => '2026-07-20 10:00:00',
            'paid_at' => '2026-07-20 10:00:00',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
            'cost_price' => 0,
            'subtotal_cost' => 0,
        ]);

        $res = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-07-20',
        ]));

        $res->assertOk();
        $summary = $res->viewData('summary');

        $this->assertEquals(50000.0, $summary['total_omzet']);
        $this->assertEquals(0.0, $summary['total_hpp']);
        $this->assertEquals(50000.0, $summary['gross_profit']);
        $this->assertTrue($summary['has_unrecorded_hpp']);
        $this->assertEquals(1, $summary['unrecorded_orders_count']);
        $this->assertEquals(1, $summary['unrecorded_items_count']);
        $this->assertEquals(0.0, $summary['completeness_pct']);

        // Verifikasi bahwa status ditandai provisional pada tampilan Blade
        $res->assertSee('Laba Kotor Provisional');
        $res->assertSee('Pemberitahuan Data HPP Belum Lengkap:');
        $res->assertSee('1 transaksi');
        $res->assertSee('1 item');
    }

    public function test_admin_reports_period_filters_yield_consistent_financial_aggregations(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        $product = Product::create([
            'sku' => 'FILTER-FIN-01',
            'name' => 'Produk Periode Filter',
            'category' => 'Testing',
            'cost_price' => 15000,
            'price' => 25000,
            'stock' => 100,
            'is_active' => true,
        ]);

        // Order 1: 2026-05-11 09:00 -> 4 pcs = 100.000 omzet, 60.000 HPP, 40.000 Laba Kotor
        $order1 = Order::create([
            'order_code' => 'ORD-PER-001',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 100000,
            'transaction_date' => '2026-05-11 09:00:00',
            'paid_at' => '2026-05-11 09:00:00',
        ]);
        $order1->items()->create([
            'product_id' => $product->id,
            'qty' => 4,
            'unit_price' => 25000,
            'subtotal' => 100000,
            'cost_price' => 15000,
            'subtotal_cost' => 60000,
        ]);

        // Order 2: 2026-05-13 14:00 -> 6 pcs = 150.000 omzet, 90.000 HPP, 60.000 Laba Kotor
        $order2 = Order::create([
            'order_code' => 'ORD-PER-002',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'manual_admin',
            'status' => 'SELESAI',
            'total_amount' => 150000,
            'transaction_date' => '2026-05-13 14:00:00',
            'paid_at' => '2026-05-13 14:00:00',
        ]);
        $order2->items()->create([
            'product_id' => $product->id,
            'qty' => 6,
            'unit_price' => 25000,
            'subtotal' => 150000,
            'cost_price' => 15000,
            'subtotal_cost' => 90000,
        ]);

        // 1. Filter Harian: 2026-05-11 (Hanya Order 1)
        $resDaily = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-05-11',
        ]));
        $sumDaily = $resDaily->viewData('summary');
        $this->assertEquals(100000.0, $sumDaily['total_omzet']);
        $this->assertEquals(60000.0, $sumDaily['total_hpp']);
        $this->assertEquals(40000.0, $sumDaily['gross_profit']);
        $this->assertEquals(1, $sumDaily['total_transactions']);

        // 2. Filter Pekanan: 2026-05-11 s/d 2026-05-17 (Mencakup Order 1 & 2)
        $resWeekly = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'weekly',
            'start_date' => '2026-05-11',
            'end_date' => '2026-05-17',
        ]));
        $sumWeekly = $resWeekly->viewData('summary');
        $this->assertEquals(250000.0, $sumWeekly['total_omzet']);
        $this->assertEquals(150000.0, $sumWeekly['total_hpp']);
        $this->assertEquals(100000.0, $sumWeekly['gross_profit']);
        $this->assertEquals(2, $sumWeekly['total_transactions']);

        // 3. Filter Bulanan: Mei 2026 (Mencakup Order 1 & 2)
        $resMonthly = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'monthly',
            'month' => 5,
            'year' => 2026,
        ]));
        $sumMonthly = $resMonthly->viewData('summary');
        $this->assertEquals(250000.0, $sumMonthly['total_omzet']);
        $this->assertEquals(150000.0, $sumMonthly['total_hpp']);
        $this->assertEquals(100000.0, $sumMonthly['gross_profit']);
        $this->assertEquals(2, $sumMonthly['total_transactions']);

        // 4. Filter Tahunan: 2026 (Mencakup Order 1 & 2)
        $resYearly = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'yearly',
            'year' => 2026,
        ]));
        $sumYearly = $resYearly->viewData('summary');
        $this->assertEquals(250000.0, $sumYearly['total_omzet']);
        $this->assertEquals(150000.0, $sumYearly['total_hpp']);
        $this->assertEquals(100000.0, $sumYearly['gross_profit']);
        $this->assertEquals(2, $sumYearly['total_transactions']);
    }

    public function test_admin_reports_excel_export_includes_consistent_financial_summary(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Verifikasi ekspor sheet dengan HPP lengkap
        $exportComplete = new \App\Exports\PeriodicReportExport(collect(), collect(), [
            'total_omzet' => 200000.0,
            'total_transactions' => 2,
            'total_items_sold' => 8,
            'avg_transaction' => 100000.0,
            'total_hpp' => 130000.0,
            'gross_profit' => 70000.0,
            'has_unrecorded_hpp' => false,
            'unrecorded_items_count' => 0,
            'unrecorded_orders_count' => 0,
        ], 'Bulanan: Mei 2026', []);

        $completeSheetData = $exportComplete->sheets()[0]->array();
        $this->assertContains(['Total Omzet Penjualan (Rp)', 'Rp 200.000'], $completeSheetData);
        $this->assertContains(['Total HPP Tercatat (Rp)', 'Rp 130.000'], $completeSheetData);
        $this->assertContains(['Laba Kotor (Rp)', 'Rp 70.000'], $completeSheetData);
        $this->assertContains(['Status Kelengkapan HPP', 'Lengkap (100% item memiliki catatan HPP)'], $completeSheetData);

        // 2. Verifikasi ekspor sheet dengan HPP provisional
        $exportProvisional = new \App\Exports\PeriodicReportExport(collect(), collect(), [
            'total_omzet' => 100000.0,
            'total_transactions' => 1,
            'total_items_sold' => 4,
            'avg_transaction' => 100000.0,
            'total_hpp' => 0.0,
            'gross_profit' => 100000.0,
            'has_unrecorded_hpp' => true,
            'unrecorded_items_count' => 1,
            'unrecorded_orders_count' => 1,
        ], 'Harian: 15 Mei 2026', []);

        $provisionalSheetData = $exportProvisional->sheets()[0]->array();
        $this->assertContains(['Total HPP Tercatat (Rp)', 'Rp 0'], $provisionalSheetData);
        $this->assertContains(['Laba Kotor (Rp)', 'Rp 100.000'], $provisionalSheetData);
        $this->assertContains(['Status Kelengkapan HPP', 'Provisional (1 pesanan / 1 item belum tercatat HPP)'], $provisionalSheetData);

        // 3. Verifikasi HTTP Download route admin.reports.export
        $resExport = $this->actingAs($admin)->get(route('admin.reports.export', [
            'period' => 'monthly',
            'month' => 5,
            'year' => 2026,
        ]));
        $resExport->assertOk();
        $this->assertStringContainsString('attachment;', $resExport->headers->get('content-disposition'));
        $this->assertStringContainsString('Laporan_Penjualan_Bulanan_2026-05.xlsx', $resExport->headers->get('content-disposition'));
    }

    public function test_admin_dashboard_sales_analytics_calculates_omzet_hpp_and_profit_only_for_completed_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        $p1 = Product::create([
            'sku' => 'DASH-FIN-01',
            'name' => 'Produk Dashboard Finansial 1',
            'category' => 'Testing',
            'cost_price' => 15000,
            'price' => 25000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $p2 = Product::create([
            'sku' => 'DASH-FIN-02',
            'name' => 'Produk Dashboard Finansial 2',
            'category' => 'Testing',
            'cost_price' => 40000,
            'price' => 60000,
            'stock' => 50,
            'is_active' => true,
        ]);

        // 1. Order SELESAI pada 2026-06-20 10:00:
        // Item 1: 2 pcs * 25.000 = 50.000 (HPP: 2 * 15.000 = 30.000)
        // Item 2: 1 pcs * 60.000 = 60.000 (HPP: 1 * 40.000 = 40.000)
        // Total Omzet = 110.000, Total HPP = 70.000, Gross Profit = 40.000
        $order1 = Order::create([
            'order_code' => 'ORD-DASH-FIN-01',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 110000,
            'transaction_date' => '2026-06-20 10:00:00',
            'paid_at' => '2026-06-20 10:00:00',
        ]);
        $order1->items()->create([
            'product_id' => $p1->id,
            'qty' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
            'cost_price' => 15000,
            'subtotal_cost' => 30000,
        ]);
        $order1->items()->create([
            'product_id' => $p2->id,
            'qty' => 1,
            'unit_price' => 60000,
            'subtotal' => 60000,
            'cost_price' => 40000,
            'subtotal_cost' => 40000,
        ]);

        // 2. Transaksi NON-SELESAI pada tanggal yang sama: TIDAK BOLEH IKUT DIHITUNG
        // a. SIAP_DIAMBIL (Omzet: 50.000, HPP: 30.000)
        $order2 = Order::create([
            'order_code' => 'ORD-DASH-FIN-02',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SIAP_DIAMBIL',
            'total_amount' => 50000,
            'transaction_date' => '2026-06-20 11:00:00',
        ]);
        $order2->items()->create([
            'product_id' => $p1->id,
            'qty' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
            'cost_price' => 15000,
            'subtotal_cost' => 30000,
        ]);

        // b. MENUNGGU_BAYAR (Omzet: 60.000, HPP: 40.000)
        $order3 = Order::create([
            'order_code' => 'ORD-DASH-FIN-03',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'MENUNGGU_BAYAR',
            'total_amount' => 60000,
            'transaction_date' => '2026-06-20 12:00:00',
        ]);
        $order3->items()->create([
            'product_id' => $p2->id,
            'qty' => 1,
            'unit_price' => 60000,
            'subtotal' => 60000,
            'cost_price' => 40000,
            'subtotal_cost' => 40000,
        ]);

        // c. BATAL (Omzet: 25.000, HPP: 15.000)
        $order4 = Order::create([
            'order_code' => 'ORD-DASH-FIN-04',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'BATAL',
            'total_amount' => 25000,
            'transaction_date' => '2026-06-20 13:00:00',
        ]);
        $order4->items()->create([
            'product_id' => $p1->id,
            'qty' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
            'cost_price' => 15000,
            'subtotal_cost' => 15000,
        ]);

        // Request dashboard admin harian 2026-06-20
        $res = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2026-06-20',
        ]));

        $res->assertOk();
        $summary = $res->viewData('periodSummary');

        // Verifikasi periodSummary
        $this->assertEquals(110000.0, $summary['total_omzet']);
        $this->assertEquals(70000.0, $summary['total_hpp']);
        $this->assertEquals(40000.0, $summary['gross_profit']);
        $this->assertEquals(1, $summary['total_transactions']);
        $this->assertEquals(3, $summary['total_items_sold']);
        $this->assertFalse($summary['has_unrecorded_hpp']);
        $this->assertEquals(0, $summary['unrecorded_orders_count']);
        $this->assertEquals(0, $summary['unrecorded_items_count']);
        $this->assertEquals(100.0, $summary['completeness_pct']);

        // Verifikasi elemen antarmuka Blade pada dashboard
        $res->assertSee('Ringkasan Finansial (HPP &amp; Laba Kotor)', false);
        $res->assertSee('Rp 110.000');
        $res->assertSee('Rp 70.000');
        $res->assertSee('Rp 40.000');
        $res->assertSee('Data HPP Lengkap (100%)');

        // Pastikan empat kartu operasional lama tetap ada dan akurat
        $res->assertSee('Total Omzet');
        $res->assertSee('Jumlah Transaksi');
        $res->assertSee('Produk Terjual');
        $res->assertSee('Rata-rata Nilai Transaksi');
    }

    public function test_admin_dashboard_sales_analytics_flags_unrecorded_hpp_as_provisional(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        $product = Product::create([
            'sku' => 'DASH-NOHPP-01',
            'name' => 'Produk Tanpa HPP Dashboard',
            'category' => 'Testing',
            'cost_price' => 0,
            'price' => 30000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-DASH-NOHPP-01',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'import_excel',
            'status' => 'SELESAI',
            'total_amount' => 60000,
            'transaction_date' => '2026-06-25 10:00:00',
            'paid_at' => '2026-06-25 10:00:00',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'unit_price' => 30000,
            'subtotal' => 60000,
            'cost_price' => 0,
            'subtotal_cost' => 0,
        ]);

        $res = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2026-06-25',
        ]));

        $res->assertOk();
        $summary = $res->viewData('periodSummary');

        $this->assertEquals(60000.0, $summary['total_omzet']);
        $this->assertEquals(0.0, $summary['total_hpp']);
        $this->assertEquals(60000.0, $summary['gross_profit']);
        $this->assertTrue($summary['has_unrecorded_hpp']);
        $this->assertEquals(1, $summary['unrecorded_orders_count']);
        $this->assertEquals(1, $summary['unrecorded_items_count']);
        $this->assertEquals(0.0, $summary['completeness_pct']);

        // Verifikasi badge dan peringatan provisional pada dashboard
        $res->assertSee('Laba Kotor Provisional');
        $res->assertSee('Perhatian Data HPP:');
        $res->assertSee('1 transaksi');
        $res->assertSee('1 item');
    }

    public function test_admin_dashboard_period_filters_strictly_isolate_hpp_and_financial_aggregations(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        $product = Product::create([
            'sku' => 'DASH-ISOL-01',
            'name' => 'Produk Isolasi Periode Dashboard',
            'category' => 'Testing',
            'cost_price' => 12000,
            'price' => 20000,
            'stock' => 100,
            'is_active' => true,
        ]);

        // Order 1 pada 2026-04-10 09:00: 6 pcs = 120.000 omzet, 72.000 HPP, 48.000 profit
        $order1 = Order::create([
            'order_code' => 'ORD-ISOL-01',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 120000,
            'transaction_date' => '2026-04-10 09:00:00',
            'paid_at' => '2026-04-10 09:00:00',
        ]);
        $order1->items()->create([
            'product_id' => $product->id,
            'qty' => 6,
            'unit_price' => 20000,
            'subtotal' => 120000,
            'cost_price' => 12000,
            'subtotal_cost' => 72000,
        ]);

        // Order 2 pada 2026-04-14 14:00: 9 pcs = 180.000 omzet, 108.000 HPP, 72.000 profit
        $order2 = Order::create([
            'order_code' => 'ORD-ISOL-02',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'manual_admin',
            'status' => 'SELESAI',
            'total_amount' => 180000,
            'transaction_date' => '2026-04-14 14:00:00',
            'paid_at' => '2026-04-14 14:00:00',
        ]);
        $order2->items()->create([
            'product_id' => $product->id,
            'qty' => 9,
            'unit_price' => 20000,
            'subtotal' => 180000,
            'cost_price' => 12000,
            'subtotal_cost' => 108000,
        ]);

        // 1. Filter Harian: 2026-04-10 (hanya Order 1)
        $resDaily = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2026-04-10',
        ]));
        $sumDaily = $resDaily->viewData('periodSummary');
        $this->assertEquals(120000.0, $sumDaily['total_omzet']);
        $this->assertEquals(72000.0, $sumDaily['total_hpp']);
        $this->assertEquals(48000.0, $sumDaily['gross_profit']);
        $this->assertEquals(1, $sumDaily['total_transactions']);

        // 2. Filter Pekanan: 2026-04-10 s/d 2026-04-16 (mencakup Order 1 dan 2)
        $resWeekly = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'weekly',
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-16',
        ]));
        $sumWeekly = $resWeekly->viewData('periodSummary');
        $this->assertEquals(300000.0, $sumWeekly['total_omzet']);
        $this->assertEquals(180000.0, $sumWeekly['total_hpp']);
        $this->assertEquals(120000.0, $sumWeekly['gross_profit']);
        $this->assertEquals(2, $sumWeekly['total_transactions']);

        // 3. Filter Bulanan: April 2026 (mencakup Order 1 dan 2)
        $resMonthly = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'monthly',
            'month' => 4,
            'year' => 2026,
        ]));
        $sumMonthly = $resMonthly->viewData('periodSummary');
        $this->assertEquals(300000.0, $sumMonthly['total_omzet']);
        $this->assertEquals(180000.0, $sumMonthly['total_hpp']);
        $this->assertEquals(120000.0, $sumMonthly['gross_profit']);
        $this->assertEquals(2, $sumMonthly['total_transactions']);

        // 4. Filter Tahunan: 2026 (mencakup Order 1 dan 2)
        $resYearly = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'yearly',
            'year' => 2026,
        ]));
        $sumYearly = $resYearly->viewData('periodSummary');
        $this->assertEquals(300000.0, $sumYearly['total_omzet']);
        $this->assertEquals(180000.0, $sumYearly['total_hpp']);
        $this->assertEquals(120000.0, $sumYearly['gross_profit']);
        $this->assertEquals(2, $sumYearly['total_transactions']);

        // 5. Penanganan data kosong pada tanggal di masa depan
        $resEmpty = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2035-01-01',
        ]));
        $sumEmpty = $resEmpty->viewData('periodSummary');
        $this->assertEquals(0.0, $sumEmpty['total_omzet']);
        $this->assertEquals(0.0, $sumEmpty['total_hpp']);
        $this->assertEquals(0.0, $sumEmpty['gross_profit']);
        $this->assertEquals(0, $sumEmpty['total_transactions']);
        $this->assertFalse($sumEmpty['has_unrecorded_hpp']);
        $this->assertNull($sumEmpty['completeness_pct']);
        $resEmpty->assertSee('—');
        $resEmpty->assertDontSee('Data HPP Lengkap (100%)');
    }

    public function test_profitability_analysis_gpm_and_markup_calculation_and_zero_division_safety(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        // Case 1: Transaksi normal SELESAI
        // Product: price 200.000, cost 150.000
        $product = Product::create([
            'sku' => 'PROFIT-CALC-01',
            'name' => 'Produk Profit Valid',
            'category' => 'Testing',
            'cost_price' => 150000,
            'price' => 200000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-PROFIT-01',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 200000,
            'transaction_date' => '2026-07-15 10:00:00',
            'paid_at' => '2026-07-15 10:00:00',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 200000,
            'subtotal' => 200000,
            'cost_price' => 150000,
            'subtotal_cost' => 150000,
        ]);

        // Verifikasi pada route admin.reports.index
        $resReport = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-07-15',
        ]));
        $resReport->assertOk();
        $reportSummary = $resReport->viewData('summary');

        // Omzet: 200.000, HPP: 150.000, Laba Kotor: 50.000
        // GPM = (50.000 / 200.000) * 100 = 25.0%
        // Markup = (50.000 / 150.000) * 100 = 33.3%
        $this->assertEquals(200000.0, $reportSummary['total_omzet']);
        $this->assertEquals(150000.0, $reportSummary['total_hpp']);
        $this->assertEquals(50000.0, $reportSummary['gross_profit']);
        $this->assertEquals(25.0, $reportSummary['gpm']);
        $this->assertEquals(33.3, $reportSummary['markup']);
        $resReport->assertSee('25%');
        $resReport->assertSee('33.3%');
        $resReport->assertSee('Gross Profit Margin');
        $resReport->assertSee('Markup Penjualan');

        // Verifikasi pada route admin.dashboard
        $resDashboard = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2026-07-15',
        ]));
        $resDashboard->assertOk();
        $dashSummary = $resDashboard->viewData('periodSummary');
        $this->assertEquals(25.0, $dashSummary['gpm']);
        $this->assertEquals(33.3, $dashSummary['markup']);
        $resDashboard->assertSee('25%');
        $resDashboard->assertSee('33.3%');

        // Case 2: Zero division safety (tanggal kosong / tanpa transaksi)
        $resEmpty = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2030-01-01',
        ]));
        $resEmpty->assertOk();
        $emptySummary = $resEmpty->viewData('summary');
        $this->assertNull($emptySummary['gpm']);
        $this->assertNull($emptySummary['markup']);

        $resEmptyDash = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2030-01-01',
        ]));
        $resEmptyDash->assertOk();
        $emptyDashSummary = $resEmptyDash->viewData('periodSummary');
        $this->assertNull($emptyDashSummary['gpm']);
        $this->assertNull($emptyDashSummary['markup']);
    }

    public function test_profitability_analysis_flags_provisional_when_unrecorded_hpp_exists(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        $product = Product::create([
            'sku' => 'PROFIT-PROV-01',
            'name' => 'Produk Impor Tanpa HPP',
            'category' => 'Testing',
            'cost_price' => 0,
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-PROV-01',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'import_excel',
            'status' => 'SELESAI',
            'total_amount' => 100000,
            'transaction_date' => '2026-07-16 10:00:00',
            'paid_at' => '2026-07-16 10:00:00',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
            'cost_price' => 0,
            'subtotal_cost' => 0,
        ]);

        // Verifikasi pada laporan penjualan
        $res = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-07-16',
        ]));
        $res->assertOk();
        $summary = $res->viewData('summary');
        $this->assertTrue($summary['has_unrecorded_hpp']);
        $this->assertEquals(100.0, $summary['gpm']);
        // Markup HPP 0 -> null
        $this->assertNull($summary['markup']);
        $res->assertSee('*Provisional (Laba / Omzet)');

        // Verifikasi pada SummaryReportSheet
        $sheet = new \App\Exports\Sheets\SummaryReportSheet($summary, 'Harian: 16 Juli 2026', []);
        $sheetRows = $sheet->array();
        $this->assertContains(['Gross Profit Margin (GPM)', '100% (Provisional)'], $sheetRows);
        $this->assertContains(['Markup Penjualan (%)', '—'], $sheetRows);
    }

    public function test_profitable_products_ranking_orders_by_profit_not_quantity_and_excludes_non_completed(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        // Product High Volume, Low Profit: 50 pcs @ Rp 2.000, cost 1.800.
        // Omzet: 100.000, Cost: 90.000, Profit: 10.000
        $productVol = Product::create([
            'sku' => 'PROFIT-VOL-01',
            'name' => 'Barang Volume Tinggi Laba Tipis',
            'category' => 'Snack',
            'cost_price' => 1800,
            'price' => 2000,
            'stock' => 100,
            'is_active' => true,
        ]);

        // Product Low Volume, High Profit: 2 pcs @ Rp 60.000, cost 20.000.
        // Omzet: 120.000, Cost: 40.000, Profit: 80.000
        $productMargin = Product::create([
            'sku' => 'PROFIT-MAR-01',
            'name' => 'Barang Volume Kecil Laba Besar',
            'category' => 'Premium',
            'cost_price' => 20000,
            'price' => 60000,
            'stock' => 20,
            'is_active' => true,
        ]);

        // Product Non-SELESAI (SIAP_DIAMBIL): Should be ignored entirely!
        $productPending = Product::create([
            'sku' => 'PROFIT-PEND-01',
            'name' => 'Barang Belum Selesai',
            'category' => 'Special',
            'cost_price' => 10000,
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Order 1: SELESAI
        $order1 = Order::create([
            'order_code' => 'ORD-RANK-01',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 220000,
            'transaction_date' => '2026-07-20 14:00:00',
            'paid_at' => '2026-07-20 14:00:00',
        ]);
        $order1->items()->create([
            'product_id' => $productVol->id,
            'qty' => 50,
            'unit_price' => 2000,
            'subtotal' => 100000,
            'cost_price' => 1800,
            'subtotal_cost' => 90000,
        ]);
        $order1->items()->create([
            'product_id' => $productMargin->id,
            'qty' => 2,
            'unit_price' => 60000,
            'subtotal' => 120000,
            'cost_price' => 20000,
            'subtotal_cost' => 40000,
        ]);

        // Order 2: SIAP_DIAMBIL (Non-SELESAI) -> Omzet 500.000, Profit 450.000
        $order2 = Order::create([
            'order_code' => 'ORD-RANK-02',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SIAP_DIAMBIL',
            'total_amount' => 500000,
            'transaction_date' => '2026-07-20 15:00:00',
        ]);
        $order2->items()->create([
            'product_id' => $productPending->id,
            'qty' => 5,
            'unit_price' => 100000,
            'subtotal' => 500000,
            'cost_price' => 10000,
            'subtotal_cost' => 50000,
        ]);

        // 1. Verifikasi pada Laporan Penjualan (admin.reports.index)
        $resReport = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-07-20',
        ]));
        $resReport->assertOk();

        // Top Products (Barang Terlaris berdasarkan kuantitas):
        // Rank 1: productVol (50 pcs)
        // Rank 2: productMargin (2 pcs)
        $topProducts = $resReport->viewData('topProducts');
        $this->assertEquals($productVol->id, $topProducts->first()->id);
        $this->assertEquals(50, $topProducts->first()->total_sold);

        // Profitable Products (Berdasarkan total_profit):
        // Rank 1: productMargin (Laba: Rp 80.000)
        // Rank 2: productVol (Laba: Rp 10.000)
        $profitableProducts = $resReport->viewData('profitableProducts');
        $this->assertEquals($productMargin->id, $profitableProducts->first()->id);
        $this->assertEquals(80000.0, $profitableProducts->first()->total_profit);
        $this->assertEquals($productVol->id, $profitableProducts->values()->get(1)->id);
        $this->assertEquals(10000.0, $profitableProducts->values()->get(1)->total_profit);

        // Pastikan order non-SELESAI tidak masuk ke profitableProducts
        $this->assertFalse($profitableProducts->contains('id', $productPending->id));

        // 2. Verifikasi pada Dashboard Penjualan (admin.dashboard)
        $resDashboard = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2026-07-20',
        ]));
        $resDashboard->assertOk();
        $periodProfitable = $resDashboard->viewData('periodProfitableProducts');
        $this->assertEquals($productMargin->id, $periodProfitable->first()->id);
        $this->assertEquals(80000.0, $periodProfitable->first()->total_profit);
        $this->assertFalse($periodProfitable->contains('id', $productPending->id));
    }

    public function test_profitable_products_isolates_unrecorded_hpp_and_handles_empty_state(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        // Product A: Complete HPP
        $prodA = Product::create([
            'sku' => 'PROFIT-CMPL-01',
            'name' => 'Barang HPP Lengkap',
            'category' => 'Testing',
            'cost_price' => 10000,
            'price' => 15000,
            'stock' => 50,
            'is_active' => true,
        ]);

        // Product B: Incomplete HPP
        $prodB = Product::create([
            'sku' => 'PROFIT-INCOMP-01',
            'name' => 'Barang HPP Kosong',
            'category' => 'Testing',
            'cost_price' => 0,
            'price' => 50000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-MIXED-01',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 65000,
            'transaction_date' => '2026-07-25 11:00:00',
            'paid_at' => '2026-07-25 11:00:00',
        ]);
        $order->items()->create([
            'product_id' => $prodA->id,
            'qty' => 1,
            'unit_price' => 15000,
            'subtotal' => 15000,
            'cost_price' => 10000,
            'subtotal_cost' => 10000,
        ]);
        $order->items()->create([
            'product_id' => $prodB->id,
            'qty' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
            'cost_price' => 0,
            'subtotal_cost' => 0,
        ]);

        $res = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-07-25',
        ]));
        $res->assertOk();
        $profitable = $res->viewData('profitableProducts');

        // Prod B harus memiliki unrecorded_items_count > 0
        $itemB = $profitable->firstWhere('id', $prodB->id);
        $this->assertNotNull($itemB);
        $this->assertGreaterThan(0, $itemB->unrecorded_items_count);

        // Blade laporan harus menampilkan bagian provisional untuk Prod B
        $res->assertSee('Data Produk Provisional (HPP Belum Lengkap)');
        $res->assertSee($prodB->name);
        $res->assertSee($prodA->name);

        // Verifikasi empty state jika tanggal tidak ada transaksi sama sekali
        $resEmpty = $this->actingAs($admin)->get(route('admin.dashboard', [
            'period' => 'daily',
            'date' => '2030-01-01',
        ]));
        $resEmpty->assertOk();
        $resEmpty->assertSee('Belum ada produk dengan data HPP lengkap pada periode ini.');
    }

    public function test_profitable_products_period_filter_isolation(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member']);

        $product = Product::create([
            'sku' => 'PROFIT-FILTER-01',
            'name' => 'Barang Filter Profit',
            'category' => 'Testing',
            'cost_price' => 10000,
            'price' => 20000,
            'stock' => 100,
            'is_active' => true,
        ]);

        // Order 1 pada 2026-08-01: 5 pcs -> profit 50.000
        $order1 = Order::create([
            'order_code' => 'ORD-FLTR-01',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 100000,
            'transaction_date' => '2026-08-01 10:00:00',
            'paid_at' => '2026-08-01 10:00:00',
        ]);
        $order1->items()->create([
            'product_id' => $product->id,
            'qty' => 5,
            'unit_price' => 20000,
            'subtotal' => 100000,
            'cost_price' => 10000,
            'subtotal_cost' => 50000,
        ]);

        // Order 2 pada 2026-08-10: 10 pcs -> profit 100.000
        $order2 = Order::create([
            'order_code' => 'ORD-FLTR-02',
            'user_id' => $member->id,
            'customer_name' => $member->name,
            'source' => 'web',
            'status' => 'SELESAI',
            'total_amount' => 200000,
            'transaction_date' => '2026-08-10 10:00:00',
            'paid_at' => '2026-08-10 10:00:00',
        ]);
        $order2->items()->create([
            'product_id' => $product->id,
            'qty' => 10,
            'unit_price' => 20000,
            'subtotal' => 200000,
            'cost_price' => 10000,
            'subtotal_cost' => 100000,
        ]);

        // Filter Harian 2026-08-01: hanya Order 1 (profit 50.000)
        $resDaily = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-08-01',
        ]));
        $dailyProfit = $resDaily->viewData('profitableProducts');
        $this->assertEquals(50000.0, $dailyProfit->first()->total_profit);

        // Filter Bulanan Agustus 2026: Order 1 + Order 2 (profit 150.000)
        $resMonthly = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'monthly',
            'month' => 8,
            'year' => 2026,
        ]));
        $monthlyProfit = $resMonthly->viewData('profitableProducts');
        $this->assertEquals(150000.0, $monthlyProfit->first()->total_profit);
    }

    public function test_excel_export_generates_four_sheets_including_profitability_analysis(): void
    {
        $profitableCollection = collect([
            (object) [
                'sku' => 'PROFIT-EXCEL-01',
                'name' => 'Barang Excel Profit',
                'category' => 'Testing',
                'total_sold' => 10,
                'total_revenue' => 200000.0,
                'total_cost' => 120000.0,
                'total_profit' => 80000.0,
                'unrecorded_items_count' => 0,
            ],
            (object) [
                'sku' => 'PROFIT-EXCEL-02',
                'name' => 'Barang Excel Tanpa HPP',
                'category' => 'Testing',
                'total_sold' => 5,
                'total_revenue' => 50000.0,
                'total_cost' => 0.0,
                'total_profit' => 50000.0,
                'unrecorded_items_count' => 5,
            ],
        ]);

        $export = new \App\Exports\PeriodicReportExport(
            collect(),
            collect(),
            [
                'total_omzet' => 250000.0,
                'total_transactions' => 2,
                'total_items_sold' => 15,
                'avg_transaction' => 125000.0,
                'total_hpp' => 120000.0,
                'gross_profit' => 130000.0,
                'gpm' => 52.0,
                'markup' => 108.3,
                'has_unrecorded_hpp' => true,
                'unrecorded_items_count' => 5,
                'unrecorded_orders_count' => 1,
            ],
            'Bulanan: Agustus 2026',
            ['web' => 1, 'manual_admin' => 1, 'import_excel' => 0, 'other' => 0],
            $profitableCollection
        );

        $sheets = $export->sheets();
        $this->assertCount(4, $sheets);
        $this->assertInstanceOf(\App\Exports\Sheets\TopProfitableProductsReportSheet::class, $sheets[3]);
        $this->assertSame('Produk Paling Menguntungkan', $sheets[3]->title());

        $this->assertCount(2, $sheets[3]->collection());

        // Row 1: Complete HPP
        $row1 = $sheets[3]->map($profitableCollection[0]);
        $this->assertEquals('PROFIT-EXCEL-01', $row1[1]);
        $this->assertEquals(80000.0, $row1[7]);
        $this->assertEquals('40%', $row1[8]);
        $this->assertEquals('Lengkap', $row1[9]);

        // Row 2: Provisional HPP
        $row2 = $sheets[3]->map($profitableCollection[1]);
        $this->assertEquals('PROFIT-EXCEL-02', $row2[1]);
        $this->assertEquals(50000.0, $row2[7]);
        $this->assertEquals('Provisional (HPP Belum Lengkap)', $row2[9]);

        // Sheet 0 Summary verifications
        $summaryData = $sheets[0]->array();
        $this->assertContains(['Gross Profit Margin (GPM)', '52% (Provisional)'], $summaryData);
        $this->assertContains(['Markup Penjualan (%)', '108.3% (Provisional)'], $summaryData);
    }
}

