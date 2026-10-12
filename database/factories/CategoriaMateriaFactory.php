<?php

namespace Database\Factories;

use App\Models\CategoriaMateria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoriaMateria>
 */
class CategoriaMateriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'nombre' => fake()->unique()->word(),
        ];
    }
}
