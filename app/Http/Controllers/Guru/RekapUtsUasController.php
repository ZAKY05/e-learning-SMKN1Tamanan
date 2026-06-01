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
use Carbon\Carbon;

class RekapUtsUasController extends Controller
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

    private function getJenisLabel($jenis)
    {
        $labels = [
            'kuis_harian' => 'Kuis Harian',
            'uts' => 'UTS',
            'uas' => 'UAS',
        ];
        return $labels[$jenis] ?? strtoupper($jenis);
    }

    /**
     * Hitung data rekap kuis/UTS/UAS per siswa
     */
    private function getRekapData($guru, $kelasId, $mapelId, $jenis)
    {
        $statistik = (object)[
            'rata_nilai' => 0,
            'tertinggi' => 0,
            'terendah' => 0,
            'jumlah_siswa' => 0,
        ];

        $rekapPerSiswa = [];

        // Cari kuis berdasarkan tipe untuk kelas & mapel ini
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

        return compact('statistik', 'rekapPerSiswa');
    }

    public function index(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan');
        }

        $jenis = $request->get('jenis', 'kuis_harian');

        $kelasList = $this->getKelasList($guru);
        $kelasId = $request->get('kelas_id');

        $mapelList = collect();
        if ($kelasId) {
            $mapelList = $this->getMapelListByKelas($guru, $kelasId);
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
            $data = $this->getRekapData($guru, $kelasId, $mapelId, $jenis);
            $statistik = $data['statistik'];
            $rekapPerSiswa = $data['rekapPerSiswa'];
        }

        return view('Guru.pages.rekap.uts_uas', compact(
            'kelasList',
            'mapelList',
            'kelasId',
            'mapelId',
            'jenis',
            'statistik',
            'rekapPerSiswa'
        ));
    }

    /**
     * Export rekap kuis/UTS/UAS ke Excel
     */
    public function exportExcel(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan');
        }

        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');
        $jenis = $request->get('jenis', 'kuis_harian');

        if (!$kelasId || !$mapelId) {
            return redirect()->back()->with('error', 'Pilih kelas dan mapel terlebih dahulu.');
        }

        $data = $this->getRekapData($guru, $kelasId, $mapelId, $jenis);
        $rekapPerSiswa = $data['rekapPerSiswa'];
        $statistik = $data['statistik'];

        $kelas = Kelas::with('jurusan')->find($kelasId);
        $mapel = Mapel::find($mapelId);
        $jenisLabel = $this->getJenisLabel($jenis);

        // Build Excel rows
        $rows = [];
        $rows[] = ['<style font-size="14" color="1B365D"><b>REKAP ' . strtoupper($jenisLabel) . ' SISWA</b></style>'];
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
            $headerStyle . 'Nilai' . $headerStyleEnd,
            $headerStyle . 'Status' . $headerStyleEnd,
            $headerStyle . 'Waktu Selesai' . $headerStyleEnd,
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
                $cellCenter . ($item->nilai !== null ? $item->nilai : '-') . $cellCenterEnd,
                $cellCenter . ($item->nilai !== null ? 'Selesai' : 'Belum Mengerjakan') . $cellCenterEnd,
                $cellCenter . ($item->waktu_selesai ? Carbon::parse($item->waktu_selesai)->format('d M Y, H:i') : '-') . $cellCenterEnd,
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
            $footerCenter . number_format($statistik->rata_nilai, 1) . $footerCenterEnd,
            $footerCenter . $statistik->jumlah_siswa . ' siswa dinilai' . $footerCenterEnd,
            $footerEmpty,
        ];

        $filename = 'Rekap_' . $jenisLabel . '_' . str_replace(' ', '_', ($kelas->tingkat ?? '') . '_' . ($kelas->jurusan->nama_jurusan ?? '') . '_' . ($kelas->golongan ?? '')) . '_' . ($mapel->nama_mapel ?? '') . '.xlsx';

        $xlsx = \Shuchkin\SimpleXLSXGen::create();
        $xlsx->addSheet($rows, 'Rekap ' . $jenisLabel);

        // Set column widths
        $xlsx->setColWidth('A', 6);
        $xlsx->setColWidth('B', 15);
        $xlsx->setColWidth('C', 30);
        $xlsx->setColWidth('D', 12);
        $xlsx->setColWidth('E', 20);
        $xlsx->setColWidth('F', 22);

        // Merge headers
        $xlsx->mergeCells('A1:F1');
        $xlsx->mergeCells('A2:F2');
        $xlsx->mergeCells('A3:F3');

        $xlsx->downloadAs($filename);
        exit;
    }
}
