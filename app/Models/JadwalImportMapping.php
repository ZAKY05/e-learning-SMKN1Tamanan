<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalImportMapping extends Model
{
    protected $table = 'jadwal_import_mapping';

    protected $fillable = ['tipe', 'singkatan', 'target_id'];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'target_id', 'id_mapel');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'target_id', 'id_kelas');
    }
}
