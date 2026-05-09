<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\JadwalPelajaran;
use App\Models\Banklokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        $lokasi = Banklokasi::all();

        // Ambil presensi hari ini
        $presensiHariIni = Presensi::where('guru_id', $guru->id_guru)
            ->whereDate('tanggal', now()->toDateString())
            ->with(['kelas', 'mapel', 'lokasi'])
            ->orderBy('jam_mulai', 'desc')
            ->get();

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

        return redirect()->route('guru.presensi.index')->with('success', 'Presensi berhasil dibuka.');
    }

    public function show($id)
    {
        $guru = Auth::user()->guru;
        $presensi = Presensi::where('guru_id', $guru->id_guru)
            ->with(['kelas.jurusan', 'mapel', 'lokasi'])
            ->findOrFail($id);

        return view('Guru.pages.presensi.show', compact('presensi'));
    }

    public function close($id)
    {
        $guru = Auth::user()->guru;
        $presensi = Presensi::where('guru_id', $guru->id_guru)->findOrFail($id);
        
        $presensi->update(['status' => 'selesai']);

        return redirect()->route('guru.presensi.index')->with('success', 'Presensi berhasil ditutup.');
    }
}
