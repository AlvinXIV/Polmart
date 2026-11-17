<?php

use App\Http\Controllers\Admin\ManualOrderController;
use App\Http\Controllers\Admin\OrderImportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Polmart Unified Frontend System
|--------------------------------------------------------------------------
*/

// Halaman Utama / Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard Utama Polmart
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Alias untuk Dashboard Admin
Route::get('/admin/dashboard', function () {
    return view('dashboard');
})->name('admin.dashboard');

// ==========================================
// FITUR ADMIN & PENGURUS KOPERASI
// ==========================================

// Inventaris / Manajemen Produk
Route::get('/inventaris', function () {
    return view('admin.inventaris');
})->name('inventaris');

// Transaksi Walk-in (Kasir Loket)
Route::get('/transaksi-walkin', function () {
    return view('admin.transaksi-walkin');
})->name('transaksi.walkin');

// Validasi Loket & Pengambilan Barang
Route::get('/validasi-loket', function () {
    return view('admin.validasi-loket');
})->name('validasi.loket');

// Impor Data Excel
Route::get('/impor-excel', function () {
    return view('admin.impor-excel');
})->name('impor.excel');

// Riwayat Transaksi
Route::get('/riwayat-transaksi', function () {
    return view('admin.riwayat-transaksi');
})->name('riwayat.transaksi');

// Laporan Koperasi
Route::get('/laporan', function () {
    return view('admin.laporan');
})->name('laporan');


// ==========================================
// FITUR ANGGOTA / MEMBER POLMART
// ==========================================
Route::prefix('anggota')->name('anggota.')->group(function () {

    // Katalog Produk Member
    Route::get('/katalog', function () {
        return view('anggota.katalog');
    })->name('katalog');

    // Keranjang Belanja
    Route::get('/keranjang', function () {
        return view('anggota.keranjang');
    })->name('keranjang');

    // Checkout Pesanan
    Route::get('/checkout', function () {
        return view('anggota.checkout');
    })->name('checkout');

    // Pembayaran QRIS
    Route::get('/pembayaran', function () {
        return view('anggota.pembayaran');
    })->name('pembayaran');

    // Riwayat & Status Pesanan Member
    Route::get('/pesanan', function () {
        return view('anggota.pesanan');
    })->name('pesanan');
});


// ==========================================
// PROFILE PENGGUNA
// ==========================================
Route::group([], function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';