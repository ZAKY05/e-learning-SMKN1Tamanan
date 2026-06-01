<?php

namespace App\Http\Controllers;

use App\Services\FcmService;
use App\Models\Mapel;
use App\Models\Presensi;
use App\Models\DetailPresensi;
use App\Models\Student;
use App\Models\JadwalPelajaran;
use App\Models\BankLokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PresensiGuruController extends Controller
{
    public function index(Request $request)
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            return redirect()->route('guru.dashboard')->with('error', 'Akun Anda belum terhubung dengan data guru.');
        }

        // Ambil kelas unik yang diajar guru ini dari jadwal_pelajaran
        $jadwalItems = JadwalPelajaran::where('guru_id', $guru->id_guru)
            ->with(['kelas.jurusan', 'mapel'])
            ->select('kelas_id', 'mapel_id')
            ->distinct()
            ->get();

        $kelasMapel = [];
        foreach ($jadwalItems as $item) {
            $key = $item->kelas_id . '-' . $item->mapel_id;
            if (!isset($kelasMapel[$key])) {
                $kelasMapel[$key] = [
                    'kelas' => $item->kelas,
                    'mapel' => $item->mapel,
                    'kelas_id' => $item->kelas_id,
                    'mapel_id' => $item->mapel_id,
                ];
            }
        }

        // Ambil data bank lokasi
        $lokasi = BankLokasi::all();

        // Ambil presensi hari ini
        $presensiHariIni = Presensi::where('guru_id', $guru->id_guru)
            ->whereDate('tanggal', now()->toDateString())
            ->with(['kelas', 'mapel', 'lokasi'])
            ->orderBy('jam_mulai', 'desc')
            ->get();

        // Hitung status waktu untuk setiap presensi
        $now = Carbon::now();
        foreach ($presensiHariIni as $presensi) {
            $jamSelesai = Carbon::parse($presensi->tanggal . ' ' . $presensi->jam_selesai);
            $batasTerlambat = $jamSelesai->copy()->addMinutes(30);

            if ($presensi->status === 'aktif') {
                if ($now->lte($jamSelesai)) {
                    $presensi->fase_waktu = 'normal'; // Masih dalam jam presensi normal
                } elseif ($now->lte($batasTerlambat)) {
                    $presensi->fase_waktu = 'terlambat'; // Dalam masa toleransi 30 menit
                } else {
                    $presensi->fase_waktu = 'expired'; // Sudah lewat masa toleransi
                }
            } else {
                $presensi->fase_waktu = 'selesai';
            }
        }

        return view('Guru.pages.presensi.index', compact('kelasMapel', 'lokasi', 'presensiHariIni'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_mapel' => 'required',
            'lokasi_id' => 'required|exists:bank_lokasi,id',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $guru = Auth::user()->guru;
        
        $exploded = explode('-', $request->kelas_mapel);
        if (count($exploded) !== 2) {
            return back()->with('error', 'Pilihan kelas dan mapel tidak valid.');
        }
        
        list($kelas_id, $mapel_id) = $exploded;

        $qrCode = Str::random(32);

        Presensi::create([
            'guru_id' => $guru->id_guru,
            'mapel_id' => $mapel_id,
            'kelas_id' => $kelas_id,
            'lokasi_id' => $request->lokasi_id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'qr_code' => $qrCode,
            'keterangan' => $request->keterangan,
            'status' => 'aktif',
        ]);
        $presensi = Presensi::latest()->first(); // Ambil presensi yang baru saja dibuat
        [$kelas_id, $mapel_id] = explode('-', $request->kelas_mapel);
        $mapel = \App\Models\Mapel::find($mapel_id);
        $siswaIds = \App\Models\Student::with('user')
            ->where('kelas_id', $kelas_id)
            ->get()
            ->map(fn($siswa) => $siswa->user?->id)
            ->filter()
            ->values()
            ->toArray();

        FcmService::kirimKeBanyakUser(
            $siswaIds,
            'presensi',
            'Presensi Dibuka',
            'Guru membuka presensi untuk ' . ($mapel->nama_mapel ?? '') . ' - Hari ini',
            ['presensi_id' => (string)$presensi->id_presensi]
        );

        return redirect()->route('guru.presensi.index')->with('success', 'Presensi berhasil dibuka.');
    }

    public function show($id)
    {
        $guru = Auth::user()->guru;
        $presensi = Presensi::where('guru_id', $guru->id_guru)
            ->with(['kelas.jurusan', 'mapel', 'lokasi', 'detailPresensi.siswa'])
            ->findOrFail($id);

        // Ambil semua siswa di kelas ini
        $semuaSiswa = Student::where('kelas_id', $presensi->kelas_id)
            ->orderBy('nama', 'asc')
            ->get();

        // Ambil ID siswa yang sudah absen
        $siswaHadirIds = $presensi->detailPresensi->pluck('siswa_id')->toArray();

        // Siswa yang belum absen
        $siswaBelumAbsen = $semuaSiswa->filter(function ($siswa) use ($siswaHadirIds) {
            return !in_array($siswa->id_siswa, $siswaHadirIds);
        });

        // Siswa yang sudah absen (dengan detail status)
        $siswaSudahAbsen = $presensi->detailPresensi->sortBy(function ($detail) {
            return $detail->siswa->nama ?? '';
        });

        // Hitung fase waktu
        $now = Carbon::now();
        $jamSelesai = Carbon::parse($presensi->tanggal . ' ' . $presensi->jam_selesai);
        $batasTerlambat = $jamSelesai->copy()->addMinutes(30);

        if ($presensi->status === 'aktif') {
            if ($now->lte($jamSelesai)) {
                $faseWaktu = 'normal';
            } elseif ($now->lte($batasTerlambat)) {
                $faseWaktu = 'terlambat';
            } else {
                $faseWaktu = 'expired';
            }
        } else {
            $faseWaktu = 'selesai';
        }

        $sisaWaktuTerlambat = null;
        if ($faseWaktu === 'terlambat') {
            $sisaWaktuTerlambat = $now->diffInMinutes($batasTerlambat);
        }

        // Statistik
        $totalSiswa = $semuaSiswa->count();
        $totalHadir = $presensi->detailPresensi->where('status_kehadiran', 'hadir')->count();
        $totalTerlambat = $presensi->detailPresensi->where('status_kehadiran', 'terlambat')->count();
        $totalSakit = $presensi->detailPresensi->where('status_kehadiran', 'sakit')->count();
        $totalIzin = $presensi->detailPresensi->where('status_kehadiran', 'izin')->count();
        $totalAlpha = $presensi->detailPresensi->where('status_kehadiran', 'alpha')->count();
        $totalBelumAbsen = $siswaBelumAbsen->count();

        return view('Guru.pages.presensi.show', compact(
            'presensi',
            'semuaSiswa',
            'siswaBelumAbsen',
            'siswaSudahAbsen',
            'faseWaktu',
            'sisaWaktuTerlambat',
            'batasTerlambat',
            'totalSiswa',
            'totalHadir',
            'totalTerlambat',
            'totalSakit',
            'totalIzin',
            'totalAlpha',
            'totalBelumAbsen'
        ));
    }

    public function close($id)
    {
        $guru = Auth::user()->guru;
        $presensi = Presensi::where('guru_id', $guru->id_guru)->findOrFail($id);
        
        $presensi->update(['status' => 'selesai']);

        return redirect()->route('guru.presensi.index')->with('success', 'Presensi berhasil ditutup.');
    }

    /**
     * Update status kehadiran siswa yang belum absen (alpha/sakit/izin)
     */
    public function updateStatusSiswa(Request $request, $id)
    {
        $guru = Auth::user()->guru;
        $presensi = Presensi::where('guru_id', $guru->id_guru)->findOrFail($id);

        $request->validate([
            'siswa' => 'required|array',
            'siswa.*.id_siswa' => 'required|exists:student,id_siswa',
            'siswa.*.status' => 'required|in:alpha,sakit,izin',
            'siswa.*.keterangan' => 'nullable|string|max:255',
        ]);

        foreach ($request->siswa as $data) {
            DetailPresensi::updateOrCreate(
                [
                    'presensi_id' => $presensi->id_presensi,
                    'siswa_id' => $data['id_siswa'],
                ],
                [
                    'waktu_presensi' => null,
                    'status_kehadiran' => $data['status'],
                    'keterangan' => $data['keterangan'] ?? null,
                ]
            );
        }

        return redirect()->route('guru.presensi.show', $id)
            ->with('success', 'Status kehadiran siswa berhasil diperbarui.');
    }

    /**
     * Update status kehadiran individual siswa
     */
    public function updateStatusSingle(Request $request, $id, $siswaId)
    {
        $guru = Auth::user()->guru;
        $presensi = Presensi::where('guru_id', $guru->id_guru)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:hadir,terlambat,alpha,sakit,izin',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DetailPresensi::updateOrCreate(
            [
                'presensi_id' => $presensi->id_presensi,
                'siswa_id' => $siswaId,
            ],
            [
                'waktu_presensi' => in_array($request->status, ['hadir', 'terlambat']) ? now()->format('H:i:s') : null,
                'status_kehadiran' => $request->status,
                'keterangan' => $request->keterangan ?? null,
            ]
        );

        return redirect()->route('guru.presensi.show', $id)
            ->with('success', 'Status siswa berhasil diubah.');
    }
}