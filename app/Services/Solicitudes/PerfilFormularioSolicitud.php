<?php

namespace App\Services\Solicitudes;

use App\Services\Academico\CatalogoAcademicoService;
use App\Services\Academico\EstudianteService;

/**
 * Precarga del paso 1 mediante los contratos C11, C12, C06 y C07 de G1.
 * Código, cédula y celular siguen editables; la facultad, la carrera y la
 * modalidad se limitan a las inscripciones activas, y el periodo es el vigente.
 */
class PerfilFormularioSolicitud
{
    public function __construct(
        private readonly EstudianteService $estudiantes,
        private readonly CatalogoAcademicoService $catalogo,
    ) {}

    public function iniciales(): array
    {
        if (! ($perfil = $this->perfil())) {
            return [];
        }

        $datos = array_intersect_key($perfil, array_flip(['codigo', 'cedula', 'celular']));
        $matriculas = $this->matriculas();
        // Si cursa varias carreras, el estudiante debe elegir sobre cuál presenta el trámite.
        if (count($matriculas) === 1) {
            $datos += $matriculas[0];
        }
        $periodo = $this->periodoVigente();
        if ($periodo === null && ($actual = $this->catalogo->periodoActual())) {
            $periodo = $actual->codigo ?: $actual->nombre;
        }
        $datos['periodo'] = $periodo;

        return array_filter($datos, fn ($dato) => is_string($dato) && trim($dato) !== '');
    }

    /**
     * Inscripciones activas cuya facultad y carrera siguen en el catálogo de G1.
     *
     * @return list<array{facultad: string, carrera: string, modalidad: string}>
     */
    public function matriculas(): array
    {
        return once(function () {
            $catalogo = CatalogoFacultades::todas();

            return collect($this->perfil()['carreras'] ?? [])
                ->map(fn ($inscripcion) => [
                    'facultad' => data_get($inscripcion, 'facultad.nombre'),
                    'carrera' => data_get($inscripcion, 'carrera.nombre'),
                    'modalidad' => data_get($inscripcion, 'modalidad.nombre'),
                ])
                ->filter(fn ($m) => is_string($m['modalidad']) && in_array($m['carrera'], $catalogo[$m['facultad']] ?? [], true))
                ->unique('carrera')
                ->values()
                ->all();
        });
    }

    /**
     * Facultades y carreras que ofrecen los selectores: las de sus inscripciones
     * o, si la cuenta no tiene ninguna, todo el catálogo activo.
     *
     * @return array<string, list<string>>
     */
    public function facultades(): array
    {
        $matriculas = $this->matriculas();
        if ($matriculas === []) {
            return CatalogoFacultades::todas();
        }

        return collect($matriculas)->groupBy('facultad')->sortKeys()
            ->map(fn ($grupo) => $grupo->pluck('carrera')->values()->all())
            ->all();
    }

    /** @return array<string, string> carrera => modalidad de la inscripción */
    public function modalidades(): array
    {
        return array_column($this->matriculas(), 'modalidad', 'carrera');
    }

    /** Código del único periodo activo en curso, que es el que acepta el envío. */
    public function periodoVigente(): ?string
    {
        return once(function () {
            $hoy = now(ReglasCampoSolicitud::zonaHoraria())->toDateString();
            $fecha = fn ($valor) => substr((string) $valor, 0, 10);
            $vigentes = $this->catalogo->periodos(true)
                ->filter(fn ($p) => $fecha($p->fecha_inicio) <= $hoy && $fecha($p->fecha_fin) >= $hoy);

            return $vigentes->count() === 1 ? ($vigentes->first()->codigo ?: $vigentes->first()->nombre) : null;
        });
    }

    /**
     * Valores que no dependen del estudiante, para imponerlos también ante un
     * POST manipulado. La modalidad sale de la carrera elegida.
     */
    public function fijos(array $datos): array
    {
        $fijos = [];
        if ($periodo = $this->periodoVigente()) {
            $fijos['periodo'] = $periodo;
        }
        $matriculas = $this->matriculas();
        if (count($matriculas) === 1) {
            $fijos += array_intersect_key($matriculas[0], array_flip(['facultad', 'carrera']));
        }
        $carrera = $fijos['carrera'] ?? $datos['carrera'] ?? null;
        $modalidad = is_string($carrera) ? ($this->modalidades()[$carrera] ?? null) : null;
        if ($modalidad !== null) {
            $fijos['modalidad'] = $modalidad;
        }

        return $fijos;
    }

    private function perfil(): ?array
    {
        return once(function () {
            if (! auth()->user()?->exists || ! ($estudiante = $this->estudiantes->estudianteActual())) {
                return null;
            }

            return $this->estudiantes->perfil($estudiante->id);
        });
    }
}
