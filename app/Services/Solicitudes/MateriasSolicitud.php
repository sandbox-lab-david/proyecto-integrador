<?php

namespace App\Services\Solicitudes;

use App\Models\Curso;
use App\Services\Academico\CatalogoAcademicoService;
use App\Services\Academico\EstudianteService;
use Illuminate\Support\Facades\Schema;

/** Consulta de lectura del catálogo de G1. No modifica sus modelos ni inscripciones. */
class MateriasSolicitud
{
    public function __construct(
        private readonly EstudianteService $estudiantes,
        private readonly CatalogoAcademicoService $catalogo,
    ) {}

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

    public function avisoSinCursos(): string
    {
        if (! Schema::hasTable('estudiantes') || ! Schema::hasTable('carrera_estudiante')) {
            return 'El catálogo académico aún no está instalado en este entorno.';
        }
        $estudiante = $this->estudiantes->estudianteActual();
        if (! $estudiante) return 'Tu cuenta todavía no tiene un perfil académico asociado. Solicita su revisión a la unidad académica.';
        $inscripciones = $this->estudiantes->carrerasActivas($estudiante->id)
            ->filter(fn ($i) => $i->carreraModalidad?->carrera?->activo);
        if ($inscripciones->isEmpty()) return 'No tienes una inscripción activa en una carrera habilitada. Solicita su revisión a la unidad académica.';
        if (! $inscripciones->pluck('malla_id')->filter()->count()) return 'Tu inscripción no tiene una malla asociada. Solicita su revisión a la unidad académica.';
        return 'No hay cursos disponibles para las materias activas de tu malla. Solicita a la unidad académica que revise los paralelos.';
    }

    public function todas(): array
    {
        // G1 aún puede no haber desplegado su esquema académico en este entorno.
        foreach (['cursos', 'materias', 'periodos', 'trabajadores'] as $tabla) {
            if (! Schema::hasTable($tabla)) {
                return [];
            }
        }

        $materias = $this->materiasDeLaMalla();

        return Curso::query()->with(['materia', 'periodo', 'profesor'])
            ->whereHas('materia', fn ($q) => $q->where('activo', true))->whereHas('periodo')
            ->whereIn('materia_id', $materias)->orderBy('id')
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

    /**
     * Materias de las mallas de las inscripciones activas del estudiante (RF-2.5),
     * por C11, C13 y C05 de G1. Sin malla no se ofrecen cursos ajenos.
     *
     * @return list<int>
     */
    private function materiasDeLaMalla(): array
    {
        foreach (['estudiantes', 'carrera_estudiante', 'malla_materia'] as $tabla) {
            if (! Schema::hasTable($tabla)) {
                return [];
            }
        }
        if (! auth()->user()?->activo || auth()->user()?->tipo !== 'estudiante') return [];
        $estudiante = $this->estudiantes->estudianteActual();
        $mallas = $estudiante
            ? $this->estudiantes->carrerasActivas($estudiante->id)
                ->filter(fn ($i) => $i->carreraModalidad?->carrera?->activo)
                ->pluck('malla_id')->filter()->unique()
            : collect();
        if ($mallas->isEmpty()) {
            return [];
        }

        return $mallas->flatMap(fn ($malla) => $this->catalogo->materiasDeMalla((int) $malla)->pluck('id'))
            ->unique()->values()->all();
    }
}
