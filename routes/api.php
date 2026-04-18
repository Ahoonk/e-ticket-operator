<?php

use App\Http\Controllers\Api\AnggotaController;
use App\Http\Controllers\Api\OpdController;
use App\Http\Controllers\Api\KegiatanController;
use App\Http\Controllers\Api\KegiatanJaringanDokumenController;
use App\Http\Controllers\Api\KegiatanJaringanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/gangguan-public', [KegiatanJaringanController::class, 'publicIndex']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('kegiatan', KegiatanController::class);
    Route::apiResource('anggota', AnggotaController::class)->except(['show']);
    Route::apiResource('opd', OpdController::class)->except(['show']);
    Route::apiResource('users', UserController::class)->except(['show']);
    Route::get('/dokumen', [KegiatanJaringanDokumenController::class, 'index']);
    Route::get('/gangguan/{gangguan}/dokumen', [KegiatanJaringanDokumenController::class, 'show']);
    Route::post('/gangguan/{gangguan}/dokumen', [KegiatanJaringanDokumenController::class, 'store']);
    Route::delete('/dokumen/{dokumen}', [KegiatanJaringanDokumenController::class, 'destroy']);
    Route::get('/gangguan', [KegiatanJaringanController::class, 'index']);
    Route::post('/gangguan/{gangguan}/complete', [KegiatanJaringanController::class, 'complete']);
    Route::apiResource('gangguan', KegiatanJaringanController::class)->only(['store', 'show', 'update', 'destroy']);
});
