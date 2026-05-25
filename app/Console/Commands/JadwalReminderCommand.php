<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\FcmService;

class JadwalReminderCommand extends Command
{
    protected $signature   = 'jadwal:reminder';
    protected $description = 'Kirim notifikasi pengingat jadwal mengajar untuk guru';

    private const HARI_MAP = [
        'senin'   => Carbon::MONDAY,
        'selasa'  => Carbon::TUESDAY,
        'rabu'    => Carbon::WEDNESDAY,
        'kamis'   => Carbon::THURSDAY,
        'jumat'   => Carbon::FRIDAY,
    ];

    public function handle(): void
    {
        $now         = Carbon::now();
        $tahunAjaran = $this->getTahunAjaran($now);
        $semester    = $this->getSemester($now);

        $this->info("Tahun: $tahunAjaran | Semester: $semester | Waktu: " . $now->format('H:i'));

        $this->kirimReminderH1Hari($now, $tahunAjaran, $semester);
        $this->kirimReminderH1Jam($now, $tahunAjaran, $semester);
        $this->kirimReminder15Menit($now, $tahunAjaran, $semester);
        $this->kirimReminderMulai($now, $tahunAjaran, $semester);

        $this->info('Jadwal reminder selesai dikirim.');
    }

    // ── Reminder H-1 hari (jam 18:00) ─────────────────────────────────────────
    private function kirimReminderH1Hari(Carbon $now, string $tahunAjaran, string $semester): void
    {
        // Hanya jalan saat jam 18:00 (toleransi ±5 menit)
        $jam18 = $now->copy()->setTime(18, 0);
        if (!$jam18->between($now->copy()->subMinutes(5), $now->copy()->addMinutes(5))) {
            $this->info("Skip H-1 hari (bukan jam 18:00)");
            return;
        }

        $besok     = $now->copy()->addDay();
        $hariBesok = $this->carbonDayToHari($besok->dayOfWeek);
        if (!$hariBesok) {
            $this->info("Skip H-1 hari (besok libur)");
            return;
        }

        $jadwalList = $this->getJadwalHari($hariBesok, $tahunAjaran, $semester);

        $perGuru = [];
        foreach ($jadwalList as $j) {
            if (!$j->guru_user_id) continue;
            $perGuru[$j->guru_user_id][] = $j;
        }

        foreach ($perGuru as $userId => $jadwals) {
            $mapelList = collect($jadwals)
                ->map(fn($j) => "{$j->nama_mapel} ({$j->nama_kelas}) jam ke-{$j->jam_ke}")
                ->unique()
                ->implode(', ');

            FcmService::kirimKeUser(
                (int) $userId,
                'jadwal',
                'Pengingat Mengajar Besok',
                "Besok kamu mengajar: {$mapelList}",
                ['screen' => 'jadwal_guru']
            );
        }

        $this->info("Reminder H-1 hari: " . count($perGuru) . " guru.");
    }

    // ── Reminder 1 jam sebelum mengajar ───────────────────────────────────────
    private function kirimReminderH1Jam(Carbon $now, string $tahunAjaran, string $semester): void
    {
        $hariIni = $this->carbonDayToHari($now->dayOfWeek);
        if (!$hariIni) {
            $this->info("Skip 1 jam (hari libur)");
            return;
        }

        $waktuMulai = $this->getWaktuMulai($tahunAjaran, $semester);
        $durasi     = $this->getDurasiJam($tahunAjaran, $semester);

        // Cari jam ke berapa yang mulai 1 jam dari sekarang
        $targetWaktu = $now->copy()->addMinutes(60);
        $jamKe       = $this->hitungJamKe($targetWaktu, $waktuMulai, $durasi);

        if (!$jamKe) {
            $this->info("Skip 1 jam (tidak ada jam ke-{$jamKe})");
            return;
        }

        $jadwalList = $this->getJadwalHariJam($hariIni, $jamKe, $tahunAjaran, $semester);

        $count = 0;
        foreach ($jadwalList as $j) {
            if (!$j->guru_user_id) continue;

            FcmService::kirimKeUser(
                (int) $j->guru_user_id,
                'jadwal',
                '1 Jam Lagi Mengajar',
                "1 jam lagi kamu mengajar {$j->nama_mapel} di kelas {$j->nama_kelas} (jam ke-{$j->jam_ke})",
                [
                    'screen' => 'jadwal_guru',
                    'mapel'  => $j->nama_mapel,
                    'kelas'  => $j->nama_kelas,
                ]
            );
            $count++;
        }

        $this->info("Reminder 1 jam: {$count} guru.");
    }

