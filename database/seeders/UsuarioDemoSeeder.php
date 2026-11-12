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
        ['asistente.salud@uees.edu.ec', 'Asistente de Salud', 'Sofía', 'Salud', 'asistente', 'AST-SAL'],
        ['director.civil@uees.edu.ec', 'Directora de Civil', 'Cecilia', 'Civil', 'director', 'DIR-CIV'],
        ['director.medicina@uees.edu.ec', 'Director de Medicina', 'Mario', 'Medicina', 'director', 'DIR-MED'],
    ];

    private const ASISTENTES = [
        'AST-001' => 'ING',
        'AST-SAL' => 'SAL',
    ];

    private const DIRECTORES = [
        'DIR-001' => 'COMP',
        'DIR-CIV' => 'CIV',
        'DIR-MED' => 'MED',
    ];

    private const ESTUDIANTES = [
        ['estudiante@uees.edu.ec', '2026000001', 'Estudiante', 'de Prueba', 'COMP'],
        ['estudiante.civil@uees.edu.ec', '2026000002', 'Pedro', 'Civil', 'CIV'],
        ['estudiante.medicina@uees.edu.ec', '2026000003', 'Lucía', 'Medicina', 'MED'],
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

        foreach (self::ASISTENTES as $codigo => $facultad) {
            $facultadId = Facultad::where('codigo', $facultad)->value('id');
            if ($facultadId) {
                Trabajador::where('codigo', $codigo)->first()->facultades()->attach($facultadId);
            }
        }

        foreach (self::DIRECTORES as $codigo => $carrera) {
            $carreraId = Carrera::where('codigo', $carrera)->value('id');
            if ($carreraId) {
                DirectorCarrera::create([
                    'carrera_id' => $carreraId,
                    'trabajador_id' => Trabajador::where('codigo', $codigo)->value('id'),
                    'fecha_inicio' => '2026-01-01',
                ]);
            }
        }

        foreach (self::ESTUDIANTES as [$correo, $codigo, $nombres, $apellidos, $carrera]) {
            $this->estudiante($correo, $codigo, $nombres, $apellidos, Carrera::where('codigo', $carrera)->first());
        }
    }

    private function estudiante(string $correo, string $codigo, string $nombres, string $apellidos, ?Carrera $carrera): void
    {
        $usuario = User::create([
            'name' => "$nombres $apellidos",
            'email' => $correo,
            'password' => Hash::make(self::CLAVE),
            'tipo' => 'estudiante',
            'activo' => true,
        ]);

        $estudiante = Estudiante::create([
            'user_id' => $usuario->id,
            'codigo' => $codigo,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'correo' => $correo,
        ]);

        if ($carrera) {
            CarreraEstudiante::create([
                'estudiante_id' => $estudiante->id,
                'carrera_modalidad_id' => CarreraModalidad::where('carrera_id', $carrera->id)
                    ->where('modalidad_id', Modalidad::where('nombre', 'Presencial')->value('id'))
                    ->value('id'),
                'malla_id' => $carrera->mallas()->where('vigente', true)->value('id'),
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
