<?php

namespace Database\Factories;

use App\Models\Malla;
use App\Models\MallaMateria;
use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MallaMateria>
 */
class MallaMateriaFactory extends Factory
{
    protected $model = MallaMateria::class;

    public function definition(): array
    {
        return [
            'malla_id' => Malla::factory(),
            'materia_id' => Materia::factory(),
            'nivel' => fake()->numberBetween(1, 10),
        ];
    }
}
