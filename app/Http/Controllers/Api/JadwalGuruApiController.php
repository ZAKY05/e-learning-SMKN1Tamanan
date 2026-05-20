<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\SettingJadwal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalGuruApiController extends Controller
{
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

    private $hariLabel = [
        'senin'  => 'Senin',
        'selasa' => 'Selasa',
        'rabu'   => 'Rabu',
        'kamis'  => 'Kamis',
        'jumat'  => 'Jumat',
        'sabtu'  => 'Sabtu',
    ];

    /**
     * GET /api/guru/jadwal
     * Jadwal mengajar guru untuk satu minggu
     * Query: ?hari=senin (opsional, default = hari ini)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data guru',
            ], 403);
        }

        $setting = SettingJadwal::latest()->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting jadwal belum dikonfigurasi',
            ], 404);
        }

        $hariFilter = $request->hari ?? $this->getHariIni();

        // Validasi hari
        if (!array_key_exists($hariFilter, $this->hariLabel)) {
            return response()->json([
                'success' => false,
                'message' => 'Hari tidak valid. Gunakan: senin, selasa, rabu, kamis, jumat, sabtu',
            ], 422);
        }

        // Ambil jadwal hari yang diminta
        $jadwalHari = JadwalPelajaran::with(['mapel', 'kelas.jurusan'])
            ->where('guru_id', $guru->id_guru)
            ->where('hari', $hariFilter)
            ->orderBy('jam_ke')
            ->get();

        // Group consecutive jam_ke
        $jadwalGrouped = $this->groupJadwal($jadwalHari, $setting, $hariFilter);

        // Hitung info minggu ini
        $today = Carbon::now();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);

        $mingguInfo = [];
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        foreach ($hariList as $i => $hari) {
            $tanggal = $startOfWeek->copy()->addDays($i);
            $jumlahJam = JadwalPelajaran::where('guru_id', $guru->id_guru)
                ->where('hari', $hari)
                ->count();

            $mingguInfo[] = [
                'hari' => $hari,
                'label' => $this->hariLabel[$hari],
                'label_singkat' => strtoupper(substr($this->hariLabel[$hari], 0, 3)),
                'tanggal' => $tanggal->format('Y-m-d'),
                'tanggal_display' => $tanggal->format('d'),
                'is_today' => $hari === $this->getHariIni(),
                'jumlah_jam' => $jumlahJam,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hari_aktif' => $hariFilter,
                'hari_label' => $this->hariLabel[$hariFilter] ?? $hariFilter,
                'tanggal' => $today->format('Y-m-d'),
                'tanggal_display' => $this->formatTanggalIndonesia($today),
                'tahun_ajaran' => $setting->tahun_ajaran,
                'semester' => $setting->semester,
                'minggu' => $mingguInfo,
                'jadwal' => $jadwalGrouped,
            ],
        ]);
    }

    /**
     * GET /api/guru/jadwal/semua
     * Semua jadwal mengajar guru selama seminggu (Senin - Jumat)
     */
    public function semua(Request $request)
    {
        $user = $request->user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data guru',
            ], 403);
        }

        $setting = SettingJadwal::latest()->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting jadwal belum dikonfigurasi',
            ], 404);
        }

        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        $jadwalMinggu = [];

        foreach ($hariList as $hari) {
            $jadwalHari = JadwalPelajaran::with(['mapel', 'kelas.jurusan'])
                ->where('guru_id', $guru->id_guru)
                ->where('hari', $hari)
                ->orderBy('jam_ke')
                ->get();

            $jadwalMinggu[$hari] = [
                'hari' => $hari,
                'label' => $this->hariLabel[$hari],
                'jadwal' => $this->groupJadwal($jadwalHari, $setting, $hari),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tahun_ajaran' => $setting->tahun_ajaran,
                'semester' => $setting->semester,
                'jadwal_minggu' => array_values($jadwalMinggu),
            ],
        ]);
    }

    /**
     * GET /api/guru/jadwal/hari-ini
     * Jadwal mengajar guru hari ini + info pelajaran berikutnya untuk notifikasi
     */
    public function hariIni(Request $request)
    {
        $user = $request->user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data guru',
            ], 403);
        }

        $setting = SettingJadwal::latest()->first();
        $hariIni = $this->getHariIni();
        $now = Carbon::now();

        if (!$setting) {
            return response()->json([
                'success' => true,
                'data' => [
                    'hari' => $hariIni,
                    'hari_label' => $this->hariLabel[$hariIni] ?? $hariIni,
                    'tanggal' => $now->format('Y-m-d'),
                    'tanggal_display' => $this->formatTanggalIndonesia($now),
                    'jadwal' => [],
                    'pelajaran_sekarang' => null,
                    'pelajaran_berikutnya' => null,
                ],
            ]);
        }

        // Ambil jadwal hari ini
        $jadwalHari = JadwalPelajaran::with(['mapel', 'kelas.jurusan'])
            ->where('guru_id', $guru->id_guru)
            ->where('hari', $hariIni)
            ->orderBy('jam_ke')
            ->get();

        $jadwalGrouped = $this->groupJadwal($jadwalHari, $setting, $hariIni);

        // Tentukan pelajaran sekarang dan berikutnya
        $pelajaranSekarang = null;
        $pelajaranBerikutnya = null;
        $currentTime = $now->format('H:i');

        foreach ($jadwalGrouped as $index => $jadwal) {
            $mulai = $jadwal['waktu_mulai'];
            $selesai = $jadwal['waktu_selesai'];

            if ($currentTime >= $mulai && $currentTime < $selesai) {
                $pelajaranSekarang = $jadwal;
                // Ambil berikutnya jika ada
                if (isset($jadwalGrouped[$index + 1])) {
                    $pelajaranBerikutnya = $jadwalGrouped[$index + 1];
                }
                break;
            } elseif ($currentTime < $mulai) {
                $pelajaranBerikutnya = $jadwal;
                break;
            }
        }

        // Hitung menit menuju pelajaran berikutnya (untuk notifikasi)
        $menitMenujuBerikutnya = null;
        if ($pelajaranBerikutnya) {
            $waktuMulaiBerikutnya = Carbon::parse($now->format('Y-m-d') . ' ' . $pelajaranBerikutnya['waktu_mulai']);
            if ($waktuMulaiBerikutnya->gt($now)) {
                $menitMenujuBerikutnya = $now->diffInMinutes($waktuMulaiBerikutnya);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hari' => $hariIni,
                'hari_label' => $this->hariLabel[$hariIni] ?? $hariIni,
                'tanggal' => $now->format('Y-m-d'),
                'tanggal_display' => $this->formatTanggalIndonesia($now),
                'jadwal' => $jadwalGrouped,
                'pelajaran_sekarang' => $pelajaranSekarang,
                'pelajaran_berikutnya' => $pelajaranBerikutnya ? array_merge($pelajaranBerikutnya, [
                    'menit_menuju' => $menitMenujuBerikutnya,
                    'notif_message' => $menitMenujuBerikutnya !== null
                        ? "Anda akan mengajar {$pelajaranBerikutnya['mapel']} di {$pelajaranBerikutnya['kelas']} dalam {$menitMenujuBerikutnya} menit (pukul {$pelajaranBerikutnya['waktu_mulai']})"
                        : null,
                ]) : null,
                'total_jam_hari_ini' => count($jadwalGrouped),
            ],
        ]);
    }

    /**
     * GET /api/guru/jadwal/notifikasi
     * Cek pelajaran berikutnya (untuk trigger notifikasi di Flutter)
     * Cocok dipanggil secara periodik (setiap 5-10 menit)
     */
    public function notifikasi(Request $request)
    {
        $user = $request->user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data guru',
            ], 403);
        }

        $setting = SettingJadwal::latest()->first();
        $hariIni = $this->getHariIni();
        $now = Carbon::now();

        if (!$setting || !array_key_exists($hariIni, $this->hariLabel)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'ada_notifikasi' => false,
                    'message' => 'Tidak ada jadwal hari ini',
                ],
            ]);
        }

        $jadwalHari = JadwalPelajaran::with(['mapel', 'kelas.jurusan'])
            ->where('guru_id', $guru->id_guru)
            ->where('hari', $hariIni)
            ->orderBy('jam_ke')
            ->get();

        $jadwalGrouped = $this->groupJadwal($jadwalHari, $setting, $hariIni);

        $currentTime = $now->format('H:i');
        $notifikasi = [];

        foreach ($jadwalGrouped as $jadwal) {
            $waktuMulai = Carbon::parse($now->format('Y-m-d') . ' ' . $jadwal['waktu_mulai']);
            $selisih = $now->diffInMinutes($waktuMulai, false); // positif = belum mulai

            // Notif 15 menit sebelum dan 5 menit sebelum
            if ($selisih > 0 && $selisih <= 15) {
                $notifikasi[] = [
                    'tipe' => $selisih <= 5 ? 'segera' : 'persiapan',
                    'menit_menuju' => $selisih,
                    'waktu_mulai' => $jadwal['waktu_mulai'],
                    'waktu_selesai' => $jadwal['waktu_selesai'],
                    'mapel' => $jadwal['mapel'],
                    'kelas' => $jadwal['kelas'],
                    'message' => $selisih <= 5
                        ? "⏰ {$jadwal['mapel']} di {$jadwal['kelas']} dimulai {$selisih} menit lagi!"
                        : "📚 Persiapan: {$jadwal['mapel']} di {$jadwal['kelas']} dimulai {$selisih} menit lagi (pukul {$jadwal['waktu_mulai']})",
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ada_notifikasi' => count($notifikasi) > 0,
                'notifikasi' => $notifikasi,
            ],
        ]);
    }

    // ========== HELPER METHODS ==========

    /**
     * Group consecutive jam_ke for same mapel+kelas into time ranges
     */
    private function groupJadwal($jadwalHari, $setting, $hari): array
    {
        if ($jadwalHari->isEmpty() || !$setting) {
            return [];
        }

        $slots = $setting->hitungWaktuSlot($hari);
        $regulerSlots = collect($slots)->where('type', 'reguler')->keyBy('jam_ke');

        $grouped = [];
        $currentGroup = null;

        foreach ($jadwalHari as $jadwal) {
            $key = $jadwal->mapel_id . '-' . $jadwal->kelas_id;

            if ($currentGroup && $currentGroup['key'] === $key && $jadwal->jam_ke === $currentGroup['jam_akhir'] + 1) {
                $currentGroup['jam_akhir'] = $jadwal->jam_ke;
            } else {
                if ($currentGroup) {
                    $grouped[] = $this->buildGroupData($currentGroup, $regulerSlots);
                }
                $currentGroup = [
                    'key'       => $key,
                    'jam_awal'  => $jadwal->jam_ke,
                    'jam_akhir' => $jadwal->jam_ke,
                    'mapel'     => $jadwal->mapel,
                    'mapel_id'  => $jadwal->mapel_id,
                    'kelas'     => $jadwal->kelas,
                    'kelas_id'  => $jadwal->kelas_id,
                ];
            }
        }

        if ($currentGroup) {
            $grouped[] = $this->buildGroupData($currentGroup, $regulerSlots);
        }

        return $grouped;
    }

    private function buildGroupData(array $group, $regulerSlots): array
    {
        $waktuMulai   = $regulerSlots->get($group['jam_awal'])['waktu_mulai'] ?? '--:--';
        $waktuSelesai = $regulerSlots->get($group['jam_akhir'])['waktu_selesai'] ?? '--:--';

        $kelas = $group['kelas'];
        $namaKelas = $kelas ? $kelas->nama_kelas : '-';

        return [
            'mapel'          => $group['mapel']->nama_mapel ?? '-',
            'mapel_id'       => $group['mapel_id'],
            'kode_mapel'     => $group['mapel']->kode_mapel ?? '',
            'kelas'          => $namaKelas,
            'kelas_id'       => $group['kelas_id'],
            'jam_ke_awal'    => $group['jam_awal'],
            'jam_ke_akhir'   => $group['jam_akhir'],
            'jumlah_jam'     => $group['jam_akhir'] - $group['jam_awal'] + 1,
            'waktu_mulai'    => $waktuMulai,
            'waktu_selesai'  => $waktuSelesai,
            'waktu_display'  => $waktuMulai . ' - ' . $waktuSelesai,
        ];
    }

    private function getHariIni(): string
    {
        return $this->hariMap[Carbon::now()->format('l')] ?? 'senin';
    }

    private function formatTanggalIndonesia(Carbon $date): string
    {
        $hariLabel = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        $bulanLabel = [
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',      6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',  9  => 'September',
            10 => 'Oktober',  11 => 'November', 12 => 'Desember',
        ];
 
        $hari = $hariLabel[$date->format('l')] ?? '';
        $bulan = $bulanLabel[(int)$date->format('m')] ?? '';

        return "{$hari}, {$date->format('d')} {$bulan} {$date->format('Y')}";
    }
}
