<?php

use App\Http\Controllers\Api\AnggotaController;
use App\Http\Controllers\Api\OpdController;
use App\Http\Controllers\Api\KegiatanController;
use App\Http\Controllers\Api\GangguanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/gangguan-public', [GangguanController::class, 'publicIndex']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('kegiatan', KegiatanController::class);
    Route::apiResource('anggota', AnggotaController::class)->except(['show']);
    Route::apiResource('opd', OpdController::class)->except(['show']);
    Route::apiResource('users', UserController::class)->except(['show']);
    Route::get('/gangguan', [GangguanController::class, 'index']);
    Route::post('/gangguan/{gangguan}/complete', [GangguanController::class, 'complete']);
    Route::apiResource('gangguan', GangguanController::class)->only(['store', 'show', 'update', 'destroy']);
});
