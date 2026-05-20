<?php
// app/Http/Controllers/Guru/RekapUtsUasController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\JadwalPelajaran;
use App\Models\Kuis;
use App\Models\HasilKuis;
use App\Models\Student;

class RekapUtsUasController extends Controller
{
    private function getGuru()
    {
        $user = Auth::user();
        return Guru::where('id_guru', $user->guru_id)->first();
    }

    private function getKelasList($guru, $tahunAjaran, $semester)
    {
        $query = JadwalPelajaran::where('guru_id', $guru->id_guru)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester);

        $jadwal = $query->with(['kelas.jurusan'])
            ->select('kelas_id')
            ->distinct()
            ->get();

        return $jadwal->pluck('kelas');
    }

    private function getMapelListByKelas($guru, $kelasId, $tahunAjaran, $semester)
    {
        $query = JadwalPelajaran::where('guru_id', $guru->id_guru)
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester);

        $jadwal = $query->with('mapel')
            ->select('mapel_id')
            ->distinct()
            ->get();

        return $jadwal->pluck('mapel');
    }

    public function index(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan');
        }

        $tahunAjaran = $request->get('tahun_ajaran', '2024/2025');
        $semester = $request->get('semester', 'ganjil');
        $jenis = $request->get('jenis', 'uts'); // uts atau uas

        $kelasList = $this->getKelasList($guru, $tahunAjaran, $semester);
        $kelasId = $request->get('kelas_id');

        $mapelList = collect();
        if ($kelasId) {
            $mapelList = $this->getMapelListByKelas($guru, $kelasId, $tahunAjaran, $semester);
        }

        $mapelId = $request->get('mapel_id');

        $statistik = (object)[
            'rata_nilai' => 0,
            'tertinggi' => 0,
            'terendah' => 0,
            'jumlah_siswa' => 0,
        ];

        $rekapPerSiswa = [];

        if ($kelasId && $mapelId) {
            // Cari kuis UTS atau UAS untuk kelas & mapel ini
            $kuis = Kuis::where('guru_id', $guru->id_guru)
                ->where('kelas_id', $kelasId)
                ->where('mapel_id', $mapelId)
                ->where('tipe', $jenis)
                ->first();

            if ($kuis) {
                $siswaList = Student::where('kelas_id', $kelasId)->orderBy('nama')->get();

                // Ambil hasil kuis
                $hasilList = HasilKuis::where('kuis_id', $kuis->id_kuis)
                    ->get()
                    ->keyBy('siswa_id');

                foreach ($siswaList as $siswa) {
                    $hasil = $hasilList->get($siswa->id_siswa);

                    $rekapPerSiswa[] = (object)[
                        'siswa' => $siswa,
                        'nilai' => $hasil ? round($hasil->nilai, 1) : null,
                        'status' => $hasil ? $hasil->status : 'belum',
                        'waktu_selesai' => $hasil ? $hasil->waktu_selesai : null,
                    ];
                }

                $nilaiList = collect($rekapPerSiswa)->pluck('nilai')->filter();
                $statistik->rata_nilai = $nilaiList->avg() ?? 0;
                $statistik->tertinggi = $nilaiList->max() ?? 0;
                $statistik->terendah = $nilaiList->min() ?? 0;
                $statistik->jumlah_siswa = $nilaiList->count();
            }
        }

        return view('Guru.pages.rekap.uts_uas', compact(
            'kelasList',
            'mapelList',
            'kelasId',
            'mapelId',
            'tahunAjaran',
            'semester',
            'jenis',
            'statistik',
            'rekapPerSiswa'
        ));
    }
}
