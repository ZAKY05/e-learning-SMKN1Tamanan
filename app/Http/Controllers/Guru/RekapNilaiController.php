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

    private function getKelasList($guru)
    {
        $jadwal = JadwalPelajaran::where('guru_id', $guru->id_guru)
            ->with(['kelas.jurusan'])
            ->select('kelas_id')
            ->distinct()
            ->get();

        return $jadwal->pluck('kelas')->filter();
    }

    private function getMapelListByKelas($guru, $kelasId)
    {
        $jadwal = JadwalPelajaran::where('guru_id', $guru->id_guru)
            ->where('kelas_id', $kelasId)
            ->with('mapel')
            ->select('mapel_id')
            ->distinct()
            ->get();

        return $jadwal->pluck('mapel')->filter();
    }

    private function getPredikat($nilai)
    {
        if ($nilai >= 85) return 'A';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 40) return 'D';
        return 'E';
    }

    /**
     * Hitung data rekap nilai per siswa
     */
    private function getRekapData($guru, $kelasId, $mapelId)
    {
        $statistik = (object)[
            'rata_tugas' => 0,
            'rata_akhir' => 0,
            'siswa_tuntas' => 0,
            'siswa_belum_tuntas' => 0,
            'nilai_tertinggi' => 0,
            'nilai_terendah' => 0,
        ];

        $rekapPerSiswa = [];

        $siswaList = Student::where('kelas_id', $kelasId)->orderBy('nama')->get();

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

        return compact('statistik', 'rekapPerSiswa');
    }

    public function index(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan');
        }

        $kelasList = $this->getKelasList($guru);
        $kelasId = $request->get('kelas_id');

        $mapelList = collect();
        if ($kelasId) {
            $mapelList = $this->getMapelListByKelas($guru, $kelasId);
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
            $data = $this->getRekapData($guru, $kelasId, $mapelId);
            $statistik = $data['statistik'];
            $rekapPerSiswa = $data['rekapPerSiswa'];
        }

        return view('Guru.pages.rekap.nilai', compact(
            'kelasList',
            'mapelList',
            'kelasId',
            'mapelId',
            'statistik',
            'rekapPerSiswa'
        ));
    }

    /**
     * Export rekap nilai ke Excel
     */
    public function exportExcel(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan');
        }

        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');

        if (!$kelasId || !$mapelId) {
            return redirect()->back()->with('error', 'Pilih kelas dan mapel terlebih dahulu.');
        }

        $data = $this->getRekapData($guru, $kelasId, $mapelId);
        $rekapPerSiswa = $data['rekapPerSiswa'];
        $statistik = $data['statistik'];

        $kelas = Kelas::with('jurusan')->find($kelasId);
        $mapel = Mapel::find($mapelId);

        // Build Excel rows
        $rows = [];
        $rows[] = ['<style font-size="14" color="1B365D"><b>REKAP NILAI TUGAS SISWA</b></style>'];
        $rows[] = ['<style font-size="10" color="4B5563"><b>Kelas:</b> ' . ($kelas->tingkat ?? '') . ' ' . ($kelas->jurusan->nama_jurusan ?? '') . ' ' . ($kelas->golongan ?? '') . '</style>'];
        $rows[] = ['<style font-size="10" color="4B5563"><b>Mata Pelajaran:</b> ' . ($mapel->nama_mapel ?? '') . '</style>'];
        $rows[] = []; // empty row

        $headerStyle = '<style bgcolor="1E3A8A" color="FFFFFF" border="thin" height="25"><b><center>';
        $headerStyleEnd = '</center></b></style>';
        $headerLeftStyle = '<style bgcolor="1E3A8A" color="FFFFFF" border="thin" height="25"><b>';
        $headerLeftStyleEnd = '</b></style>';

        $rows[] = [
            $headerStyle . 'No' . $headerStyleEnd,
            $headerStyle . 'NIS' . $headerStyleEnd,
            $headerLeftStyle . 'Nama Siswa' . $headerLeftStyleEnd,
            $headerStyle . 'Jml Tugas' . $headerStyleEnd,
            $headerStyle . 'Dinilai' . $headerStyleEnd,
            $headerStyle . 'Rata-rata Tugas' . $headerStyleEnd,
            $headerStyle . 'Nilai Akhir' . $headerStyleEnd,
            $headerStyle . 'Predikat' . $headerStyleEnd,
        ];

        foreach ($rekapPerSiswa as $index => $item) {
            $bg = ($index % 2 === 0) ? 'F9FAFB' : 'FFFFFF';
            $cellCenter = '<style border="thin" bgcolor="' . $bg . '"><center>';
            $cellCenterEnd = '</center></style>';
            $cellLeft = '<style border="thin" bgcolor="' . $bg . '">';
            $cellLeftEnd = '</style>';

            $rows[] = [
                $cellCenter . ($index + 1) . $cellCenterEnd,
                $cellCenter . ($item->siswa->nis ?? '-') . $cellCenterEnd,
                $cellLeft . $item->siswa->nama . $cellLeftEnd,
                $cellCenter . $item->jumlah_tugas . $cellCenterEnd,
                $cellCenter . $item->jumlah_dinilai . $cellCenterEnd,
                $cellCenter . $item->tugas . $cellCenterEnd,
                $cellCenter . $item->akhir . $cellCenterEnd,
                $cellCenter . $item->predikat . $cellCenterEnd,
            ];
        }

        // Footer
        $footerCenter = '<style border="thin" bgcolor="F3F4F6"><b><center>';
        $footerCenterEnd = '</center></b></style>';
        $footerLeft = '<style border="thin" bgcolor="F3F4F6"><b>';
        $footerLeftEnd = '</b></style>';
        $footerEmpty = '<style border="thin" bgcolor="F3F4F6"></style>';

        $rows[] = []; // empty row
        $rows[] = [
            $footerEmpty,
            $footerEmpty,
            $footerLeft . 'Rata-rata Kelas' . $footerLeftEnd,
            $footerEmpty,
            $footerEmpty,
            $footerCenter . number_format($statistik->rata_tugas, 1) . $footerCenterEnd,
            $footerCenter . number_format($statistik->rata_akhir, 1) . $footerCenterEnd,
            $footerEmpty,
        ];

        $rows[] = [
            $footerEmpty,
            $footerEmpty,
            $footerLeft . 'Tuntas: ' . $statistik->siswa_tuntas . ' | Belum Tuntas: ' . $statistik->siswa_belum_tuntas . $footerLeftEnd,
            $footerEmpty,
            $footerEmpty,
            $footerEmpty,
            $footerEmpty,
            $footerEmpty,
        ];

        $filename = 'Rekap_Nilai_' . str_replace(' ', '_', ($kelas->tingkat ?? '') . '_' . ($kelas->jurusan->nama_jurusan ?? '') . '_' . ($kelas->golongan ?? '')) . '_' . ($mapel->nama_mapel ?? '') . '.xlsx';

        $xlsx = \Shuchkin\SimpleXLSXGen::create();
        $xlsx->addSheet($rows, 'Rekap Nilai');

        // Set column widths
        $xlsx->setColWidth('A', 6);
        $xlsx->setColWidth('B', 15);
        $xlsx->setColWidth('C', 30);
        $xlsx->setColWidth('D', 12);
        $xlsx->setColWidth('E', 10);
        $xlsx->setColWidth('F', 18);
        $xlsx->setColWidth('G', 15);
        $xlsx->setColWidth('H', 12);

        // Merge headers
        $xlsx->mergeCells('A1:H1');
        $xlsx->mergeCells('A2:H2');
        $xlsx->mergeCells('A3:H3');

        // Merge tuntas row
        $count = count($rekapPerSiswa);
        $xlsx->mergeCells('C' . ($count + 8) . ':H' . ($count + 8));

        $xlsx->downloadAs($filename);
        exit;
    }
}
