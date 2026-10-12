<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Periodo;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Pide al modelo los cursos, trayendo también la materia y el profesor
        $cursos = Curso::with(['materia', 'profesor'])->paginate(10); // Paginamos de 10 en 10 como pide el proyecto
        
        // 2. Devuelve la vista inyectándole la variable $cursos
        return view('cursos.index', compact('cursos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Traemos materias y periodos activos para llenar los menús desplegables del formulario
        $materias = Materia::where('activo', true)->get();
        $periodos = Periodo::where('activo', true)->get();
        
        return view('cursos.create', compact('materias', 'periodos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validamos que el usuario no envíe datos basura o vacíos
        $request-> validate([
            'materia_id' => 'required|exists:materias,id',
            'periodo_id' => 'required|exists:periodoos,id',
            'profesor_id' => 'required|exists:trabajador,id',
            'paralelo' => 'required|string|max:10',
            'cupo' => 'required|integer|min;1',
        ]);

        // 2. Guardamos en la base de datos
        Curso::create($request->all());

        // 3. Redireccionamos a la lista con un mensaje de exito
        return redirect()->route('cursos.index')->with('success', 'Curso creado exitosamente');
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
    public function edit(curso $curso)
    {
        $materias = Materia::where('activo', true)->get();
        $periodos = Periodo::where('activo', true)->get();
        
        // Pasamos el $curso a la vista para que el HTML pueda imprimir $curso->paralelo en los inputs
        return view('cursos.edit', compact('curso', 'materias', 'periodos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, curso $curso)
    {
        // 1. Validamos que los datos ingresados sean correctos
        $request->validate([
            'materia_id'=>'required|exists:materia,id',
            'periodo_id'=>'required|exists:periodo,id',
            'profesor_id'=>'required|exists:trabajador,id',
            'paralelo'=>'required|string|max:10',
            'cupo'=>'required|integer|min:1',
        ]);

        // 2. Actualizamos el curso en la base de datos
        $curso->update($request->all());

        return redirect()->route('cursos.index')->with('success', 'Curso actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(curso $curso)
    {
        // 1. Verificamos si hay solicitudes ligadas a este curso.
        if ($curso->solicitudMaterias()->count() > 0) {
        
            return redirect()->route('cursos.index')->with('error', 'No puedes eliminar este curso porque ya tiene solicitudes de estudiantes asociadas.');
        }

        // 2. Si nadie lo ha usado, lo borramos permanentemente
        $curso->delete();


        return redirect()->route('cursos.index')->with('success', 'Curso eliminado permanentemente');
    }
}
