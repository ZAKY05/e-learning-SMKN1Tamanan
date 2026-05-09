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
        
        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya untuk siswa.'
            ], 403);
        }

        $siswa = $user->siswa;

        // Ambil semua tugas untuk kelas siswa ini
        $tugas = Tugas::with(['mapel', 'guru', 'materi'])
            ->where('kelas_id', $siswa->kelas_id)
            ->orderBy('tanggal_deadline', 'asc')
            ->get();

        // Ambil status pengumpulan untuk masing-masing tugas
        $tugasIdList = $tugas->pluck('id_tugas');
        
        $pengumpulan = PengumpulanTugas::where('siswa_id', $siswa->id_siswa)
            ->whereIn('tugas_id', $tugasIdList)
            ->get()
            ->keyBy('tugas_id');

        $tugasList = $tugas->map(function($t) use ($pengumpulan) {
            $isSubmitted = isset($pengumpulan[$t->id_tugas]);
            $submission = $isSubmitted ? $pengumpulan[$t->id_tugas] : null;

            if ($t->file_path) {
                $t->file_url = asset('storage/' . $t->file_path);
            }

            return [
                'id_tugas' => $t->id_tugas,
                'judul_tugas' => $t->judul_tugas,
                'deskripsi' => $t->deskripsi,
                'tanggal_mulai' => $t->tanggal_mulai,
                'tanggal_deadline' => $t->tanggal_deadline,
                'mapel' => $t->mapel->nama_mapel ?? null,
                'guru' => $t->guru->nama_lengkap ?? null,
                'file_url' => $t->file_url ?? null,
                'status_tugas' => $t->status,
                'pengumpulan' => $submission ? [
                    'id_pengumpulan' => $submission->id_pengumpulan,
                    'status' => $submission->status,
                    'tanggal_pengumpulan' => $submission->tanggal_pengumpulan,
                    'nilai' => $submission->nilai,
                    'catatan_guru' => $submission->catatan_guru,
                    'file_url' => $submission->file_path ? asset('storage/' . $submission->file_path) : null,
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tugasList
        ]);
    }

    /**
     * Upload jawaban tugas dari mobile app
     */
    public function upload(Request $request, $tugasId)
    {
        $user = $request->user();
        
        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya untuk siswa.'
            ], 403);
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
            'file_jawaban' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,ppt,pptx' // Max 10MB
        ], [
            'file_jawaban.mimes' => 'Format file yang diizinkan: PDF, JPG, PNG, DOC, DOCX, PPT, PPTX'
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
            'jawaban' => $request->jawaban,
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
            $path = $file->store('pengumpulan', 'public');
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
        }

        if ($existing) {
            // Update jika belum dinilai (opsional: jika guru belum menilai)
            if ($existing->status === 'dinilai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas sudah dinilai, tidak dapat diubah.'
                ], 400);
            }
            $existing->update($data);
            $pengumpulan = $existing;
        } else {
            $pengumpulan = PengumpulanTugas::create($data);
        }

        if ($pengumpulan->file_path) {
            $pengumpulan->file_url = asset('storage/' . $pengumpulan->file_path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diupload.',
            'data' => $pengumpulan
        ]);
    }
}
