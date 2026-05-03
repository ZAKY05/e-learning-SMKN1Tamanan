<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Mapel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // \App\Models\Jurusan::insert([
        //     ['nama_jurusan'=>'RPL'],
        //     ['nama_jurusan'=>'TKJ'],
        //     ['nama_jurusan'=>'Elektro'],
        // ]);
        // \App\Models\Kelas::factory()->count(9)->create();
        // \App\Models\Student::factory()->count(30)->create();

        // Admin
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Administrator', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        // // Mapel
        // \App\Models\Mapel::insert([
        //     ['nama_mapel' => 'Matematika'],
        //     ['nama_mapel' => 'Bahasa Indonesia'],
        //     ['nama_mapel' => 'Bahasa Inggris'],
        // ]);

        // // Guru
        // $guru1 = \App\Models\Guru::create([
        //     'nip' => '12344567765',
        //     'nama' => 'Budi Santoso, S.Pd',
        //     'no_telp' => '081234567890',
        //     'alamat' => 'Jl. Merdeka No. 1, Jakarta',
        // ]);
        // $guru1->mapels()->attach([1, 3]);

        // $guru2 = \App\Models\Guru::create([
        //     'nip' => '19851022010012002',
        //     'nama' => 'Siti Aminah, M.Kom',
        //     'no_telp' => '082345678901',
        //     'alamat' => 'Jl. Sudirman No. 2, Bandung',
        // ]);
        // $guru2->mapels()->attach([2]);

        // Akun Guru
        // \App\Models\User::firstOrCreate(
        //     ['email' => 'budi@guru.com'],
        //     ['name' => 'Budi Santoso, S.Pd', 'password' => bcrypt('password'), 'role' => 'guru', 'guru_id' => $guru1->id_guru]
        // );
        // \App\Models\User::firstOrCreate(
        //     ['email' => 'siti@guru.com'],
        //     ['name' => 'Siti Aminah, M.Kom', 'password' => bcrypt('password'), 'role' => 'guru', 'guru_id' => $guru2->id_guru]
        // );

    }
}
