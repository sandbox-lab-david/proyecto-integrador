<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\DirectorCarrera;
use App\Models\Carrera;
use App\Models\Trabajador;

class DirectorCarreraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $directores = DirectorCarrera::with('carrera', 'trabajador')->paginate(10);

        return view('directores_carrera.index', compact('directores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $carrera = Carreras::where('activo', true)->get();
        $trabajador = Trabajador::where('activo', true)->get();

        return view('directores_carrera.create', compact('carrera', 'trabajador'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'trabajador_id' => 'required|exists:trabajadores,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        DirrectorCarrera::create($request->all());

        return redirect()->route('directores_carrera.index')->with('Success', 'Director de Carrera creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DirectorCarrera $directorCarrera)
    {
        $carreras = Carrera::where('activo', true)->get();
        $trabajadores = Trabajador::where('activo', true)->get();

        return view ('directores_carrera.update', compact('directorCarrera', 'carreras', 'trabajadores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DirectorCarrera $directorCarrera)
    {
        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'trabajador_id' => 'required|exists:trabajadores,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $directorCarrera->update($request->all());

        return redirect()->route('directores_carrera.index')->with('success', 'Registro actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DirectorCarrera $directorCarrera)
    {
        $directorCarrera->delete();

        return redirect()->route('directores_carrera.index')->with('success', 'Registro de director eliminado permanentemente');
    }
}
