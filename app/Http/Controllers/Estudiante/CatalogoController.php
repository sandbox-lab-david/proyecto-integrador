<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\Solicitudes\SolicitudesSinTerminar;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    public function index(SolicitudesSinTerminar $sinTerminar): View
    {
        return view('estudiante.catalogo', ['borradores' => $sinTerminar->todas()]);
    }
}
