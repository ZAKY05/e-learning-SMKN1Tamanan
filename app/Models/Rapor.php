<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapor extends Model
{
    use HasFactory;

    protected $table = 'rapor';
    protected $primaryKey = 'id_rapor';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'tahun_ajaran',
        'semester',
        'nilai_mapels',
        'catatan_wali_kelas',
        'rata_rata',
        'ranking_kelas',
    ];

    protected $casts = [
        'nilai_mapels' => 'array', // JSON auto cast
        'rata_rata' => 'integer',
        'ranking_kelas' => 'integer',
    ];

    /**
     * Relasi ke siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }

    /**
     * Relasi ke kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id_kelas');
    }

    /**
     * Mendapatkan nilai untuk mapel tertentu
     */
    public function getNilaiMapel($mapelId)
    {
        $nilaiMapels = $this->nilai_mapels ?? [];
        foreach ($nilaiMapels as $item) {
            if ($item['mapel_id'] == $mapelId) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Mendapatkan predikat dari nilai
     */
    public static function getPredikat($nilai)
    {
        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B';
        if ($nilai >= 70) return 'C';
        if ($nilai >= 60) return 'D';
        return 'E';
    }
}
