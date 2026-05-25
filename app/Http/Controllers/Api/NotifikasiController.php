<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    // ── GET /api/notifikasi ───────────────────────────────────────────────────
    /**
     * Ambil daftar notifikasi milik user yang login.
     * Query param opsional: ?per_page=20
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 20);

        $notifikasi = Notifikasi::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $notifikasi->getCollection()->map(fn($n) => $this->format($n)),
            'pagination' => [
                'current_page' => $notifikasi->currentPage(),
                'last_page'    => $notifikasi->lastPage(),
                'per_page'     => $notifikasi->perPage(),
                'total'        => $notifikasi->total(),
            ],
            'unread_count' => Notifikasi::where('user_id', $request->user()->id)
                ->where('is_read', false)
                ->count(),
        ]);
    }

    // ── POST /api/notifikasi/baca-semua ──────────────────────────────────────
    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function bacaSemua(Request $request)
    {
        Notifikasi::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sudah dibaca',
        ]);
    }

    // ── POST /api/notifikasi/{id}/baca ───────────────────────────────────────
    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function baca(Request $request, $id)
    {
        $notif = Notifikasi::where('user_id', $request->user()->id)
            ->where('id_notifikasi', $id)
            ->first();

        if (!$notif) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan'], 404);
        }

        $notif->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca',
        ]);
    }

    // ── GET /api/notifikasi/unread-count ─────────────────────────────────────
    /**
     * Ambil jumlah notifikasi yang belum dibaca (untuk badge di navbar).
     */
    public function unreadCount(Request $request)
    {
        $count = Notifikasi::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data'    => ['unread_count' => $count],
        ]);
    }

    // ── POST /api/notifikasi/update-token ────────────────────────────────────
    /**
     * Flutter memanggil ini setelah login untuk menyimpan FCM token.
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token berhasil disimpan',
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────
    private function format(Notifikasi $n): array
    {
        return [
            'id_notifikasi' => $n->id_notifikasi,
            'tipe'          => $n->tipe,
            'judul'         => $n->judul,
            'isi'           => $n->isi,
            'data'          => $n->data,
            'is_read'       => $n->is_read,
            'created_at'    => $n->created_at?->toIso8601String(),
        ];
    }
}