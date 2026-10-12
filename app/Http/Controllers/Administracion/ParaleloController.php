<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ParaleloController extends Controller
{
    public function index()
    {
        return view('administracion.paralelos');
    }
}