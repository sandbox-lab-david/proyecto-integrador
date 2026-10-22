<?php

use App\Modules\Chatbot\Http\Controllers\ChatbotController;
use App\Services\Academico\CatalogoAcademicoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Endpoints API del Chatbot (middleware api: sin CSRF, stateless)
// El throttle de 60 peticiones/minuto protege contra abuso.
Route::controller(ChatbotController::class)
    ->prefix('chatbot')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/catalogo', 'catalogo');
        Route::post('/mensaje', 'mensaje');
    });

// Agrupamos las rutas internas protegidas
Route::prefix('interno')->middleware(['web', 'auth'])->group(function () {

    // C32: Carreras activas de una facultad
    Route::get('/carreras', function (Request $request, CatalogoAcademicoService $servicio) {
        $request->validate(['facultad_id' => 'required|integer'], [
            'facultad_id.required' => 'Indica la facultad (facultad_id).',
            'facultad_id.integer' => 'facultad_id debe ser un número.',
        ]);

        return response()->json(
            $servicio->carrerasPorFacultad((int) $request->facultad_id)->map->only(['id', 'nombre'])->values()
        );
    })->name('interno.carreras');

    // C33: Periodos académicos y el periodo activo
    Route::get('/periodos', function (CatalogoAcademicoService $servicio) {
        return response()->json(
            $servicio->periodos()->map->only(['id', 'codigo', 'nombre', 'tipo', 'fecha_inicio', 'fecha_fin', 'activo'])->values()
        );
    })->name('interno.periodos');

    Route::get('/periodos/actual', function (CatalogoAcademicoService $servicio) {
        $periodo = $servicio->periodoActual();

        return $periodo
            ? response()->json($periodo->only(['id', 'codigo', 'nombre', 'tipo', 'fecha_inicio', 'fecha_fin', 'semana_primer_parcial']))
            : response()->json(['message' => 'No hay un periodo activo.'], 404);
    })->name('interno.periodos.actual');

    // C34: Materias de una malla, con búsqueda opcional por código o nombre
    Route::get('/materias', function (Request $request, CatalogoAcademicoService $servicio) {
        $request->validate([
            'malla_id' => 'required|integer',
            'q' => 'nullable|string|max:100',
        ], [
            'malla_id.required' => 'Indica la malla (malla_id).',
            'malla_id.integer' => 'malla_id debe ser un número.',
            'q.max' => 'La búsqueda no puede tener más de 100 caracteres.',
        ]);

        return response()->json(
            $servicio->materiasDeMalla((int) $request->malla_id, $request->q)->map->only(['id', 'codigo', 'nombre'])->values()
        );
    })->name('interno.materias');

    // C35: Endpoint para obtener los paralelos y docentes de una materia
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
