<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Student;

class JurusanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_jurusan'=> fake()->randomElement(['RPL','TKJ','Elektro']),
            'deskripsi'=>fake()->paragraph(),
        ];
    }
}
