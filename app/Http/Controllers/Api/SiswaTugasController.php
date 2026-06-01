<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use App\Models\PengumpulanTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiswaTugasController extends Controller
{
    /**
     * Mendapatkan daftar tugas untuk siswa (berdasarkan kelas_id siswa)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Validasi user adalah siswa
        if (!$user || $user->role !== 'siswa') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya untuk siswa.'
            ], 403);
        }

        // Load siswa data jika belum di-load
        if (!$user->siswa) {
            $user->load('siswa');
        }

        if (!$user->siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan. Hubungi admin.'
            ], 404);
        }

        $siswa = $user->siswa;

        // Validasi siswa punya kelas
        if (!$siswa->kelas_id) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Siswa belum di-assign ke kelas'
            ]);
        }

        try {
            // Ambil semua tugas untuk kelas siswa ini
            $tugas = Tugas::with(['mapel', 'guru', 'materi'])
                ->where('kelas_id', $siswa->kelas_id)
                ->where('status', 'published')  // Hanya tugas yang published
                ->orderBy('tanggal_deadline', 'asc')
                ->get();

            // Jika tidak ada tugas, return empty array
            if ($tugas->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Tidak ada tugas untuk kelas Anda'
                ]);
            }

            // Ambil status pengumpulan untuk masing-masing tugas
            $tugasIdList = $tugas->pluck('id_tugas')->toArray();
            
            $pengumpulan = PengumpulanTugas::where('siswa_id', $siswa->id_siswa)
                ->whereIn('tugas_id', $tugasIdList)
                ->get()
                ->keyBy('tugas_id');

            $tugasList = $tugas->map(function($t) use ($pengumpulan) {
                $isSubmitted = isset($pengumpulan[$t->id_tugas]);
                $submission = $isSubmitted ? $pengumpulan[$t->id_tugas] : null;

                $fileUrl = null;
                if ($t->file_path) {
                    $fileUrl = asset('storage/' . $t->file_path);
                }

                return [
                    'id_tugas' => (int)$t->id_tugas,
                    'judul_tugas' => $t->judul_tugas,
                    'deskripsi' => $t->deskripsi,
                    'tanggal_mulai' => $t->tanggal_mulai,
                    'tanggal_deadline' => $t->tanggal_deadline,
                    'mapel' => $t->mapel ? $t->mapel->nama_mapel : null,
                    'guru' => $t->guru ? $t->guru->nama : null,
                    'file_url' => $fileUrl,
                    'status_tugas' => $t->status,
                    'pengumpulan' => $submission ? [
                        'id_pengumpulan' => (int)$submission->id_pengumpulan,
                        'status' => $submission->status,
                        'tanggal_pengumpulan' => $submission->tanggal_pengumpulan,
                        'nilai' => $submission->nilai,
                        'catatan_guru' => $submission->catatan_guru,
                        'file_url' => $submission->file_path ? asset('storage/' . $submission->file_path) : null,
                    ] : null
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $tugasList,
                'count' => $tugasList->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in SiswaTugasController@index: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'siswa_id' => $siswa->id_siswa ?? null,
                'kelas_id' => $siswa->kelas_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data tugas'
            ], 500);
        }
    }

    /**
     * Upload jawaban tugas dari mobile app
     */
    public function upload(Request $request, $tugasId)
    {
        try {
            $user = $request->user();
            
            if (!$user || $user->role !== 'siswa') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya untuk siswa.'
                ], 403);
            }

            if (!$user->siswa) {
                $user->load('siswa');
            }

            if (!$user->siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan.'
                ], 404);
            }

            $siswa = $user->siswa;
            $tugas = Tugas::find($tugasId);

            if (!$tugas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tidak ditemukan.'
                ], 404);
            }

            // Cek apakah sudah pernah upload tugas
            $existing = PengumpulanTugas::where('tugas_id', $tugasId)
                ->where('siswa_id', $siswa->id_siswa)
                ->first();

            // Validasi format file
            $request->validate([
                'jawaban' => 'nullable|string',
                'file_jawaban' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,ppt,pptx,txt,xls,xlsx'
            ], [
                'file_jawaban.max' => 'File maksimal 10MB',
                'file_jawaban.mimes' => 'Format file harus: PDF, JPG, PNG, DOC, DOCX, PPT, PPTX, TXT, XLS, XLSX'
            ]);

            if (!$request->jawaban && !$request->hasFile('file_jawaban')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jawaban teks atau file jawaban wajib diisi.'
                ], 400);
            }

            $now = now();
            $status = $now->gt($tugas->tanggal_deadline) ? 'terlambat' : 'dikumpulkan';

            $data = [
                'tugas_id' => $tugasId,
                'siswa_id' => $siswa->id_siswa,
                'jawaban' => $request->jawaban ?? '',
                'tanggal_pengumpulan' => $now,
                'status' => $status
            ];

            if ($request->hasFile('file_jawaban')) {
                // Hapus file lama jika ada dan sedang update
                if ($existing && $existing->file_path) {
                    if (Storage::disk('public')->exists($existing->file_path)) {
                        Storage::disk('public')->delete($existing->file_path);
                    }
                }

                $file = $request->file('file_jawaban');
                $filename = time() . '_' . $siswa->id_siswa . '_' . $tugasId . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pengumpulan', $filename, 'public');
                $data['file_path'] = $path;
                $data['file_name'] = $file->getClientOriginalName();
            }

            if ($existing) {
                // Update jika belum dinilai
                if ($existing->status === 'dinilai') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tugas sudah dinilai, tidak dapat diubah.'
                    ], 400);
                }
                $existing->update($data);
                $pengumpulan = $existing;
                $message = 'Tugas berhasil diperbarui.';
            } else {
                $pengumpulan = PengumpulanTugas::create($data);
                $message = 'Tugas berhasil diupload.';
            }

            $responseData = [
                'id_pengumpulan' => (int)$pengumpulan->id_pengumpulan,
                'tugas_id' => (int)$pengumpulan->tugas_id,
                'status' => $pengumpulan->status,
                'tanggal_pengumpulan' => $pengumpulan->tanggal_pengumpulan,
                'file_url' => $pengumpulan->file_path ? asset('storage/' . $pengumpulan->file_path) : null,
            ];

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error in SiswaTugasController@upload: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'tugas_id' => $tugasId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupload tugas: ' . $e->getMessage()
            ], 500);
        }
    }
}
