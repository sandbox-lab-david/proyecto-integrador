<?php

namespace App\Http\Middleware;

use App\Services\Academico\AutorizacionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Valida que el usuario autenticado tenga al menos uno de los roles solicitados.
     * Compatible con el servicio AutorizacionService (G1) y con verificación directa de rol.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();
        if (! $usuario) {
            abort(403, 'Acceso denegado: se requiere autenticación.');
        }

        if (app(AutorizacionService::class)->tieneRol($usuario, $roles)) {
            return $next($request);
        }
        abort(403, 'Acceso denegado: no tienes el rol requerido para ver este recurso.');
    }
}
