<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guru;

class GuruApiController extends Controller
{
    // GET 
    public function index()
    {
        $guru = Guru::with('mapels')->get();
        return response()->json($guru);
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:guru,nip',
            'nama' => 'required',
        ]);

        $data = $request->only(['nip','nama','no_telp','alamat']);

        // upload foto
        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/guru'), $filename);
            $data['foto_profil'] = 'uploads/guru/'.$filename;
        }

        $guru = Guru::create($data);

        if ($request->mapel_ids) {
            $guru->mapels()->sync($request->mapel_ids);
        }

        return response()->json([
            'message' => 'Data berhasil ditambahkan',
            'data' => $guru
        ]);
    }

    //  SHOW
    public function show($id)
    {
        $guru = Guru::with('mapels')->find($id);

        if (!$guru) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($guru);
    }

    //  UPDATE
    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $data = $request->only(['nip','nama','no_telp','alamat']);

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/guru'), $filename);
            $data['foto_profil'] = 'uploads/guru/'.$filename;
        }

        $guru->update($data);

        if ($request->mapel_ids) {
            $guru->mapels()->sync($request->mapel_ids);
        }

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $guru
        ]);
    }

    //  DELETE
    public function destroy($id)
    {
        $guru = Guru::findOrFail($id);

        $guru->mapels()->detach();
        $guru->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }
}