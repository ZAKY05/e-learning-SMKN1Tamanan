<?php
// app/Http/Controllers/Guru/RekapAbsensiController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\JadwalPelajaran;
use App\Models\Presensi;
use App\Models\DetailPresensi;
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
    private function getKelasList($guru)
    {
        $jadwal = JadwalPelajaran::where('guru_id', $guru->id_guru)
            ->with(['kelas.jurusan'])
            ->select('kelas_id')
            ->distinct()
            ->get();

        return $jadwal->pluck('kelas')->filter();
    }

    /**
     * Ambil daftar mapel yang diajar guru di kelas tertentu
     */
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

    /**
     * Hitung data rekap presensi per siswa
     */
    private function getRekapData($guru, $kelasId, $mapelId, $bulan, $tahun)
    {
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

        // Ambil semua siswa di kelas ini
        $siswaList = Student::where('kelas_id', $kelasId)->orderBy('nama')->get();

        // Ambil semua presensi guru ini untuk kelas & mapel di bulan/tahun tertentu
        $presensiList = Presensi::where('guru_id', $guru->id_guru)
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->pluck('id_presensi');

        $statistik->total_pertemuan = $presensiList->count();

        // Ambil semua detail presensi untuk presensi-presensi tersebut
        $detailList = DetailPresensi::whereIn('presensi_id', $presensiList)->get();

        foreach ($siswaList as $siswa) {
            $detailSiswa = $detailList->where('siswa_id', $siswa->id_siswa);

            $hadir = $detailSiswa->where('status_kehadiran', 'hadir')->count();
            $terlambat = $detailSiswa->where('status_kehadiran', 'terlambat')->count();
            $izin = $detailSiswa->where('status_kehadiran', 'izin')->count();
            $sakit = $detailSiswa->where('status_kehadiran', 'sakit')->count();
            $alpha = $detailSiswa->where('status_kehadiran', 'alpha')->count();

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

        if ($kelasId && $mapelId) {
            $data = $this->getRekapData($guru, $kelasId, $mapelId, $bulan, $tahun);
            $statistik = $data['statistik'];
            $rekapPerSiswa = $data['rekapPerSiswa'];
        }

        return view('Guru.pages.rekap.absensi', compact(
            'kelasList',
            'mapelList',
            'kelasId',
            'mapelId',
            'statistik',
            'rekapPerSiswa',
            'bulan',
            'tahun'
        ));
    }

    /**
     * Export rekap presensi ke Excel
     */
    public function exportExcel(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) {
            return redirect()->back()->with('error', 'Data guru tidak ditemukan');
        }

        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        if (!$kelasId || !$mapelId) {
            return redirect()->back()->with('error', 'Pilih kelas dan mapel terlebih dahulu.');
        }

        $data = $this->getRekapData($guru, $kelasId, $mapelId, $bulan, $tahun);
        $rekapPerSiswa = $data['rekapPerSiswa'];
        $statistik = $data['statistik'];

        $kelas = Kelas::with('jurusan')->find($kelasId);
        $mapel = Mapel::find($mapelId);
        $namaBulan = Carbon::create()->month((int)$bulan)->translatedFormat('F');

        // Build Excel rows
        $rows = [];
        $rows[] = ['<style font-size="14" color="1B365D"><b>REKAP PRESENSI SISWA</b></style>'];
        $rows[] = ['<style font-size="10" color="4B5563"><b>Kelas:</b> ' . ($kelas->tingkat ?? '') . ' ' . ($kelas->jurusan->nama_jurusan ?? '') . ' ' . ($kelas->golongan ?? '') . '</style>'];
        $rows[] = ['<style font-size="10" color="4B5563"><b>Mata Pelajaran:</b> ' . ($mapel->nama_mapel ?? '') . '</style>'];
        $rows[] = ['<style font-size="10" color="4B5563"><b>Periode:</b> ' . $namaBulan . ' ' . $tahun . '</style>'];
        $rows[] = ['<style font-size="10" color="4B5563"><b>Total Pertemuan:</b> ' . $statistik->total_pertemuan . '</style>'];
        $rows[] = []; // empty row

        $headerStyle = '<style bgcolor="1E3A8A" color="FFFFFF" border="thin" height="25"><b><center>';
        $headerStyleEnd = '</center></b></style>';
        $headerLeftStyle = '<style bgcolor="1E3A8A" color="FFFFFF" border="thin" height="25"><b>';
        $headerLeftStyleEnd = '</b></style>';

        $rows[] = [
            $headerStyle . 'No' . $headerStyleEnd,
            $headerStyle . 'NIS' . $headerStyleEnd,
            $headerLeftStyle . 'Nama Siswa' . $headerLeftStyleEnd,
            $headerStyle . 'Hadir' . $headerStyleEnd,
            $headerStyle . 'Terlambat' . $headerStyleEnd,
            $headerStyle . 'Izin' . $headerStyleEnd,
            $headerStyle . 'Sakit' . $headerStyleEnd,
            $headerStyle . 'Alpha' . $headerStyleEnd,
            $headerStyle . 'Total Hadir' . $headerStyleEnd,
            $headerStyle . 'Kehadiran (%)' . $headerStyleEnd,
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
                $cellCenter . $item->hadir . $cellCenterEnd,
                $cellCenter . $item->terlambat . $cellCenterEnd,
                $cellCenter . $item->izin . $cellCenterEnd,
                $cellCenter . $item->sakit . $cellCenterEnd,
                $cellCenter . $item->alpha . $cellCenterEnd,
                $cellCenter . ($item->total_hadir . '/' . $item->total) . $cellCenterEnd,
                $cellCenter . ($item->persen . '%') . $cellCenterEnd,
            ];
        }

        // Footer
        $count = max(count($rekapPerSiswa), 1);
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
            $footerCenter . round($statistik->total_hadir / $count, 1) . $footerCenterEnd,
            $footerCenter . round($statistik->total_terlambat / $count, 1) . $footerCenterEnd,
            $footerCenter . round($statistik->total_izin / $count, 1) . $footerCenterEnd,
            $footerCenter . round($statistik->total_sakit / $count, 1) . $footerCenterEnd,
            $footerCenter . round($statistik->total_alpha / $count, 1) . $footerCenterEnd,
            $footerEmpty,
            $footerCenter . ($statistik->persen_kehadiran . '%') . $footerCenterEnd,
        ];

        $filename = 'Rekap_Presensi_' . str_replace(' ', '_', ($kelas->tingkat ?? '') . '_' . ($kelas->jurusan->nama_jurusan ?? '') . '_' . ($kelas->golongan ?? '')) . '_' . $namaBulan . '_' . $tahun . '.xlsx';

        $xlsx = \Shuchkin\SimpleXLSXGen::create();
        $xlsx->addSheet($rows, 'Rekap Presensi');
        
        // Set column widths
        $xlsx->setColWidth('A', 6);
        $xlsx->setColWidth('B', 15);
        $xlsx->setColWidth('C', 30);
        $xlsx->setColWidth('D', 10);
        $xlsx->setColWidth('E', 12);
        $xlsx->setColWidth('F', 10);
        $xlsx->setColWidth('G', 10);
        $xlsx->setColWidth('H', 10);
        $xlsx->setColWidth('I', 15);
        $xlsx->setColWidth('J', 18);

        // Merge title blocks
        $xlsx->mergeCells('A1:J1');
        $xlsx->mergeCells('A2:J2');
        $xlsx->mergeCells('A3:J3');
        $xlsx->mergeCells('A4:J4');
        $xlsx->mergeCells('A5:J5');

        $xlsx->downloadAs($filename);
        exit;
    }
}
