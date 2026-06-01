<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';
    protected $primaryKey = 'id_presensi';

    protected $fillable = [
        'guru_id',
        'mapel_id',
        'kelas_id',
        'lokasi_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'qr_code',
        'keterangan',
        'status',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'id_guru');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id_mapel');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id_kelas');
    }

    public function lokasi()
    {
        return $this->belongsTo(BankLokasi::class, 'lokasi_id', 'id');
    }

    public function detailPresensi()
    {
        return $this->hasMany(DetailPresensi::class, 'presensi_id', 'id_presensi');
    }
}
