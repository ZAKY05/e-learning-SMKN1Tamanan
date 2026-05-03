<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;

class JadwalTestSeeder extends Seeder
{
    /**
     * Seed dummy data untuk testing generate jadwal otomatis.
     * 5 Jurusan, 30 Kelas (5×3×2), 25 Guru, Mapel umum + jurusan, relasi guru_mapel
     */
    // public function run(): void
    // {
    //     // Hapus data lama supaya tidak bentrok
    //     DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    //     DB::table('jadwal_pelajaran')->truncate();
    //     DB::table('guru_mapel')->truncate();
    //     DB::table('mapel')->truncate();
    //     DB::table('kelas')->truncate();
    //     DB::table('guru')->truncate();
    //     DB::table('jurusan')->truncate();
    //     DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    //     // Perbesar kolom nama_jurusan jika masih 30
    //     Schema::table('jurusan', function (Blueprint $table) {
    //         $table->string('nama_jurusan', 60)->change();
    //     });

    //     // ============================================
    //     // 1. JURUSAN (5 jurusan)
    //     // ============================================
    //     $jurusanData = [
    //         ['nama_jurusan' => 'Kriya Kreatif Kayu dan Rotan',       'deskripsi' => 'Jurusan keahlian kriya kayu dan rotan'],
    //         ['nama_jurusan' => 'Teknik Audio Video',                  'deskripsi' => 'Jurusan keahlian teknik audio video'],
    //         ['nama_jurusan' => 'Desain Komunikasi Visual',            'deskripsi' => 'Jurusan keahlian desain grafis dan visual'],
    //         ['nama_jurusan' => 'Desain dan Produksi Busana',          'deskripsi' => 'Jurusan keahlian tata busana dan fashion'],
    //         ['nama_jurusan' => 'Kriya Kreatif Batik dan Tekstil',     'deskripsi' => 'Jurusan keahlian batik dan tekstil'],
    //     ];

