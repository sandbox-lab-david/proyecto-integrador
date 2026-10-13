<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use Illuminate\Http\Request;

class FacultadController extends Controller
{
    public function index()
    {
     
        $facultades = Facultad::all();
        return view('facultades.index', compact('facultades'));
    }

    public function create()
    {
       
        return view('facultades.create');
    }

    public function store(Request $request)
    {
       
        $request->validate([
            'codigo' => 'required|string|max:20|unique:facultades',
            'nombre' => 'required|string|max:255',
            'correo_notificaciones' => 'nullable|email|max:255',
        ]);

        Facultad::create($request->all());

        return redirect()->route('facultades.index')->with('success', 'Facultad creada exitosamente.');
    }

    public function edit(Facultad $facultad)
    {
      
        return view('facultades.edit', compact('facultad'));
    }

    public function update(Request $request, Facultad $facultad)
    {
       
        $request->validate([
            'codigo' => 'required|string|max:20|unique:facultades,codigo,' . $facultad->id,
            'nombre' => 'required|string|max:255',
            'correo_notificaciones' => 'nullable|email|max:255',
        ]);

        $facultad->update($request->all());

        return redirect()->route('facultades.index')->with('success', 'Facultad actualizada exitosamente.');
    }

    public function destroy(Facultad $facultad)
    {
        
        $facultad->update(['activo' => false]);

        return redirect()->route('facultades.index')->with('success', 'Facultad desactivada correctamente.');
    }
}