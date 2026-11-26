<?php

namespace App\Http\Controllers\Acceso;

use App\Http\Controllers\Controller;
use App\Services\Acceso\ModulosDelUsuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function __invoke(Request $request, ModulosDelUsuario $modulos): RedirectResponse|View
    {
        $usuario = $request->user();

        if ($usuario->tipo === 'estudiante' && Route::has('estudiante.catalogo')) {
            return redirect()->route('estudiante.catalogo');
        }

        $disponibles = $modulos->de($usuario);

        if ($disponibles) {
            return redirect()->route(reset($disponibles));
        }

        return view('acceso.sin-modulo');
    }
}
