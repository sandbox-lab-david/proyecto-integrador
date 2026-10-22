<?php
namespace App\Services\Academico;

use App\Models\Curso;
use App\Models\Malla;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Periodo;
use Illuminate\Support\Carbon;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Trabajador;
use Illuminate\Support\Facades\DB;

class CatalogoAcademicoService
{

    // ===C01: Devuelve la lista de facultades
    public function facultades(bool $soloActivas=true): Collection
    {
        /*
        // =STUB=
        $facultadesFalsas = collect([
            (object)['id' => 1, 'codigo' => 'ING', 'nombre' => 'Ingenieria', 'correo_notificaciones' => 'ingenieriauees@uees.edu.ec' 'activo' => true],
            (object)['id' => 2, 'codigo' => 'MED', 'nombre' => 'Ciencias de la Salud', 'correo_notificaciones' => 'areasaluduees@uees.edu.ec' 'activo' => true],
            (object)['id' => 3, 'codigo' => 'ARQ', 'nombre' => 'Arquitectura y Diseño', 'correo_notificaciones' => 'arquitectura@uees.edu.ec' 'activo' => false],
        ]);

        if ($soloActivas){
        return $facultadesFalsas->where('activo', true)->values();
        }

        return $facultadesFalsas;
        */

        if ($soloActivas){
            return Facultad::where('activo', true)->get();
        }

        return Facultad::all();
    }

    // ===C02: Carreras por facultad
    public function carrerasPorFacultad(?int $facultadId = null, bool $soloActivas = true): Collection
    {
        return Carrera::query()
            ->when($facultadId, fn ($q) => $q->where('facultad_id', $facultadId))
            ->when($soloActivas, fn ($q) => $q->where('activo', true))
            ->orderBy('nombre')
            ->get();
    }

    // ===C03: Modalidades en que se ofrece una carrera
    public function modalidadesDeCarrera(int $carreraId): Collection
    {
        return Modalidad::whereIn('id', DB::table('carrera_modalidad')->where('carrera_id', $carreraId)->select('modalidad_id'))
            ->orderBy('nombre')
            ->get();
    }

    // ===C06: Periodos (Selects y filtros)
    public function periodos(bool $soloActivos = false): Collection
    {
        return Periodo::query()
            ->when($soloActivos, fn ($q) => $q->where('activo', true))
            ->orderByDesc('fecha_inicio')
            ->get();
    }

    // ===C07: Devuelve el periodo academico activo actualmente
    public function periodoActual()
    {
        /*
        // =STUB=
        return (object)[
            'id' => '1',
            'codigo' => 'ORD-2-2026',
            'nombre' => 'Ordinario 2 2026',
            'tipo' => 'Ordinario',
            'fecha_incio' => '2026-09-01',
            'fecha_fin' => '2026-12-17',
            'semana_primer_parcial' => 8,
            'activo' => true
        ];
        */

        return \App\Models\Periodo::where('activo', true)->first();
    } 




    //===C09: Devuelve los paralelos (cursos) activos de una materia en un periodo dado.
    public function cursosDeMateria(int $materiaId, int $periodoId): Collection
    {

    /*
    // =STUB =
    return collect([
            (object)[
                'id' => 1,
                'paralelo' => 'A10',
                'profesor' => (object)['nombres' => 'Juan', 'apellidos' => 'Pérez']
            ],
            (object)[
                'id' => 2,
                'paralelo' => 'B20',
                'profesor' => (object)['nombres' => 'María', 'apellidos' => 'Gómez']
            ]
        ]);
    */
        return Curso::with('profesor')
                    ->where('materia_id', $materiaId)
                    ->where('periodo_id', $periodoId)
                    ->get();
        
    }

    //===C04: Devuelve las mallas de una carrera, la vigente primero.
    public function mallasDeCarrera(int $carreraId): Collection
    {
        return Malla::where('carrera_id', $carreraId)
                    ->orderByDesc('vigente')
                    ->orderByDesc('version')
                    ->get();
    }

    //===C05: Materias activas de una malla, por nivel; se pueden buscar por código o nombre
    public function materiasDeMalla(int $mallaId, ?string $buscar = null): Collection
    {
        $buscar = $buscar !== null ? mb_strtolower(trim($buscar)) : '';

        return Materia::query()
            ->select('materias.*', 'malla_materia.nivel')
            ->join('malla_materia', 'malla_materia.materia_id', '=', 'materias.id')
            ->where('malla_materia.malla_id', $mallaId)
            ->where('materias.activo', true)
            ->when($buscar !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->whereRaw('LOWER(materias.codigo) LIKE ?', ['%'.$buscar.'%'])
                ->orWhereRaw('LOWER(materias.nombre) LIKE ?', ['%'.$buscar.'%'])))
            ->orderBy('malla_materia.nivel')
            ->orderBy('materias.nombre')
            ->get();
    }

    //===C08:Devuelve la semana actual del periodo activo, o null si no aplica.
    public function semanaActual(?Carbon $fecha = null) : ?int
    {
        $fecha = $fecha ?? now();
        $periodo = Periodo::where('activo', true)
                    ->first();
        if (! $periodo) { 
            return null;
        }
        $inicio = Carbon::parse($periodo->fecha_inicio);
        $fin = Carbon::parse($periodo->fecha_fin);
        if ($fecha->lt($inicio) || $fecha->gt($fin->endOfDay())) { 
            return null; 
        }
        $dias = (int) $inicio->diffInDays($fecha);
        return intdiv($dias, 7) + 1;
    }

    //===C10: Trabajadores con rol docente
    public function docentes(bool $soloActivos = true): Collection
    {
        return Trabajador::conRol('docente')
            ->when($soloActivos, fn ($q) => $q->where('activo', true))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }
    
    // ===C31: Carreras sin tercer registro
    public function carrerasSinTercerRegistro(): Collection
    {
        // C31: según el Excel, sin parámetros; devuelve las carreras que no admiten tercer registro
        return Carrera::where('admite_tercer_registro', false)->get();
    }
}

