<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Mapel;



class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';
    protected $primaryKey = 'id_guru';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nip',
        'nama',
        'jenis_kelamin',
        'no_telp',
        'alamat',
        'foto_profil',
        'max_jam_per_minggu',
    ];

    /**
     * Kode guru per semester
     */
    public function guruKodes()
    {
        return $this->hasMany(GuruKode::class, 'guru_id', 'id_guru');
    }

    /**
     * Get kode for specific tahun_ajaran & semester
     */
    public function getKode(string $tahunAjaran, string $semester): ?string
    {
        return $this->guruKodes()
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->value('kode');
    }

    /**
     * The subjects taught by the teacher.
     */
    public function mapels(): BelongsToMany
    {
        return $this->belongsToMany(
            Mapel::class,
            'guru_mapel',
            'guru_id',
            'mapel_id'
        );
    }

    // Tambahkan method ini ke model Guru yang sudah ada

    // Relasi ke user (login)
    public function user()
    {
        return $this->hasOne(User::class, 'guru_id', 'id_guru');
    }

   
}
