<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilKuis extends Model
{
    use HasFactory;

    protected $table = 'hasil_kuis';
    protected $primaryKey = 'id_hasil';

    protected $fillable = [
        'kuis_id',
        'siswa_id',
        'waktu_mulai',
        'waktu_selesai',
        'nilai',
        'jumlah_benar',
        'jumlah_salah',
        'tidak_dijawab',
        'status',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function kuis()
    {
        return $this->belongsTo(Kuis::class, 'kuis_id', 'id_kuis');
    }

    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }

    public function jawabanSiswa()
    {
        return $this->hasMany(JawabanSiswa::class, 'hasil_id', 'id_hasil');
    }
}
