<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodoController extends Controller
{
    private const MENSAJES = [
        'required' => 'Este campo es obligatorio.',
        'unique' => 'Ya existe un periodo con este código.',
        'date' => 'Escribe una fecha válida.',
        'after' => 'La fecha de fin debe ser posterior a la de inicio.',
        'integer' => 'Escribe un número entero.',
        'in' => 'Elige una opción válida.',
        'min' => 'El valor es demasiado pequeño.',
        'max' => 'El valor es demasiado largo o grande.',
    ];

    public function index()
    {
        $periodos = Periodo::orderByDesc('fecha_inicio')->get();

        return view('administracion.periodos', compact('periodos'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'codigo' => 'required|string|max:30|unique:periodos,codigo',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:ordinario,extraordinario,intensivo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'semana_primer_parcial' => 'required|integer|min:1|max:20',
        ], self::MENSAJES);

        $datos['activo'] = $request->boolean('activo');

        DB::transaction(function () use ($datos) {
            if ($datos['activo']) {
                Periodo::where('activo', true)->update(['activo' => false]);
            }

            Periodo::create($datos);
        });

        return redirect()->route('administracion.periodos')
            ->with('exito', "Periodo {$datos['nombre']} creado.");
    }
}
