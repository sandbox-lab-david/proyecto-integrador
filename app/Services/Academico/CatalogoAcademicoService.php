<?php
namespace App\Services\Academico;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Collection;

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
}