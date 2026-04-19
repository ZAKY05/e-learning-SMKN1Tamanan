<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianHasil extends Model
{
    use HasFactory;

    protected $table = 'ujian_hasil';
    protected $primaryKey = 'id_hasil';

    protected $fillable = [
        'ujian_id',
        'siswa_id',
        'nilai_total',
        'waktu_mulai',
        'waktu_selesai',
        'is_finished',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'is_finished' => 'boolean',
        'nilai_total' => 'integer',
    ];

    /**
     * Relasi ke ujian
     */
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'id_ujian');
    }

    /**
     * Relasi ke siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }

    /**
     * Relasi ke jawaban per soal
     */
    public function jawaban()
    {
        return $this->hasMany(UjianJawaban::class, 'hasil_id', 'id_hasil');
    }
}
