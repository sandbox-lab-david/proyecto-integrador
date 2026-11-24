<?php

namespace Database\Factories;

use App\Models\DocumentoFirmado;
use App\Models\DocumentoGenerado;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentoFirmado>
 */
class DocumentoFirmadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'documento_generado_id' => DocumentoGenerado::factory(),
            'ruta' => 'firmados/'.fake()->uuid().'.pdf',
            'tamano_bytes' => fake()->numberBetween(50_000, 5_000_000),
            'subido_por' => User::factory(),
        ];
    }
}
