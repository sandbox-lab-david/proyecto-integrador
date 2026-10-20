<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Facultad;

class FacultadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Facultad::create([
            'codigo' => 'ING',
            'nombre' => 'Sistemas, Telecomunicaciones y Electrónica',
            'correo_notificaciones' => 'asistente_sistemas@uees.edu.ec',
            'activo' => true,
        ]);

        Facultad::create([
            'codigo' => 'MED',
            'nombre' => 'Ciencias Médicas',
            'correo_notificaciones' => 'asistente_medicina@uees.edu.ec',
            'activo' => true,
        ]);
    }
}
