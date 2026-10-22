<?php

namespace App\Http\Controllers\Acceso;

use App\Http\Controllers\Controller;
use App\Services\Academico\AutorizacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class PanelController extends Controller
{
    private const DESTINOS = [
        ['roles' => ['admin'], 'ruta' => 'administracion.inicio'],
        ['roles' => ['asistente', 'director'], 'ruta' => 'revision.asistente'],
    ];

    public function __invoke(Request $request, AutorizacionService $autorizacion): RedirectResponse|View
    {
        $usuario = $request->user();

        if ($usuario->tipo === 'estudiante' && Route::has('estudiante.catalogo')) {
            return redirect()->route('estudiante.catalogo');
        }

        foreach (self::DESTINOS as $destino) {
            if (Route::has($destino['ruta']) && $autorizacion->tieneRol($usuario, $destino['roles'])) {
                return redirect()->route($destino['ruta']);
            }
        }

        return view('acceso.sin-modulo');
    }
}
