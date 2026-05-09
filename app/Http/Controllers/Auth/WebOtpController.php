<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebOtpController extends Controller
{
    public function show(Request $request)
    {
        // Pastikan ada email di session, kalau tidak ada kembali ke forgot password
        if (!session()->has('reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4'
        ]);

        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $cachedOtp = Cache::get('web_otp_' . $email);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kadaluarsa.']);
        }

        // OTP valid, simpan penanda di session bahwa user boleh reset password
        session(['otp_verified' => true]);

        // Hapus OTP
        Cache::forget('web_otp_' . $email);

        return redirect()->route('password.reset');
    }
}
