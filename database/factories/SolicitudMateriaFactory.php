<?php

namespace Database\Factories;

use App\Enums\EstadoMateria;
use App\Models\Curso;
use App\Models\Solicitud;
use App\Models\SolicitudMateria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SolicitudMateria>
 */
class SolicitudMateriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'solicitud_id' => Solicitud::factory(),
            // Factory de G1; mientras no exista, usar ->externa() o pasar curso_id.
            'curso_id' => fn () => Curso::factory(),
            'materia_externa' => null,
            'institucion_externa' => null,
            'nota_declarada' => fake()->randomFloat(2, 0, 100),
            'asistencia_declarada' => fake()->randomFloat(2, 0, 100),
            'estado_declarado' => fake()->randomElement(EstadoMateria::cases()),
            'intento_declarado' => fake()->numberBetween(1, 3),
            'verificado' => false,
            'verificado_por' => null,
            'verificado_at' => null,
            'observacion_verificacion' => null,
        ];
    }

    /**
     * Materia de otra institución (homologación): no tiene curso.
     */
    public function externa(): static
    {
        return $this->state(fn (array $attributes) => [
            'curso_id' => null,
            'materia_externa' => 'Materia externa de prueba',
            'institucion_externa' => 'Institución de prueba',
        ]);
    }
}
