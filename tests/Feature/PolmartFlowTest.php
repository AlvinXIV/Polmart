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
}

