<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\DirectorCarrera;
use App\Models\Estudiante;
use App\Models\Facultad;
use App\Models\Modalidad;
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

    private const DOCENTES = [
        ['DOC-001', 'Luis', 'Paredes', 'lparedes.demo@uees.edu.ec'],
        ['DOC-002', 'María', 'Andrade', 'mandrade.demo@uees.edu.ec'],
        ['DOC-003', 'Carlos', 'Vera', 'cvera.demo@uees.edu.ec'],
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

            $this->trabajador($usuario->id, $codigo, $nombres, $apellidos, $correo, $rol);
        }

        foreach (self::DOCENTES as [$codigo, $nombres, $apellidos, $correo]) {
            $this->trabajador(null, $codigo, $nombres, $apellidos, $correo, 'docente');
        }

        $ingenieria = Facultad::where('codigo', 'ING')->first();
        if ($ingenieria) {
            Trabajador::where('codigo', 'AST-001')->first()->facultades()->attach($ingenieria->id);
        }

        $computacion = Carrera::where('codigo', 'COMP')->first();
        if ($computacion) {
            DirectorCarrera::create([
                'carrera_id' => $computacion->id,
                'trabajador_id' => Trabajador::where('codigo', 'DIR-001')->value('id'),
                'fecha_inicio' => '2026-01-01',
            ]);
        }

        $usuarioEstudiante = User::create([
            'name' => 'Estudiante de Prueba',
            'email' => 'estudiante@uees.edu.ec',
            'password' => Hash::make(self::CLAVE),
            'tipo' => 'estudiante',
            'activo' => true,
        ]);

        $estudiante = Estudiante::create([
            'user_id' => $usuarioEstudiante->id,
            'codigo' => '2026000001',
            'nombres' => 'Estudiante',
            'apellidos' => 'de Prueba',
            'correo' => 'estudiante@uees.edu.ec',
        ]);

        if ($computacion) {
            CarreraEstudiante::create([
                'estudiante_id' => $estudiante->id,
                'carrera_modalidad_id' => CarreraModalidad::where('carrera_id', $computacion->id)
                    ->where('modalidad_id', Modalidad::where('nombre', 'Presencial')->value('id'))
                    ->value('id'),
                'malla_id' => $computacion->mallas()->where('vigente', true)->value('id'),
                'fecha_inscripcion' => '2025-05-05',
                'estado' => 'activo',
            ]);
        }
    }

    private function trabajador(?int $usuarioId, string $codigo, string $nombres, string $apellidos, string $correo, string $rol): Trabajador
    {
        $trabajador = Trabajador::create([
            'user_id' => $usuarioId,
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

        return $trabajador;
    }
}
