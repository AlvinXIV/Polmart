
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Admin
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Halaman Inventaris
Route::get('/inventaris', function () {
    return view('admin.inventaris');
})->middleware(['auth', 'verified'])->name('inventaris');

// Profile
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

        Route::get('/transaksi-walkin', function () {
    return view('admin.transaksi-walkin');
})->name('transaksi.walkin');

Route::get('/validasi-loket', function () {
    return view('admin.validasi-loket');
})->name('validasi.loket');

Route::get('/impor-excel', function () {
    return view('admin.impor-excel');
})->name('impor.excel');

Route::get('/riwayat-transaksi', function () {
    return view('admin.riwayat-transaksi');
})->name('riwayat.transaksi');

Route::get('/laporan', function () {
    return view('admin.laporan');
})->name('laporan');

});

require __DIR__.'/auth.php';
