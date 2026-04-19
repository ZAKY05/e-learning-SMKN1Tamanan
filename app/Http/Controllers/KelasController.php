<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('golongan')->get();
        $jurusans = Jurusan::all();
        return view('Admin.pages.data.data-kelas', compact('kelas', 'jurusans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tingkat' => 'required|integer|min:1|max:13',
            'jurusan_id' => 'required|exists:jurusan,id_jurusan',
            'golongan' => 'required|integer|min:1',
        ]);

        Kelas::create($request->only('tingkat', 'jurusan_id', 'golongan'));

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tingkat' => 'required|integer|min:1|max:13',
            'jurusan_id' => 'required|exists:jurusan,id_jurusan',
            'golongan' => 'required|integer|min:1',
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->update($request->only('tingkat', 'jurusan_id', 'golongan'));

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
