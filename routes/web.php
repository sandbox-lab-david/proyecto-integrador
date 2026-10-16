<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\ResolucionController;
use Illuminate\Support\Facades\Storage;

Route::get('/descargar-documento/{nombre}', function ($nombre) {
    $path = storage_path('app/public/documentos/' . $nombre);
    if (file_exists($path)) {
        return response()->download($path);
    }
    abort(404, 'Archivo no encontrado.');
})->name('documento.descargar');

Route::get('/', function () {
    return view('welcome');
});

<<<<<<< HEAD
Route::prefix('revision')->name('revision.')->group(function () {
    Route::get('/asistente', [RevisionController::class, 'bandejaAsistente'])->name('asistente');
    Route::get('/caso/{id}', [RevisionController::class, 'detalleCaso'])->name('caso.detalle');
    Route::post('/decidir/{id}', [RevisionController::class, 'decidirAsistente'])->name('decidir');
    Route::post('/caso/{id}/decidir', [RevisionController::class, 'decidirAsistente'])->name('caso.decidir');
    Route::get('/agenda/descargar/{corteId}', [RevisionController::class, 'descargarAgenda'])->name('agenda.descargar');
});

Route::get('/revision/asistente-index', [RevisionController::class, 'indexAsistente'])->name('index.asistente');
Route::get('/revision/detalle/{id}', [RevisionController::class, 'detalle'])->name('detalle');

Route::get('/resoluciones', [ResolucionController::class, 'index'])->name('resoluciones.index');
Route::post('/resoluciones/guardar/{solicitudId}', [ResolucionController::class, 'store'])->name('resoluciones.store');
=======
require __DIR__.'/chatbot.php';
require __DIR__.'/estudiantes.php';
Route::get('/tramites', function () {
    return view('tramites.index');
})->name('tramites.index');

Route::get('/tramites/configurar', function () {
    return view('tramites.configurar');
});

Route::get('/tramites/plantillas', function () {
    return view('tramites.plantillas');
});

Route::get('/app', function () {
    return view('layouts.app');
});

Route::get('/periodos', function () {
    return view('administracion.periodos');
})->name('administracion.periodos');

Route::get('/materias', function () {
    return view('administracion.materias');
})->name('administracion.materias');

Route::get('/paralelos', function () {
    return view('administracion.paralelos');
})->name('administracion.paralelos');

Route::resource('cursos', CursoController::class);

Route::resource('users', UserController::class);

Route::resource('directores_carrera', DirectorCarreraController::class);

Route::resource('importaciones', ImportacionesController::class)->only(['index', 'show', 'destroy']);

// ===== Grupo 3: Revisión y Consejo =====
Route::get('/revision/asistente', [RevisionController::class, 'bandejaAsistente'])->name('revision.asistente');
Route::get('/revision/caso/{id}', [RevisionController::class, 'detalleCaso'])->name('revision.detalle');
Route::post('/revision/caso/{id}', [RevisionController::class, 'guardarRevision'])->name('revision.guardar');
>>>>>>> 14da9b03ae2b6f3bbe13fb26951d91d301d13bd9