    //     $jurusanIds = [];
    //     foreach ($jurusanData as $j) {
    //         $jurusanIds[] = DB::table('jurusan')->insertGetId(array_merge($j, [
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]));
    //     }

    //     // ============================================
    //     // 2. KELAS (5 jurusan × 3 tingkat × 2 golongan = 30 kelas)
    //     // ============================================
    //     $kelasIds = [];
    //     foreach ($jurusanIds as $jId) {
    //         foreach ([10, 11, 12] as $tingkat) {
    //             foreach (['A', 'B'] as $golongan) {
    //                 $kelasIds[] = DB::table('kelas')->insertGetId([
    //                     'tingkat'    => $tingkat,
    //                     'jurusan_id' => $jId,
    //                     'golongan'   => $golongan,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         }
    //     }

    //     // ============================================
    //     // 3. GURU (25 guru)
    //     // ============================================
    //     $guruData = [
    //         // Guru mapel umum (10 guru)
    //         ['nip' => '198501012010011001', 'nama' => 'Siti Aminah'],
    //         ['nip' => '198602022011012002', 'nama' => 'Budi Santoso'],
    //         ['nip' => '198703032012013003', 'nama' => 'Dewi Lestari'],
    //         ['nip' => '198804042013014004', 'nama' => 'Ahmad Fauzi'],
    //         ['nip' => '198905052014015005', 'nama' => 'Rina Wulandari'],
    //         ['nip' => '199006062015016006', 'nama' => 'Hendra Wijaya'],
    //         ['nip' => '199107072016017007', 'nama' => 'Nur Hasanah'],
    //         ['nip' => '199208082017018008', 'nama' => 'Teguh Prasetyo'],
    //         ['nip' => '199309092018019009', 'nama' => 'Lina Marlina'],
    //         ['nip' => '199410102019020010', 'nama' => 'Dani Kurniawan'],
    //         // Guru mapel jurusan (15 guru, 3 per jurusan)
    //         ['nip' => '199511112020021011', 'nama' => 'Agus Supriyadi'],
    //         ['nip' => '199612122021022012', 'nama' => 'Eko Purnomo'],
    //         ['nip' => '199713132022023013', 'nama' => 'Fitri Handayani'],
    //         ['nip' => '199814142023024014', 'nama' => 'Irwan Setiawan'],
    //         ['nip' => '199915152024025015', 'nama' => 'Joko Susilo'],
    //         ['nip' => '200016162025026016', 'nama' => 'Kartini Rahayu'],
    //         ['nip' => '200117172026027017', 'nama' => 'Lukman Hakim'],
    //         ['nip' => '200218182027028018', 'nama' => 'Maya Sari'],
    //         ['nip' => '200319192028029019', 'nama' => 'Nanda Pratama'],
    //         ['nip' => '200420202029030020', 'nama' => 'Oktavia Putri'],
    //         ['nip' => '200521212030031021', 'nama' => 'Pandu Wicaksono'],
    //         ['nip' => '200622222031032022', 'nama' => 'Qori Fatimah'],
    //         ['nip' => '200723232032033023', 'nama' => 'Rudi Hermawan'],
    //         ['nip' => '200824242033034024', 'nama' => 'Sari Indah'],
    //         ['nip' => '200925252034035025', 'nama' => 'Taufik Hidayat'],
    //     ];

    //     $guruIds = [];
    //     foreach ($guruData as $g) {
    //         $guruIds[] = DB::table('guru')->insertGetId(array_merge($g, [
    //             'no_telp'    => '08' . rand(1000000000, 9999999999),
    //             'alamat'     => 'Bondowoso, Jawa Timur',
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]));
    //     }

    //     // ============================================
    //     // 4. MAPEL UMUM (10 mapel, berlaku semua jurusan)
    //     // ============================================
    //     $mapelUmum = [
    //         ['nama_mapel' => 'Matematika',            'jam_per_minggu' => 4],
    //         ['nama_mapel' => 'Bahasa Indonesia',      'jam_per_minggu' => 3],
    //         ['nama_mapel' => 'Bahasa Inggris',        'jam_per_minggu' => 3],
    //         ['nama_mapel' => 'Pendidikan Agama',      'jam_per_minggu' => 2],
    //         ['nama_mapel' => 'PKN',                   'jam_per_minggu' => 2],
    //         ['nama_mapel' => 'Sejarah Indonesia',     'jam_per_minggu' => 2],
    //         ['nama_mapel' => 'Penjaskes',             'jam_per_minggu' => 2],
    //         ['nama_mapel' => 'Seni Budaya',           'jam_per_minggu' => 2],
    //         ['nama_mapel' => 'Informatika',           'jam_per_minggu' => 2],
    //         ['nama_mapel' => 'Projek IPAS',           'jam_per_minggu' => 2],
    //     ];

    //     $mapelUmumIds = [];
    //     foreach ($mapelUmum as $m) {
    //         $mapelUmumIds[] = DB::table('mapel')->insertGetId([
    //             'nama_mapel'     => $m['nama_mapel'],
    //             'jenis'          => 'umum',
    //             'jam_per_minggu' => $m['jam_per_minggu'],
    //             'jurusan_id'     => null,
    //             'created_at'     => now(),
    //             'updated_at'     => now(),
    //         ]);
    //     }

    //     // ============================================
    //     // 5. MAPEL JURUSAN (3 mapel per jurusan = 15 mapel)
    //     // ============================================
    //     $mapelJurusan = [
    //         // Kriya Kreatif Kayu dan Rotan
    //         ['nama_mapel' => 'Dasar Kriya Kayu',        'jam_per_minggu' => 6, 'jur_idx' => 0],
    //         ['nama_mapel' => 'Teknik Ukir Kayu',        'jam_per_minggu' => 4, 'jur_idx' => 0],
    //         ['nama_mapel' => 'Desain Produk Rotan',     'jam_per_minggu' => 4, 'jur_idx' => 0],
    //         // Teknik Audio Video
    //         ['nama_mapel' => 'Dasar Elektronika',       'jam_per_minggu' => 6, 'jur_idx' => 1],
    //         ['nama_mapel' => 'Teknik Audio',            'jam_per_minggu' => 4, 'jur_idx' => 1],
    //         ['nama_mapel' => 'Produksi Video',          'jam_per_minggu' => 4, 'jur_idx' => 1],
    //         // Desain Komunikasi Visual
    //         ['nama_mapel' => 'Dasar Desain Grafis',     'jam_per_minggu' => 6, 'jur_idx' => 2],
    //         ['nama_mapel' => 'Fotografi',               'jam_per_minggu' => 4, 'jur_idx' => 2],
    //         ['nama_mapel' => 'Animasi 2D/3D',           'jam_per_minggu' => 4, 'jur_idx' => 2],
    //         // Desain dan Produksi Busana
    //         ['nama_mapel' => 'Dasar Tata Busana',       'jam_per_minggu' => 6, 'jur_idx' => 3],
    //         ['nama_mapel' => 'Pola dan Menjahit',       'jam_per_minggu' => 4, 'jur_idx' => 3],
    //         ['nama_mapel' => 'Desain Fashion',          'jam_per_minggu' => 4, 'jur_idx' => 3],
    //         // Kriya Kreatif Batik dan Tekstil
    //         ['nama_mapel' => 'Dasar Batik',             'jam_per_minggu' => 6, 'jur_idx' => 4],
    //         ['nama_mapel' => 'Teknik Pewarnaan',        'jam_per_minggu' => 4, 'jur_idx' => 4],
    //         ['nama_mapel' => 'Desain Tekstil',          'jam_per_minggu' => 4, 'jur_idx' => 4],
    //     ];

    //     $mapelJurusanIds = [];
    //     foreach ($mapelJurusan as $m) {
    //         $mapelJurusanIds[] = DB::table('mapel')->insertGetId([
    //             'nama_mapel'     => $m['nama_mapel'],
    //             'jenis'          => 'jurusan',
    //             'jam_per_minggu' => $m['jam_per_minggu'],
    //             'jurusan_id'     => $jurusanIds[$m['jur_idx']],
    //             'created_at'     => now(),
    //             'updated_at'     => now(),
    //         ]);
    //     }

    //     // ============================================
    //     // 6. RELASI GURU ↔ MAPEL (guru_mapel)
    //     // ============================================
    //     $guruMapel = [];

    //     // 10 guru umum → masing-masing mengajar 1 mapel umum
    //     for ($i = 0; $i < 10; $i++) {
    //         $guruMapel[] = [
    //             'guru_id'    => $guruIds[$i],
    //             'mapel_id'   => $mapelUmumIds[$i],
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ];
    //     }

    //     // 15 guru jurusan → 3 guru per jurusan, masing-masing 1 mapel jurusan
    //     for ($i = 0; $i < 15; $i++) {
    //         $guruMapel[] = [
    //             'guru_id'    => $guruIds[10 + $i],
    //             'mapel_id'   => $mapelJurusanIds[$i],
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ];
    //     }

    //     DB::table('guru_mapel')->insert($guruMapel);

    //     // ============================================
    //     // 7. AKUN USER ADMIN (untuk login testing)
    //     // ============================================
    //     if (DB::table('users')->where('email', 'admin@smkn1tamanan.sch.id')->doesntExist()) {
    //         DB::table('users')->insert([
    //             'name'       => 'Administrator',
    //             'email'      => 'admin@smkn1tamanan.sch.id',
    //             'password'   => Hash::make('password'),
    //             'role'       => 'admin',
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //     }

    //     $this->command->info('');
    //     $this->command->info('=== DATA DUMMY BERHASIL DI-SEED ===');
    //     $this->command->info('Jurusan   : 5');
    //     $this->command->info('Kelas     : 30  (5 jurusan × 3 tingkat × 2 golongan)');
    //     $this->command->info('Guru      : 25  (10 umum + 15 jurusan)');
    //     $this->command->info('Mapel     : 25  (10 umum + 15 jurusan)');
    //     $this->command->info('Guru-Mapel: 25 relasi');
    //     $this->command->info('Admin     : admin@smkn1tamanan.sch.id / password');
    //     $this->command->info('===================================');
    // }
}
