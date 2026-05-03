<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

$kelas = App\Models\Kelas::with('jurusan')->get();
$hari = ['senin','selasa','rabu','kamis','jumat'];

echo "=== JADWAL PER KELAS PER HARI ===\n";
foreach ($kelas as $k) {
    $counts = [];
    foreach ($hari as $h) {
        $counts[$h] = DB::table('jadwal_pelajaran')
            ->where('kelas_id', $k->id_kelas)->where('hari', $h)
            ->where('tahun_ajaran', '2026/2027')->where('semester', 'ganjil')->count();
    }
    $total = array_sum($counts);
    echo str_pad($k->id_kelas, 4) . '| ' . str_pad($k->nama_kelas, 42) . '| '
        . implode(' | ', array_map(fn($h) => substr($h,0,3) . '=' . str_pad($counts[$h], 2), $hari))
        . " | total=$total\n";
}

echo "\n=== SETTING JADWAL ===\n";
$s = DB::table('setting_jadwal')->where('tahun_ajaran','2026/2027')->where('semester','ganjil')->first();
if ($s) {
    echo "jam_senin={$s->jam_senin} selasa={$s->jam_selasa} rabu={$s->jam_rabu} kamis={$s->jam_kamis} jumat={$s->jam_jumat}\n";
}

echo "\n=== SAMPLE: Senin kelas_id=17 ===\n";
$rows = DB::table('jadwal_pelajaran')->where('kelas_id',17)->where('hari','senin')->get();
foreach ($rows as $r) echo "jam={$r->jam_ke} mapel={$r->mapel_id} guru={$r->guru_id}\n";
if ($rows->isEmpty()) echo "(KOSONG)\n";
