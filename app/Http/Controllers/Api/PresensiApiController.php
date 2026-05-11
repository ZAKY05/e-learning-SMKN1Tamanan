<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\DetailPresensi;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiApiController extends Controller
{
    /**
     * GET /api/presensi/active
     * Cek apakah ada presensi aktif untuk kelas siswa saat ini
     */
    public function activePresensi(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data siswa',
            ], 403);
        }

        $now = Carbon::now();

        // Cari presensi aktif hari ini untuk kelas siswa
        $presensiAktif = Presensi::where('kelas_id', $siswa->kelas_id)
            ->where('status', 'aktif')
            ->whereDate('tanggal', $now->toDateString())
            ->with(['mapel', 'guru', 'lokasi', 'kelas.jurusan'])
            ->orderBy('jam_mulai', 'desc')
            ->get();

        $result = [];

        foreach ($presensiAktif as $presensi) {
            $jamSelesai = Carbon::parse($presensi->tanggal . ' ' . $presensi->jam_selesai);
            $batasTerlambat = $jamSelesai->copy()->addMinutes(30);

            // Tentukan fase waktu
            if ($now->lte($jamSelesai)) {
                $faseWaktu = 'normal';
            } elseif ($now->lte($batasTerlambat)) {
                $faseWaktu = 'terlambat';
            } else {
                // Sudah expired, skip
                continue;
            }

            // Cek apakah siswa sudah absen
            $sudahAbsen = DetailPresensi::where('presensi_id', $presensi->id_presensi)
                ->where('siswa_id', $siswa->id_siswa)
                ->first();

            $result[] = [
                'id_presensi' => $presensi->id_presensi,
                'mapel' => $presensi->mapel->nama_mapel ?? '-',
                'guru' => $presensi->guru->nama ?? '-',
                'kelas' => $presensi->kelas->nama_kelas ?? '-',
                'lokasi' => [
                    'nama' => $presensi->lokasi->nama_lokasi ?? '-',
                    'latitude' => $presensi->lokasi->latitude ?? null,
                    'longitude' => $presensi->lokasi->longitude ?? null,
                    'radius' => $presensi->lokasi->radius ?? null,
                ],
                'jam_mulai' => $presensi->jam_mulai,
                'jam_selesai' => $presensi->jam_selesai,
                'batas_terlambat' => $batasTerlambat->format('H:i:s'),
                'fase_waktu' => $faseWaktu,
                'sisa_waktu_menit' => $faseWaktu === 'terlambat'
                    ? $now->diffInMinutes($batasTerlambat)
                    : $now->diffInMinutes($jamSelesai),
                'sudah_absen' => $sudahAbsen ? true : false,
                'status_kehadiran' => $sudahAbsen ? $sudahAbsen->status_kehadiran : null,
                'waktu_presensi' => $sudahAbsen ? $sudahAbsen->waktu_presensi : null,
                'keterangan' => $presensi->keterangan,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => count($result) > 0
                ? 'Ditemukan ' . count($result) . ' presensi aktif'
                : 'Tidak ada presensi aktif saat ini',
            'data' => $result,
        ]);
    }

    /**
     * POST /api/presensi/scan
     * Siswa scan QR code untuk melakukan presensi
     *
     * Body: {
     *   "qr_code": "string",
     *   "latitude": "string",
     *   "longitude": "string"
     * }
     */
    public function scanQr(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data siswa',
            ], 403);
        }

        // Cari presensi berdasarkan QR code
        $presensi = Presensi::where('qr_code', $request->qr_code)
            ->where('status', 'aktif')
            ->with(['lokasi', 'mapel', 'kelas.jurusan'])
            ->first();

        if (!$presensi) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau presensi sudah ditutup',
            ], 404);
        }

        // Validasi kelas siswa
        if ($presensi->kelas_id !== $siswa->kelas_id) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi ini bukan untuk kelas Anda',
            ], 403);
        }

        // Cek apakah sudah pernah absen
        $existing = DetailPresensi::where('presensi_id', $presensi->id_presensi)
            ->where('siswa_id', $siswa->id_siswa)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi untuk sesi ini',
                'data' => [
                    'status_kehadiran' => $existing->status_kehadiran,
                    'waktu_presensi' => $existing->waktu_presensi,
                ],
            ], 409);
        }

        // Cek waktu presensi
        $now = Carbon::now();
        $jamSelesai = Carbon::parse($presensi->tanggal . ' ' . $presensi->jam_selesai);
        $batasTerlambat = $jamSelesai->copy()->addMinutes(30);

        if ($now->gt($batasTerlambat)) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu presensi sudah berakhir (termasuk masa toleransi 30 menit)',
            ], 422);
        }

        // Tentukan status: hadir atau terlambat
        $statusKehadiran = $now->lte($jamSelesai) ? 'hadir' : 'terlambat';

        // Hitung jarak jika lokasi disediakan
        $jarakMeter = null;
        if ($request->latitude && $request->longitude && $presensi->lokasi) {
            $jarakMeter = $this->hitungJarak(
                $request->latitude,
                $request->longitude,
                $presensi->lokasi->latitude,
                $presensi->lokasi->longitude
            );

            // Validasi radius jika lokasi punya radius
            if ($presensi->lokasi->radius && $jarakMeter > $presensi->lokasi->radius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar jangkauan lokasi presensi. Jarak Anda: ' . round($jarakMeter) . ' meter (maksimal: ' . $presensi->lokasi->radius . ' meter)',
                    'data' => [
                        'jarak_meter' => round($jarakMeter, 2),
                        'radius_maksimal' => $presensi->lokasi->radius,
                    ],
                ], 422);
            }
        }

        // Simpan presensi
        $detail = DetailPresensi::create([
            'presensi_id' => $presensi->id_presensi,
            'siswa_id' => $siswa->id_siswa,
            'waktu_presensi' => $now->format('H:i:s'),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'jarak_meter' => $jarakMeter ? round($jarakMeter, 2) : null,
            'status_kehadiran' => $statusKehadiran,
            'keterangan' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => $statusKehadiran === 'hadir'
                ? 'Presensi berhasil! Status: Hadir'
                : 'Presensi berhasil! Status: Terlambat (melewati batas waktu)',
            'data' => [
                'status_kehadiran' => $statusKehadiran,
                'waktu_presensi' => $detail->waktu_presensi,
                'jarak_meter' => $detail->jarak_meter,
                'mapel' => $presensi->mapel->nama_mapel ?? '-',
                'kelas' => $presensi->kelas->nama_kelas ?? '-',
            ],
        ]);
    }

    /**
     * GET /api/presensi/riwayat
     * Riwayat presensi siswa (opsional filter: ?tanggal_mulai=&tanggal_selesai=&mapel_id=)
     */
    public function riwayat(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data siswa',
            ], 403);
        }

        $query = DetailPresensi::where('siswa_id', $siswa->id_siswa)
            ->with(['presensi.mapel', 'presensi.guru', 'presensi.kelas.jurusan'])
            ->orderBy('created_at', 'desc');

        // Filter tanggal
        if ($request->tanggal_mulai && $request->tanggal_selesai) {
            $query->whereHas('presensi', function ($q) use ($request) {
                $q->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);
            });
        }

        // Filter mapel
        if ($request->mapel_id) {
            $query->whereHas('presensi', function ($q) use ($request) {
                $q->where('mapel_id', $request->mapel_id);
            });
        }

        $riwayat = $query->paginate(20);

        $data = $riwayat->getCollection()->map(function ($detail) {
            return [
                'id_detail' => $detail->id_detail,
                'tanggal' => $detail->presensi->tanggal ?? null,
                'mapel' => $detail->presensi->mapel->nama_mapel ?? '-',
                'guru' => $detail->presensi->guru->nama ?? '-',
                'kelas' => $detail->presensi->kelas->nama_kelas ?? '-',
                'jam_mulai' => $detail->presensi->jam_mulai ?? null,
                'jam_selesai' => $detail->presensi->jam_selesai ?? null,
                'waktu_presensi' => $detail->waktu_presensi,
                'status_kehadiran' => $detail->status_kehadiran,
                'jarak_meter' => $detail->jarak_meter,
                'keterangan' => $detail->keterangan,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $riwayat->currentPage(),
                'last_page' => $riwayat->lastPage(),
                'per_page' => $riwayat->perPage(),
                'total' => $riwayat->total(),
            ],
        ]);
    }

    /**
     * GET /api/presensi/rekap
     * Rekap statistik presensi siswa
     */
    public function rekap(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung dengan data siswa',
            ], 403);
        }

        $query = DetailPresensi::where('siswa_id', $siswa->id_siswa);

        // Filter bulan/tahun
        if ($request->bulan && $request->tahun) {
            $query->whereHas('presensi', function ($q) use ($request) {
                $q->whereMonth('tanggal', $request->bulan)
                  ->whereYear('tanggal', $request->tahun);
            });
        }

        $total = (clone $query)->count();
        $hadir = (clone $query)->where('status_kehadiran', 'hadir')->count();
        $terlambat = (clone $query)->where('status_kehadiran', 'terlambat')->count();
        $sakit = (clone $query)->where('status_kehadiran', 'sakit')->count();
        $izin = (clone $query)->where('status_kehadiran', 'izin')->count();
        $alpha = (clone $query)->where('status_kehadiran', 'alpha')->count();

        $persentaseKehadiran = $total > 0
            ? round((($hadir + $terlambat) / $total) * 100, 1)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpha' => $alpha,
                'persentase_kehadiran' => $persentaseKehadiran,
            ],
        ]);
    }

    /**
     * Hitung jarak antara 2 koordinat (Haversine formula)
     * Return: jarak dalam meter
     */
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter

        $lat1 = deg2rad(floatval($lat1));
        $lon1 = deg2rad(floatval($lon1));
        $lat2 = deg2rad(floatval($lat2));
        $lon2 = deg2rad(floatval($lon2));

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) * sin($dlat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dlon / 2) * sin($dlon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
