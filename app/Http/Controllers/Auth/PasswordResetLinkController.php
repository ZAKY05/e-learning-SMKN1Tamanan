<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        // Generate OTP 4 digit (1000 - 9999)
        $otp = rand(1000, 9999);

        // Simpan OTP ke cache selama 15 menit
        \Illuminate\Support\Facades\Cache::put('web_otp_' . $request->email, $otp, now()->addMinutes(15));

        // Kirim OTP via email
        \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\OtpMail($otp));

        // Simpan email di session agar tidak perlu mengetik lagi di halaman OTP
        session(['reset_email' => $request->email]);

        return redirect()->route('password.otp')->with('status', 'Kode OTP telah dikirim ke email Anda.');
    }
}
