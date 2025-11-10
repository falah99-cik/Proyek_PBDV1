<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\PengadaanController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\MarginPenjualanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JenisBarangController;
use App\Http\Controllers\PenjualanController;

Route::middleware(['auth', 'role:1,2'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('barang', BarangController::class);
    Route::put('/barang/{id}/toggle-status', [BarangController::class, 'toggleStatus'])->name('barang.toggleStatus');
    Route::get('/barang/{id}/harga', [BarangController::class, 'getHarga']);
    Route::resource('vendor', VendorController::class)->except(['edit', 'update', 'show']);
    Route::prefix('pengadaan')->middleware(['auth', 'role:1,2'])->group(function () {
        Route::get('/', [PengadaanController::class, 'index'])->name('pengadaan.index');
        Route::get('/pengadaan/create', [PengadaanController::class, 'create'])->name('pengadaan.create');
        Route::post('/store', [PengadaanController::class, 'store'])->name('pengadaan.store');
        Route::get('/{id}', [PengadaanController::class, 'show'])->name('pengadaan.show');
        Route::get('/{id}/edit', [PengadaanController::class, 'edit'])->name('pengadaan.edit');
        Route::put('/{id}', [PengadaanController::class, 'update'])->name('pengadaan.update');
        Route::post('/{id}/cancel', [PengadaanController::class, 'cancel'])->name('pengadaan.cancel');
        Route::delete('/{id}', [PengadaanController::class, 'destroy'])->name('pengadaan.destroy');
    });
    Route::prefix('penerimaan')->group(function () {
        Route::get('/', [PenerimaanController::class, 'index'])->name('penerimaan.index');
        Route::get('/create', [PenerimaanController::class, 'create'])->name('penerimaan.create');
        Route::post('/store', [PenerimaanController::class, 'store'])->name('penerimaan.store');
        Route::get('/{id}', [PenerimaanController::class, 'show'])->name('penerimaan.show');
        Route::get('/pengadaan/{id}/barang', [App\Http\Controllers\PenerimaanController::class, 'getBarangByPengadaan']);
    });
    Route::resource('satuan', SatuanController::class);
    Route::resource('margin_penjualan', MarginPenjualanController::class);
    Route::resource('jenis-barang', JenisBarangController::class);
    Route::get('margin_penjualan/{id}/activate', [MarginPenjualanController::class, 'activate'])
        ->name('margin_penjualan.activate');
    Route::resource('penjualan', PenjualanController::class);
});

Route::middleware(['auth', 'role:1'])->group(function () {
    Route::resource('user', UserController::class)->except(['edit', 'update', 'show']);
    Route::resource('role', RoleController::class)->except(['edit', 'update', 'show']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
