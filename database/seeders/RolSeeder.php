<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['codigo' => 'admin', 'nombre' => 'Administrador', 'descripcion' => 'Control total del sistema'],
            ['codigo' => 'asistente', 'nombre' => 'Asistente Académica', 'descripcion' => 'Revisa y procesa solicitudes'],
            ['codigo' => 'director', 'nombre' => 'Director de Carrera', 'descripcion' => 'Aprueba solicitudes académicas'],
            ['codigo' => 'docente', 'nombre' => 'Docente', 'descripcion' => 'Profesor titular'],
            ['codigo' => 'consulta', 'nombre' => 'Consulta', 'descripcion' => 'Solo lectura'],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}