    // ── Reminder 15 menit sebelum mengajar ────────────────────────────────────
    private function kirimReminder15Menit(Carbon $now, string $tahunAjaran, string $semester): void
    {
        $hariIni = $this->carbonDayToHari($now->dayOfWeek);
        if (!$hariIni) {
            $this->info("Skip 15 menit (hari libur)");
            return;
        }

        $waktuMulai = $this->getWaktuMulai($tahunAjaran, $semester);
        $durasi     = $this->getDurasiJam($tahunAjaran, $semester);

        $targetWaktu = $now->copy()->addMinutes(15);
        $jamKe       = $this->hitungJamKe($targetWaktu, $waktuMulai, $durasi);

        if (!$jamKe) {
            $this->info("Skip 15 menit (tidak ada jam ke-{$jamKe})");
            return;
        }

        $jadwalList = $this->getJadwalHariJam($hariIni, $jamKe, $tahunAjaran, $semester);

        $count = 0;
        foreach ($jadwalList as $j) {
            if (!$j->guru_user_id) continue;

            FcmService::kirimKeUser(
                (int) $j->guru_user_id,
                'jadwal',
                '15 Menit Lagi Mengajar!',
                "Segera siapkan diri! 15 menit lagi mengajar {$j->nama_mapel} di kelas {$j->nama_kelas}",
                [
                    'screen' => 'jadwal_guru',
                    'mapel'  => $j->nama_mapel,
                    'kelas'  => $j->nama_kelas,
                ]
            );
            $count++;
        }

        $this->info("Reminder 15 menit: {$count} guru.");
    }

    // ── Reminder tepat jam mulai mengajar ─────────────────────────────────────
    private function kirimReminderMulai(Carbon $now, string $tahunAjaran, string $semester): void
    {
        $hariIni = $this->carbonDayToHari($now->dayOfWeek);
        if (!$hariIni) {
            $this->info("Skip mulai (hari libur)");
            return;
        }

        $waktuMulai = $this->getWaktuMulai($tahunAjaran, $semester);
        $durasi     = $this->getDurasiJam($tahunAjaran, $semester);

        // Jam ke berapa tepat sekarang
        $jamKe = $this->hitungJamKe($now, $waktuMulai, $durasi);

        if (!$jamKe) {
            $this->info("Skip mulai (belum/sudah lewat jam pelajaran)");
            return;
        }

        // Cek apakah sekarang tepat di awal jam (toleransi ±2 menit)
        $menitJamIni = ($jamKe - 1) * $durasi;
        $waktuMulaiJam = $waktuMulai->copy()->addMinutes($menitJamIni);
        $selisih = abs($waktuMulaiJam->diffInMinutes($now, false));

        if ($selisih > 2) {
            $this->info("Skip mulai (selisih {$selisih} menit dari jam ke-{$jamKe})");
            return;
        }

        $jadwalList = $this->getJadwalHariJam($hariIni, $jamKe, $tahunAjaran, $semester);

        $count = 0;
        foreach ($jadwalList as $j) {
            if (!$j->guru_user_id) continue;

            FcmService::kirimKeUser(
                (int) $j->guru_user_id,
                'jadwal',
                'Waktunya Mengajar!',
                "Saatnya mengajar {$j->nama_mapel} di kelas {$j->nama_kelas} sekarang!",
                [
                    'screen' => 'jadwal_guru',
                    'mapel'  => $j->nama_mapel,
                    'kelas'  => $j->nama_kelas,
                ]
            );
            $count++;
        }

        $this->info("Reminder mulai: {$count} guru.");
    }

    // ── Query helpers ──────────────────────────────────────────────────────────

