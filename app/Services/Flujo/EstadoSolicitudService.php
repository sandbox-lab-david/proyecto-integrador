<?php

namespace App\Services\Flujo;

use App\Models\EstadoSolicitud;
use App\Models\Solicitud;

class EstadoSolicitudService
{
    public function estados(bool $soloActivos = true)
    {
        return EstadoSolicitud::all();
    }

    public function etiquetaParaEstudiante(string $codigoEstado)
    {
        $estado = EstadoSolicitud::where('codigo', $codigoEstado)->first();

        return $estado ? $estado->nombre : 'Desconocido';
    }

    public function transicionesPermitidas(string $codigoActual)
    {
        return [];
    }

    public function lineaDeTiempo(Solicitud $solicitud, bool $internas = false)
    {
        $consulta = $solicitud->historial()->oldest('id');
        if (! $internas) {
            $consulta->where('visible_estudiante', true);
        }

        return $consulta->get();
    }
}
