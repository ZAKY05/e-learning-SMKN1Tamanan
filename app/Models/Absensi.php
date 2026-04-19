<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';

    protected $fillable = [
        'pengajaran_id',
        'siswa_id',
        'tanggal',
        'status',
        'keterangan',
        'pertemuan_ke',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'pertemuan_ke' => 'integer',
    ];

    /**
     * Relasi ke pengajaran
     */
    public function pengajaran()
    {
        return $this->belongsTo(Pengajaran::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Relasi ke siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }

    /**
     * Scope: filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('tanggal', [$start, $end]);
    }
}
