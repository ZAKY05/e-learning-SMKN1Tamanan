<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Student extends Model
{
    use HasFactory;

    protected $table = 'student';
    protected $primaryKey = 'id_siswa';

    protected $fillable = [
        'nis',
        'nama',
        'jurusan_id',
        'kelas_id',
        'foto_profil',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'id_jurusan');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id_kelas');
    }

    // Tambahkan method ini ke model Student yang sudah ada

    // Relasi ke user (login)
    public function user()
    {
        return $this->hasOne(User::class, 'siswa_id', 'id_siswa');
    }

    // Relasi ke tugas_kumpul
    public function tugasKumpul()
    {
        return $this->hasMany(TugasKumpul::class, 'siswa_id', 'id_siswa');
    }

    // Relasi ke ujian_hasil
    public function ujianHasil()
    {
        return $this->hasMany(UjianHasil::class, 'siswa_id', 'id_siswa');
    }

    // Relasi ke absensi
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'siswa_id', 'id_siswa');
    }

    // Relasi ke nilai
    public function nilai()
    {
        return $this->hasMany(Nilai::class, 'siswa_id', 'id_siswa');
    }

    // Relasi ke rapor
    public function rapor()
    {
        return $this->hasMany(Rapor::class, 'siswa_id', 'id_siswa');
    }
}
