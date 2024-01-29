<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class BmnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nup' => $this->faker->isbn13(),
            'nama' => $this->faker->word,
            'keterangan' => $this->faker->realText($maxNbChars = 200, $indexSize = 2),
            'penanggung_jawab' => $this->faker->firstName(),
            'ruangan' => $this->faker->word,
            'tahun_perolehan' => '2023',
        ];
    }
}
