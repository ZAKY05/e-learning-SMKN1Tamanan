<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Tugas;
use App\Models\PengumpulanTugas;
use App\Models\JadwalPelajaran;
use App\Models\SettingJadwal;
use App\Models\Presensi;
use Carbon\Carbon;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return view('Guru.pages.dashboard', [
                'guru' => null,
                'namaGuru' => $user->name,
                'totalKelas' => 0,
                'totalMateri' => 0,
                'totalTugasAktif' => 0,
                'totalTugasBelumDinilai' => 0,
                'rataKehadiran' => 0,
                'totalPresensi' => 0,
                'totalPresensiHadir' => 0,
                'jadwalHariIni' => collect(),
                'hariIni' => $this->getHariIndonesia(),
            ]);
        }

        $guruId = $guru->id_guru;

        // --- Stat Cards ---

        // 1. Jumlah Kelas (unique kelas from jadwal)
        $totalKelas = JadwalPelajaran::where('guru_id', $guruId)
            ->distinct('kelas_id')
            ->count('kelas_id');

        // 2. Materi yang diunggah
        $totalMateri = Materi::where('guru_id', $guruId)->count();

        // 3. Tugas Aktif (status published & deadline belum lewat)
        $totalTugasAktif = Tugas::where('guru_id', $guruId)
            ->where('status', 'published')
            ->where('tanggal_deadline', '>=', Carbon::today())
            ->count();

        // 4. Tugas Belum Dinilai
        $tugasIds = Tugas::where('guru_id', $guruId)->pluck('id_tugas');
        $totalTugasBelumDinilai = PengumpulanTugas::whereIn('tugas_id', $tugasIds)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', '!=', 'dinilai');
            })
            ->count();

        // --- Statistik Kehadiran (Presensi yang dibuat guru bulan ini) ---
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $totalPresensi = Presensi::where('guru_id', $guruId)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->count();

        // Presensi "selesai" dianggap terlaksana
        $totalPresensiHadir = Presensi::where('guru_id', $guruId)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->where('status', 'selesai')
            ->count();

        $rataKehadiran = $totalPresensi > 0
            ? round(($totalPresensiHadir / $totalPresensi) * 100)
            : 0;

        // --- Jadwal Hari Ini ---
        $hariIni = $this->getHariIndonesia();

        $jadwalHariIni = JadwalPelajaran::with(['mapel', 'kelas.jurusan'])
            ->where('guru_id', $guruId)
            ->where('hari', $hariIni)
            ->orderBy('jam_ke')
            ->get();

        // Group consecutive jam_ke for same mapel+kelas into time ranges
        $setting = SettingJadwal::latest()->first();
        $jadwalGrouped = $this->groupJadwal($jadwalHariIni, $setting, $hariIni);

        return view('Guru.pages.dashboard', compact(
            'guru',
            'totalKelas',
            'totalMateri',
            'totalTugasAktif',
            'totalTugasBelumDinilai',
            'rataKehadiran',
            'totalPresensi',
            'totalPresensiHadir',
            'jadwalGrouped',
            'hariIni',
        ));
    }

    /**
     * Get current day name in Indonesian (lowercase)
     */
    private function getHariIndonesia(): string
    {
        $days = [
            'Monday'    => 'senin',
            'Tuesday'   => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday'  => 'kamis',
            'Friday'    => 'jumat',
            'Saturday'  => 'sabtu',
            'Sunday'    => 'minggu',
        ];

        return $days[Carbon::now()->format('l')] ?? 'senin';
    }

    /**
     * Group jadwal items by mapel+kelas to form time ranges
     */
    private function groupJadwal($jadwalHariIni, $setting, $hari): array
    {
        if ($jadwalHariIni->isEmpty() || !$setting) {
            return [];
        }

        $slots = $setting->hitungWaktuSlot($hari);
        $regulerSlots = collect($slots)->where('type', 'reguler')->keyBy('jam_ke');

        $grouped = [];
        $currentGroup = null;

        foreach ($jadwalHariIni as $jadwal) {
            $key = $jadwal->mapel_id . '-' . $jadwal->kelas_id;

            if ($currentGroup && $currentGroup['key'] === $key && $jadwal->jam_ke === $currentGroup['jam_akhir'] + 1) {
                // Extend the group
                $currentGroup['jam_akhir'] = $jadwal->jam_ke;
            } else {
                // Save previous group
                if ($currentGroup) {
                    $grouped[] = $this->buildGroupData($currentGroup, $regulerSlots);
                }
                // Start new group
                $currentGroup = [
                    'key'       => $key,
                    'jam_awal'  => $jadwal->jam_ke,
                    'jam_akhir' => $jadwal->jam_ke,
                    'mapel'     => $jadwal->mapel,
                    'kelas'     => $jadwal->kelas,
                ];
            }
        }

        // Don't forget the last group
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
        $namaKelas = $kelas ? $kelas->nama_kelas_lengkap : '-';

        return [
            'waktu'     => $waktuMulai . ' - ' . $waktuSelesai,
            'mapel'     => $group['mapel']->nama_mapel ?? '-',
            'sub_mapel' => $group['mapel']->kode_mapel ?? '',
            'kelas'     => $namaKelas,
            'ruangan'   => 'R. Kls ' . ($kelas ? $kelas->id_kelas : '-'),
        ];
    }
}
