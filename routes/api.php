<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

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
            
            // Tambahkan URL lengkap untuk foto_profil jika ada
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
    Route::post('/update-password', [AuthController::class, 'updatePassword'])->middleware('auth:sanctum');
    Route::post('/update-email', [AuthController::class, 'updateEmail'])->middleware('auth:sanctum');
    // Student Tugas Routes
    Route::get('/tugas', [\App\Http\Controllers\Api\SiswaTugasController::class, 'index']);
    Route::post('/tugas/{tugasId}/upload', [\App\Http\Controllers\Api\SiswaTugasController::class, 'upload']);

    // Mapel & Materi Routes (untuk halaman "Daftar Mata Pelajaran" di mobile)
    Route::get('/mapel', [\App\Http\Controllers\Api\MapelMateriController::class, 'mapelIndex']);
    Route::get('/mapel/{mapelId}', [\App\Http\Controllers\Api\MapelMateriController::class, 'mapelShow']);
    Route::get('/mapel/{mapelId}/materi', [\App\Http\Controllers\Api\MapelMateriController::class, 'materiByMapel']);
    Route::get('/materi/{materiId}', [\App\Http\Controllers\Api\MapelMateriController::class, 'materiShow']);

    // Presensi Routes (untuk scan QR & riwayat kehadiran di mobile)
    Route::get('/presensi/active', [\App\Http\Controllers\Api\PresensiApiController::class, 'activePresensi']);
    Route::post('/presensi/scan', [\App\Http\Controllers\Api\PresensiApiController::class, 'scanQr']);
    Route::get('/presensi/riwayat', [\App\Http\Controllers\Api\PresensiApiController::class, 'riwayat']);
    Route::get('/presensi/rekap', [\App\Http\Controllers\Api\PresensiApiController::class, 'rekap']);

    // Guru Jadwal Mengajar Routes (untuk mobile guru)
    Route::get('/guru/jadwal', [\App\Http\Controllers\Api\JadwalGuruApiController::class, 'index']);
    Route::get('/guru/jadwal/semua', [\App\Http\Controllers\Api\JadwalGuruApiController::class, 'semua']);
    Route::get('/guru/jadwal/hari-ini', [\App\Http\Controllers\Api\JadwalGuruApiController::class, 'hariIni']);
    Route::get('/guru/jadwal/notifikasi', [\App\Http\Controllers\Api\JadwalGuruApiController::class, 'notifikasi']);
});