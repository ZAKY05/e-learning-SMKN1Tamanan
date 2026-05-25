<?php

namespace App\Http\Controllers;

use App\Services\FcmService;
use App\Models\Kuis;
use App\Models\SoalKuis;
use App\Models\PilihanJawaban;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\HasilKuis;
use App\Models\JawabanSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KuisGuruController extends Controller
{
    public function index()
    {
        $guru_id = Auth::user()->guru->id_guru;
        $kuis = Kuis::with(['kelas', 'mapel', 'materi'])
                    ->where('guru_id', $guru_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
        return view('Guru.pages.kuis.index', compact('kuis'));
    }

    public function create()
    {
        $guru_id = Auth::user()->guru->id_guru;
        // Ambil kelas yang diajar oleh guru (sementara ambil semua atau relasi yang ada)
        $kelas = Kelas::all(); 
        $mapel = Mapel::all();
        $materi = Materi::where('guru_id', $guru_id)->get(); // jika materi ada guru_id
        
        return view('Guru.pages.kuis.create', compact('kelas', 'mapel', 'materi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_kuis' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'mapel_id' => 'required|exists:mapel,id_mapel',
            'tipe' => 'required|in:kuis_harian,uts,uas',
            'durasi_menit' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        $guru_id = Auth::user()->guru->id_guru;

        $kuis = Kuis::create([
            'guru_id' => $guru_id,
            'kelas_id' => $request->kelas_id,
            'mapel_id' => $request->mapel_id,
            'materi_id' => $request->materi_id,
            'judul_kuis' => $request->judul_kuis,
            'deskripsi' => $request->deskripsi,
            'tipe' => $request->tipe,
            'durasi_menit' => $request->durasi_menit,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'bobot_nilai' => $request->bobot_nilai ?? 100,
            'acak_soal' => $request->has('acak_soal'),
            'tampilkan_nilai' => $request->has('tampilkan_nilai'),
            'status' => $request->status ?? 'draft',
        ]);


        if (($request->status ?? 'draft') === 'published') {
            $mapel = \App\Models\Mapel::find($request->mapel_id);
            $siswaIds = \App\Models\Student::with('user')
                ->where('kelas_id', $request->kelas_id)
                ->get()
                ->map(fn($siswa) => $siswa->user?->id)
                ->filter()
                ->values()
                ->toArray();

            FcmService::kirimKeBanyakUser(
                $siswaIds,
                'kuis',
                'Kuis Tersedia - ' . ($mapel->nama_mapel ?? ''),
                $request->judul_kuis . ' sudah tersedia. Segera kerjakan!',
                ['kuis_id' => (string)$kuis->id_kuis]
            );
        }

        // Simpan soal jika ada yang dikirim dari halaman create
        if ($request->has('soal') && is_array($request->soal)) {
            foreach ($request->soal as $index => $dataSoal) {
                if (empty($dataSoal['pertanyaan'])) continue; // skip jika pertanyaan kosong

                $gambarPath = null;
                if (isset($dataSoal['gambar']) && $dataSoal['gambar']->isValid()) {
                    $gambarPath = $dataSoal['gambar']->store('kuis_images', 'public');
                }

                $soal = SoalKuis::create([
                    'kuis_id' => $kuis->id_kuis,
                    'pertanyaan' => $dataSoal['pertanyaan'],
                    'gambar' => $gambarPath,
                    'tipe_soal' => $dataSoal['tipe_soal'] ?? 'pilihan_ganda',
                    'poin' => 1, // poin diganti rumus rata-rata
                    'nomor_urut' => $index + 1,
                ]);

                if (($dataSoal['tipe_soal'] ?? 'pilihan_ganda') === 'pilihan_ganda') {
                    if (isset($dataSoal['pilihan']) && isset($dataSoal['kunci_jawaban'])) {
                        foreach ($dataSoal['pilihan'] as $huruf => $isi) {
                            if (!empty($isi) || (isset($dataSoal['gambar_pilihan'][$huruf]) && $dataSoal['gambar_pilihan'][$huruf]->isValid())) {
                                $gambarPilihanPath = null;
                                if (isset($dataSoal['gambar_pilihan'][$huruf]) && $dataSoal['gambar_pilihan'][$huruf]->isValid()) {
                                    $gambarPilihanPath = $dataSoal['gambar_pilihan'][$huruf]->store('kuis_images/pilihan', 'public');
                                }

                                PilihanJawaban::create([
                                    'soal_id' => $soal->id_soal,
                                    'pilihan' => $huruf,
                                    'isi_pilihan' => $isi ?? '',
                                    'gambar_pilihan' => $gambarPilihanPath,
                                    'is_correct' => ($dataSoal['kunci_jawaban'] === $huruf),
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->route('guru.kuis.index')->with('success', 'Kuis dan Soal berhasil dibuat');
    }

    public function show($id)
    {
        $kuis = Kuis::with(['soalKuis.pilihanJawaban', 'kelas', 'mapel'])->findOrFail($id);
        
        // Ensure the guru owns this quiz
        if ($kuis->guru_id !== Auth::user()->guru->id_guru) {
            abort(403, 'Unauthorized access');
        }

        return view('Guru.pages.kuis.show', compact('kuis'));
    }

    public function edit($id)
    {
        $kuis = Kuis::findOrFail($id);
        
        if ($kuis->guru_id !== Auth::user()->guru->id_guru) {
            abort(403, 'Unauthorized access');
        }

        $guru_id = Auth::user()->guru->id_guru;
        $kelas = Kelas::all(); 
        $mapel = Mapel::all();
        $materi = Materi::where('guru_id', $guru_id)->get();

        return view('Guru.pages.kuis.edit', compact('kuis', 'kelas', 'mapel', 'materi'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'judul_kuis' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id_kelas',
            'mapel_id' => 'required|exists:mapel,id_mapel',
            'tipe' => 'required|in:kuis_harian,uts,uas',
            'durasi_menit' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        $kuis = Kuis::findOrFail($id);
        
        if ($kuis->guru_id !== Auth::user()->guru->id_guru) {
            abort(403, 'Unauthorized access');
        }

        $kuis->update([
            'kelas_id' => $request->kelas_id,
            'mapel_id' => $request->mapel_id,
            'materi_id' => $request->materi_id,
            'judul_kuis' => $request->judul_kuis,
            'deskripsi' => $request->deskripsi,
            'tipe' => $request->tipe,
            'durasi_menit' => $request->durasi_menit,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'bobot_nilai' => $request->bobot_nilai ?? 100,
            'acak_soal' => $request->has('acak_soal'),
            'tampilkan_nilai' => $request->has('tampilkan_nilai'),
            'status' => $request->status ?? 'draft',
        ]);

        return redirect()->route('guru.kuis.index')->with('success', 'Kuis berhasil diupdate');
    }

    public function destroy($id)
    {
        $kuis = Kuis::findOrFail($id);
        
        if ($kuis->guru_id !== Auth::user()->guru->id_guru) {
            abort(403, 'Unauthorized access');
        }

        $kuis->delete();
        return redirect()->route('guru.kuis.index')->with('success', 'Kuis berhasil dihapus');
    }

    // --- Soal Management ---
    
    public function storeSoal(Request $request, $kuis_id)
    {
        $request->validate([
            'pertanyaan' => 'required|string',
            'tipe_soal' => 'required|in:pilihan_ganda,essay',
            'poin' => 'required|integer|min:1',
            'nomor_urut' => 'required|integer|min:1',
        ]);

        $kuis = Kuis::findOrFail($kuis_id);
        if ($kuis->guru_id !== Auth::user()->guru->id_guru) abort(403);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('kuis_images', 'public');
        }

        $soal = SoalKuis::create([
            'kuis_id' => $kuis_id,
            'pertanyaan' => $request->pertanyaan,
            'gambar' => $gambarPath,
            'tipe_soal' => $request->tipe_soal,
            'poin' => 1, // poin diganti rumus rata-rata
            'nomor_urut' => $request->nomor_urut,
        ]);

        if ($request->tipe_soal === 'pilihan_ganda') {
            if ($request->has('pilihan') && $request->has('kunci_jawaban')) {
                foreach ($request->pilihan as $huruf => $isi) {
                    $hasGambar = $request->hasFile("gambar_pilihan.{$huruf}");
                    
                    if (!empty($isi) || $hasGambar) {
                        $gambarPilihanPath = null;
                        if ($hasGambar) {
                            $gambarPilihanPath = $request->file("gambar_pilihan.{$huruf}")->store('kuis_images/pilihan', 'public');
                        }

                        PilihanJawaban::create([
                            'soal_id' => $soal->id_soal,
                            'pilihan' => $huruf,
                            'isi_pilihan' => $isi ?? '',
                            'gambar_pilihan' => $gambarPilihanPath,
                            'is_correct' => ($request->kunci_jawaban === $huruf),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('guru.kuis.show', $kuis_id)->with('success', 'Soal berhasil ditambahkan');
    }

    public function destroySoal($kuis_id, $soal_id)
    {
        $kuis = Kuis::findOrFail($kuis_id);
        if ($kuis->guru_id !== Auth::user()->guru->id_guru) abort(403);

        $soal = SoalKuis::where('kuis_id', $kuis_id)->findOrFail($soal_id);
        $soal->delete();

        return redirect()->route('guru.kuis.show', $kuis_id)->with('success', 'Soal berhasil dihapus');
    }

    // --- Hasil & Penilaian ---

    public function hasil($id)
    {
        $kuis = Kuis::findOrFail($id);
        if ($kuis->guru_id !== Auth::user()->guru->id_guru) abort(403);

        $hasil = HasilKuis::with(['siswa'])->where('kuis_id', $id)->orderBy('nilai', 'desc')->get();

        return view('Guru.pages.kuis.hasil', compact('kuis', 'hasil'));
    }

    public function review($kuis_id, $hasil_id)
    {
        $kuis = Kuis::findOrFail($kuis_id);
        if ($kuis->guru_id !== Auth::user()->guru->id_guru) abort(403);

        $hasil = HasilKuis::with(['siswa', 'jawabanSiswa.soal', 'jawabanSiswa.pilihan'])
            ->where('kuis_id', $kuis_id)
            ->findOrFail($hasil_id);

        return view('Guru.pages.kuis.review', compact('kuis', 'hasil'));
    }

    public function nilaiEssay(Request $request)
    {
        $request->validate([
            'jawaban_id' => 'required|exists:jawaban_siswa,id_jawaban',
            'is_correct' => 'required|boolean',
            'catatan' => 'nullable|string'
        ]);

        $jawaban = JawabanSiswa::with('hasil')->findOrFail($request->jawaban_id);
        
        // Update poin & catatan
        $jawaban->update([
            'poin' => $request->is_correct ? 1 : 0,
            'catatan_guru' => $request->catatan,
            'is_correct' => $request->is_correct
        ]);

        // Recalculate total score
        $hasil = $jawaban->hasil;
        
        // Jumlah soal kuis ini
        $totalSoal = SoalKuis::where('kuis_id', $hasil->kuis_id)->count();
        
        // Hitung total benar terbaru
        $totalBenar = JawabanSiswa::where('hasil_id', $hasil->id_hasil)
                                  ->where('is_correct', true)
                                  ->count();
        
        // Calculate final score using user formula: (Benar / Total Soal) * 100
        $nilaiAkhir = $totalSoal > 0 ? ($totalBenar / $totalSoal) * 100 : 0;
        
        // Update hasil
        $hasil->update([
            'jumlah_benar' => $totalBenar,
            'jumlah_salah' => $totalSoal - $totalBenar,
            'nilai' => $nilaiAkhir,
            'status' => 'dinilai'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil disimpan',
            'nilai_akhir' => round($nilaiAkhir, 2)
        ]);
    }
}