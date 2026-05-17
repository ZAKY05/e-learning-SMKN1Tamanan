<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kuis extends Model
{
    use HasFactory;

    protected $table = 'kuis';
    protected $primaryKey = 'id_kuis';

    protected $fillable = [
        'guru_id',
        'mapel_id',
        'kelas_id',
        'materi_id',
        'judul_kuis',
        'deskripsi',
        'tipe',
        'durasi_menit',
        'tanggal_mulai',
        'tanggal_selesai',
        'bobot_nilai',
        'acak_soal',
        'tampilkan_nilai',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'acak_soal' => 'boolean',
        'tampilkan_nilai' => 'boolean',
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

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id', 'id_materi');
    }

    public function soalKuis()
    {
        return $this->hasMany(SoalKuis::class, 'kuis_id', 'id_kuis');
    }
}
