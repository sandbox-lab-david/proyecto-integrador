<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RevisionController; // ===== Grupo 3
use App\Http\Controllers\Administracion\CursoController;
use App\Http\Controllers\Administracion\DirectorCarreraController;
use App\Http\Controllers\Administracion\UserController;
use App\Http\Controllers\Administracion\ImportacionesController;

Route::get('/', function () {
    return view('welcome');
});

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