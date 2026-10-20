<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Periodo;

class PeriodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Periodo::create([
            'codigo' => 'ORD-1-2026',
            'nombre' => 'Ordinario I 2026',
            'tipo' => 'ordinario',
            'fecha_inicio' => '2026-05-04',
            'fecha_fin' => '2026-08-28',
            'semana_primer_parcial' => 8,
            'activo' => true,
        ]);
    }
}
