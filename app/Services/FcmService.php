<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmService
{
    // ─────────────────────────────────────────────
    // KIRIM KE SATU USER
    // ─────────────────────────────────────────────
    public static function kirimKeUser(
        int $userId,
        string $tipe,
        string $judul,
        string $isi,
        array $data = []
    ): void {

        // simpan ke database
        Notifikasi::create([
            'user_id' => $userId,
            'tipe'    => $tipe,
            'judul'   => $judul,
            'isi'     => $isi,
            'data'    => $data,
            'is_read' => false,
        ]);

        // ambil user
        $user = User::find($userId);

        if (!$user || !$user->fcm_token) {
            Log::warning('[FCM] User/token tidak ditemukan', [
                'user_id' => $userId
            ]);
            return;
        }

        // kirim notif
        self::kirimFcmV1(
            $user,
            $judul,
            $isi,
            array_merge($data, [
                'tipe' => $tipe,
            ])
        );
    }

    // ─────────────────────────────────────────────
    // KIRIM KE BANYAK USER
    // ─────────────────────────────────────────────
    public static function kirimKeBanyakUser(
        array $userIds,
        string $tipe,
        string $judul,
        string $isi,
        array $data = []
    ): void {

        if (empty($userIds)) return;

        // simpan database
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

        // ambil users
        $users = User::whereIn('id', $userIds)
            ->whereNotNull('fcm_token')
            ->get();

        if ($users->isEmpty()) return;

        $accessToken = self::getAccessToken();

        foreach ($users as $user) {

            self::kirimFcmV1(
                $user,
                $judul,
                $isi,
                array_merge($data, [
                    'tipe' => $tipe,
                ]),
                $accessToken
            );
        }
    }

    // ─────────────────────────────────────────────
    // INTERNAL SEND FCM
    // ─────────────────────────────────────────────
    private static function kirimFcmV1(
        User $user,
        string $judul,
        string $isi,
        array $data = [],
        ?string $accessToken = null
    ): void {

        try {

            $accessToken ??= self::getAccessToken();

            if (!$accessToken) {
                Log::error('[FCM] Access token gagal');
                return;
            }

            $projectId = self::getProjectId();

            // tambahkan role & user_id
            $data = array_merge($data, [
                'user_id' => (string) $user->id,
                'role'    => (string) $user->role,
            ]);

            // semua data wajib string
            $dataString = array_map('strval', $data);

            $payload = [
                'message' => [

                    'token' => $user->fcm_token,

                    // NOTIFICATION BAR
                    'notification' => [
                        'title' => $judul,
                        'body'  => $isi,
                    ],

                    // DATA FLUTTER
                    'data' => $dataString,

                    // ANDROID
                    'android' => [

                        'priority' => 'high',

                        'notification' => [

                            'channel_id' => 'elearning_channel',
                            'sound' => 'default',

                            'default_sound' => true,
                            'default_vibrate_timings' => true,

                        ],
                    ],

                    // IOS
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                $payload
            );

            // DEBUG LOG
            Log::info('[FCM DEBUG]', [
                'user_id' => $user->id,
                'role'    => $user->role,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);

            if (!$response->successful()) {

                Log::warning('[FCM] Gagal kirim notif', [
                    'user_id' => $user->id,
                    'response' => $response->body(),
                ]);

            } else {

                Log::info('[FCM] Berhasil kirim notif', [
                    'user_id' => $user->id,
                    'title'   => $judul,
                ]);
            }

        } catch (\Exception $e) {

            Log::error('[FCM ERROR]', [
                'message' => $e->getMessage()
            ]);
        }
    }

    // ─────────────────────────────────────────────
    // ACCESS TOKEN
    // ─────────────────────────────────────────────
    private static function getAccessToken(): ?string
    {
        return Cache::remember(
            'fcm_access_token',
            now()->addMinutes(55),

            function () {

                try {

                    $path = storage_path('app/firebase-credentials.json');

                    if (!file_exists($path)) {

                        Log::error('[FCM] firebase-credentials.json tidak ditemukan');

                        return null;
                    }

                    $creds = json_decode(file_get_contents($path), true);

                    $now = time();

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

                    openssl_sign(
                        $unsignedJwt,
                        $signature,
                        $creds['private_key'],
                        'SHA256'
                    );

                    $signedJwt = $unsignedJwt . '.' .
                        rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

                    $response = Http::asForm()->post(
                        'https://oauth2.googleapis.com/token',
                        [
                            'grant_type' =>
                                'urn:ietf:params:oauth:grant-type:jwt-bearer',

                            'assertion' => $signedJwt,
                        ]
                    );

                    if ($response->successful()) {

                        return $response->json('access_token');
                    }

                    Log::error('[FCM] Gagal ambil access token', [
                        'body' => $response->body()
                    ]);

                    return null;

                } catch (\Exception $e) {

                    Log::error('[FCM TOKEN ERROR]', [
                        'message' => $e->getMessage()
                    ]);

                    return null;
                }
            }
        );
    }

    // ─────────────────────────────────────────────
    // PROJECT ID
    // ─────────────────────────────────────────────
    private static function getProjectId(): string
    {
        $creds = json_decode(
            file_get_contents(
                storage_path('app/firebase-credentials.json')
            ),
            true
        );

        return $creds['project_id'];
    }
}