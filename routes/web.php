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