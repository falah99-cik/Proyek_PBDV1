<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\ReturController;

Route::middleware(['auth', 'role:1,2'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('barang', BarangController::class);
    Route::put('/barang/{id}/toggle-status', [BarangController::class, 'toggleStatus'])
        ->name('barang.toggleStatus');
    Route::get('/barang/{id}/harga', [BarangController::class, 'getHarga'])
        ->name('barang.getHarga');

    Route::resource('vendor', VendorController::class)->except(['edit', 'show']);
    Route::put('/vendor/{id}/toggle-status', [VendorController::class, 'toggleStatus'])
        ->name('vendor.toggleStatus');

    Route::get(
        '/pengadaan/get-harga/{idbarang}',
        [PengadaanController::class, 'getHargaBarang']
    )
        ->name('pengadaan.getHarga');

    Route::prefix('pengadaan')->group(function () {
        Route::get('/', [PengadaanController::class, 'index'])->name('pengadaan.index');
        Route::get('/create', [PengadaanController::class, 'create'])->name('pengadaan.create');
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
        Route::get('/pengadaan/{id}/barang', [PenerimaanController::class, 'getBarangByPengadaan'])
            ->name('penerimaan.getBarangByPengadaan');
        Route::post('/{id}/add-detail', [PenerimaanController::class, 'addDetail'])
            ->name('penerimaan.addDetail');
    });

    Route::resource('satuan', SatuanController::class);
    Route::put('/satuan/{id}/toggle-status', [SatuanController::class, 'toggleStatus'])->name('satuan.toggleStatus');
    Route::resource('jenis-barang', JenisBarangController::class);
    Route::resource('penjualan', PenjualanController::class);

    Route::resource('margin_penjualan', MarginPenjualanController::class);
    Route::get('margin_penjualan/{id}/activate', [MarginPenjualanController::class, 'activate'])
        ->name('margin_penjualan.activate');
    Route::put('margin_penjualan/{id}/toggle', [MarginPenjualanController::class, 'toggle'])
        ->name('margin_penjualan.toggle');

    Route::get('/', [ReturController::class, 'index'])->name('retur.index');
    Route::post('/store', [ReturController::class, 'store'])->name('retur.store');
    Route::get('/{id}', [ReturController::class, 'show'])->name('retur.show');
    Route::put('/{id}/status', [ReturController::class, 'updateStatus'])->name('retur.updateStatus');
    Route::delete('/{id}', [ReturController::class, 'destroy'])->name('retur.destroy');
    Route::get('/get-items-penerimaan/{id}', [ReturController::class, 'getItemsPenerimaan'])->name('retur.getItemsPenerimaan');
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

Route::get('/barang/{id}/info', [BarangController::class, 'getInfo']);
Route::get('/penjualan/{id}/detail', [PenjualanController::class, 'getDetail']);

Route::get('/retur/get-barang-penerimaan/{idpenerimaan}', [ReturController::class, 'getBarangPenerimaan']);

require __DIR__ . '/auth.php';
