<?php

namespace Database\Seeders;

use App\Models\Periodo;
use Illuminate\Database\Seeder;

class PeriodoSeeder extends Seeder
{
    public function run(): void
    {
        Periodo::create([
            'codigo' => 'ORD-1-2026',
            'nombre' => 'Ordinario I 2026',
            'tipo' => 'ordinario',
            'fecha_inicio' => '2026-05-04',
            'fecha_fin' => '2026-08-28',
            'semana_primer_parcial' => 8,
            'activo' => false,
        ]);

        Periodo::create([
            'codigo' => 'ORD-2-2026',
            'nombre' => 'Ordinario II 2026',
            'tipo' => 'ordinario',
            'fecha_inicio' => '2026-09-07',
            'fecha_fin' => '2026-12-18',
            'semana_primer_parcial' => 8,
            'activo' => true,
        ]);
    }
}
