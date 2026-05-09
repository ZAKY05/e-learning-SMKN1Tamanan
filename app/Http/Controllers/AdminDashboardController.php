<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelajar;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalSiswa = Pelajar::count();
        $totalGuru = Guru::count();
        $totalKelas = Kelas::count();
        $totalMapel = Mapel::count();

        return view('Admin.pages.dashboard', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'totalMapel'
        ));
    }
}
