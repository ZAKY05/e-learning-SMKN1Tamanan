<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\Materi;
use App\Services\FcmService;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    public function index(Request $request)
    {
        $guru = Auth::user()->guru;

        if (!$guru) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Akun Anda belum terhubung dengan data guru.');
        }

        // Ambil semua tugas
        $tugasList = Tugas::with(['kelas', 'mapel', 'materi'])
            ->where('guru_id', $guru->id_guru)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Guru.pages.tugas-index', compact('tugasList', 'guru'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id'         => 'required|exists:kelas,id_kelas',
            'mapel_id'         => 'required|exists:mapel,id_mapel',
            'materi_id'        => 'required|exists:materi,id_materi',
            'judul_tugas'      => 'required|string|max:255',
            'deskripsi'        => 'required|string',
            'tanggal_mulai'    => 'required|date',
            'tanggal_deadline' => 'required|date|after_or_equal:tanggal_mulai',
            'file_tugas'       => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,png',
        ]);

        $guru = Auth::user()->guru;

        $data = [
            'guru_id'          => $guru->id_guru,
            'kelas_id'         => $request->kelas_id,
            'mapel_id'         => $request->mapel_id,
            'materi_id'        => $request->materi_id,
            'judul_tugas'      => $request->judul_tugas,
            'deskripsi'        => $request->deskripsi,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_deadline' => $request->tanggal_deadline,
            'status'           => 'published',
            'bobot_nilai'      => 100,
        ];

        if ($request->hasFile('file_tugas')) {
            $file = $request->file('file_tugas');
            $path = $file->store('tugas', 'public');
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
        }

        Tugas::create($data);
        $tugas = Tugas::latest()->first(); // Ambil tugas yang baru saja dibuat
        $mapel = Mapel::find($request->mapel_id);
        $siswaIds = \App\Models\Student::with('user')
            ->where('kelas_id', $request->kelas_id)
            ->get()
            ->map(fn($siswa) => $siswa->user?->id)
            ->filter()
            ->values()
            ->toArray();

        FcmService::kirimKeBanyakUser(
            $siswaIds,
            'tugas',
            'Tugas Baru - ' . ($mapel->nama_mapel ?? ''),
            'Guru telah mengupload tugas baru: "' . $request->judul_tugas . '"',
            ['tugas_id' => (string)$tugas->id_tugas]
        );

        return redirect()
            ->back()
            ->with('success', "Tugas berhasil ditambahkan!");
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'judul_tugas'      => 'required|string|max:255',
            'deskripsi'        => 'required|string',
            'tanggal_mulai'    => 'required|date',
            'tanggal_deadline' => 'required|date|after_or_equal:tanggal_mulai',
            'file_tugas'       => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,png',
        ]);

        $tugas = Tugas::findOrFail($id);

        $tugas->judul_tugas = $request->judul_tugas;
        $tugas->deskripsi = $request->deskripsi;
        $tugas->tanggal_mulai = $request->tanggal_mulai;
        $tugas->tanggal_deadline = $request->tanggal_deadline;

        if ($request->hasFile('file_tugas')) {
            if ($tugas->file_path && Storage::disk('public')->exists($tugas->file_path)) {
                Storage::disk('public')->delete($tugas->file_path);
            }

            $file = $request->file('file_tugas');
            $tugas->file_path = $file->store('tugas', 'public');
            $tugas->file_name = $file->getClientOriginalName();
        }

        $tugas->save();

        return redirect()
            ->back()
            ->with('success', "Tugas berhasil diupdate!");
    }

    public function destroy($id)
    {
        $tugas = Tugas::findOrFail($id);
        $kelasId = $tugas->kelas_id;
        $mapelId = $tugas->mapel_id;

        if ($tugas->file_path && Storage::disk('public')->exists($tugas->file_path)) {
            Storage::disk('public')->delete($tugas->file_path);
        }

        $tugas->delete();

        return redirect()
            ->back()
            ->with('success', "Tugas berhasil dihapus!");
    }

    public function pengumpulan($id)
    {
        $guru = Auth::user()->guru;
        $tugas = Tugas::with(['kelas', 'mapel', 'materi'])->findOrFail($id);

        if ($tugas->guru_id !== $guru->id_guru) {
            return redirect()->route('guru.tugas.index')->with('error', 'Akses ditolak.');
        }

        // Ambil semua siswa di kelas tersebut
        $siswaKelas = \App\Models\Student::where('kelas_id', $tugas->kelas_id)->orderBy('nama')->get();

        // Ambil data pengumpulan
        $pengumpulanList = \App\Models\PengumpulanTugas::with('siswa')
            ->where('tugas_id', $id)
            ->get()
            ->keyBy('siswa_id');

        return view('Guru.pages.tugas-pengumpulan', compact('tugas', 'siswaKelas', 'pengumpulanList'));
    }

    public function nilai(Request $request, $id)
    {
        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'catatan_guru' => 'nullable|string'
        ]);

        $pengumpulan = \App\Models\PengumpulanTugas::findOrFail($id);
        $pengumpulan->nilai = $request->nilai;
        $pengumpulan->catatan_guru = $request->catatan_guru;
        $pengumpulan->status = 'dinilai';
        $pengumpulan->save();

        return redirect()->back()->with('success', 'Nilai berhasil disimpan.');
    }
}