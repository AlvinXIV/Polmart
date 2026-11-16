<?php

use App\Http\Controllers\Admin\ManualOrderController;
use App\Http\Controllers\Admin\OrderImportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    if ($request->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('anggota.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rute Khusus Pengurus / Admin Polmart (FR-02, FR-03, FR-04, FR-09, FR-10)
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('products', ProductController::class)->except(['show', 'destroy']);
        Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('products.toggle-status');
        Route::post('products/{product}/restock', [ProductController::class, 'restock'])
            ->name('products.restock');
        Route::get('stock-mutations', [ProductController::class, 'mutations'])
            ->name('stock-mutations.index');

        Route::resource('manual-orders', ManualOrderController::class)
            ->only(['index', 'create', 'store', 'show']);

        Route::get('order-imports', [OrderImportController::class, 'index'])
            ->name('order-imports.index');
        Route::get('order-imports/template', [OrderImportController::class, 'downloadTemplate'])
            ->name('order-imports.template');
        Route::post('order-imports/preview', [OrderImportController::class, 'preview'])
            ->name('order-imports.preview');
        Route::post('order-imports/store', [OrderImportController::class, 'store'])
            ->name('order-imports.store');
    });

// Rute Khusus Anggota Polmart (FR-02)
Route::middleware(['auth', 'role:member'])
    ->prefix('anggota')
    ->name('anggota.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('anggota.dashboard');
        })->name('dashboard');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';