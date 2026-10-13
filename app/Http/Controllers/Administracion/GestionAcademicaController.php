<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\Modalidad;
use Illuminate\Http\Request;

class GestionAcademicaController extends Controller
{
    public function indexMaterias()
    {
        $materias = Materia::paginate(10);
        return view('administracion.materias.index', compact('materias'));
    }

    public function createMateria()
    {
        return view('administracion.materias.create');
    }

    public function storeMateria(Request $request)
    {
        $request->validate([
            'codigo' => 'required|unique:materias,codigo',
            'nombre' => 'required|string|max:255',
            'creditos' => 'required|integer',
        ]);

        Materia::create($request->all());

        return redirect()->route('materias.index')->with('success', 'Materia creada exitosamente.');
    }

    public function editMateria($id)
    {
        $materia = Materia::findOrFail($id);
        return view('administracion.materias.edit', compact('materia'));
    }

    public function updateMateria(Request $request, $id)
    {
        $materia = Materia::findOrFail($id);
        
        $request->validate([
            'codigo' => 'required|unique:materias,codigo,' . $materia->id,
            'nombre' => 'required|string|max:255',
        ]);

        $materia->update($request->all());

        return redirect()->route('materias.index')->with('success', 'Materia actualizada correctamente.');
    }

    public function destroyMateria($id)
    {
        $materia = Materia::findOrFail($id);
        $materia->delete();

        return redirect()->route('materias.index')->with('success', 'Materia eliminada correctamente.');
    }

    public function indexModalidades()
    {
        $modalidades = Modalidad::all();
        return view('administracion.modalidades.index', compact('modalidades'));
    }

    public function storeModalidad(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:100']);
        Modalidad::create($request->all());
        return back()->with('success', 'Modalidad registrada.');
    }

    public function updateModalidad(Request $request, $id)
    {
        $modalidad = Modalidad::findOrFail($id);
        $modalidad->update($request->all());
        return back()->with('success', 'Modalidad actualizada.');
    }

    public function destroyModalidad($id)
    {
        Modalidad::destroy($id);
        return back()->with('success', 'Modalidad eliminada.');
    }
}