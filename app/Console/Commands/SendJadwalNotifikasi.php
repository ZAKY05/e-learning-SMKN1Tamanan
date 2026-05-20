<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JadwalPelajaran;
use App\Models\SettingJadwal;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendJadwalNotifikasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jadwal:notify-guru';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi ke guru 30 menit dan 15 menit sebelum jam mengajar dimulai';

    /**
     * Mapping hari Inggris → Indonesia
     */
    private $hariMap = [
        'Monday'    => 'senin',
        'Tuesday'   => 'selasa',
        'Wednesday' => 'rabu',
        'Thursday'  => 'kamis',
        'Friday'    => 'jumat',
        'Saturday'  => 'sabtu',
        'Sunday'    => 'minggu',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $setting = SettingJadwal::latest()->first();

        if (!$setting) {
            $this->error('Setting jadwal belum dikonfigurasi.');
            return;
        }

        $now = Carbon::now();
        $hariIni = $this->hariMap[$now->format('l')] ?? 'senin';

        // Get schedules for today
        $jadwalHariIni = JadwalPelajaran::with(['guru', 'mapel', 'kelas'])
            ->where('hari', $hariIni)
            ->whereNotNull('guru_id')
            ->orderBy('jam_ke')
            ->get();

        if ($jadwalHariIni->isEmpty()) {
            $this->info('Tidak ada jadwal hari ini.');
            return;
        }

        // Generate time slots based on setting
        $slots = $setting->hitungWaktuSlot($hariIni);
        $regulerSlots = collect($slots)->where('type', 'reguler')->keyBy('jam_ke');

        $notifiedGroups = [];

        foreach ($jadwalHariIni as $jadwal) {
            // Check if guru has fcm_token
            if (!$jadwal->guru || empty($jadwal->guru->fcm_token)) {
                continue;
            }

            $jamSlot = $regulerSlots->get($jadwal->jam_ke);
            if (!$jamSlot) {
                continue;
            }

            $waktuMulaiStr = $jamSlot['waktu_mulai']; // Format H:i
            $waktuMulai = Carbon::parse($now->format('Y-m-d') . ' ' . $waktuMulaiStr);

            // Avoid sending multiple notifications if the teacher has consecutive classes for the SAME class and subject.
            // But for simplicity, we just notify the FIRST jam_ke of that session.
            // If they have jam ke 1 and 2 same, we only notify before jam ke 1.
            $sessionKey = $jadwal->guru_id . '-' . $jadwal->kelas_id . '-' . $jadwal->mapel_id;
            
            // Allow notification for the first consecutive hour only
            if (isset($notifiedGroups[$sessionKey]) && $jadwal->jam_ke == $notifiedGroups[$sessionKey] + 1) {
                $notifiedGroups[$sessionKey] = $jadwal->jam_ke;
                continue; 
            }
            $notifiedGroups[$sessionKey] = $jadwal->jam_ke;


            $diffMinutes = $now->diffInMinutes($waktuMulai, false);

            // We want to send notification EXACTLY at 30 minutes and 15 minutes before.
            // Since this command runs every minute via cron, $diffMinutes will hit 30 and 15.
            if ($diffMinutes == 30 || $diffMinutes == 15) {
                
                $title = "Persiapan Mengajar";
                $body = "Jadwal Anda mengajar " . ($jadwal->mapel->nama_mapel ?? '-') . 
                        " di kelas " . ($jadwal->kelas->nama_kelas ?? '-') . 
                        " akan dimulai dalam " . $diffMinutes . " menit (pukul " . $waktuMulaiStr . ").";

                $sent = FcmService::sendNotification(
                    $jadwal->guru->fcm_token,
                    $title,
                    $body,
                    [
                        'type' => 'jadwal_reminder',
                        'jadwal_id' => $jadwal->id_jadwal
                    ]
                );

                if ($sent) {
                    $this->info("Notifikasi terkirim ke " . $jadwal->guru->nama . " untuk persiapan $diffMinutes menit.");
                } else {
                    $this->error("Gagal mengirim notifikasi ke " . $jadwal->guru->nama);
                }
            }
        }

        $this->info('Pengecekan jadwal selesai.');
    }
}
