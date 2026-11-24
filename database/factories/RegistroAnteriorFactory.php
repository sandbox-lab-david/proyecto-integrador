<?php

namespace Database\Factories;

use App\Models\RegistroAnterior;
use App\Models\SolicitudMateria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistroAnterior>
 */
class RegistroAnteriorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'solicitud_materia_id' => SolicitudMateria::factory(),
            'anio' => fake()->numberBetween(2022, 2025),
            'periodo_texto' => fake()->randomElement(['Ordinario I', 'Ordinario II', 'Extraordinario']),
            'nota' => fake()->randomFloat(2, 0, 59),
        ];
    }
}
