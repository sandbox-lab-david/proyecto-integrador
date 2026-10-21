<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Carrera;
use App\Models\Facultad;

class CarreraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
        $facultadIngenieria = Facultad::where('codigo', 'ING')->first();
        $facultadMedicina = Facultad::where('codigo', 'MED')->first();

        // Carreras de Ingeniería
        Carrera::create([
            'facultad_id' => $facultadIngenieria->id,
            'codigo' => 'SIS',
            'nombre' => 'Ingeniería en Sistemas',
            'nivel' => 'grado',
            'admite_tercer_registro' => true,
            'activo' => true,
        ]);

        // Carreras de Medicina
        Carrera::create([
            'facultad_id' => $facultadMedicina->id,
            'codigo' => 'MED-GEN',
            'nombre' => 'Medicina',
            'nivel' => 'grado',
            'admite_tercer_registro' => false,
            'activo' => true,
        ]);
    }
}
