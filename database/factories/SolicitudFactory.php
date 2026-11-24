<?php

namespace Database\Factories;

use App\Models\CarreraEstudiante;
use App\Models\Periodo;
use App\Models\Solicitud;
use App\Models\SolicitudMateria;
use App\Models\TipoTramite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Solicitud>
 */
class SolicitudFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Las llaves hacia otros grupos usan la factory de su dueño (regla 9 de
     * modelos.xlsx). Mientras esas factories no existan hay que pasar los ids
     * al crear. estado_solicitud_id no tiene valor por defecto: depende de los
     * estados y constantes que publique G3.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => sprintf('SOL-%d-%05d', now()->year, fake()->unique()->numberBetween(1, 99999)),
            'carrera_estudiante_id' => fn () => CarreraEstudiante::factory(),
            'tipo_tramite_id' => fn () => TipoTramite::factory(),
            'periodo_id' => fn () => Periodo::factory(),
            'detalle' => ['motivo' => fake()->sentence()],
            'gpa_declarado' => fake()->randomFloat(2, 60, 100),
            'gpa_verificado' => false,
            'fecha_ultima_recuperacion' => null,
            'declaracion_veracidad' => true,
            'codigo_sistema' => null,
            'enviada_at' => null,
        ];
    }

    /**
     * El estudiante ya subió el PDF firmado. El estado «Enviado» se pasa al
     * crear hasta que G3 publique sus constantes.
     */
    public function enviada(): static
    {
        return $this->state(fn (array $attributes) => [
            'enviada_at' => now(),
        ]);
    }

    public function conMaterias(int $cantidad = 1): static
    {
        return $this->has(SolicitudMateria::factory()->count($cantidad), 'materias');
    }
}
