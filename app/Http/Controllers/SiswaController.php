<?php

namespace App\Http\Controllers;

use App\Models\Student as Pelajar;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index()
    {
        $pelajar = Pelajar::with('jurusan', 'kelas.jurusan')->get();
        $jurusans = Jurusan::all();
        $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('golongan')->get();
        // Fetch siswa accounts that aren't linked yet
        $akuns_siswa = User::where('role', 'siswa')->whereNull('siswa_id')->get();
        return view('Admin.pages.data.data-siswa', compact('pelajar', 'jurusans', 'kelas', 'akuns_siswa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|string|max:15|unique:Pelajar,nis',
            'nama' => 'required|string|max:30',
            'jurusan_id' => 'nullable|exists:jurusan,id_jurusan',
            'kelas_id' => 'nullable|exists:kelas,id_kelas',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['nis', 'nama', 'jurusan_id', 'kelas_id']);

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/siswa'), $filename);
            $data['foto_profil'] = 'uploads/siswa/' . $filename;
        }

        $student = Pelajar::create($data);

        // Link the chosen user account
        $user = User::where('role', 'siswa')->where('nis', $request->nis)->first();
        if ($user) {
            $user->siswa_id = $student->id_siswa;
            $user->save();
        }

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $student = Pelajar::findOrFail($id);

        $request->validate([
            'nis' => 'required|string|max:15|unique:Pelajar,nis,' . $id . ',id_siswa',
            'nama' => 'required|string|max:30',
            'jurusan_id' => 'nullable|exists:jurusan,id_jurusan',
            'kelas_id' => 'nullable|exists:kelas,id_kelas',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['nis', 'nama', 'jurusan_id', 'kelas_id']);

        if ($request->hasFile('foto_profil')) {
            if ($student->foto_profil && file_exists(public_path($student->foto_profil))) {
                unlink(public_path($student->foto_profil));
            }
            $file = $request->file('foto_profil');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/siswa'), $filename);
            $data['foto_profil'] = 'uploads/siswa/' . $filename;
        }

        $student->update($data);

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $student = Pelajar::findOrFail($id);

        if ($student->foto_profil && file_exists(public_path($student->foto_profil))) {
            unlink(public_path($student->foto_profil));
        }

        // Unlink user account
        $user = User::where('siswa_id', $student->id_siswa)->first();
        if ($user) {
            $user->siswa_id = null;
            $user->save();
        }

        $student->delete();

        return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}
