<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';
    protected $primaryKey = 'id_tugas';

    protected $fillable = [
        'pengajaran_id',
        'judul',
        'deskripsi',
        'file_soal',
        'deadline',
        'maksimal_nilai',
        'status',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'maksimal_nilai' => 'integer',
    ];

    /**
     * Relasi ke pengajaran
     */
    public function pengajaran()
    {
        return $this->belongsTo(Pengajaran::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Relasi ke pengumpulan tugas
     */
    public function kumpulan()
    {
        return $this->hasMany(TugasKumpul::class, 'tugas_id', 'id_tugas');
    }

    /**
     * Relasi ke nilai (polymorphic)
     */
    public function nilai()
    {
        return $this->morphMany(Nilai::class, 'nilaiable');
    }

    /**
     * Scope: tugas yang belum dinilai (ada kumpulan tapi nilai null)
     */
    public function scopeBelumDinilai($query)
    {
        return $query->whereHas('kumpulan', function ($q) {
            $q->whereNull('nilai');
        });
    }

    /**
     * Cek apakah deadline sudah lewat
     */
    public function isExpired()
    {
        return now()->gt($this->deadline);
    }
}
