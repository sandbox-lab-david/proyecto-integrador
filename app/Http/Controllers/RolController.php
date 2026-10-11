<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
      
        $roles = Rol::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        
        return view('roles.create');
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre',
        ]);

        Rol::create($request->all());

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Rol $rol)
    {
        
        return view('roles.edit', compact('rol'));
    }

    public function update(Request $request, Rol $rol)
    {
        
        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre,' . $rol->id,
        ]);

        $rol->update($request->all());

        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Rol $rol)
    {
       
        $roles_protegidos = ['admin', 'asistente', 'director', 'docente', 'consulta', 'estudiante'];
        
        if (in_array(strtolower($rol->nombre), $roles_protegidos)) {
            return redirect()->route('roles.index')->with('error', 'No puedes eliminar un rol vital del sistema.');
        }

        $rol->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}