<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

/** Listado básico real, independiente de la línea de tiempo aún pendiente en G3. */
class SolicitudesPropias
{
    public function consultar(?User $usuario): ?Collection
    {
        foreach ([
            'solicitudes' => ['carrera_estudiante_id', 'estado_solicitud_id', 'detalle'],
            'carrera_estudiante' => ['estudiante_id'],
            'estudiantes' => ['user_id'],
            'estados_solicitud' => ['codigo'],
        ] as $tabla => $columnas) {
            if (! Schema::hasTable($tabla) || ! Schema::hasColumns($tabla, $columnas)) return null;
        }
        if ($usuario === null || ! $usuario->activo) return new Collection;
        return Solicitud::whereHas('carreraEstudiante.estudiante', fn ($q) => $q->where('user_id', $usuario->getKey()))
            ->with(Schema::hasTable('tipos_tramite') ? ['tipoTramite', 'estadoSolicitud'] : ['estadoSolicitud'])->latest('id')->get();
    }
}
