<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParaleloController extends Controller
{
    private const MENSAJES = [
        'required' => 'Este campo es obligatorio.',
        'unique' => 'Esa materia ya tiene un paralelo con ese nombre en el periodo elegido.',
        'integer' => 'Escribe un número entero.',
        'exists' => 'Elige una opción válida.',
        'min' => 'El valor es demasiado pequeño.',
        'max' => 'El valor es demasiado largo o grande.',
    ];

    public function index()
    {
        $cursos = Curso::with(['materia', 'periodo', 'profesor'])->latest()->get();
        $materias = Materia::where('activo', true)->orderBy('nombre')->get();
        $periodos = Periodo::orderByDesc('fecha_inicio')->get();
        $docentes = Trabajador::where('activo', true)->orderBy('apellidos')->get();

        return view('administracion.paralelos', compact('cursos', 'materias', 'periodos', 'docentes'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'periodo_id' => 'required|exists:periodos,id',
            'profesor_id' => 'nullable|exists:trabajadores,id',
            'paralelo' => [
                'required', 'string', 'max:20',
                Rule::unique('cursos')->where(fn ($q) => $q
                    ->where('materia_id', $request->input('materia_id'))
                    ->where('periodo_id', $request->input('periodo_id'))),
            ],
            'cupo' => 'nullable|integer|min:1|max:200',
        ], self::MENSAJES);

        Curso::create($datos);

        return redirect()->route('administracion.paralelos')
            ->with('exito', "Paralelo {$datos['paralelo']} creado.");
    }
}
