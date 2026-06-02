<?php
 
namespace App\Console\Commands;
 
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\FcmService;
 
/**
 * Kirim notifikasi pengingat jadwal mengajar kepada guru.
 *
 * Tiga jenis reminder (TANPA toleransi waktu):
 *   1. 1 jam sebelum  → dikirim tepat saat now()->format('H:i') == waktu_mulai_jam_ke_N - 60 menit
 *   2. 15 menit sebelum → dikirim tepat saat now()->format('H:i') == waktu_mulai_jam_ke_N - 15 menit
 *   3. Waktunya mengajar → dikirim tepat saat now()->format('H:i') == waktu_mulai_jam_ke_N
 *
 * Cache idempotency (TTL 5 menit) memastikan tidak ada notifikasi ganda
 * meskipun cron overlap karena jalan tiap menit.
 *
 * Schedule: Schedule::command('jadwal:reminder')->everyMinute()->withoutOverlapping();
 */
class JadwalReminderCommand extends Command
{
    protected $signature   = 'jadwal:reminder';
    protected $description = 'Kirim notifikasi pengingat jadwal mengajar untuk guru (tanpa toleransi)';
 
    private const HARI_MAP = [
        'senin'  => Carbon::MONDAY,
        'selasa' => Carbon::TUESDAY,
        'rabu'   => Carbon::WEDNESDAY,
        'kamis'  => Carbon::THURSDAY,
        'jumat'  => Carbon::FRIDAY,
    ];
 
    // Offset menit untuk tiap jenis reminder
    private const OFFSET_1JAM    = 60;
    private const OFFSET_15MENIT = 15;
    private const OFFSET_MULAI   = 0;
 
    public function handle(): void
    {
        $now         = Carbon::now()->second(0)->microsecond(0); // bulatkan ke menit
        $tahunAjaran = $this->getTahunAjaran($now);
        $semester    = $this->getSemester($now);
        $hariIni     = $this->carbonDayToHari($now->dayOfWeek);
 
        $this->info("Tahun: $tahunAjaran | Semester: $semester | Waktu: " . $now->format('H:i'));
 
        if (!$hariIni) {
            $this->info('Hari libur, tidak ada pengingat.');
            return;
        }
 
        $waktuMulai = $this->getWaktuMulai($tahunAjaran, $semester);
        $durasi     = $this->getDurasiJam($tahunAjaran, $semester);
        $nowHHmm    = $now->format('H:i');
 
        // Bangun daftar: jam_ke => waktu_mulai (string 'H:i')
        $jadwalWaktu = $this->buildJadwalWaktu($waktuMulai, $durasi);
 
        foreach ($jadwalWaktu as $jamKe => $mulaiStr) {
            // --- Reminder tepat waktunya mengajar ---
            if ($mulaiStr === $nowHHmm) {
                $this->prosesReminder(
                    'mulai', $jamKe, $hariIni, $tahunAjaran, $semester, $now,
                    'Waktunya Mengajar!',
                    fn($j) => "Saatnya mengajar {$j->nama_mapel} di kelas {$j->nama_kelas} sekarang!"
                );
            }
 
            // --- Reminder 15 menit sebelum ---
            $mulai15 = Carbon::createFromFormat('H:i', $mulaiStr)
                ->subMinutes(self::OFFSET_15MENIT)
                ->format('H:i');
            if ($mulai15 === $nowHHmm) {
                $this->prosesReminder(
                    '15mnt', $jamKe, $hariIni, $tahunAjaran, $semester, $now,
                    '15 Menit Lagi Mengajar!',
                    fn($j) => "Segera siapkan diri! 15 menit lagi mengajar {$j->nama_mapel} di kelas {$j->nama_kelas}"
                );
            }
 
            // --- Reminder 1 jam sebelum ---
            $mulai1j = Carbon::createFromFormat('H:i', $mulaiStr)
                ->subMinutes(self::OFFSET_1JAM)
                ->format('H:i');
            if ($mulai1j === $nowHHmm) {
                $this->prosesReminder(
                    '1jam', $jamKe, $hariIni, $tahunAjaran, $semester, $now,
                    '1 Jam Lagi Mengajar',
                    fn($j) => "1 jam lagi kamu mengajar {$j->nama_mapel} di kelas {$j->nama_kelas} (jam ke-{$j->jam_ke})"
                );
            }
        }
 
        $this->info('Selesai.');
    }
 
