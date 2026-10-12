<?php

namespace App\Services\Flujo;

use App\Models\EstadoSolicitud;

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
}