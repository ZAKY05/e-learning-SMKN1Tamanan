<?php

namespace App\Observers;

use App\Models\Presensi;
use App\Models\Student;
use App\Services\FcmService;

class PresensiObserver
{
    /**
     * Handle the Presensi "created" event.
     * Notifikasi sudah ditangani di PresensiGuruController::store()
     * Observer ini dibiarkan kosong untuk menghindari duplikasi.
     */
    public function created(Presensi $presensi): void
    {
        // Notifikasi ditangani di PresensiGuruController::store()
        // menggunakan FcmService::kirimKeBanyakUser()
    }

    /**
     * Handle the Presensi "updated" event.
     */
    public function updated(Presensi $presensi): void
    {
        //
    }

    /**
     * Handle the Presensi "deleted" event.
     */
    public function deleted(Presensi $presensi): void
    {
        //
    }

    /**
     * Handle the Presensi "restored" event.
     */
    public function restored(Presensi $presensi): void
    {
        //
    }

    /**
     * Handle the Presensi "force deleted" event.
     */
    public function forceDeleted(Presensi $presensi): void
    {
        //
    }
}
