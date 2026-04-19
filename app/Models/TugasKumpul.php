<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasKumpul extends Model
{
    use HasFactory;

    protected $table = 'tugas_kumpul';
    protected $primaryKey = 'id_kumpul';

    protected $fillable = [
        'tugas_id',
        'siswa_id',
        'file_jawaban',
        'komentar_siswa',
        'waktu_kumpul',
        'nilai',
        'komentar_guru',
        'status_penilaian',
    ];

    protected $casts = [
        'waktu_kumpul' => 'datetime',
        'nilai' => 'integer',
    ];

    /**
     * Relasi ke tugas
     */
    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id', 'id_tugas');
    }

    /**
     * Relasi ke siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }

    /**
     * Cek apakah kumpul tepat waktu atau terlambat
     */
    public function isTerlambat()
    {
        return $this->waktu_kumpul->gt($this->tugas->deadline);
    }

    /**
     * Accessor: status kelengkapan
     */
    public function getStatusAttribute()
    {
        if ($this->nilai !== null) {
            return 'Dinilai';
        }
        return 'Belum Dinilai';
    }
}
