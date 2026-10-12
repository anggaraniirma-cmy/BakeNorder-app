<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes BakeNOrder
|--------------------------------------------------------------------------
*/

// ---- Beranda ----
Route::get('/', [HomeController::class, 'index'])->name('home');

// ---- Keranjang ----
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::get('/cart/tambah', [CartController::class, 'tambah'])->name('cart.tambah');
Route::get('/cart/hapus/{id}', [CartController::class, 'hapus'])->name('cart.hapus');
Route::post('/cart/kosongkan', [CartController::class, 'kosongkan'])->name('cart.kosongkan');

// ---- Auth ----
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ---- Pesanan user ----
Route::middleware('auth')->group(function () {
    Route::post('/pesanan', [PesananController::class, 'store'])->name('pesanan.store');
    Route::get('/nota', [PesananController::class, 'nota'])->name('nota');

    // ---- Admin ----
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Kasir (POS)
        Route::get('/kasir', [AdminController::class, 'kasir'])->name('kasir');
        Route::post('/kasir', [AdminController::class, 'kasirStore'])->name('kasir.store');
        Route::get('/kasir/struk/{id}', [AdminController::class, 'struk'])->name('kasir.struk');

        // Pengiriman (delivery)
        Route::get('/pengiriman', [AdminController::class, 'pengiriman'])->name('pengiriman');
        Route::post('/pengiriman/toko', [AdminController::class, 'simpanLokasiToko'])->name('pengiriman.toko');

        Route::post('/produk', [AdminController::class, 'tambahProduk'])->name('produk.tambah');
        Route::get('/produk/{id}/edit', [AdminController::class, 'editProduk'])->name('produk.edit');
        Route::post('/produk/update', [AdminController::class, 'updateProduk'])->name('produk.update');
        Route::get('/produk/{id}/hapus', [AdminController::class, 'hapusProduk'])->name('produk.hapus');
        Route::post('/status', [AdminController::class, 'updateStatus'])->name('status.update');
    });
});
