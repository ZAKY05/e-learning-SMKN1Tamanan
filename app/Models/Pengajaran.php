<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajaran extends Model
{
    use HasFactory;

    protected $table = 'pengajaran';
    protected $primaryKey = 'id_pengajaran';

    protected $fillable = [
        'guru_id',
        'kelas_id',
        'mapel_id',
        'tahun_ajaran',
        'semester',
    ];

    /**
     * Relasi ke guru (pengajar)
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'id_guru');
    }

    /**
     * Relasi ke kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id_kelas');
    }

    /**
     * Relasi ke mata pelajaran
     */
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id_mapel');
    }

    /**
     * Relasi ke materi
     */
    public function materi()
    {
        return $this->hasMany(Materi::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Relasi ke tugas
     */
    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Relasi ke bank soal
     */
    public function bankSoal()
    {
        return $this->hasMany(BankSoal::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Relasi ke ujian
     */
    public function ujian()
    {
        return $this->hasMany(Ujian::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Relasi ke absensi
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Accessor: nama lengkap pengajaran (contoh: TKJ X - Pemrograman Dasar - Ganji 2024/2025)
     */
    public function getNamaPengajaranAttribute()
    {
        return "{$this->kelas->nama_kelas_lengkap} - {$this->mapel->nama_mapel} - " . ucfirst($this->semester) . " {$this->tahun_ajaran}";
    }
}
