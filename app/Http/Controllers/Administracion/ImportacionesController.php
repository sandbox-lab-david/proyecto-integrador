<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Importaciones;

class ImportacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $importaciones = Importaciones::with('user')->orderBy('created_at', 'desc')->paginate(10);
        
        return view('importaciones.index', compact('importaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Importaciones $importacion)
    {
        
        return view('importaciones.show', compact('importacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Importaciones $importacion)
    {

        
        $importacion->delete();

        return redirect()->route('importaciones.index')->with('success', 'Registro del historial eliminado');
    }
}
