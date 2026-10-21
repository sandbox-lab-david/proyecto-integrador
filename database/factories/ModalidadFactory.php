<?php

namespace Database\Factories;

use App\Models\Modalidad;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModalidadFactory extends Factory
{
    protected $model = Modalidad::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Presencial', 'En Línea', 'Híbrida']),
        ];
    }
}