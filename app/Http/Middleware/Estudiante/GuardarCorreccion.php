<?php

namespace App\Http\Middleware\Estudiante;

use App\Models\Solicitud;
use App\Services\Solicitudes\ContextoCorreccion;
use App\Services\Solicitudes\CorreccionesGuardadas;
use App\Services\Solicitudes\EnvioSolicitud;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class GuardarCorreccion
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->routeIs('solicitudes.edit', 'solicitudes.reenviar')
            ? $request->route('solicitud') : $request->input('solicitud_correccion');
        if ($id === null || ! CorreccionesGuardadas::disponible()) {
            return $next($request);
        }
        abort_unless(is_scalar($id) && ctype_digit((string) $id) && (int) $id > 0, 422);
        $solicitud = Solicitud::findOrFail((int) $id);
        Gate::authorize('update', $solicitud);
        $tramite = $solicitud->detalle['tramite'] ?? null;
        abort_unless(is_string($tramite), 409);
        // La ruta y los parámetros deben apuntar al mismo expediente.
        abort_if($request->filled('solicitud_correccion') && (int) $request->input('solicitud_correccion') !== (int) $id, 422);
        $request->query->set('solicitud_correccion', (string) $id);
        ContextoCorreccion::clave($tramite);
        $guardadas = app(CorreccionesGuardadas::class);
        $guardadas->restaurar($solicitud, $tramite);
        if (! $request->session()->has('correcciones_base.'.$id)) {
            $request->session()->put('correcciones_base.'.$id, EnvioSolicitud::revision($solicitud));
        }
        $respuesta = $next($request);
        if ($respuesta->getStatusCode() < 400) {
            $enCuenta = $guardadas->guardar($solicitud, $tramite);
            if ($respuesta instanceof JsonResponse && $request->routeIs('borradores-temporales.update')) {
                $respuesta->setData(array_replace($respuesta->getData(true), ['en_cuenta' => $enCuenta]));
            }
        }

        return $respuesta;
    }
}