    // ── Core processor ────────────────────────────────────────────────────────
 
    private function prosesReminder(
        string   $tipe,
        int      $jamKe,
        string   $hariIni,
        string   $tahunAjaran,
        string   $semester,
        Carbon   $now,
        string   $judul,
        callable $pesanFn
    ): void {
        $jadwalList = $this->getJadwalHariJam($hariIni, $jamKe, $tahunAjaran, $semester);
 
        $count = 0;
        foreach ($jadwalList as $j) {
            if (!$j->guru_user_id) continue;
 
            // Idempotency key: 1 notif per guru per jenis per jam ke per hari
            $cacheKey = "reminder:{$tipe}:{$j->guru_user_id}:{$hariIni}:{$jamKe}:" . $now->format('Ymd');
            if (Cache::has($cacheKey)) {
                $this->info("  [skip duplikat] {$tipe} guru#{$j->guru_user_id} jam ke-{$jamKe}");
                continue;
            }
            // TTL 5 menit cukup — cron jalan tiap menit, kita hanya perlu block 1 menit
            Cache::put($cacheKey, true, now()->addMinutes(5));
 
            FcmService::kirimKeUser(
                (int) $j->guru_user_id,
                'jadwal',
                $judul,
                $pesanFn($j),
                [
                    'screen' => 'jadwal_guru',
                    'mapel'  => $j->nama_mapel,
                    'kelas'  => $j->nama_kelas,
                    'jam_ke' => (string) $j->jam_ke,
                ]
            );
            $count++;
        }
 
        if ($count > 0) {
            $this->info("Reminder [{$tipe}] jam ke-{$jamKe}: {$count} guru dikirim.");
        }
    }
 
    // ── Helpers ───────────────────────────────────────────────────────────────
 
    /**
     * Bangun map [jam_ke => 'H:i'] berdasarkan waktu_mulai dan durasi.
     * Berhenti saat waktu_mulai jam ke-N sudah melewati jam 18:00.
     *
     * @return array<int, string>  contoh: [1 => '07:00', 2 => '07:40', ...]
     */
    private function buildJadwalWaktu(Carbon $waktuMulai, int $durasi): array
    {
        $result  = [];
        $batasFin = Carbon::createFromTimeString('18:00');
 
        for ($n = 1; $n <= 16; $n++) {
            $mulaiJamN = $waktuMulai->copy()->addMinutes(($n - 1) * $durasi);
            if ($mulaiJamN->greaterThan($batasFin)) break;
            $result[$n] = $mulaiJamN->format('H:i');
        }
 
        return $result;
    }
 
    private function getJadwalHariJam(string $hari, int $jamKe, string $tahunAjaran, string $semester)
    {
        return DB::table('jadwal_pelajaran as jp')
            ->join('guru as g',    'g.id_guru',    '=', 'jp.guru_id')
            ->join('users as u',   'u.guru_id',    '=', 'g.id_guru')
            ->join('mapel as m',   'm.id_mapel',   '=', 'jp.mapel_id')
            ->join('kelas as k',   'k.id_kelas',   '=', 'jp.kelas_id')
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
            ->map(fn($i) => tap($i, fn($i) =>
                $i->nama_kelas = "{$i->tingkat} {$i->nama_jurusan} {$i->golongan}"
            ));
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
        } catch (\Throwable) {
            $this->warn("Format waktu_mulai tidak valid: {$setting}. Pakai default 07:00.");
            return Carbon::createFromTimeString('07:00');
        }
    }
 
    private function getDurasiJam(string $tahunAjaran, string $semester): int
    {
        return (int) (DB::table('setting_jadwal')
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->value('durasi_jam_menit') ?? 40);
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