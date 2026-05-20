<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotifikasiController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        // Load relasi berdasarkan role
        if ($user->role === 'siswa') {
            $user->load('siswa.kelas', 'siswa.jurusan');

            if ($user->siswa && $user->siswa->foto_profil) {
                $user->siswa->foto_url = asset('storage/' . $user->siswa->foto_profil);
            }
        } elseif ($user->role === 'guru') {
            $user->load('guru');

            if ($user->guru && $user->guru->foto_profil) {
                $user->guru->foto_url = asset('storage/' . $user->guru->foto_profil);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    });

    Route::post('/update-password', [AuthController::class, 'updatePassword']);
    Route::post('/update-email', [AuthController::class, 'updateEmail']);

    // Student Tugas Routes
    Route::get('/tugas', [\App\Http\Controllers\Api\SiswaTugasController::class, 'index']);
    Route::post('/tugas/{tugasId}/upload', [\App\Http\Controllers\Api\SiswaTugasController::class, 'upload']);

    // Mapel & Materi Routes
    Route::get('/mapel', [\App\Http\Controllers\Api\MapelMateriController::class, 'mapelIndex']);
    Route::get('/mapel/{mapelId}', [\App\Http\Controllers\Api\MapelMateriController::class, 'mapelShow']);
    Route::get('/mapel/{mapelId}/materi', [\App\Http\Controllers\Api\MapelMateriController::class, 'materiByMapel']);
    Route::get('/materi/{materiId}', [\App\Http\Controllers\Api\MapelMateriController::class, 'materiShow']);

    // Presensi Routes
    Route::get('/presensi/active', [\App\Http\Controllers\Api\PresensiApiController::class, 'activePresensi']);
    Route::post('/presensi/scan', [\App\Http\Controllers\Api\PresensiApiController::class, 'scanQr']);
    Route::get('/presensi/riwayat', [\App\Http\Controllers\Api\PresensiApiController::class, 'riwayat']);
    Route::get('/presensi/rekap', [\App\Http\Controllers\Api\PresensiApiController::class, 'rekap']);

    // Guru Jadwal Mengajar Routes
    Route::get('/guru/jadwal', [\App\Http\Controllers\Api\JadwalGuruApiController::class, 'index']);
    Route::get('/guru/jadwal/semua', [\App\Http\Controllers\Api\JadwalGuruApiController::class, 'semua']);
    Route::get('/guru/jadwal/hari-ini', [\App\Http\Controllers\Api\JadwalGuruApiController::class, 'hariIni']);
    Route::get('/guru/jadwal/notifikasi', [\App\Http\Controllers\Api\JadwalGuruApiController::class, 'notifikasi']);

    // Siswa Kuis Routes
    Route::get('/kuis', [\App\Http\Controllers\Api\SiswaKuisController::class, 'index']);
    Route::get('/kuis/{id}', [\App\Http\Controllers\Api\SiswaKuisController::class, 'show']);
    Route::post('/kuis/{id}/start', [\App\Http\Controllers\Api\SiswaKuisController::class, 'start']);
    Route::get('/kuis/{id}/soal', [\App\Http\Controllers\Api\SiswaKuisController::class, 'getSoal']);
    Route::post('/kuis/{id}/submit', [\App\Http\Controllers\Api\SiswaKuisController::class, 'submit']);
    Route::get('/kuis/{id}/result', [\App\Http\Controllers\Api\SiswaKuisController::class, 'result']);

    // Notifikasi Routes
    Route::prefix('notifikasi')->group(function () {
        Route::get('/', [NotifikasiController::class, 'index']);
        Route::get('/unread-count', [NotifikasiController::class, 'unreadCount']);
        Route::post('/baca-semua', [NotifikasiController::class, 'bacaSemua']);
        Route::post('/{id}/baca', [NotifikasiController::class, 'baca']);
        Route::post('/update-token', [NotifikasiController::class, 'updateFcmToken']);
    });

    // FCM Token Routes
    Route::post('/fcm/update', [AuthController::class, 'updateFcmToken']);
    Route::post('/fcm/remove', [AuthController::class, 'removeFcmToken']);
});