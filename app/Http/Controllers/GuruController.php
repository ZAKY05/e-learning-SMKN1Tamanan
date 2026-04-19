<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruController extends Controller
{
    public function index()
    {
        $gurus = \App\Models\Guru::with('mapels')->orderBy('nama')->get();
        $mapels = \App\Models\Mapel::orderBy('nama_mapel')->get();
        return view('Admin.pages.data.data-guru', compact('gurus', 'mapels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|max:20|unique:guru,nip',
            'nama' => 'required|string|max:50',
            'no_telp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'mapel_ids' => 'nullable|array',
            'mapel_ids.*' => 'exists:mapel,id_mapel',
        ]);

        $data = $request->only(['nip', 'nama', 'no_telp', 'alamat']);

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/guru'), $filename);
            $data['foto_profil'] = 'uploads/guru/' . $filename;
        }

        $guru = \App\Models\Guru::create($data);

        if ($request->filled('mapel_ids')) {
            $guru->mapels()->sync($request->mapel_ids);
        }

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $guru = \App\Models\Guru::findOrFail($id);

        $request->validate([
            'nip' => 'required|string|max:20|unique:guru,nip,' . $id . ',id_guru',
            'nama' => 'required|string|max:50',
            'no_telp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'mapel_ids' => 'nullable|array',
            'mapel_ids.*' => 'exists:mapel,id_mapel',
        ]);

        $data = $request->only(['nip', 'nama', 'no_telp', 'alamat']);

        if ($request->hasFile('foto_profil')) {
            if ($guru->foto_profil && file_exists(public_path($guru->foto_profil))) {
                unlink(public_path($guru->foto_profil));
            }
            $file = $request->file('foto_profil');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/guru'), $filename);
            $data['foto_profil'] = 'uploads/guru/' . $filename;
        }

        $guru->update($data);
        $guru->mapels()->sync($request->mapel_ids ?? []);

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $guru = \App\Models\Guru::findOrFail($id);

        if ($guru->foto_profil && file_exists(public_path($guru->foto_profil))) {
            unlink(public_path($guru->foto_profil));
        }

        $guru->mapels()->detach();
        $guru->delete();

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil dihapus.');
    }
}
