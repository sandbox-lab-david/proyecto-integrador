<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use Illuminate\Support\Facades\Gate;

/** Separa temporales por expediente; una corrección nunca pisa un trámite nuevo. */
class ContextoCorreccion
{
    public static function clave(string $tramite): string
    {
        $id = request()->input('solicitud_correccion');
        if ($id === null) return $tramite;
        abort_unless(is_scalar($id) && ctype_digit((string) $id) && (int) $id > 0, 422);
        $clave = 'correccion-'.(int) $id.'--'.$tramite;
        if (! request()->attributes->get($clave)) {
            abort_unless(auth()->check(), 401);
            $solicitud = Solicitud::findOrFail((int) $id);
            Gate::authorize('update', $solicitud);
            abort_unless(($solicitud->detalle['tramite'] ?? null) === $tramite, 422, 'El trámite no corresponde a esta solicitud.');
            request()->attributes->set($clave, true);
        }
        return $clave;
    }
}
