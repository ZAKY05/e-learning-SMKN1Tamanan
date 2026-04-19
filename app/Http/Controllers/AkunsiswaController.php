<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AkunSiswaController extends Controller
{
<<<<<<< HEAD
    public function index()
    {
        // Ambil semua akun siswa dari tabel users
        $akuns = User::where('role', 'siswa')
            ->select('id', 'nis', 'name', 'email', 'created_at', 'siswa_id')
            ->orderBy('name')
            ->get();

        return view('Admin.pages.akun.siswa-akun', compact('akuns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|string|max:15|unique:users,nis',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'nis' => $request->nis,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
        ]);

        return redirect()->route('admin.akun-siswa.index')
            ->with('success', 'Akun siswa berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->where('role', 'siswa')->firstOrFail();

        $request->validate([
            'nis' => 'required|string|max:15|unique:users,nis,' . $id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        $user->nis = $request->nis;
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.akun-siswa.index')
            ->with('success', 'Akun siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::where('id', $id)->where('role', 'siswa')->firstOrFail();

        $user->delete();

        return redirect()->route('admin.akun-siswa.index')
            ->with('success', 'Akun siswa berhasil dihapus.');
=======
    public function (){
    siswas = DB::table('users')->orderBy('');
>>>>>>> 1b7b45012dee117f4a472ec9e0ed7b0d90846bea
    }
}
