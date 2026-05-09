<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Tugas;
use App\Models\JadwalPelajaran;
use Illuminate\Http\Request;

class MapelMateriController extends Controller
{
    /**
     * =============================================
     * GET /api/mapel
     * =============================================
     * Daftar Mata Pelajaran untuk siswa yang login.
     * Mapel ditentukan dari jadwal_pelajaran berdasarkan kelas siswa.
     * Response menyertakan jumlah materi & tugas (proyek) per mapel.
     */
    public function mapelIndex(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya untuk siswa.',
            ], 403);
        }

        $siswa = $user->siswa;
        $kelasId = $siswa->kelas_id;

        if (!$kelasId) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'Siswa belum memiliki kelas.',
            ]);
        }

        // Ambil mapel_id unik dari jadwal pelajaran kelas siswa
        $mapelIds = JadwalPelajaran::where('kelas_id', $kelasId)
            ->distinct()
            ->pluck('mapel_id');

        if ($mapelIds->isEmpty()) {
            // Fallback: ambil mapel dari materi yang sudah ada untuk kelas ini
            $mapelIds = Materi::where('kelas_id', $kelasId)
                ->where('status', 'published')
                ->distinct()
                ->pluck('mapel_id');
        }

        // Ambil mapel beserta relasi jurusan
        $mapels = Mapel::with('jurusan')
            ->whereIn('id_mapel', $mapelIds)
            ->orderBy('nama_mapel', 'asc')
            ->get();

        $data = $mapels->map(function ($mapel) use ($kelasId) {
            // Hitung jumlah materi published untuk kelas & mapel ini
            $jumlahMateri = Materi::where('mapel_id', $mapel->id_mapel)
                ->where('kelas_id', $kelasId)
                ->where('status', 'published')
                ->count();

            // Hitung jumlah tugas (proyek) published untuk kelas & mapel ini
            $jumlahTugas = Tugas::where('mapel_id', $mapel->id_mapel)
                ->where('kelas_id', $kelasId)
                ->where('status', 'published')
                ->count();

            return [
                'id_mapel'    => $mapel->id_mapel,
                'nama_mapel'  => $mapel->nama_mapel,
                'kode_mapel'  => $mapel->kode_mapel,
                'jenis'       => $mapel->jenis,        // umum / jurusan
                'kategori'    => $mapel->kategori,      // umum / produktif / mulok
                'jurusan'     => $mapel->jurusan?->nama_jurusan,
                'jumlah_materi' => $jumlahMateri,
                'jumlah_tugas'  => $jumlahTugas,        // "Proyek" pada tampilan mobile
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * =============================================
     * GET /api/mapel/{mapelId}
     * =============================================
     * Detail satu Mata Pelajaran beserta guru pengajar
     * dan ringkasan materi/tugas.
     */
    public function mapelShow(Request $request, $mapelId)
    {
        $user = $request->user();

        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya untuk siswa.',
            ], 403);
        }

        $siswa = $user->siswa;
        $kelasId = $siswa->kelas_id;

        $mapel = Mapel::with('jurusan')->find($mapelId);

        if (!$mapel) {
            return response()->json([
                'success' => false,
                'message' => 'Mata pelajaran tidak ditemukan.',
            ], 404);
        }

        // Guru yang mengajar mapel ini di kelas siswa
        $guruIds = JadwalPelajaran::where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->distinct()
            ->pluck('guru_id');

        $guruList = \App\Models\Guru::whereIn('id_guru', $guruIds)
            ->get()
            ->map(fn ($g) => [
                'id_guru' => $g->id_guru,
                'nama'    => $g->nama,
                'nip'     => $g->nip,
                'foto'    => $g->foto_profil ? asset($g->foto_profil) : null,
            ]);

        // Hitung materi & tugas
        $jumlahMateri = Materi::where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->where('status', 'published')
            ->count();

        $jumlahTugas = Tugas::where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->where('status', 'published')
            ->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'id_mapel'      => $mapel->id_mapel,
                'nama_mapel'    => $mapel->nama_mapel,
                'kode_mapel'    => $mapel->kode_mapel,
                'jenis'         => $mapel->jenis,
                'kategori'      => $mapel->kategori,
                'jurusan'       => $mapel->jurusan?->nama_jurusan,
                'guru'          => $guruList,
                'jumlah_materi' => $jumlahMateri,
                'jumlah_tugas'  => $jumlahTugas,
            ],
        ]);
    }

    /**
     * =============================================
     * GET /api/mapel/{mapelId}/materi
     * =============================================
     * Daftar materi untuk mapel tertentu,
     * hanya yang published dan sesuai kelas siswa.
     * Dikelompokkan berdasarkan minggu_ke.
     */
    public function materiByMapel(Request $request, $mapelId)
    {
        $user = $request->user();

        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya untuk siswa.',
            ], 403);
        }

        $siswa = $user->siswa;
        $kelasId = $siswa->kelas_id;

        $mapel = Mapel::find($mapelId);
        if (!$mapel) {
            return response()->json([
                'success' => false,
                'message' => 'Mata pelajaran tidak ditemukan.',
            ], 404);
        }

        $materis = Materi::with('guru')
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->where('status', 'published')
            ->orderBy('minggu_ke', 'asc')
            ->orderBy('tanggal_upload', 'desc')
            ->get();

        $data = $materis->map(function ($m) {
            return [
                'id_materi'      => $m->id_materi,
                'judul_materi'   => $m->judul_materi,
                'deskripsi'      => $m->deskripsi,
                'semester'       => $m->semester,
                'minggu_ke'      => $m->minggu_ke,
                'tanggal_upload' => $m->tanggal_upload,
                'file_name'      => $m->file_name,
                'file_type'      => $m->file_type,
                'file_size'      => $m->file_size,
                'file_url'       => $m->file_path ? asset('storage/' . $m->file_path) : null,
                'guru'           => $m->guru?->nama,
            ];
        });

        // Kelompokkan berdasarkan minggu_ke
        $grouped = $data->groupBy('minggu_ke')->map(function ($items, $minggu) {
            return [
                'minggu_ke' => $minggu ?: null,
                'materi'    => $items->values(),
            ];
        })->values();

        return response()->json([
            'success'    => true,
            'nama_mapel' => $mapel->nama_mapel,
            'data'       => $grouped,
        ]);
    }

    /**
     * =============================================
     * GET /api/materi/{materiId}
     * =============================================
     * Detail satu materi termasuk file URL dan tugas terkait.
     */
    public function materiShow(Request $request, $materiId)
    {
        $user = $request->user();

        if ($user->role !== 'siswa' || !$user->siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya untuk siswa.',
            ], 403);
        }

        $materi = Materi::with(['guru', 'mapel', 'kelas.jurusan', 'tugas'])
            ->find($materiId);

        if (!$materi) {
            return response()->json([
                'success' => false,
                'message' => 'Materi tidak ditemukan.',
            ], 404);
        }

        // Cek materi harus sesuai kelas siswa
        $siswa = $user->siswa;
        if ($materi->kelas_id && $materi->kelas_id !== $siswa->kelas_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke materi ini.',
            ], 403);
        }

        $tugasData = null;
        if ($materi->tugas) {
            $t = $materi->tugas;
            $tugasData = [
                'id_tugas'          => $t->id_tugas,
                'judul_tugas'       => $t->judul_tugas,
                'deskripsi'         => $t->deskripsi,
                'tanggal_mulai'     => $t->tanggal_mulai,
                'tanggal_deadline'  => $t->tanggal_deadline,
                'bobot_nilai'       => $t->bobot_nilai,
                'status'            => $t->status,
                'file_url'          => $t->file_path ? asset('storage/' . $t->file_path) : null,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id_materi'      => $materi->id_materi,
                'judul_materi'   => $materi->judul_materi,
                'deskripsi'      => $materi->deskripsi,
                'semester'       => $materi->semester,
                'minggu_ke'      => $materi->minggu_ke,
                'tanggal_upload' => $materi->tanggal_upload,
                'status'         => $materi->status,
                'file_name'      => $materi->file_name,
                'file_type'      => $materi->file_type,
                'file_size'      => $materi->file_size,
                'file_url'       => $materi->file_path ? asset('storage/' . $materi->file_path) : null,
                'guru'           => $materi->guru?->nama,
                'mapel'          => $materi->mapel?->nama_mapel,
                'kelas'          => $materi->kelas?->nama_kelas,
                'tugas'          => $tugasData,
            ],
        ]);
    }
}
