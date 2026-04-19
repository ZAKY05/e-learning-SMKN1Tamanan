<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Guru;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $mapels    = Mapel::with(['jurusan', 'gurus'])->orderBy('nama_mapel')->get();
        $gurus     = Guru::orderBy('nama')->get();
        $jurusans  = Jurusan::orderBy('nama_jurusan')->get();

        return view('Admin.pages.data.data-mapel', compact('mapels', 'gurus', 'jurusans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel' => 'required|string|max:100|unique:mapel,nama_mapel',
            'jenis'      => 'required|in:umum,jurusan',
            'jurusan_id' => 'nullable|exists:jurusan,id_jurusan',
            'guru_ids'   => 'nullable|array',
            'guru_ids.*' => 'exists:guru,id_guru',
        ]);

        $mapel = Mapel::create($request->only(['nama_mapel', 'jenis', 'jurusan_id']));

        if ($request->filled('guru_ids')) {
            $mapel->gurus()->sync($request->guru_ids);
        }

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $mapel = Mapel::findOrFail($id);

        $request->validate([
            'nama_mapel' => 'required|string|max:100|unique:mapel,nama_mapel,' . $id . ',id_mapel',
            'jenis'      => 'required|in:umum,jurusan',
            'jurusan_id' => 'nullable|exists:jurusan,id_jurusan',
            'guru_ids'   => 'nullable|array',
            'guru_ids.*' => 'exists:guru,id_guru',
        ]);

        $mapel->update($request->only(['nama_mapel', 'jenis', 'jurusan_id']));
        $mapel->gurus()->sync($request->guru_ids ?? []);

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $mapel = Mapel::findOrFail($id);
        $mapel->gurus()->detach();
        $mapel->delete();

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
