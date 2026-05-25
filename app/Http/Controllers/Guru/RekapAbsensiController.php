<?php
// app/Http/Controllers/Guru/RekapAbsensiController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\JadwalPelajaran;
use App\Models\Absensi;
use App\Models\Student;
use Carbon\Carbon;

class RekapAbsensiController extends Controller
{
    private function getGuru()
    {
        $user = Auth::user();
        return Guru::where('id_guru', $user->guru_id)->first();
    }

    /**
     * Ambil daftar kelas yang diajar guru (dari jadwal_pelajaran)
     */
    private function getKelasList($guru, $tahunAjaran = null, $semester = null)
    {
        $query = JadwalPelajaran::where('guru_id', $guru->id_guru);

        if ($tahunAjaran) {
            $query->where('tahun_ajaran', $tahunAjaran);
        }
        if ($semester) {
            $query->where('semester', $semester);
        }

        $jadwal = $query->with(['kelas.jurusan'])
            ->select('kelas_id')
            ->distinct()
            ->get();

        return $jadwal->pluck('kelas');
    }

    public function index(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan');
        }

        $tahunAjaran = $request->get('tahun_ajaran', '2024/2025');
        $semester = $request->get('semester', 'ganjil');
        $kelasList = $this->getKelasList($guru, $tahunAjaran, $semester);

        $kelasId = $request->get('kelas_id');
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $statistik = (object)[
            'total_hadir' => 0,
            'total_terlambat' => 0,
            'total_izin' => 0,
            'total_sakit' => 0,
            'total_alpha' => 0,
            'total_pertemuan' => 0,
            'persen_kehadiran' => 0,
        ];

        $rekapPerSiswa = [];

        if ($kelasId) {
            // Ambil semua siswa di kelas ini
            $siswaList = Student::where('kelas_id', $kelasId)->orderBy('nama')->get();

            // Ambil semua absensi di bulan dan tahun ini untuk kelas tersebut
            $absensiList = Absensi::whereIn('siswa_id', $siswaList->pluck('id_siswa'))
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            $statistik->total_pertemuan = $absensiList->groupBy('tanggal')->count();

            foreach ($siswaList as $siswa) {
                $absensiSiswa = $absensiList->where('siswa_id', $siswa->id_siswa);

                $hadir = $absensiSiswa->where('status', 'hadir')->count();
                $terlambat = $absensiSiswa->where('status', 'terlambat')->count();
                $izin = $absensiSiswa->where('status', 'izin')->count();
                $sakit = $absensiSiswa->where('status', 'sakit')->count();
                $alpha = $absensiSiswa->where('status', 'alpha')->count();

                // Kehadiran = hadir + terlambat
                $totalHadirEfektif = $hadir + $terlambat;
                $total = $hadir + $terlambat + $izin + $sakit + $alpha;

                $rekapPerSiswa[] = (object)[
                    'siswa' => $siswa,
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpha' => $alpha,
                    'total_hadir' => $totalHadirEfektif,
                    'total' => $total,
                    'persen' => $total > 0 ? round(($totalHadirEfektif / $total) * 100, 1) : 0,
                ];

                $statistik->total_hadir += $hadir;
                $statistik->total_terlambat += $terlambat;
                $statistik->total_izin += $izin;
                $statistik->total_sakit += $sakit;
                $statistik->total_alpha += $alpha;
            }

            $totalKehadiran = $statistik->total_hadir + $statistik->total_terlambat + $statistik->total_izin + $statistik->total_sakit + $statistik->total_alpha;
            $totalHadirEfektif = $statistik->total_hadir + $statistik->total_terlambat;
            $statistik->persen_kehadiran = $totalKehadiran > 0 ? round(($totalHadirEfektif / $totalKehadiran) * 100, 1) : 0;
        }

        return view('Guru.pages.rekap.absensi', compact(
            'kelasList',
            'kelasId',
            'statistik',
            'rekapPerSiswa',
            'bulan',
            'tahun',
            'tahunAjaran',
            'semester'
        ));
    }
}
