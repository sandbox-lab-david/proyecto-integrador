<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Trabajador;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    public const PARALELOS = [
        ['MAT101', 'A1', 'DOC-001'],
        ['COM101', 'A1', 'DOC-002'],
        ['COM101', 'B1', 'DOC-002'],
        ['GEN101', 'A1', 'DOC-003'],
        ['MAT201', 'A1', 'DOC-001'],
        ['COM201', 'A1', 'DOC-002'],
        ['FIS101', 'A1', 'DOC-003'],
    ];

    public function run(): void
    {
        $periodo = Periodo::where('codigo', 'ORD-2-2026')->firstOrFail();

        foreach (self::PARALELOS as [$materia, $paralelo, $docente]) {
            Curso::create([
                'materia_id' => Materia::where('codigo', $materia)->value('id'),
                'periodo_id' => $periodo->id,
                'profesor_id' => Trabajador::where('codigo', $docente)->value('id'),
                'paralelo' => $paralelo,
                'cupo' => 35,
            ]);
        }
    }
}
