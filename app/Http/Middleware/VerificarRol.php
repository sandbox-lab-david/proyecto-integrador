<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Academico\AutorizacionService;

class VerificarRol
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();
        $servicio = app(AutorizacionService::class);
        if (! $usuario || ! $servicio->tieneRol($usuario, $roles)) { 
            abort(403); 
        }
        return $next($request);
    }
}
