<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kelas>
 */
class KelasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tingkat'=>fake()->randomElement([10,11,12]),
            'jurusan_id' => \App\Models\Jurusan::inRandomOrder()->first()->id_jurusan,
            'golongan'=>fake()->numberBetween(1,3),
        ];
    }
}
