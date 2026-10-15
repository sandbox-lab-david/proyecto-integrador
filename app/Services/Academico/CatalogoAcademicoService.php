<?php
namespace App\Services\Academico;

use App\Models\Curso;
use App\Models\Malla;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Periodo;
use Illuminate\Support\Carbon;

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
        return $facultadesFalsas->where('activo', true)->values();
        }

        return Fcultad::all();
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

    //===C05
    public function materiasDeMalla(int $mallaId, ?string $buscar = null): Collection
    {
        // Contrato Semana 1: Devolver colección vacía (stub).
        return collect([]);
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

    //===C10
    public function docentes(bool $soloActivos = true): Collection
    {
        // Contrato Semana 1: Devolver colección vacía (stub).
        return collect([]);
    }
    
    
}

