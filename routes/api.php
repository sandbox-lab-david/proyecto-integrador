<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\Academico\CatalogoAcademicoService;
use App\Models\Carrera;

// Agrupamos las rutas internas protegidas
Route::prefix('interno')->middleware('auth')->group(function () {

    //C32: Endpoint JSON para obtener las carreras de una facultad específica.
    Route::get('/carreras', function (Request $request) {
        // Validamos que la URL contenga el parámetro 'facultad_id' y sea numérico
        $request->validate([
            'facultad_id' => 'required|integer'
        ]);
        /*
        // ===STUB===
        // Si el frontend envía facultad_id=1 (Ej: Ingeniería), le devolvemos sus carreras.
        if ($request->facultad_id == 1) {
            return response()->json([
                ['id' => 10, 'nombre' => 'Ingeniería en Sistemas'],
                ['id' => 11, 'nombre' => 'Ingeniería Civil'],
            ]);
        }
        
        // Si envían cualquier otra facultad, devolvemos otra lista
        return response()->json([
            ['id' => 20, 'nombre' => 'Medicina'],
            ['id' => 21, 'nombre' => 'Nutrición'],
        ]);
        */
        
        return response()->json(
            Carrera::where('facultad_id', $request->facultad_id)
                   ->where('activo', true)
                   ->get(['id', 'nombre'])
        );
    
    });


    
    //C35: Endpoint para obtener los paralelos y docentes de una materia
    Route::get('/cursos', function (Request $request, CatalogoAcademicoService $servicio) {
        // Validamos que nos envíen los parámetros obligatorios en la URL
        $request->validate([
            'materia_id' => 'required|integer',
            'periodo_id' => 'required|integer',
        ]);

        // Llamamos al método C09 que acabamos de crear arriba
        $cursos = $servicio->cursosDeMateria($request->materia_id, $request->periodo_id);

        // Devolvemos el resultado en formato JSON para que el frontend lo lea fácilmente
        return response()->json($cursos);
    });
});