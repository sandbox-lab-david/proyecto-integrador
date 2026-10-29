<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RevisionController; // ===== Grupo 3
use App\Http\Controllers\AgendaConsejoController;
use App\Http\Controllers\ResolucionesConsejoController;
use App\Http\Controllers\Administracion\CarreraController;
use App\Http\Controllers\Administracion\FacultadController;
use App\Http\Controllers\Administracion\MallaController;
use App\Http\Controllers\Administracion\RolController;
use App\Http\Controllers\Administracion\TrabajadorController;
use App\Http\Controllers\Administracion\DirectorCarreraController;
use App\Http\Controllers\Administracion\UserController;
use App\Http\Controllers\Administracion\InicioController;
use App\Http\Controllers\Administracion\PeriodoController;
use App\Http\Controllers\Administracion\MateriaController;
use App\Http\Controllers\Administracion\ParaleloController;
use App\Http\Controllers\Administracion\CargaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Acceso\PanelController;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::get('/dashboard', PanelController::class)->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::view('/estilos', 'estilos')->name('estilos');

require __DIR__.'/chatbot.php';
require __DIR__.'/estudiantes.php';

// ===== Grupo 1: Administración académica =====
Route::middleware(['auth', 'rol:admin'])->group(function () {
    Route::get('/administracion', [InicioController::class, 'index'])->name('administracion.inicio');

    Route::get('/facultades', [FacultadController::class, 'index'])->name('administracion.facultades');
    Route::post('/facultades', [FacultadController::class, 'store'])->name('administracion.facultades.store');
    Route::get('/facultades/{facultad}/editar', [FacultadController::class, 'index'])->name('administracion.facultades.editar');
    Route::put('/facultades/{facultad}', [FacultadController::class, 'update'])->name('administracion.facultades.update');
    Route::patch('/facultades/{facultad}/estado', [FacultadController::class, 'cambiarEstado'])->name('administracion.facultades.estado');

    Route::get('/carreras', [CarreraController::class, 'index'])->name('administracion.carreras');
    Route::post('/carreras', [CarreraController::class, 'store'])->name('administracion.carreras.store');
    Route::get('/carreras/{carrera}/editar', [CarreraController::class, 'index'])->name('administracion.carreras.editar');
    Route::put('/carreras/{carrera}', [CarreraController::class, 'update'])->name('administracion.carreras.update');
    Route::patch('/carreras/{carrera}/estado', [CarreraController::class, 'cambiarEstado'])->name('administracion.carreras.estado');

    Route::get('/mallas', [MallaController::class, 'index'])->name('administracion.mallas');
    Route::post('/mallas', [MallaController::class, 'store'])->name('administracion.mallas.store');
    Route::get('/mallas/{malla}', [MallaController::class, 'show'])->name('administracion.mallas.ver');
    Route::get('/mallas/{malla}/editar', [MallaController::class, 'index'])->name('administracion.mallas.editar');
    Route::put('/mallas/{malla}', [MallaController::class, 'update'])->name('administracion.mallas.update');
    Route::patch('/mallas/{malla}/vigente', [MallaController::class, 'marcarVigente'])->name('administracion.mallas.vigente');
    Route::post('/mallas/{malla}/materias', [MallaController::class, 'agregarMateria'])->name('administracion.mallas.materias.store');
    Route::delete('/mallas/{malla}/materias/{materia}', [MallaController::class, 'quitarMateria'])->name('administracion.mallas.materias.destroy');

    Route::get('/periodos', [PeriodoController::class, 'index'])->name('administracion.periodos');
    Route::post('/periodos', [PeriodoController::class, 'store'])->name('administracion.periodos.store');
    Route::get('/periodos/{periodo}/editar', [PeriodoController::class, 'index'])->name('administracion.periodos.editar');
    Route::put('/periodos/{periodo}', [PeriodoController::class, 'update'])->name('administracion.periodos.update');
    Route::patch('/periodos/{periodo}/activar', [PeriodoController::class, 'activar'])->name('administracion.periodos.activar');

    Route::get('/materias', [MateriaController::class, 'index'])->name('administracion.materias');
    Route::post('/materias', [MateriaController::class, 'store'])->name('administracion.materias.store');
    Route::get('/materias/{materia}/editar', [MateriaController::class, 'index'])->name('administracion.materias.editar');
    Route::put('/materias/{materia}', [MateriaController::class, 'update'])->name('administracion.materias.update');
    Route::patch('/materias/{materia}/estado', [MateriaController::class, 'cambiarEstado'])->name('administracion.materias.estado');

    Route::get('/paralelos', [ParaleloController::class, 'index'])->name('administracion.paralelos');
    Route::post('/paralelos', [ParaleloController::class, 'store'])->name('administracion.paralelos.store');
    Route::get('/paralelos/{curso}/editar', [ParaleloController::class, 'index'])->name('administracion.paralelos.editar');
    Route::put('/paralelos/{curso}', [ParaleloController::class, 'update'])->name('administracion.paralelos.update');
    Route::delete('/paralelos/{curso}', [ParaleloController::class, 'destroy'])->name('administracion.paralelos.destroy');

    Route::prefix('administracion/cargas')->name('cargas.')->controller(CargaController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/vista-previa', 'subir')->name('preview');
        Route::get('/historial', 'historial')->name('historial');
        Route::get('/plantilla/{tipo}', 'descargarPlantilla')->name('plantilla');
        Route::get('/ejemplo/{tipo}', 'descargarEjemplo')->name('ejemplo');
        Route::get('/{importacion}/vista-previa', 'vistaPrevia')->name('vista-previa');
        Route::post('/{importacion}/procesar', 'procesar')->name('procesar');
        Route::delete('/{importacion}', 'cancelar')->name('cancelar');
        Route::get('/{importacion}', 'detalle')->name('detalle');
        Route::get('/{importacion}/errores', 'descargarReporteErrores')->name('reporte-errores');
    });

    Route::get('/personal', [TrabajadorController::class, 'index'])->name('administracion.personal');
    Route::post('/personal', [TrabajadorController::class, 'store'])->name('administracion.personal.store');
    Route::get('/personal/{trabajador}/editar', [TrabajadorController::class, 'index'])->name('administracion.personal.editar');
    Route::put('/personal/{trabajador}', [TrabajadorController::class, 'update'])->name('administracion.personal.update');
    Route::patch('/personal/{trabajador}/estado', [TrabajadorController::class, 'cambiarEstado'])->name('administracion.personal.estado');

    Route::get('/directores', [DirectorCarreraController::class, 'index'])->name('administracion.directores');
    Route::post('/directores', [DirectorCarreraController::class, 'store'])->name('administracion.directores.store');
    Route::patch('/directores/{asignacion}/terminar', [DirectorCarreraController::class, 'terminar'])->name('administracion.directores.terminar');

    Route::get('/cuentas', [UserController::class, 'index'])->name('administracion.cuentas');
    Route::patch('/cuentas/{usuario}/estado', [UserController::class, 'cambiarEstado'])->name('administracion.cuentas.estado');
    Route::post('/cuentas/{usuario}/enlace', [UserController::class, 'enviarEnlace'])->name('administracion.cuentas.enlace');

    Route::get('/roles', [RolController::class, 'index'])->name('administracion.roles');

});

