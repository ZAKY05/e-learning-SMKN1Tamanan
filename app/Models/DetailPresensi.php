<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPresensi extends Model
{
    use HasFactory;

    protected $table = 'detail_presensi';
    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'presensi_id',
        'siswa_id',
        'waktu_presensi',
        'latitude',
        'longitude',
        'jarak_meter',
        'status_kehadiran',
        'keterangan',
    ];

    public function presensi()
    {
        return $this->belongsTo(Presensi::class, 'presensi_id', 'id_presensi');
    }

    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }
}
