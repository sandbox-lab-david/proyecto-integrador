<?php

namespace App\Services\Solicitudes;

use App\Models\Curso;
use Illuminate\Support\Facades\Schema;

/** Consulta de lectura del catálogo de G1. No modifica sus modelos ni inscripciones. */
class MateriasSolicitud
{
    /** Actualiza los metadatos en el servidor cuando el formulario usa el catálogo. */
    public function entrada(array $datos): array
    {
        if (($datos['origen_materias'] ?? null) !== 'catalogo' || !is_array($datos['materias'] ?? null)) return [];
        $cursos = collect($this->todas())->keyBy('id');
        return ['materias' => array_map(function ($fila) use ($cursos) {
            if (!is_array($fila) || !is_scalar($fila['id'] ?? null)) return $fila;
            $curso = $cursos->get((string) $fila['id']);
            return $curso ? \Illuminate\Support\Arr::only($curso, ['id', ...array_keys(DatosFormularioSolicitud::MATERIA)])
                + ['datos' => $fila['datos'] ?? []] : $fila;
        }, $datos['materias'])];
    }

    public function todas(): array
    {
        // G1 aún puede no haber desplegado su esquema académico en este entorno.
        foreach (['cursos', 'materias', 'periodos', 'trabajadores'] as $tabla) {
            if (! Schema::hasTable($tabla)) {
                return [];
            }
        }

        return Curso::query()->with(['materia', 'periodo', 'profesor'])
            ->whereHas('materia')->whereHas('periodo')->orderBy('id')
            ->get()->map(fn (Curso $curso) => [
                'id' => (string) $curso->getKey(),
                'curso_id' => $curso->getKey(),
                'codigo' => $curso->materia->codigo,
                'nombre' => $curso->materia->nombre,
                'periodo' => $curso->periodo->nombre,
                'paralelo' => $curso->paralelo,
                'docente' => trim(($curso->profesor?->nombres ?? '').' '.($curso->profesor?->apellidos ?? '')) ?: 'Sin docente asignado',
            ])->all();
    }
}
