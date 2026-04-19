<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nip' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $guru = \App\Models\Guru::where('nip', $request->nip)->first();
        if (!$guru) {
            $guru = \App\Models\Guru::create([
                'nip' => $request->nip,
                'nama' => $request->name,
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru',
            'guru_id' => $guru->id_guru,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Arahkan ke dashboard yang sesuai setelah registrasi, sama seperti logika login.
        // Pengguna baru mungkin belum memiliki role, jadi akan masuk ke kasus default.
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }
}
