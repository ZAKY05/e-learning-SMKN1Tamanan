<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AkunSiswaController extends Controller
{
    public function index()
    {
        
        $akuns = DB::table('users')
            ->join('student', 'users.siswa_id', '=', 'student.id_siswa')
            ->where('users.role', 'siswa')
            ->select(
            'users.id',
            'users.email',
            'users.password',
            'users.created_at',
            'student.id_siswa',
            'student.nis',
            'student.nama',
            'student.foto_profil'
        )
            ->orderBy('student.nama')
            ->get();

        // Ambil daftar siswa yang BELUM punya akun (untuk dropdown tambah)
        $siswaTanpaAkun = DB::table('student')
            ->leftJoin('users', function ($join) {
            $join->on('student.id_siswa', '=', 'users.siswa_id')
                ->where('users.role', '=', 'siswa');
        })
            ->whereNull('users.id')
            ->select('student.id_siswa', 'student.nis', 'student.nama')
            ->orderBy('student.nama')
            ->get();

        return view('Admin.pages.akun.siswa-akun', compact('akuns', 'siswaTanpaAkun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:student,id_siswa',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        // Ambil data siswa
        $siswa = DB::table('student')->where('id_siswa', $request->siswa_id)->first();
        if (!$siswa)
            abort(404);

        // Cek apakah siswa sudah punya akun
        $exists = DB::table('users')
            ->where('siswa_id', $siswa->id_siswa)
            ->where('role', 'siswa')
            ->exists();

        if ($exists) {
            return redirect()->route('admin.akun-siswa.index')
                ->withErrors(['siswa_id' => 'Siswa ini sudah memiliki akun.']);
        }

        DB::table('users')->insert([
            'name' => $siswa->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
            'siswa_id' => $siswa->id_siswa,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.akun-siswa.index')
            ->with('success', 'Akun siswa berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->where('role', 'siswa')->first();
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

        return redirect()->route('admin.akun-siswa.index')
            ->with('success', 'Akun siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = DB::table('users')->where('id', $id)->where('role', 'siswa')->first();
        if (!$user)
            abort(404);

        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('admin.akun-siswa.index')
            ->with('success', 'Akun siswa berhasil dihapus.');
    }
}
