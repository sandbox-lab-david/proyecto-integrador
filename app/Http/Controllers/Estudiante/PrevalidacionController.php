<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudiante\PrevalidarSolicitudRequest;
use App\Services\Solicitudes\PrevalidacionSolicitud;
use Illuminate\Http\Response;

/**
 * Pre-validación del paso 4 (RF-2.8). Devuelve el fragmento HTML que el
 * formulario inserta en el resumen; nunca decide si la solicitud se envía.
 */
class PrevalidacionController extends Controller
{
    public function __invoke(PrevalidarSolicitudRequest $request, PrevalidacionSolicitud $prevalidacion): Response
    {
        $datos = $request->validated();

        return response()->view('estudiante.partials.prevalidacion', [
            // Un objeto de campos vacío no aparece entre los datos validados.
            'prevalidacion' => $prevalidacion->consultar($datos['tramite'], $datos['campos'] ?? []),
        ])->header('Cache-Control', 'private, no-store');
    }
}
