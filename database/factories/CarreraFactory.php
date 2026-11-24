<?php

namespace Database\Factories;

use App\Models\Carrera;
use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Carrera>
 */
class CarreraFactory extends Factory
{
    protected $model = Carrera::class;

    public function definition(): array
    {
        return [
            'facultad_id' => Facultad::factory(),
            'codigo' => strtoupper($this->faker->unique()->lexify('C????')),
            'nombre' => 'Carrera de '.$this->faker->unique()->words(2, true),
            'nivel' => 'grado',
            'admite_tercer_registro' => true,
            'activo' => true,
        ];
    }
}