// ===== Grupo 3: Revisión y Consejo =====
Route::middleware(['auth', 'rol:asistente,admin'])->group(function () {
    Route::get('/revision/asistente', [RevisionController::class, 'bandejaAsistente'])->name('revision.asistente');
    Route::post('/revision/caso/{id}', [RevisionController::class, 'guardarRevision'])->name('revision.guardar');

});

Route::middleware(['auth', 'rol:asistente,admin'])->group(function () {
    Route::get('/revision/agendas', [AgendaConsejoController::class, 'index'])->name('revision.agendas');
    Route::post('/revision/agendas', [AgendaConsejoController::class, 'store'])->name('revision.agendas.store');
    Route::post('/revision/agendas/solicitudes/rechazar-varias', [AgendaConsejoController::class, 'rechazarVariasSolicitudes'])->name('revision.agendas.solicitudes.rechazar-varias');
});

Route::middleware(['auth', 'rol:consulta,asistente,admin'])->group(function () {
    Route::get('/revision/resoluciones', [ResolucionesConsejoController::class, 'index'])->name('revision.resoluciones');
    Route::get('/revision/agendas/{agenda}/resoluciones', [ResolucionesConsejoController::class, 'porAgenda'])->name('revision.agendas.resoluciones');
    Route::get('/revision/resoluciones/documento', [ResolucionesConsejoController::class, 'documento'])->name('revision.resoluciones.documento');
    Route::get('/revision/resoluciones/exportar', [ResolucionesConsejoController::class, 'exportar'])->name('revision.resoluciones.exportar');
    Route::get('/revision/agendas/{agenda}/documento', [ResolucionesConsejoController::class, 'documentoAgenda'])->name('revision.agendas.documento');
});

Route::middleware(['auth', 'rol:asistente,admin'])->group(function () {
    Route::post('/revision/agendas/{agenda}/resoluciones/{solicitud}', [ResolucionesConsejoController::class, 'guardar'])->name('revision.agendas.resoluciones.guardar');
    Route::post('/revision/resoluciones/{id}', [ResolucionesConsejoController::class, 'actualizarEstado'])->name('revision.resoluciones.estado');
});

Route::middleware(['auth', 'rol:director,admin'])->group(function () {
    Route::get('/revision/director', [RevisionController::class, 'bandejaDirector'])->name('revision.director');
    Route::post('/revision/director/caso/{id}', [RevisionController::class, 'guardarDecisionDirector'])->name('revision.director.guardar');
});

Route::middleware(['auth', 'rol:asistente,director,admin'])->group(function () {
    Route::get('/revision/caso/{id}', [RevisionController::class, 'detalleCaso'])->name('revision.detalle');
    Route::get('/revision/caso/{id}/documento/{documento}', [RevisionController::class, 'verDocumento'])->name('revision.documento');
});
