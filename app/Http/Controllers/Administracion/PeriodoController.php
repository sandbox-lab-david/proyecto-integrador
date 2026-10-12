<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    public function index()
    {
        
        return view('administracion.periodos'); 
    }
}