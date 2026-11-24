<?php

namespace Database\Factories;

use App\Models\Respaldo;
use App\Models\Solicitud;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Respaldo>
 */
class RespaldoFactory extends Factory
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
            'documento_requerido_id' => null,
            'nombre_original' => 'respaldo-de-prueba.pdf',
            'ruta' => 'respaldos/'.fake()->uuid().'.pdf',
            'mime' => 'application/pdf',
            'tamano_bytes' => fake()->numberBetween(10_000, 2_000_000),
        ];
    }
}
