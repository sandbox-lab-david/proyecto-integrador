<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolicitudRevisionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados_solicitud')->insertOrIgnore([
            ['id' => 1, 'codigo' => 'ENVIADO', 'nombre' => 'Enviado por Estudiante'],
            ['id' => 2, 'codigo' => 'CORRECCION', 'nombre' => 'Requiere Corrección'],
            ['id' => 3, 'codigo' => 'APROBADO_ASISTENTE', 'nombre' => 'Aprobado por Asistente'],
            ['id' => 4, 'codigo' => 'EN_CONSEJO', 'nombre' => 'En Agenda de Consejo'],
            ['id' => 5, 'codigo' => 'RESUELTO', 'nombre' => 'Resuelto por Consejo'],
        ]);
    }
}