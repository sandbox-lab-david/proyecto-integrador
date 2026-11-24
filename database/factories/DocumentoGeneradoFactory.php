<?php

namespace Database\Factories;

use App\Models\DocumentoGenerado;
use App\Models\Solicitud;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentoGenerado>
 */
class DocumentoGeneradoFactory extends Factory
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
            'version' => 1,
            'ruta_docx' => 'documentos/'.fake()->uuid().'.docx',
            'ruta_pdf' => null,
        ];
    }
}
