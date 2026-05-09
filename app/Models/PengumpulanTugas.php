<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengumpulanTugas extends Model
{
    use HasFactory;

    protected $table = 'pengumpulan_tugas';
    protected $primaryKey = 'id_pengumpulan';

    protected $guarded = ['id_pengumpulan'];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id', 'id_tugas');
    }

    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }
}
