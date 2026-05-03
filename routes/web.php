<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\AkunGuruController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\BanklokasiController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\MateriController;

Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        if ($role === 'admin') return redirect()->route('admin.dashboard');
        if ($role === 'guru') return redirect()->route('guru.dashboard');
    }
    return redirect()->route('login');
});
Route::get('/landing', function () {
    return view('landing.master');
})->name('landing');

// ============ Profile Routes (Breeze) ============
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');
});

// ============ Admin Routes ============
Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::get('/', function () {
            return view('Admin.pages.dashboard');
        }
        )->name('dashboard');

        // Data Siswa
        Route::get('/data-siswa', [SiswaController::class , 'index'])->name('siswa.index');
        Route::post('/data-siswa', [SiswaController::class , 'store'])->name('siswa.store');
        Route::put('/data-siswa/{id}', [SiswaController::class , 'update'])->name('siswa.update');
        Route::delete('/data-siswa/{id}', [SiswaController::class , 'destroy'])->name('siswa.destroy');

        // Data Guru
        Route::get('/data-guru', [GuruController::class , 'index'])->name('guru.index');
        Route::post('/data-guru', [GuruController::class , 'store'])->name('guru.store');
        Route::put('/data-guru/{id}', [GuruController::class , 'update'])->name('guru.update');
        Route::delete('/data-guru/{id}', [GuruController::class , 'destroy'])->name('guru.destroy');

        // Data Kelas
        Route::get('/data-kelas', [KelasController::class , 'index'])->name('kelas.index');
        Route::post('/data-kelas', [KelasController::class , 'store'])->name('kelas.store');
        Route::put('/data-kelas/{id}', [KelasController::class , 'update'])->name('kelas.update');
        Route::delete('/data-kelas/{id}', [KelasController::class , 'destroy'])->name('kelas.destroy');

        // Data Jurusan
        Route::get('/data-jurusan', [JurusanController::class , 'index'])->name('jurusan.index');
        Route::post('/data-jurusan', [JurusanController::class , 'store'])->name('jurusan.store');
        Route::put('/data-jurusan/{id}', [JurusanController::class , 'update'])->name('jurusan.update');
        Route::delete('/data-jurusan/{id}', [JurusanController::class , 'destroy'])->name('jurusan.destroy');

        // Data Mapel
        Route::get('/data-mapel', [MapelController::class , 'index'])->name('mapel.index');
        Route::post('/data-mapel', [MapelController::class , 'store'])->name('mapel.store');
        Route::put('/data-mapel/{id}', [MapelController::class , 'update'])->name('mapel.update');
        Route::delete('/data-mapel/{id}', [MapelController::class , 'destroy'])->name('mapel.destroy');

        // Akun Guru
        Route::get('/akun-guru', [AkunGuruController::class , 'index'])->name('akun-guru.index');
        Route::post('/akun-guru', [AkunGuruController::class , 'store'])->name('akun-guru.store');
        Route::put('/akun-guru/{id}', [AkunGuruController::class , 'update'])->name('akun-guru.update');
        Route::delete('/akun-guru/{id}', [AkunGuruController::class , 'destroy'])->name('akun-guru.destroy');

        // Akun Siswa
        Route::get('/akun-siswa', [\App\Http\Controllers\AkunSiswaController::class , 'index'])->name('akun-siswa.index');
        Route::post('/akun-siswa', [\App\Http\Controllers\AkunSiswaController::class , 'store'])->name('akun-siswa.store');
        Route::put('/akun-siswa/{id}', [\App\Http\Controllers\AkunSiswaController::class , 'update'])->name('akun-siswa.update');
        Route::delete('/akun-siswa/{id}', [\App\Http\Controllers\AkunSiswaController::class , 'destroy'])->name('akun-siswa.destroy');

        //Bank lokasi
        Route::get('/bank-lokasi', [\App\Http\Controllers\BankLokasiController::class , 'index'])->name('bank-lokasi.index');
        Route::post('/bank-lokasi', [\App\Http\Controllers\BankLokasiController::class , 'store'])->name('bank-lokasi.store');
        Route::put('/bank-lokasi/{id}', [\App\Http\Controllers\BankLokasiController::class , 'update'])->name('bank-lokasi.update');
        Route::delete('/bank-lokasi/{id}', [\App\Http\Controllers\BankLokasiController::class , 'destroy'])->name('bank-lokasi.destroy');

        // Jadwal Mengajar
        Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/jadwal/setting', [JadwalController::class, 'settingForm'])->name('jadwal.setting');
        Route::post('/jadwal/setting', [JadwalController::class, 'settingSave'])->name('jadwal.setting.save');
        Route::post('/jadwal/generate', [JadwalController::class, 'generate'])->name('jadwal.generate');
        Route::post('/jadwal/reset', [JadwalController::class, 'reset'])->name('jadwal.reset');
        Route::get('/jadwal/import/template', [JadwalController::class, 'downloadTemplateExcel'])->name('jadwal.import.template');
        Route::post('/jadwal/import/excel', [JadwalController::class, 'importExcel'])->name('jadwal.import.excel');
        Route::get('/jadwal/mapping', [JadwalController::class, 'mappingIndex'])->name('jadwal.mapping');
        Route::post('/jadwal/mapping', [JadwalController::class, 'mappingSave'])->name('jadwal.mapping.save');
        Route::delete('/jadwal/mapping/{id}', [JadwalController::class, 'mappingDelete'])->name('jadwal.mapping.delete');
        Route::get('/jadwal/guru-kode', [JadwalController::class, 'guruKodeIndex'])->name('jadwal.guru-kode');
        Route::post('/jadwal/guru-kode', [JadwalController::class, 'guruKodeSave'])->name('jadwal.guru-kode.save');
        Route::delete('/jadwal/guru-kode/{id}', [JadwalController::class, 'guruKodeDelete'])->name('jadwal.guru-kode.delete');
        Route::post('/jadwal/update-slot', [JadwalController::class, 'updateSlot'])->name('jadwal.update-slot');
        Route::post('/jadwal/delete-slot', [JadwalController::class, 'deleteSlot'])->name('jadwal.delete-slot');
        Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy'])->name('jadwal.destroy');

    });

// ============ Guru Routes ============
Route::prefix('guru')->name('guru.')->middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('Guru.pages.dashboard');
    })->name('dashboard');

    // Bank-lokasi guru (Sekarang ada di dalam grup 'guru.')
    // URL akan menjadi: /guru/bank-lokasi
    // Nama rute akan menjadi: guru.bank-lokasi-guru.index
    Route::get('/bank-lokasi', [BanklokasiController::class, 'index_guru'])->name('bank-lokasi-guru.index');
    Route::post('/bank-lokasi', [BanklokasiController::class, 'store'])->name('bank-lokasi-guru.store');
    Route::put('/bank-lokasi/{id}', [BanklokasiController::class, 'update'])->name('bank-lokasi-guru.update');
    Route::delete('/bank-lokasi/{id}', [BanklokasiController::class, 'destroy'])->name('bank-lokasi-guru.destroy');

    // Upload Materi
    Route::get('/materi', [MateriController::class, 'index'])->name('materi.index');
    Route::get('/materi/{kelasId}/{mapelId}', [MateriController::class, 'show'])->name('materi.show');
    Route::post('/materi', [MateriController::class, 'store'])->name('materi.store');
    Route::put('/materi/{id}', [MateriController::class, 'update'])->name('materi.update');
    Route::delete('/materi/{id}', [MateriController::class, 'destroy'])->name('materi.destroy');
});   

require __DIR__ . '/auth.php';