    private function getJadwalHari(string $hari, string $tahunAjaran, string $semester)
    {
        return DB::table('jadwal_pelajaran as jp')
            ->join('guru as g', 'g.id_guru', '=', 'jp.guru_id')
            ->join('users as u', 'u.guru_id', '=', 'g.id_guru')
            ->join('mapel as m', 'm.id_mapel', '=', 'jp.mapel_id')
            ->join('kelas as k', 'k.id_kelas', '=', 'jp.kelas_id')
            ->join('jurusan as j', 'j.id_jurusan', '=', 'k.jurusan_id')
            ->where('jp.hari', $hari)
            ->where('jp.tahun_ajaran', $tahunAjaran)
            ->where('jp.semester', $semester)
            ->whereNotNull('jp.guru_id')
            ->select(
                'u.id as guru_user_id',
                'g.nama as nama_guru',
                'm.nama_mapel',
                'k.tingkat',
                'j.nama_jurusan',
                'k.golongan',
                'jp.jam_ke',
            )
            ->get()
            ->map(function ($item) {
                $item->nama_kelas = $item->tingkat . ' ' . $item->nama_jurusan . ' ' . $item->golongan;
                return $item;
            });
    }

    private function getJadwalHariJam(string $hari, int $jamKe, string $tahunAjaran, string $semester)
    {
        return DB::table('jadwal_pelajaran as jp')
            ->join('guru as g', 'g.id_guru', '=', 'jp.guru_id')
            ->join('users as u', 'u.guru_id', '=', 'g.id_guru')
            ->join('mapel as m', 'm.id_mapel', '=', 'jp.mapel_id')
            ->join('kelas as k', 'k.id_kelas', '=', 'jp.kelas_id')
            ->join('jurusan as j', 'j.id_jurusan', '=', 'k.jurusan_id')
            ->where('jp.hari', $hari)
            ->where('jp.jam_ke', $jamKe)
            ->where('jp.tahun_ajaran', $tahunAjaran)
            ->where('jp.semester', $semester)
            ->whereNotNull('jp.guru_id')
            ->select(
                'u.id as guru_user_id',
                'g.nama as nama_guru',
                'm.nama_mapel',
                'k.tingkat',
                'j.nama_jurusan',
                'k.golongan',
                'jp.jam_ke',
            )
            ->get()
            ->map(function ($item) {
                $item->nama_kelas = $item->tingkat . ' ' . $item->nama_jurusan . ' ' . $item->golongan;
                return $item;
            });
    }

    private function getWaktuMulai(string $tahunAjaran, string $semester): Carbon
    {
        $setting = DB::table('setting_jadwal')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->value('waktu_mulai');

        if (!$setting) {
            return Carbon::createFromTimeString('07:00');
        }

        try {
            return Carbon::createFromTimeString((string) $setting);
        } catch (\Throwable $e) {
            $this->warn("Format waktu_mulai tidak valid: {$setting}. Pakai default 07:00.");
            return Carbon::createFromTimeString('07:00');
        }
    }

    private function getDurasiJam(string $tahunAjaran, string $semester): int
    {
        return (int) (DB::table('setting_jadwal')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->value('durasi_jam_menit') ?? 45);
    }

    private function hitungJamKe(Carbon $target, Carbon $waktuMulai, int $durasi): ?int
    {
        $selisihMenit = $waktuMulai->diffInMinutes($target, false);
        if ($selisihMenit < 0) return null;

        $jamKe = (int) floor($selisihMenit / $durasi) + 1;
        return $jamKe >= 1 ? $jamKe : null;
    }

    private function carbonDayToHari(int $dayOfWeek): ?string
    {
        $hari = array_search($dayOfWeek, self::HARI_MAP, true);
        return $hari === false ? null : $hari;
    }

    private function getTahunAjaran(Carbon $now): string
    {
        $year = $now->month >= 1 ? $now->year : $now->year - 1;
        return $year . '/' . ($year + 1);
    }

    private function getSemester(Carbon $now): string
    {
        return $now->month <= 6 ? 'ganjil' : 'genap';
    }
}