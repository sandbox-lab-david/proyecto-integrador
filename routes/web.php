<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RevisionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the web middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rutas completas para el Módulo de Revisión y Consejo
Route::get('/revision/asistente', [RevisionController::class, 'bandejaAsistente'])->name('revision.asistente');
Route::get('/revision/caso/{id}', [RevisionController::class, 'detalleCaso'])->name('revision.detalle');
Route::post('/revision/caso/{id}', [RevisionController::class, 'guardarRevision'])->name('revision.guardar');