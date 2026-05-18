<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kuis;
use App\Models\HasilKuis;
use App\Models\SoalKuis;
use App\Models\JawabanSiswa;
use App\Models\PilihanJawaban;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SiswaKuisController extends Controller
{
    /**
     * Menampilkan daftar kuis yang tersedia untuk siswa.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya untuk siswa.',
            ], 403);
        }

        $siswaId = $user->siswa->id_siswa;
        $kelasId = $user->siswa->kelas_id;

        // Ambil kuis yang ditugaskan ke kelas siswa
        $kuis = Kuis::with(['mapel', 'materi', 'guru'])
            ->where('kelas_id', $kelasId)
            ->where('status', 'aktif')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        // Cek status pengerjaan untuk setiap kuis
        $kuis->transform(function ($k) use ($siswaId) {
            $hasil = HasilKuis::where('kuis_id', $k->id_kuis)
                ->where('siswa_id', $siswaId)
                ->first();

            $k->status_pengerjaan = $hasil ? $hasil->status : 'belum_mulai';
            $k->nilai = $hasil ? $hasil->nilai : null;
            
            // Tambahkan URL gambar mapel jika perlu
            return $k;
        });

        return response()->json([
            'success' => true,
            'data' => $kuis
        ]);
    }

    /**
     * Menampilkan detail kuis sebelum mulai.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $kuis = Kuis::with(['mapel', 'guru'])->find($id);

        if (!$kuis) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }

        // Pastikan kuis untuk kelas siswa
        if ($kuis->kelas_id != $user->siswa->kelas_id) {
            return response()->json(['success' => false, 'message' => 'Kuis ini bukan untuk kelas Anda'], 403);
        }

        $siswaId = $user->siswa->id_siswa;
        $hasil = HasilKuis::where('kuis_id', $id)->where('siswa_id', $siswaId)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'kuis' => $kuis,
                'status_pengerjaan' => $hasil ? $hasil->status : 'belum_mulai',
                'hasil' => $hasil
            ]
        ]);
    }

    /**
     * Mulai mengerjakan kuis.
     */
    public function start(Request $request, $id)
    {
        $user = $request->user();
        
        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $kuis = Kuis::find($id);

        if (!$kuis) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }

        $siswaId = $user->siswa->id_siswa;

        // Cek apakah sudah pernah mulai
        $hasil = HasilKuis::where('kuis_id', $id)->where('siswa_id', $siswaId)->first();

        if ($hasil) {
            if ($hasil->status == 'selesai') {
                return response()->json(['success' => false, 'message' => 'Anda sudah menyelesaikan kuis ini'], 400);
            }
            // Sedang mengerjakan, kembalikan data sesi
            return response()->json([
                'success' => true,
                'message' => 'Melanjutkan kuis',
                'data' => [
                    'id_hasil' => $hasil->id_hasil,
                    'waktu_mulai' => $hasil->waktu_mulai,
                    'batas_waktu' => Carbon::parse($hasil->waktu_mulai)->addMinutes($kuis->durasi_menit)
                ]
            ]);
        }

        // Cek waktu mulai dan selesai kuis
        $now = Carbon::now();
        if ($kuis->tanggal_mulai && $now->lt($kuis->tanggal_mulai)) {
            return response()->json(['success' => false, 'message' => 'Kuis belum dimulai'], 400);
        }

        if ($kuis->tanggal_selesai && $now->gt($kuis->tanggal_selesai)) {
            return response()->json(['success' => false, 'message' => 'Kuis sudah ditutup'], 400);
        }

        // Buat sesi baru
        $hasilBaru = HasilKuis::create([
            'kuis_id' => $id,
            'siswa_id' => $siswaId,
            'waktu_mulai' => now(),
            'status' => 'sedang_mengerjakan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil memulai kuis',
            'data' => [
                'id_hasil' => $hasilBaru->id_hasil,
                'waktu_mulai' => $hasilBaru->waktu_mulai,
                'batas_waktu' => Carbon::parse($hasilBaru->waktu_mulai)->addMinutes($kuis->durasi_menit)
            ]
        ]);
    }

    /**
     * Mengambil daftar soal untuk kuis yang sedang dikerjakan.
     */
    public function getSoal(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $kuis = Kuis::find($id);
        if (!$kuis) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }

        $siswaId = $user->siswa->id_siswa;
        $hasil = HasilKuis::where('kuis_id', $id)->where('siswa_id', $siswaId)->first();

        if (!$hasil || $hasil->status != 'sedang_mengerjakan') {
            return response()->json(['success' => false, 'message' => 'Anda tidak sedang mengerjakan kuis ini'], 400);
        }

        // Ambil soal dan pilihan (sembunyikan is_correct)
        $soal = SoalKuis::with(['pilihanJawaban' => function($q) {
            $q->select('id_pilihan', 'soal_id', 'pilihan', 'isi_pilihan', 'gambar_pilihan');
        }])->where('kuis_id', $id);

        if ($kuis->acak_soal) {
            $soal = $soal->inRandomOrder()->get();
        } else {
            $soal = $soal->orderBy('nomor_urut', 'asc')->get();
        }

        // Tambahkan full URL untuk gambar soal & pilihan
        $soal->transform(function ($s) {
            if ($s->gambar) {
                $s->gambar_url = asset('storage/' . $s->gambar);
            }
            $s->pilihanJawaban->transform(function ($p) {
                if ($p->gambar_pilihan) {
                    $p->gambar_url = asset('storage/' . $p->gambar_pilihan);
                }
                return $p;
            });
            return $s;
        });

        // Ambil jawaban yang mungkin sudah tersimpan (jika resume)
        $jawabanTersimpan = JawabanSiswa::where('hasil_id', $hasil->id_hasil)
            ->get()
            ->keyBy('soal_id');

        return response()->json([
            'success' => true,
            'data' => [
                'soal' => $soal,
                'jawaban_tersimpan' => $jawabanTersimpan,
                'batas_waktu' => Carbon::parse($hasil->waktu_mulai)->addMinutes($kuis->durasi_menit)
            ]
        ]);
    }

    /**
     * Submit seluruh jawaban kuis.
     */
    public function submit(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'jawaban' => 'required|array', // Format: [soal_id => pilihan_id, soal_id => 'jawaban essay']
        ]);

        $kuis = Kuis::find($id);
        if (!$kuis) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }

        $siswaId = $user->siswa->id_siswa;
        $hasil = HasilKuis::where('kuis_id', $id)->where('siswa_id', $siswaId)->first();

        if (!$hasil) {
            return response()->json(['success' => false, 'message' => 'Sesi kuis tidak ditemukan'], 400);
        }

        if ($hasil->status == 'selesai') {
            return response()->json(['success' => false, 'message' => 'Anda sudah menyelesaikan kuis ini'], 400);
        }

        DB::beginTransaction();
        try {
            $jawaban = $request->jawaban;
            $jumlahBenar = 0;
            $jumlahSalah = 0;
            $tidakDijawab = 0;
            $totalPoin = 0;
            $adaEssay = false;

            $semuaSoal = SoalKuis::where('kuis_id', $id)->get();
            $totalSoal = $semuaSoal->count();
            
            if ($totalSoal == 0) {
                 return response()->json(['success' => false, 'message' => 'Kuis tidak memiliki soal'], 400);
            }

            foreach ($semuaSoal as $soal) {
                $jawabanSiswaInput = $jawaban[$soal->id_soal] ?? null;

                $jawabanRecord = JawabanSiswa::firstOrNew([
                    'hasil_id' => $hasil->id_hasil,
                    'soal_id' => $soal->id_soal,
                ]);

                if ($soal->tipe_soal == 'pilihan_ganda') {
                    if ($jawabanSiswaInput) {
                        $pilihan = PilihanJawaban::find($jawabanSiswaInput);
                        if ($pilihan) {
                            $jawabanRecord->pilihan_id = $pilihan->id_pilihan;
                            $jawabanRecord->is_correct = $pilihan->is_correct;
                            
                            if ($pilihan->is_correct) {
                                $jumlahBenar++;
                            } else {
                                $jumlahSalah++;
                            }
                        } else {
                            $tidakDijawab++;
                        }
                    } else {
                        $tidakDijawab++;
                    }
                } elseif ($soal->tipe_soal == 'essay') {
                    $adaEssay = true;
                    if ($jawabanSiswaInput) {
                        $jawabanRecord->jawaban_essay = $jawabanSiswaInput;
                        $jawabanRecord->is_correct = null; // Menunggu dinilai guru
                    } else {
                        $tidakDijawab++;
                        $jawabanRecord->is_correct = false; // Kosong = salah
                    }
                }

                $jawabanRecord->save();
            }

            // Hitung nilai (hanya untuk pilihan ganda, essay nunggu guru)
            // Nilai = (Jumlah Benar / Total Soal) * 100 
            // Jika ada essay, ini nilai sementara
            $nilaiAkhir = ($jumlahBenar / $totalSoal) * 100;

            $hasil->waktu_selesai = now();
            $hasil->jumlah_benar = $jumlahBenar;
            $hasil->jumlah_salah = $jumlahSalah;
            $hasil->tidak_dijawab = $tidakDijawab;
            $hasil->nilai = $nilaiAkhir;
            $hasil->status = 'selesai';
            $hasil->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengirimkan jawaban kuis',
                'data' => [
                    'nilai' => $kuis->tampilkan_nilai ? $nilaiAkhir : null,
                    'tampilkan_nilai' => $kuis->tampilkan_nilai,
                    'menunggu_penilaian' => $adaEssay
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ambil hasil kuis
     */
    public function result(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $kuis = Kuis::find($id);
        if (!$kuis) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }
        
        $siswaId = $user->siswa->id_siswa;
        $hasil = HasilKuis::where('kuis_id', $id)->where('siswa_id', $siswaId)->first();
        
        if (!$hasil) {
            return response()->json(['success' => false, 'message' => 'Hasil kuis tidak ditemukan'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'kuis' => [
                    'judul' => $kuis->judul_kuis,
                    'tampilkan_nilai' => $kuis->tampilkan_nilai,
                ],
                'hasil' => [
                    'nilai' => $kuis->tampilkan_nilai ? $hasil->nilai : null,
                    'waktu_mulai' => $hasil->waktu_mulai,
                    'waktu_selesai' => $hasil->waktu_selesai,
                    'jumlah_benar' => $kuis->tampilkan_nilai ? $hasil->jumlah_benar : null,
                    'jumlah_salah' => $kuis->tampilkan_nilai ? $hasil->jumlah_salah : null,
                    'tidak_dijawab' => $kuis->tampilkan_nilai ? $hasil->tidak_dijawab : null,
                ]
            ]
        ]);
    }
}
