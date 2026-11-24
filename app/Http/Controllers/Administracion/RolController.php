<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Trabajador;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::orderBy('id')->get()->each(function ($rol) {
            $rol->personas = Trabajador::conRol($rol->codigo)->where('activo', true)->count();
        });

        return view('administracion.roles', compact('roles'));
    }
}
