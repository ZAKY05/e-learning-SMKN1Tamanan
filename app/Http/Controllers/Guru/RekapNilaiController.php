<?php
// app/Http/Controllers/Guru/RekapNilaiController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\JadwalPelajaran;
use App\Models\Tugas;
use App\Models\PengumpulanTugas;
use App\Models\Student;

class RekapNilaiController extends Controller
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

    private function getPredikat($nilai)
    {
        if ($nilai >= 85) return 'A';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 40) return 'D';
        return 'E';
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

        $mapelList = collect();
        if ($kelasId) {
            $mapelList = $this->getMapelListByKelas($guru, $kelasId, $tahunAjaran, $semester);
        }

        $mapelId = $request->get('mapel_id');

        $statistik = (object)[
            'rata_tugas' => 0,
            'rata_akhir' => 0,
            'siswa_tuntas' => 0,
            'siswa_belum_tuntas' => 0,
            'nilai_tertinggi' => 0,
            'nilai_terendah' => 0,
        ];

        $rekapPerSiswa = [];

        if ($kelasId && $mapelId) {
            $siswaList = Student::where('kelas_id', $kelasId)->orderBy('nama')->get();

            // Ambil semua tugas untuk kelas & mapel ini
            $tugasList = Tugas::where('guru_id', $guru->id_guru)
                ->where('kelas_id', $kelasId)
                ->where('mapel_id', $mapelId)
                ->get();

            $tugasIds = $tugasList->pluck('id_tugas');

            foreach ($siswaList as $siswa) {
                $pengumpulan = PengumpulanTugas::whereIn('tugas_id', $tugasIds)
                    ->where('siswa_id', $siswa->id_siswa)
                    ->get();

                $nilaiList = $pengumpulan->whereNotNull('nilai')->pluck('nilai');
                $rataTugas = $nilaiList->avg() ?? 0;
                $nilaiAkhir = round($rataTugas, 1);
                $predikat = $this->getPredikat($nilaiAkhir);

                $rekapPerSiswa[] = (object)[
                    'siswa' => $siswa,
                    'tugas' => round($rataTugas, 1),
                    'jumlah_tugas' => $pengumpulan->count(),
                    'jumlah_dinilai' => $pengumpulan->whereNotNull('nilai')->count(),
                    'akhir' => $nilaiAkhir,
                    'predikat' => $predikat,
                ];
            }

            $nilaiAkhirs = collect($rekapPerSiswa)->pluck('akhir')->filter();
            $statistik->rata_tugas = collect($rekapPerSiswa)->avg('tugas') ?? 0;
            $statistik->rata_akhir = $nilaiAkhirs->avg() ?? 0;
            $statistik->siswa_tuntas = collect($rekapPerSiswa)->filter(fn($s) => $s->akhir >= 75)->count();
            $statistik->siswa_belum_tuntas = collect($rekapPerSiswa)->filter(fn($s) => $s->akhir < 75 && $s->akhir > 0)->count();
            $statistik->nilai_tertinggi = $nilaiAkhirs->max() ?? 0;
            $statistik->nilai_terendah = $nilaiAkhirs->min() ?? 0;
        }

        return view('Guru.pages.rekap.nilai', compact(
            'kelasList',
            'mapelList',
            'kelasId',
            'mapelId',
            'tahunAjaran',
            'semester',
            'statistik',
            'rekapPerSiswa'
        ));
    }
}
