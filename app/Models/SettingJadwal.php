<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingJadwal extends Model
{
    use HasFactory;

    protected $table = 'setting_jadwal';

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'total_jam_per_minggu',
        'jam_mulok_tambahan',
        'jam_senin',
        'jam_selasa',
        'jam_rabu',
        'jam_kamis',
        'jam_jumat',
        'waktu_mulai',
        'durasi_jam_menit',
        'slot_khusus',
    ];

    protected $casts = [
        'slot_khusus' => 'array',
    ];

    /**
     * Total jam efektif (termasuk mulok)
     */
    public function getTotalEfektifAttribute(): int
    {
        return $this->total_jam_per_minggu + $this->jam_mulok_tambahan;
    }

    /**
     * Distribusi jam per hari sebagai array
     */
    public function getJamPerHari(): array
    {
        return [
            'senin'  => $this->jam_senin,
            'selasa' => $this->jam_selasa,
            'rabu'   => $this->jam_rabu,
            'kamis'  => $this->jam_kamis,
            'jumat'  => $this->jam_jumat,
        ];
    }

    /**
     * Total jam dari distribusi per hari
     */
    public function getTotalDistribusiAttribute(): int
    {
        return $this->jam_senin + $this->jam_selasa + $this->jam_rabu + $this->jam_kamis + $this->jam_jumat;
    }

    /**
     * Max jam pada hari manapun (untuk menentukan jumlah kolom)
     */
    public function getMaxJamPerHariAttribute(): int
    {
        return max($this->jam_senin, $this->jam_selasa, $this->jam_rabu, $this->jam_kamis, $this->jam_jumat);
    }

    /**
     * Hitung waktu mulai dan selesai untuk setiap jam_ke pada hari tertentu
     * Memperhitungkan slot khusus (istirahat, upacara, dll)
     */
    public function hitungWaktuSlot(string $hari): array
    {
        $slots = [];
        $durasi = $this->durasi_jam_menit;
        $current = \Carbon\Carbon::parse($this->waktu_mulai);
        $jamHari = $this->getJamPerHari()[$hari] ?? 0;
        $slotKhusus = $this->slot_khusus ?? [];

        // Cek apakah ada slot "sebelum_jam" 1 untuk hari ini
        foreach ($slotKhusus as $sk) {
            if (isset($sk['sebelum_jam']) && $sk['sebelum_jam'] == 1) {
                $berlaku = !isset($sk['hari']) || $sk['hari'] === null || $sk['hari'] === $hari;
                if ($berlaku) {
                    $slots[] = [
                        'type'          => 'khusus',
                        'label'         => $sk['label'] ?? 'Khusus',
                        'waktu_mulai'   => $current->format('H:i'),
                        'waktu_selesai' => $current->copy()->addMinutes($sk['durasi'] ?? 30)->format('H:i'),
                        'durasi'        => $sk['durasi'] ?? 30,
                    ];
                    $current->addMinutes($sk['durasi'] ?? 30);
                }
            }
        }

        for ($j = 1; $j <= $jamHari; $j++) {
            $slots[] = [
                'type'          => 'reguler',
                'jam_ke'        => $j,
                'waktu_mulai'   => $current->format('H:i'),
                'waktu_selesai' => $current->copy()->addMinutes($durasi)->format('H:i'),
            ];
            $current->addMinutes($durasi);

            // Cek slot khusus setelah jam ini
            foreach ($slotKhusus as $sk) {
                if (isset($sk['setelah_jam']) && $sk['setelah_jam'] == $j) {
                    $berlaku = !isset($sk['hari']) || $sk['hari'] === null || $sk['hari'] === $hari;
                    if ($berlaku) {
                        $slots[] = [
                            'type'          => 'khusus',
                            'label'         => $sk['label'] ?? 'Istirahat',
                            'waktu_mulai'   => $current->format('H:i'),
                            'waktu_selesai' => $current->copy()->addMinutes($sk['durasi'] ?? 15)->format('H:i'),
                            'durasi'        => $sk['durasi'] ?? 15,
                        ];
                        $current->addMinutes($sk['durasi'] ?? 15);
                    }
                }
            }
        }

        return $slots;
    }
}
