<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\JadwalPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MateriController extends Controller
{
    /**
     * Halaman daftar kelas yang diajar guru (dari jadwal_pelajaran)
     */
    public function index(Request $request)
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Akun Anda belum terhubung dengan data guru.');
        }

        $search = $request->input('search');

        // Ambil kelas unik yang diajar guru ini dari jadwal_pelajaran
        $kelasQuery = JadwalPelajaran::where('guru_id', $guru->id_guru)
            ->with(['kelas.jurusan', 'mapel'])
            ->select('kelas_id', 'mapel_id')
            ->distinct();

        $jadwalItems = $kelasQuery->get();

        // Group berdasarkan kelas_id + mapel_id
        $kelasMapel = [];
        foreach ($jadwalItems as $item) {
            $key = $item->kelas_id . '-' . $item->mapel_id;
            if (!isset($kelasMapel[$key])) {
                $kelasMapel[$key] = [
                    'kelas' => $item->kelas,
                    'mapel' => $item->mapel,
                ];
            }
        }

        // Filter search
        if ($search) {
            $search = strtolower($search);
            $kelasMapel = array_filter($kelasMapel, function ($item) use ($search) {
                $kelasNama = strtolower($item['kelas']->nama_kelas ?? '');
                $mapelNama = strtolower($item['mapel']->nama_mapel ?? '');
                return str_contains($kelasNama, $search) || str_contains($mapelNama, $search);
            });
        }

        return view('Guru.pages.materi-kelas', compact('kelasMapel', 'search', 'guru'));
    }

    /**
     * Halaman materi per kelas + mapel (tampilan card minggu 1-15)
     */
    public function show($kelasId, $mapelId)
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            return redirect()->route('guru.dashboard');
        }

        $kelas = Kelas::with('jurusan')->findOrFail($kelasId);
        $mapel = Mapel::findOrFail($mapelId);

        // Ambil semua materi untuk kelas+mapel ini oleh guru ini
        $materiList = Materi::where('guru_id', $guru->id_guru)
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->orderBy('minggu_ke')
            ->get()
            ->keyBy('minggu_ke');

        $mingguList = range(1, 15);

        return view('Guru.pages.materi-detail', compact(
            'kelas', 'mapel', 'guru', 'materiList', 'mingguList'
        ));
    }

    /**
     * Simpan materi baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas_id'      => 'required|exists:kelas,id_kelas',
            'mapel_id'      => 'required|exists:mapel,id_mapel',
            'minggu_ke'     => 'required|integer|min:1|max:15',
            'judul_materi'  => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'semester'      => 'required|in:ganjil,genap',
            'file_materi'   => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,png',
        ]);

        $guru = Auth::user()->guru;

        $data = [
            'guru_id'       => $guru->id_guru,
            'kelas_id'      => $request->kelas_id,
            'mapel_id'      => $request->mapel_id,
            'minggu_ke'     => $request->minggu_ke,
            'judul_materi'  => $request->judul_materi,
            'deskripsi'     => $request->deskripsi,
            'semester'      => $request->semester,
            'tanggal_upload' => now()->toDateString(),
            'status'        => 'published',
        ];

        // Handle file upload
        if ($request->hasFile('file_materi')) {
            $file = $request->file('file_materi');
            $path = $file->store('materi', 'public');

            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        Materi::create($data);

        return redirect()
            ->route('guru.materi.show', [$request->kelas_id, $request->mapel_id])
            ->with('success', "Materi Minggu ke-{$request->minggu_ke} berhasil ditambahkan!");
    }

    /**
     * Update materi
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'judul_materi'  => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'semester'      => 'required|in:ganjil,genap',
            'file_materi'   => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,png',
        ]);

        $materi = Materi::findOrFail($id);

        $materi->judul_materi = $request->judul_materi;
        $materi->deskripsi = $request->deskripsi;
        $materi->semester = $request->semester;

        if ($request->hasFile('file_materi')) {
            // Hapus file lama
            if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
                Storage::disk('public')->delete($materi->file_path);
            }

            $file = $request->file('file_materi');
            $materi->file_path = $file->store('materi', 'public');
            $materi->file_name = $file->getClientOriginalName();
            $materi->file_type = $file->getClientOriginalExtension();
            $materi->file_size = $file->getSize();
        }

        $materi->save();

        return redirect()
            ->route('guru.materi.show', [$materi->kelas_id, $materi->mapel_id])
            ->with('success', "Materi Minggu ke-{$materi->minggu_ke} berhasil diupdate!");
    }

    /**
     * Hapus materi
     */
    public function destroy($id)
    {
        $materi = Materi::findOrFail($id);
        $kelasId = $materi->kelas_id;
        $mapelId = $materi->mapel_id;
        $minggu = $materi->minggu_ke;

        if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
            Storage::disk('public')->delete($materi->file_path);
        }

        $materi->delete();

        return redirect()
            ->route('guru.materi.show', [$kelasId, $mapelId])
            ->with('success', "Materi Minggu ke-{$minggu} berhasil dihapus!");
    }
}
