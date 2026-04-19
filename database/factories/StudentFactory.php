<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Validation\Rules\Unique;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nis' => fake()->unique()->numerify('######'),
            'nama' => fake()->name(),
            'jurusan_id' => \App\Models\Jurusan::inRandomOrder()->first()->id_jurusan,
            'kelas_id' => \App\Models\Kelas::inRandomOrder()->first()->id_kelas,
            'foto_profil'=>'default.jpg',
        ];
    }
}
