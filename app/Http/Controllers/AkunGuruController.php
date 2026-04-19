<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AkunGuruController extends Controller
{
    public function index()
    {
        // Ambil semua akun guru dari tabel users JOIN guru
        $akuns = DB::table('users')
            ->join('guru', 'users.guru_id', '=', 'guru.id_guru')
            ->where('users.role', 'guru')
            ->select(
            'users.id',
            'users.email',
            'users.password',
            'users.created_at',
            'guru.id_guru',
            'guru.nip',
            'guru.nama',
            'guru.foto_profil'
        )
            ->orderBy('guru.nama')
            ->get();

        // Ambil daftar guru yang BELUM punya akun (untuk dropdown tambah)
        $guruTanpaAkun = DB::table('guru')
            ->leftJoin('users', function ($join) {
            $join->on('guru.id_guru', '=', 'users.guru_id')
                ->where('users.role', '=', 'guru');
        })
            ->whereNull('users.id')
            ->select('guru.id_guru', 'guru.nip', 'guru.nama')
            ->orderBy('guru.nama')
            ->get();

        return view('Admin.pages.akun.guru-akun', compact('akuns', 'guruTanpaAkun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id_guru',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        // Ambil data guru
        $guru = DB::table('guru')->where('id_guru', $request->guru_id)->first();
        if (!$guru)
            abort(404);

        // Cek apakah guru sudah punya akun
        $exists = DB::table('users')
            ->where('guru_id', $guru->id_guru)
            ->where('role', 'guru')
            ->exists();

        if ($exists) {
            return redirect()->route('admin.akun-guru.index')
                ->withErrors(['guru_id' => 'Guru ini sudah memiliki akun.']);
        }

        DB::table('users')->insert([
            'name' => $guru->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru',
            'guru_id' => $guru->id_guru,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.akun-guru.index')
            ->with('success', 'Akun guru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->where('role', 'guru')->first();
        if (!$user)
            abort(404);

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'email' => $request->email,
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->update($data);

        return redirect()->route('admin.akun-guru.index')
            ->with('success', 'Akun guru berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = DB::table('users')->where('id', $id)->where('role', 'guru')->first();
        if (!$user)
            abort(404);

        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('admin.akun-guru.index')
            ->with('success', 'Akun guru berhasil dihapus.');
    }
}
