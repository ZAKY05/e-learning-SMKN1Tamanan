<?php

namespace App\Observers;

use App\Models\Presensi;
use App\Models\Pelajar;
use App\Services\FcmService;

class PresensiObserver
{
    /**
     * Handle the Presensi "created" event.
     */
    public function created(Presensi $presensi): void
    {
        // Only notify if status is active
        if ($presensi->status !== 'aktif') {
            return;
        }

        // Get all students in this class who have an fcm_token
        $studentsTokens = Pelajar::where('kelas_id', $presensi->kelas_id)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($studentsTokens)) {
            return;
        }

        // Load relations for notification details
        $presensi->load(['mapel', 'guru']);
        $mapelName = $presensi->mapel->nama_mapel ?? 'Pelajaran';
        $guruName = $presensi->guru->nama ?? 'Guru';

        $title = "Presensi Dibuka!";
        $body = "Presensi untuk $mapelName oleh $guruName telah dibuka. Silakan lakukan absensi sebelum waktu habis.";

        FcmService::sendNotification($studentsTokens, $title, $body, [
            'type' => 'presensi_baru',
            'presensi_id' => $presensi->id_presensi
        ]);
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
