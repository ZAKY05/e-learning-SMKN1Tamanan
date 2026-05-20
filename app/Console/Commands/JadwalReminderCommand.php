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
    private function kirimReminderH1Jam(Carbon $now, string $tahunAjaran, string $semester)
{
    // Ambil semua jadwal guru hari ini
    $hariIni = $this->carbonDayToHari($now->dayOfWeek);

    if (!$hariIni) {
        return;
    }

    $jadwalList = $this->getJadwalHari(
        $hariIni,
        $tahunAjaran,
        $semester
    );

    foreach ($jadwalList as $j) {

        if (!$j->guru_user_id) {
            continue;
        }

        // TEST DELAY 1 MENIT
        $waktuNotif = $now->copy()->addMinute();

        FcmService::kirimKeUser(
            userId: (int) $j->guru_user_id,

            tipe: 'jadwal_guru',

            judul: 'TEST FOREGROUND/BACKGROUND',

            isi: "Notif test {$j->nama_mapel} kelas {$j->nama_kelas}",

            data: [
                'screen' => 'jadwal_guru',
                'role' => 'guru',
                'kelas' => $j->nama_kelas,
                'mapel' => $j->nama_mapel,
                'test_time' => $waktuNotif->format('H:i:s'),
            ],
        );
    }

    $this->info("TEST notif berhasil dikirim");
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

    private function getWaktuMulai(string $tahunAjaran, string $semester): ?Carbon
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
        $hari = array_search($dayOfWeek, self::HARI_MAP, true);
        return $hari === false ? null : $hari;
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