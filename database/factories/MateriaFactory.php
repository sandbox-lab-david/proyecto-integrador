<?php

namespace Database\Factories;

use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

class MateriaFactory extends Factory
{
    protected $model = Materia::class;

    public function definition(): array
    {
        return [
            'codigo' => 'MAT-' . $this->faker->unique()->numerify('###'),
            'nombre' => $this->faker->words(3, true),
            'creditos' => $this->faker->numberBetween(2, 6),
            'categoria_materia_id' => 1,
            'activo' => true, // Soluciona el fallo en /cursos/create
        ];
    }
}