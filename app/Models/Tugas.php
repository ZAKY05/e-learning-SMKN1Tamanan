<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';
    protected $primaryKey = 'id_tugas';

    protected $guarded = ['id_tugas'];

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

    public function pengumpulan()
    {
        return $this->hasMany(PengumpulanTugas::class, 'tugas_id', 'id_tugas');
    }
}
