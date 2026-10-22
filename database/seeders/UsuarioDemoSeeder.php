<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use App\Models\Rol;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioDemoSeeder extends Seeder
{
    private const CLAVE = 'password123';

    private const PERSONAL = [
        ['admin@uees.edu.ec', 'Administrador G1', 'Administrador', 'Académico', 'admin', 'ADM-001'],
        ['asistente@uees.edu.ec', 'Asistente de Ingeniería', 'Ana', 'Asistente', 'asistente', 'AST-001'],
        ['director@uees.edu.ec', 'Director de Computación', 'Diego', 'Director', 'director', 'DIR-001'],
        ['consulta@uees.edu.ec', 'Consulta Secretaría General', 'Carla', 'Consulta', 'consulta', 'CON-001'],
    ];

    public function run(): void
    {
        foreach (self::PERSONAL as [$correo, $nombre, $nombres, $apellidos, $rol, $codigo]) {
            $usuario = User::create([
                'name' => $nombre,
                'email' => $correo,
                'password' => Hash::make(self::CLAVE),
                'tipo' => 'trabajador',
                'activo' => true,
            ]);

            $trabajador = Trabajador::create([
                'user_id' => $usuario->id,
                'codigo' => $codigo,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'correo' => $correo,
                'activo' => true,
            ]);

            DB::table('rol_trabajador')->insert([
                'trabajador_id' => $trabajador->id,
                'rol' => $rol,
                'rol_id' => Rol::where('codigo', $rol)->value('id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $estudiante = User::create([
            'name' => 'Estudiante de Prueba',
            'email' => 'estudiante@uees.edu.ec',
            'password' => Hash::make(self::CLAVE),
            'tipo' => 'estudiante',
            'activo' => true,
        ]);

        Estudiante::create([
            'user_id' => $estudiante->id,
            'codigo' => '2026000001',
            'nombres' => 'Estudiante',
            'apellidos' => 'de Prueba',
            'correo' => 'estudiante@uees.edu.ec',
        ]);

        User::factory()->count(10)->create([
            'tipo' => 'estudiante',
        ]);
    }
}
