<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\CategoriaMateria;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MateriaController extends Controller
{
    use MensajesValidacion;

    public function index(Request $request, ?Materia $materia = null)
    {
        $materias = $this->buscar(Materia::with('categoria'), $request->input('buscar'), ['codigo', 'nombre'])
            ->orderBy('codigo')
            ->paginate(15)
            ->withQueryString();

        return view('administracion.materias', [
            'materias' => $materias,
            'categorias' => CategoriaMateria::orderBy('nombre')->get(),
            'editando' => $materia?->exists ? $materia : null,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $datos['activo'] = true;

        Materia::create($datos);

        return redirect()->route('administracion.materias')
            ->with('exito', "Materia {$datos['nombre']} registrada.");
    }

    public function update(Request $request, Materia $materia)
    {
        $materia->update($this->validar($request, $materia));

        return redirect()->route('administracion.materias')
            ->with('exito', "Materia {$materia->nombre} actualizada.");
    }

    public function cambiarEstado(Materia $materia)
    {
        $materia->update(['activo' => ! $materia->activo]);

        return back()->with('exito', "Materia {$materia->nombre} ".($materia->activo ? 'activada.' : 'desactivada. Ya no aparecerá en formularios nuevos.'));
    }

    private function validar(Request $request, ?Materia $materia = null): array
    {
        return $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('materias', 'codigo')->ignore($materia)],
            'nombre' => 'required|string|max:255',
            'creditos' => 'required|integer|min:0|max:20',
            'categoria_materia_id' => 'nullable|exists:categorias_materia,id',
        ], $this->mensajes(['unique' => 'Ya existe una materia con este código.'])) + ['categoria_materia_id' => null];
    }
}
