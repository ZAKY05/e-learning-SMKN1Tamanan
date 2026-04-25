<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BanklokasiController extends Controller
{
    public function index()
    {
        $banklokasis = \App\Models\Banklokasi::orderBy('nama_lokasi')->get();
        return view('Admin.pages.bank_lokasi', compact('banklokasis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric',
            'alamat' => 'nullable|string',
        ]);
        $data = $request->only(['nama_lokasi', 'latitude', 'longitude', 'radius', 'alamat']);

        \App\Models\Banklokasi::create($data);

        return redirect()->route('admin.bank-lokasi.index')->with('success', 'Data bank lokasi berhasil ditambahkan.');
    }
    public function update(Request $request, $id)
    {
        $banklokasi = \App\Models\Banklokasi::findOrFail($id);

        $request->validate([
            'nama_lokasi' => 'required|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric',
            'alamat' => 'nullable|string',
        ]);
        $data = $request->only(['nama_lokasi', 'latitude', 'longitude', 'radius', 'alamat']);

        $banklokasi->update($data);

        return redirect()->route('admin.bank-lokasi.index')->with('success', 'Data bank lokasi berhasil diperbarui.');
    }
    public function destroy($id)
    {
        $banklokasi = \App\Models\Banklokasi::findOrFail($id);
        $banklokasi->delete();

        return redirect()->route('admin.bank-lokasi.index')->with('success', 'Data bank lokasi berhasil dihapus.');
    }
}
