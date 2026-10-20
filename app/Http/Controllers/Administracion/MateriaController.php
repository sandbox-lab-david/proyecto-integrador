<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\CategoriaMateria;
use App\Models\Materia;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    private const MENSAJES = [
        'required' => 'Este campo es obligatorio.',
        'unique' => 'Ya existe una materia con este código.',
        'integer' => 'Escribe un número entero.',
        'exists' => 'Elige una opción válida.',
        'min' => 'El valor es demasiado pequeño.',
        'max' => 'El valor es demasiado largo o grande.',
    ];

    public function index()
    {
        $materias = Materia::with('categoria')->orderBy('codigo')->get();
        $categorias = CategoriaMateria::orderBy('nombre')->get();

        return view('administracion.materias', compact('materias', 'categorias'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'codigo' => 'required|string|max:20|unique:materias,codigo',
            'nombre' => 'required|string|max:255',
            'creditos' => 'required|integer|min:0|max:20',
            'categoria_materia_id' => 'nullable|exists:categorias_materia,id',
        ], self::MENSAJES);

        $datos['activo'] = true;

        Materia::create($datos);

        return redirect()->route('administracion.materias')
            ->with('exito', "Materia {$datos['nombre']} registrada.");
    }
}
