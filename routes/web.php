<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ManualOrderController;
use App\Http\Controllers\Admin\OrderImportController;
use App\Http\Controllers\Admin\PickupController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Member\CartController;
use App\Http\Controllers\Member\CatalogController;
use App\Http\Controllers\Member\CheckoutController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrisSimulationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Polmart Sistem Informasi Manajemen Koperasi
|--------------------------------------------------------------------------
*/

// Halaman Utama Publik / Anggota
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Redirect Dashboard Berdasarkan Role (FR-02)
Route::get('/dashboard', function (Request $request) {
    if ($request->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('anggota.dashboard');
})->middleware(['auth'])->name('dashboard');

// ==========================================
// SIMULASI QRIS DINAMIS & API POLLING (FR-07)
// ==========================================
Route::get('/qris/pay/{order_code}', [QrisSimulationController::class, 'show'])
    ->name('qris.pay');
Route::post('/api/qris/simulate-callback', [QrisSimulationController::class, 'simulateCallback'])
    ->name('api.qris.simulate-callback');
Route::get('/api/qris/status/{order_code}', [QrisSimulationController::class, 'status'])
    ->name('api.qris.status');

// ==========================================
// RUTE KHUSUS PENGURUS / ADMIN POLMART (FR-02)
// ==========================================
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard Analitik (FR-11)
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Master Barang & Mutasi Stok (FR-03, FR-04)
        Route::resource('products', ProductController::class)
            ->except(['show', 'destroy']);
        Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('products.toggle-status');
        Route::post('products/{product}/restock', [ProductController::class, 'restock'])
            ->name('products.restock');
        Route::get('stock-mutations', [ProductController::class, 'mutations'])
            ->name('stock-mutations.index');

        // Transaksi Walk-in / Input Pesanan Manual (FR-09)
        Route::resource('manual-orders', ManualOrderController::class)
            ->only(['index', 'create', 'store', 'show']);

        // Impor Data Excel (FR-10)
        Route::get('order-imports', [OrderImportController::class, 'index'])
            ->name('order-imports.index');
        Route::get('order-imports/template', [OrderImportController::class, 'downloadTemplate'])
            ->name('order-imports.template');
        Route::post('order-imports/preview', [OrderImportController::class, 'preview'])
            ->name('order-imports.preview');
        Route::post('order-imports/store', [OrderImportController::class, 'store'])
            ->name('order-imports.store');

        // Validasi Loket Pengambilan & Riwayat Transaksi
        Route::get('pickup', [PickupController::class, 'index'])
            ->name('pickup.index');
        Route::post('pickup/verify-code', [PickupController::class, 'verifyByCode'])
            ->name('pickup.verify-code');
        Route::post('pickup/{order}/complete', [PickupController::class, 'complete'])
            ->name('pickup.complete');
        Route::get('transactions', [PickupController::class, 'history'])
            ->name('transactions.index');

        // Laporan Berkala & Export Excel Multi-Sheet (FR-12)
        Route::get('reports', [ReportController::class, 'index'])
            ->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])
            ->name('reports.export');
    });

// ==========================================
// RUTE KHUSUS ANGGOTA / MEMBER POLMART (FR-02, FR-05 s.d. FR-08)
// ==========================================
Route::middleware(['auth', 'role:member'])
    ->prefix('anggota')
    ->name('anggota.')
    ->group(function () {
        // Dashboard Anggota
        Route::get('/dashboard', [MemberDashboardController::class, 'index'])
            ->name('dashboard');

        // Katalog Produk Anggota
        Route::get('/katalog', [CatalogController::class, 'index'])
            ->name('katalog');

        // Keranjang Belanja
        Route::get('/keranjang', [CartController::class, 'index'])
            ->name('keranjang');
        Route::post('/keranjang/add', [CartController::class, 'add'])
            ->name('keranjang.add');
        Route::patch('/keranjang/{product}', [CartController::class, 'update'])
            ->name('keranjang.update');
        Route::delete('/keranjang/{product}', [CartController::class, 'remove'])
            ->name('keranjang.remove');
        Route::delete('/keranjang', [CartController::class, 'clear'])
            ->name('keranjang.clear');

        // Checkout & Simulasi Pembayaran QRIS
        Route::get('/checkout', [CheckoutController::class, 'index'])
            ->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->name('checkout.store');
        Route::get('/pembayaran/{order_code?}', [CheckoutController::class, 'payment'])
            ->name('pembayaran');

        // Riwayat & Status Pesanan Anggota
        Route::get('/pesanan', [CheckoutController::class, 'orders'])
            ->name('pesanan');
    });

// ==========================================
// PROFILE PENGGUNA
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
