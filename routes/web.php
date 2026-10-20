<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RevisionController; // ===== Grupo 3
use App\Http\Controllers\Administracion\CursoController;
use App\Http\Controllers\Administracion\DirectorCarreraController;
use App\Http\Controllers\Administracion\UserController;
use App\Http\Controllers\Administracion\ImportacionesController;
use App\Http\Controllers\Administracion\InicioController;
use App\Http\Controllers\Administracion\PeriodoController;
use App\Http\Controllers\Administracion\MateriaController;
use App\Http\Controllers\Administracion\ParaleloController;
use App\Http\Controllers\Administracion\CargaController;

Route::get('/', function () {
    return view('welcome');
});

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

// ===== Grupo 1: Administración académica =====
Route::get('/administracion', [InicioController::class, 'index'])->name('administracion.inicio');

Route::get('/periodos', [PeriodoController::class, 'index'])->name('administracion.periodos');
Route::post('/periodos', [PeriodoController::class, 'store'])->name('administracion.periodos.store');

Route::get('/materias', [MateriaController::class, 'index'])->name('administracion.materias');
Route::post('/materias', [MateriaController::class, 'store'])->name('administracion.materias.store');

Route::get('/paralelos', [ParaleloController::class, 'index'])->name('administracion.paralelos');
Route::post('/paralelos', [ParaleloController::class, 'store'])->name('administracion.paralelos.store');

Route::prefix('administracion/cargas')->name('cargas.')->controller(CargaController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/vista-previa', 'preview')->name('preview');
    Route::post('/procesar', 'procesar')->name('procesar');
    Route::get('/historial', 'historial')->name('historial');
    Route::get('/plantilla/{tipo}', 'descargarPlantilla')->name('plantilla');
    Route::get('/reporte-errores/{id}', 'descargarReporteErrores')->name('reporte-errores');
});

Route::resource('cursos', CursoController::class);

Route::resource('users', UserController::class);

Route::resource('directores_carrera', DirectorCarreraController::class);

Route::resource('importaciones', ImportacionesController::class)->only(['index', 'show', 'destroy']);

// ===== Grupo 3: Revisión y Consejo =====
Route::get('/revision/asistente', [RevisionController::class, 'bandejaAsistente'])->name('revision.asistente');
Route::get('/revision/caso/{id}', [RevisionController::class, 'detalleCaso'])->name('revision.detalle');
Route::post('/revision/caso/{id}', [RevisionController::class, 'guardarRevision'])->name('revision.guardar');
