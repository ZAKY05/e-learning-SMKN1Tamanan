<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $table = 'ujian';
    protected $primaryKey = 'id_ujian';

    protected $fillable = [
        'pengajaran_id',
        'judul',
        'deskripsi',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'acak_soal',
        'acak_opsi',
        'status',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'acak_soal' => 'boolean',
        'acak_opsi' => 'boolean',
        'durasi_menit' => 'integer',
    ];

    /**
     * Relasi ke pengajaran
     */
    public function pengajaran()
    {
        return $this->belongsTo(Pengajaran::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Relasi ke bank soal (through ujian_soal)
     */
    public function soal()
    {
        return $this->belongsToMany(BankSoal::class, 'ujian_soal', 'ujian_id', 'soal_id')
                    ->withPivot('urutan')
                    ->withTimestamps();
    }

    /**
     * Relasi ke hasil ujian siswa
     */
    public function hasil()
    {
        return $this->hasMany(UjianHasil::class, 'ujian_id', 'id_ujian');
    }

    /**
     * Relasi ke nilai (polymorphic)
     */
    public function nilai()
    {
        return $this->morphMany(Nilai::class, 'nilaiable');
    }

    /**
     * Cek apakah ujian sedang berlangsung
     */
    public function isActive()
    {
        $now = now();
        return $now->between($this->waktu_mulai, $this->waktu_selesai);
    }

    /**
     * Scope: ujian yang butuh koreksi essay
     */
    public function scopeButuhKoreksiEssay($query)
    {
        return $query->whereHas('hasil.jawaban', function ($q) {
            $q->whereNull('nilai_perolehan');
        });
    }
}
