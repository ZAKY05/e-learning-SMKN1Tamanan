<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INDEXES ON jadwal_pelajaran ===\n";
$indexes = \DB::select('SHOW INDEX FROM jadwal_pelajaran');
foreach ($indexes as $idx) {
    echo "  {$idx->Key_name} | col={$idx->Column_name} | unique=" . ($idx->Non_unique ? 'no' : 'YES') . " | seq={$idx->Seq_in_index}\n";
}

echo "\n=== K3R SENIN after latest import ===\n";
$count = \DB::table('jadwal_pelajaran')->where('kelas_id', 3)->where('hari', 'senin')->count();
echo "Count: {$count}\n";

echo "\n=== CREATE TABLE ===\n";
$create = \DB::select('SHOW CREATE TABLE jadwal_pelajaran');
echo $create[0]->{'Create Table'} . "\n";
