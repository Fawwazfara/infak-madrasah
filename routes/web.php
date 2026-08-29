<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\Api\InfakController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\AuthController;

Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/api/user', [AuthController::class, 'me'])->middleware('auth');

Route::middleware('auth')->prefix('api')->group(function () {
    // Kelas
    Route::get('/kelas', [DashboardController::class, 'getKelas']);

    // Siswa
    Route::get('/siswa', [SiswaController::class, 'index']);
    Route::post('/siswa', [SiswaController::class, 'store']);
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy']);

    // Infak
    Route::post('/infak', [InfakController::class, 'store']);
    
    // Guru (Management)
    Route::get('/guru', [GuruController::class, 'index']);
    Route::post('/guru', [GuruController::class, 'store']);
    Route::delete('/guru/{id}', [GuruController::class, 'destroy']);
    
    // Guru (Dashboard & Kepatuhan)
    Route::get('/dashboard/kepatuhan', [DashboardController::class, 'kepatuhan']);
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
