<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class GuruProfileController extends Controller
{
    /**
     * Display the guru profile page.
     */
    public function show()
    {
        return view('Guru.pages.profile');
    }

    /**
     * Update guru profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $guru = $user->guru;

        $request->validate([
            'nama'         => ['required', 'string', 'max:50'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'no_telp'      => ['nullable', 'string', 'max:15'],
            'alamat'       => ['nullable', 'string'],
            'foto_profil'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // Update guru data
        if ($guru) {
            $guru->nama    = $request->nama;
            $guru->no_telp = $request->no_telp;
            $guru->alamat  = $request->alamat;

            // Handle foto upload
            if ($request->hasFile('foto_profil')) {
                $file = $request->file('foto_profil');
                $filename = 'guru_' . $guru->id_guru . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/foto_profil'), $filename);
                
                // Delete old photo if exists
                if ($guru->foto_profil && file_exists(public_path($guru->foto_profil))) {
                    unlink(public_path($guru->foto_profil));
                }
                
                $guru->foto_profil = 'uploads/foto_profil/' . $filename;
            }

            $guru->save();
        }

        // Update user data
        $user->name  = $request->nama;
        $user->email = $request->email;
        $user->save();

        return redirect()->route('guru.profile.show')->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update guru password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('guru.profile.show')->with('success', 'Password berhasil diubah!');
    }
}
