<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::withCount(['students', 'kelas'])->orderBy('nama_jurusan')->get();
        return view('Admin.pages.data.data-jurusan', compact('jurusans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:50|unique:jurusan,nama_jurusan',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        Jurusan::create($request->only(['nama_jurusan', 'deskripsi']));

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $jurusan = Jurusan::findOrFail($id);

        $request->validate([
            'nama_jurusan' => 'required|string|max:50|unique:jurusan,nama_jurusan,' . $id . ',id_jurusan',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $jurusan->update($request->only(['nama_jurusan', 'deskripsi']));

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil dihapus.');
    }
}
