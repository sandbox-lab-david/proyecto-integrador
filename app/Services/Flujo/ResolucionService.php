<?php

namespace App\Services\Flujo;

use App\Models\Resolucion;
use App\Models\Solicitud;

class ResolucionService
{
    /** C67: devuelve la decisión vinculada al expediente. */
    public function resolucionDe(Solicitud|int $solicitud): ?Resolucion
    {
        $id = $solicitud instanceof Solicitud ? $solicitud->getKey() : $solicitud;

        return Resolucion::with('corteAgenda')->where('solicitud_id', $id)->first();
    }
}
