<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BukuController;
use App\Http\Controllers\Api\ExternalBookController;
use App\Http\Controllers\Api\PeminjamanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/bukus', [BukuController::class, 'index'])->name('bukus.index');
    Route::get('/bukus/{buku}', [BukuController::class, 'show'])->name('bukus.show');
    Route::get('/bukus/{buku}/referensi-eksternal', [ExternalBookController::class, 'show'])->name('bukus.external');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/bukus', [BukuController::class, 'store'])->name('bukus.store');
        Route::put('/bukus/{buku}', [BukuController::class, 'update'])->name('bukus.update');
        Route::patch('/bukus/{buku}', [BukuController::class, 'update'])->name('bukus.patch');
        Route::delete('/bukus/{buku}', [BukuController::class, 'destroy'])->name('bukus.destroy');
        Route::get('/peminjamans', [PeminjamanController::class, 'index'])->name('peminjamans.index');
        Route::post('/peminjamans', [PeminjamanController::class, 'store'])->name('peminjamans.store');
        Route::get('/peminjamans/{peminjaman}', [PeminjamanController::class, 'show'])->name('peminjamans.show');
        Route::patch('/peminjamans/{peminjaman}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjamans.kembali');
    });
});
