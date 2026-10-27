<?php

namespace App\Services\Solicitudes;

use App\Services\Academico\CatalogoAcademicoService;
use App\Services\Academico\EstudianteService;

/** Precarga editable del paso 1 mediante los contratos C11, C12 y C07 de G1. */
class PerfilFormularioSolicitud
{
    public function __construct(
        private readonly EstudianteService $estudiantes,
        private readonly CatalogoAcademicoService $catalogo,
    ) {}

    public function iniciales(): array
    {
        if (! auth()->user()?->exists || ! ($estudiante = $this->estudiantes->estudianteActual())) {
            return [];
        }

        $perfil = $this->estudiantes->perfil($estudiante->id);
        $datos = array_intersect_key($perfil, array_flip(['codigo', 'cedula', 'celular']));
        $carreras = $perfil['carreras'] ?? [];
        // Si cursa varias carreras, el estudiante debe elegir sobre cuál presenta el trámite.
        if (count($carreras) === 1) {
            $datos += [
                'facultad' => data_get($carreras[0], 'facultad.nombre'),
                'carrera' => data_get($carreras[0], 'carrera.nombre'),
                'modalidad' => data_get($carreras[0], 'modalidad.nombre'),
            ];
        }
        if ($periodo = $this->catalogo->periodoActual()) {
            $datos['periodo'] = $periodo->codigo ?: $periodo->nombre;
        }

        return array_filter($datos, fn ($dato) => is_string($dato) && trim($dato) !== '');
    }
}
