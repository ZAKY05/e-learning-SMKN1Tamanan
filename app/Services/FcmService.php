<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmService
{
    // ── Kirim ke satu user ────────────────────────────────────────────────────
    public static function kirimKeUser(
        int $userId,
        string $tipe,
        string $judul,
        string $isi,
        array $data = []
    ): void {
        // 1. Simpan ke database dulu (selalu, meski FCM gagal)
        Notifikasi::create([
            'user_id' => $userId,
            'tipe'    => $tipe,
            'judul'   => $judul,
            'isi'     => $isi,
            'data'    => $data,
            'is_read' => false,
        ]);

        // 2. Ambil FCM token user
        $user = User::find($userId);
        if (!$user || !$user->fcm_token) return;

        // 3. Kirim via HTTP v1
        self::kirimFcmV1($user->fcm_token, $judul, $isi, array_merge($data, ['tipe' => $tipe]));
    }

    // ── Kirim ke banyak user (broadcast per kelas) ────────────────────────────
    public static function kirimKeBanyakUser(
        array $userIds,
        string $tipe,
        string $judul,
        string $isi,
        array $data = []
    ): void {
        if (empty($userIds)) return;

        // 1. Bulk insert ke database
        $rows = array_map(fn($id) => [
            'user_id'    => $id,
            'tipe'       => $tipe,
            'judul'      => $judul,
            'isi'        => $isi,
            'data'       => json_encode($data),
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ], $userIds);

        Notifikasi::insert($rows);

        // 2. Ambil semua FCM token
        $tokens = User::whereIn('id', $userIds)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) return;

        // 3. HTTP v1 tidak support multicast langsung —
        //    kirim satu per satu tapi gunakan access token yang sama (di-cache)
        $accessToken = self::getAccessToken();
        if (!$accessToken) return;

        foreach ($tokens as $token) {
            self::kirimFcmV1($token, $judul, $isi, array_merge($data, ['tipe' => $tipe]), $accessToken);
        }
    }

    // ── Internal: kirim satu notif via FCM HTTP v1 ────────────────────────────
    private static function kirimFcmV1(
        string $token,
        string $judul,
        string $isi,
        array $data = [],
        ?string $accessToken = null
    ): void {
        try {
            $accessToken ??= self::getAccessToken();
            if (!$accessToken) {
                Log::error('[FCM] Gagal mendapatkan access token');
                return;
            }

            // Semua nilai di data[] harus string
            $dataString = array_map('strval', $data);
            $projectId  = self::getProjectId();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token'        => $token,
                    'notification' => [
                        'title' => $judul,
                        'body'  => $isi,
                    ],
                    'data'    => $dataString,
                    'android' => [
                        'priority'     => 'high',
                        'notification' => [
                            'sound'      => 'default',
                            'channel_id' => 'elearning_channel',
                        ],
                    ],
                ],
            ]);

            Log::info('[FCM DEBUG]', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                Log::warning('[FCM] Gagal kirim notifikasi', [
                    'token'    => substr($token, 0, 20) . '...',
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[FCM] Exception: ' . $e->getMessage());
        }
    }

    // ── Ambil OAuth2 Access Token dari Service Account JSON ───────────────────
    // Di-cache 55 menit (token FCM berlaku 60 menit)
    private static function getAccessToken(): ?string
    {
        return Cache::remember('fcm_access_token', now()->addMinutes(55), function () {
            try {
                $path = storage_path('app/firebase-credentials.json');

                if (!file_exists($path)) {
                    Log::error('[FCM] File tidak ditemukan: storage/app/firebase-credentials.json');
                    return null;
                }

                $creds = json_decode(file_get_contents($path), true);

                // Buat JWT assertion
                $now    = time();
                $header = rtrim(strtr(base64_encode(json_encode([
                    'alg' => 'RS256',
                    'typ' => 'JWT',
                ])), '+/', '-_'), '=');

                $payload = rtrim(strtr(base64_encode(json_encode([
                    'iss'   => $creds['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud'   => 'https://oauth2.googleapis.com/token',
                    'iat'   => $now,
                    'exp'   => $now + 3600,
                ])), '+/', '-_'), '=');

                $unsignedJwt = $header . '.' . $payload;

                openssl_sign($unsignedJwt, $signature, $creds['private_key'], 'SHA256');
                $signedJwt = $unsignedJwt . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

                // Tukar JWT dengan access token Google
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $signedJwt,
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::error('[FCM] Gagal tukar JWT: ' . $response->body());
                return null;

            } catch (\Exception $e) {
                Log::error('[FCM] Exception getAccessToken: ' . $e->getMessage());
                return null;
            }
        });
    }

    // ── Baca project_id dari credentials ─────────────────────────────────────
    private static function getProjectId(): string
    {
        $creds = json_decode(
            file_get_contents(storage_path('app/firebase-credentials.json')),
            true
        );
        return $creds['project_id'];
    }
}