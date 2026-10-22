<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Facultad;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Trabajador;

class InicioController extends Controller
{
    public function index()
    {
        $conteos = [
            'Facultades' => Facultad::count(),
            'Carreras' => Carrera::count(),
            'Periodos' => Periodo::count(),
            'Materias' => Materia::count(),
            'Paralelos' => Curso::count(),
            'Docentes' => Trabajador::conRol('docente')->where('activo', true)->count(),
        ];

        $periodoActivo = Periodo::where('activo', true)->first();

        return view('administracion.inicio', compact('conteos', 'periodoActivo'));
    }
}
