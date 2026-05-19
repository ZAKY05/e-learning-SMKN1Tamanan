<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\FcmService;

class JadwalReminderCommand extends Command
{
    /**
     * Jalankan via scheduler:
     *   - Setiap hari pukul 18:00 → kirim reminder H-1
     *   - Setiap jam               → kirim reminder H-1 jam
     */
    protected $signature   = 'jadwal:reminder';
    protected $description = 'Kirim notifikasi pengingat jadwal mengajar untuk guru';

    // Mapping hari Indonesia → Carbon dayOfWeek (0=Minggu, 1=Senin, ...)
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

        $this->kirimReminderH1Hari($now, $tahunAjaran, $semester);
        $this->kirimReminderH1Jam($now, $tahunAjaran, $semester);

        $this->info('Jadwal reminder selesai dikirim.');
    }

    // ── Reminder H-1 hari (kirim tiap hari jam 18:00) ─────────────────────────
    private function kirimReminderH1Hari(Carbon $now, string $tahunAjaran, string $semester): void
    {
        // Hanya jalan saat jam 18:00 (toleransi ±5 menit)
        if (!$now->copy()->setTime(18, 0)->between(
            $now->copy()->subMinutes(5),
            $now->copy()->addMinutes(5)
        )) return;

        $besok     = $now->copy()->addDay();
        $hariBesok = $this->carbonDayToHari($besok->dayOfWeek);
        if (!$hariBesok) return;

        $jadwalList = $this->getJadwalHari($hariBesok, $tahunAjaran, $semester);

        // Kelompokkan per guru
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
                userId: (int) $userId,
                tipe:   'jadwal_guru',          // ← tipe khusus guru
                judul:  'Pengingat Mengajar Besok',
                isi:    "Besok kamu mengajar: {$mapelList}",
                data:   ['screen' => 'jadwal_guru'],
            );
        }

        $this->info("Reminder H-1 hari: " . count($perGuru) . " guru.");
    }

    // ── Reminder H-1 jam (kirim tiap jam, cek jadwal 1 jam ke depan) ──────────
    private function kirimReminderH1Jam(Carbon $now, string $tahunAjaran, string $semester): void
    {
        $satu_jam_lagi = $now->copy()->addHour();
        $hariIni       = $this->carbonDayToHari($now->dayOfWeek);
        if (!$hariIni) return;

        // Hitung jam ke berapa 1 jam dari sekarang berdasarkan waktu mulai sekolah
        $waktuMulai = $this->getWaktuMulai($tahunAjaran, $semester); // Carbon
        $durasi     = $this->getDurasiJam($tahunAjaran, $semester);  // menit (default 45)

        if (!$waktuMulai) return;

        $jamKe = $this->hitungJamKe($satu_jam_lagi, $waktuMulai, $durasi);
        if (!$jamKe) return;

        $jadwalList = $this->getJadwalHariJam($hariIni, $jamKe, $tahunAjaran, $semester);

        foreach ($jadwalList as $j) {
            if (!$j->guru_user_id) continue;

            FcmService::kirimKeUser(
                userId: (int) $j->guru_user_id,
                tipe:   'jadwal_guru',
                judul:  'Segera Mengajar!',
                isi:    "1 jam lagi kamu mengajar {$j->nama_mapel} di kelas {$j->nama_kelas} (jam ke-{$j->jam_ke})",
                data:   ['screen' => 'jadwal_guru'],
            );
        }

        $this->info("Reminder H-1 jam: " . count($jadwalList) . " jadwal.");
    }

    // ── Query helpers ──────────────────────────────────────────────────────────

    private function getJadwalHari(string $hari, string $tahunAjaran, string $semester)
    {
        return DB::table('jadwal_pelajaran as jp')
            ->join('guru as g', 'g.id_guru', '=', 'jp.guru_id')
            ->join('users as u', 'u.id', '=', 'g.user_id')
            ->join('mapel as m', 'm.id_mapel', '=', 'jp.mapel_id')
            ->join('kelas as k', 'k.id_kelas', '=', 'jp.kelas_id')
            ->where('jp.hari', $hari)
            ->where('jp.tahun_ajaran', $tahunAjaran)
            ->where('jp.semester', $semester)
            ->whereNotNull('jp.guru_id')
            ->select(
                'u.id as guru_user_id',
                'g.nama as nama_guru',
                'm.nama_mapel',
                'k.nama_kelas',
                'jp.jam_ke',
            )
            ->get();
    }

    private function getJadwalHariJam(string $hari, int $jamKe, string $tahunAjaran, string $semester)
    {
        return DB::table('jadwal_pelajaran as jp')
            ->join('guru as g', 'g.id_guru', '=', 'jp.guru_id')
            ->join('users as u', 'u.id', '=', 'g.user_id')
            ->join('mapel as m', 'm.id_mapel', '=', 'jp.mapel_id')
            ->join('kelas as k', 'k.id_kelas', '=', 'jp.kelas_id')
            ->where('jp.hari', $hari)
            ->where('jp.jam_ke', $jamKe)
            ->where('jp.tahun_ajaran', $tahunAjaran)
            ->where('jp.semester', $semester)
            ->whereNotNull('jp.guru_id')
            ->select(
                'u.id as guru_user_id',
                'g.nama as nama_guru',
                'm.nama_mapel',
                'k.nama_kelas',
                'jp.jam_ke',
            )
            ->get();
    }

    private function getWaktuMulai(string $tahunAjaran, string $semester): ?Carbon
    {
        $setting = DB::table('setting_jadwal')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->value('waktu_mulai');

        return $setting ? Carbon::createFromFormat('H:i', $setting) : Carbon::createFromFormat('H:i', '07:00');
    }

    private function getDurasiJam(string $tahunAjaran, string $semester): int
    {
        return (int) (DB::table('setting_jadwal')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->value('durasi_jam_menit') ?? 45);
    }

    /**
     * Hitung jam ke-berapa berdasarkan waktu target.
     * Contoh: waktu_mulai=07:00, durasi=45 menit
     *   jam ke-1 = 07:00–07:45
     *   jam ke-2 = 07:45–08:30
     *   dst.
     */
    private function hitungJamKe(Carbon $target, Carbon $waktuMulai, int $durasi): ?int
    {
        $selisihMenit = $waktuMulai->diffInMinutes($target, false);
        if ($selisihMenit < 0) return null;

        $jamKe = (int) floor($selisihMenit / $durasi) + 1;
        return $jamKe >= 1 ? $jamKe : null;
    }

    private function carbonDayToHari(int $dayOfWeek): ?string
    {
        return array_search($dayOfWeek, self::HARI_MAP) ?: null;
    }

    private function getTahunAjaran(Carbon $now): string
    {
        $year = $now->month >= 7 ? $now->year : $now->year - 1;
        return $year . '/' . ($year + 1);
    }

    private function getSemester(Carbon $now): string
    {
        // Juli–Desember = ganjil, Januari–Juni = genap
        return $now->month >= 7 ? 'ganjil' : 'genap';
    }
}