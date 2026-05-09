<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Mail\OtpMail;

class AuthController extends Controller
{
   public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
            ], 401);
        }

        $user->load('siswa.kelas', 'guru');

        $token = $user->createToken('mobile')->plainTextToken;

        // ================= BASE DATA =================
    $userData = [
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'role' => $user->role,
    'foto' => null,
    'kelas' => null,
    'nis' => null,
    'nip' => null,
    'nama_mapel' => null,
    'no_telp' => null,
    'alamat' => null,
];

        // ================= SISWA =================        // 
if ($user->role === 'siswa' && $user->siswa) {

    $userData['kelas'] = $user->siswa->kelas?->nama_kelas ?? '-';
    $userData['nis'] = $user->siswa->nis  ?? '-';
    $userData['foto'] = $user->siswa->foto_profil
        ? asset($user->siswa->foto_profil)
        : null;
}

        // ================= GURU =================
if ($user->role === 'guru' && $user->guru) {

    $userData['nip'] = $user->guru->nip ?? '-';

    $userData['nama_mapel'] =
        $user->guru->mapels->pluck('nama_mapel')->join(', ');

    $userData['no_telp'] = $user->guru->no_telp ?? '-';

    $userData['alamat'] = $user->guru->alamat ?? '-';

    $userData['foto'] = $user->guru->foto_profil
        ? asset($user->guru->foto_profil)
        : null;
} 
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => $userData,
                'token' => $token,
            ]
        ]);
    } 

    // ================= CHECK EMAIL =================
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'success' => (bool) $user,
            'message' => $user ? 'Email ditemukan' : 'Email tidak terdaftar',
        ], $user ? 200 : 404);
    }

    // ================= SEND OTP =================
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan',
            ], 404);
        }

        $otp = rand(1000, 9999);

        Cache::put('otp_' . $user->email, $otp, now()->addMinutes(5));

        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'OTP dikirim ke email',
        ]);
    }

    // ================= VERIFY OTP =================
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:4'
        ]);

        $cachedOtp = Cache::get('otp_' . $request->email);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau kadaluarsa',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        Cache::forget('otp_' . $request->email);

        $user->load('siswa.kelas', 'siswa.jurusan', 'guru');

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    // ================= RESET PASSWORD =================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }

    // ================= LOGOUT =================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
    public function updatePassword(Request $request)
{
    $user = auth()->user();

    if (!Hash::check($request->old_password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Password lama salah'
        ], 400);
    }

    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Password berhasil diubah'
    ]);
}
public function updateEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email|unique:users,email',
    ]);

    $user = auth()->user();
    $user->email = $request->email;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Email berhasil diupdate',
        'data' => $user
    ]);
}
    
} 