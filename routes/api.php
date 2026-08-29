<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\InfakController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\RiwayatController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PushController;
use App\Models\Kelas;

Route::get('/setup-kelas', function () {
    $kelasNames = [
        'Bambim', 'TK', 'Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4A', 'Kelas 4B', 
        'Kelas 5', 'Kelas 6', 'Kelas 7', 'Kelas 8', 'Kelas 9', 'Kelas Ulya'
    ];

    // Buat kelas yang belum ada
    foreach ($kelasNames as $nama) {
        Kelas::firstOrCreate(['nama_kelas' => $nama]);
    }

    // Hapus kelas lama yang tidak dipakai (jika kosong)
    $oldKelas = ['Kelas 4', 'Kelas 10', 'Kelas 11 & 12'];
    Kelas::whereIn('nama_kelas', $oldKelas)->delete();

    return response()->json(['message' => 'Kelas berhasil di-update!']);
});

Route::get('/delete-dummy-bambim', function () {
    $bambim = \App\Models\Kelas::where('nama_kelas', 'Bambim')->first();
    if ($bambim) {
        \App\Models\Infak::where('kelas_id', $bambim->id)->whereNull('siswa_id')->delete();
    }
    \App\Models\Infak::where('keterangan', 'Setoran Akhir Bulan Bambim')->delete();
    return response()->json(['message' => 'Semua data dummy Bambim berhasil dihapus bersih!']);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/siswa', [SiswaController::class, 'index']);
    Route::get('/siswa/export', [SiswaController::class, 'exportExcel']);
    Route::get('/siswa/{id}', [SiswaController::class, 'show']);
    Route::post('/siswa', [SiswaController::class, 'store']);
    Route::put('/siswa/{id}', [SiswaController::class, 'update']);
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy']);
    
    Route::get('/guru', [GuruController::class, 'index']);
    Route::post('/guru', [GuruController::class, 'store']);
    Route::put('/guru/{id}/reset-password', [GuruController::class, 'resetPassword']);
    Route::delete('/guru/{id}', [GuruController::class, 'destroy']);

    Route::get('/infak/terbaru', [InfakController::class, 'getTerbaru']);
    Route::get('/kelas', [KelasController::class, 'index']);
    Route::post('/kelas', [KelasController::class, 'store']);
    Route::get('/kelas/{kelas_id}/unpaid-students', [SiswaController::class, 'getUnpaidStudents']);
    Route::get('/infak', [InfakController::class, 'index']);
    Route::post('/infak', [InfakController::class, 'store']);
    Route::post('/infak/sync', [InfakController::class, 'syncBySiswa']);
    Route::get('/siswa/{id}/infak', [InfakController::class, 'getBySiswa']);
    Route::put('/infak/{id}', [InfakController::class, 'update']);
    Route::delete('/infak/{id}', [InfakController::class, 'destroy']);

    Route::get('/pengeluaran', [PengeluaranController::class, 'index']);
    Route::post('/pengeluaran', [PengeluaranController::class, 'store']);
    Route::delete('/pengeluaran/{id}', [PengeluaranController::class, 'destroy']);

    Route::get('/log-aktivitas', [RiwayatController::class, 'index']);
    Route::post('/log-aktivitas/clear', [RiwayatController::class, 'clear']);

    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::get('/laporan/cetak-pdf', [LaporanController::class, 'cetakPdf']);
    Route::get('/kelas/{id}/form-setoran', [LaporanController::class, 'cetakFormSetoran']);

    // Message Routes
    Route::get('/messages/admin-profile', [\App\Http\Controllers\Api\MessageController::class, 'getAdminProfile']);
    Route::get('/messages/chat-list', [\App\Http\Controllers\Api\MessageController::class, 'getChatList']);
    Route::get('/messages/conversation/{userId}', [\App\Http\Controllers\Api\MessageController::class, 'getConversation']);
    Route::post('/messages/send', [\App\Http\Controllers\Api\MessageController::class, 'sendMessage']);

    Route::get('/dashboard/statistik', [DashboardController::class, 'statistik']);
    Route::get('/dashboard/kepatuhan', [DashboardController::class, 'kepatuhan']);
    Route::get('/kelas', [DashboardController::class, 'getKelas']);

    // Web Push Notifications
    Route::post('/push-subscribe', [PushController::class, 'subscribe']);
    Route::post('/push-test', [PushController::class, 'testPush']);
    Route::post('/push-send-reminders', [PushController::class, 'sendReminders']);
});
