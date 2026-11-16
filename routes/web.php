
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ========================================
// HALAMAN UTAMA
// ========================================

Route::get('/', function () {
    return view('welcome');
});


// ========================================
// DASHBOARD
// Setelah login, arahkan ke Katalog Member
// ========================================

Route::get('/dashboard', function () {
    return redirect()->route('anggota.katalog');
})->middleware(['auth', 'verified'])->name('dashboard');


// ========================================
// HALAMAN YANG MEMERLUKAN LOGIN
// ========================================

Route::middleware('auth')->group(function () {

    // ------------------------------------
    // PROFILE MEMBER
    // ------------------------------------

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    // ------------------------------------
    // MEMBER POLMART
    // ------------------------------------

    Route::prefix('anggota')->name('anggota.')->group(function () {

        // Katalog Produk
        Route::get('/katalog', function () {
            return view('anggota.katalog');
        })->name('katalog');


        // Keranjang Belanja
        Route::get('/keranjang', function () {
            return view('anggota.keranjang');
        })->name('keranjang');


        // Checkout
        Route::get('/checkout', function () {
            return view('anggota.checkout');
        })->name('checkout');


        // Pembayaran QRIS
        Route::get('/pembayaran', function () {
            return view('anggota.pembayaran');
        })->name('pembayaran');


        // Riwayat dan Status Pesanan
        Route::get('/pesanan', function () {
            return view('anggota.pesanan');
        })->name('pesanan');

    });

});


// ========================================
// ROUTE AUTENTIKASI LARAVEL BREEZE
// ========================================

require __DIR__.'/auth.php';
