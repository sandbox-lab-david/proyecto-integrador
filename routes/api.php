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
        Route::get('/historial', 'historial');
        Route::delete('/historial', 'limpiarHistorial');
    });

// Agrupamos las rutas internas protegidas
Route::prefix('interno')->middleware('auth')->group(function () {

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
