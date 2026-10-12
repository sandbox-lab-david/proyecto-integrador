<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Solicitud;

class SolicitudSeeder extends Seeder
{
    public function run(): void
    {
        Solicitud::create([
            'estudiante' => 'Carlos Mendoza',
            'codigo' => 'U20231234',
            'carrera' => 'Ingeniería en Software',
            'tramite' => 'Examen de recuperación',
            'materia' => 'Programación Avanzada',
            'estado' => 'Enviado',
            'motivo' => 'Justificación por calamidad doméstica durante la semana de exámenes.'
        ]);

        Solicitud::create([
            'estudiante' => 'Ana Torres',
            'codigo' => 'U20235678',
            'carrera' => 'Ingeniería en Sistemas',
            'tramite' => 'Cambio de carrera',
            'materia' => 'General',
            'estado' => 'Enviado',
            'motivo' => 'Solicitud formal de transicion a la malla de Tecnologías de la Información.'
        ]);
    }
}