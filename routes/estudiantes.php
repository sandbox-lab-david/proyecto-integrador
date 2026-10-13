<?php

use App\Http\Controllers\Estudiante\ArchivoController;
use App\Http\Controllers\Estudiante\BorradorTemporalController;
use App\Http\Controllers\Estudiante\CatalogoController;
use App\Http\Controllers\Estudiante\FirmadoTemporalController;
use App\Http\Controllers\Estudiante\RespaldoTemporalController;
use App\Http\Controllers\Estudiante\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::post('/estudiante/solicitudes/documento', [SolicitudController::class, 'documento'])
    ->middleware('throttle:10,1')->name('solicitudes.documento');

Route::get('/estudiante/tramites', [CatalogoController::class, 'index'])
    ->name('estudiante.catalogo');

Route::view(
    '/estudiante/tramites/examen-recuperacion',
    'estudiante.recuperacion'
)->name('estudiante.tramites.recuperacion');

Route::view(
    '/estudiante/tramites/examen-gracia',
    'estudiante.gracia'
)->name('estudiante.tramites.gracia');

Route::view(
    '/estudiante/tramites/ayudante-catedra',
    'estudiante.ayudante'
)->name('estudiante.tramites.ayudante');

Route::view(
    '/estudiante/tramites/tercer-registro',
    'estudiante.tercer-registro'
)->name('estudiante.tramites.tercer-registro');

Route::view(
    '/estudiante/tramites/recalificacion-examen',
    'estudiante.recalificacion'
)->name('estudiante.tramites.recalificacion');

Route::view(
    '/estudiante/tramites/examen-supletorio',
    'estudiante.supletorio'
)->name('estudiante.tramites.supletorio');

Route::view(
    '/estudiante/tramites/homologacion',
    'estudiante.homologacion'
)->name('estudiante.tramites.homologacion');

Route::get(
    '/estudiante/solicitudes/recuperacion/nueva',
    [SolicitudController::class, 'create']
)->defaults('tramite', 'recuperacion')->name('estudiante.solicitudes.recuperacion.crear');

Route::view(
    '/estudiante/solicitudes/gracia/nueva',
    'estudiante.formulario-gracia'
)->name('estudiante.solicitudes.gracia.crear');

Route::view(
    '/estudiante/acceso',
    'estudiante.login'
)->name('estudiante.acceso');

Route::view(
    '/estudiante/tramites/retiro-materia',
    'estudiante.retiro-materia'
)->name('estudiante.tramites.retiro-materia');

Route::view(
    '/estudiante/tramites/retiro-carrera',
    'estudiante.retiro-carrera'
)->name('estudiante.tramites.retiro-carrera');

Route::view(
    '/estudiante/tramites/retiro-universidad',
    'estudiante.retiro-universidad'
)->name('estudiante.tramites.retiro-universidad');

Route::view(
    '/estudiante/tramites/retiro-extemporaneo',
    'estudiante.retiro-extemporaneo'
)->name('estudiante.tramites.retiro-extemporaneo');

Route::view(
    '/estudiante/tramites/cambio-carrera',
    'estudiante.cambio-carrera'
)->name('estudiante.tramites.cambio-carrera');

Route::view(
    '/estudiante/tramites/cambio-modalidad',
    'estudiante.cambio-modalidad'
)->name('estudiante.tramites.cambio-modalidad');

Route::view(
    '/estudiante/tramites/cambio-malla',
    'estudiante.cambio-malla'
)->name('estudiante.tramites.cambio-malla');

Route::view(
    '/estudiante/tramites/reincorporacion',
    'estudiante.reincorporacion'
)->name('estudiante.tramites.reincorporacion');

Route::view(
    '/estudiante/tramites/examen-suficiencia',
    'estudiante.suficiencia'
)->name('estudiante.tramites.suficiencia');

Route::view(
    '/estudiante/tramites/incompleto',
    'estudiante.incompleto'
)->name('estudiante.tramites.incompleto');

Route::view(
    '/estudiante/tramites/person-to-person',
    'estudiante.person-to-person'
)->name('estudiante.tramites.person-to-person');

Route::view(
    '/estudiante/tramites/alcance-homologacion',
    'estudiante.alcance-homologacion'
)->name('estudiante.tramites.alcance-homologacion');

Route::view(
    '/estudiante/tramites/registro-extemporaneo',
    'estudiante.registro-extemporaneo'
)->name('estudiante.tramites.registro-extemporaneo');

Route::get(
    '/estudiante/solicitudes/nueva',
    [SolicitudController::class, 'create']
)->name('solicitudes.create');

// «Mis solicitudes» y el seguimiento de cada una (RF-2.13).
Route::get('/estudiante/solicitudes', [SolicitudController::class, 'index'])
    ->name('solicitudes.index');

Route::get('/estudiante/solicitudes/{solicitud}', [SolicitudController::class, 'show'])
    ->whereNumber('solicitud')->name('solicitudes.show');

// Respaldos del paso 3 mientras la solicitud todavía no existe: carpeta temporal de la sesión.
Route::post('/estudiante/respaldos-temporales', [RespaldoTemporalController::class, 'store'])
    ->middleware('throttle:60,1')->name('respaldos-temporales.store');

Route::get('/estudiante/respaldos-temporales/{id}', [RespaldoTemporalController::class, 'show'])
    ->whereUuid('id')->name('respaldos-temporales.show');

Route::delete('/estudiante/respaldos-temporales/{id}', [RespaldoTemporalController::class, 'destroy'])
    ->whereUuid('id')->name('respaldos-temporales.destroy');

// Borrador del formulario mientras la solicitud todavía no existe: uno por trámite, en la sesión.
Route::put('/estudiante/borradores-temporales/{tramite}', [BorradorTemporalController::class, 'update'])
    ->where('tramite', '[a-z0-9_-]+')->middleware('throttle:60,1')->name('borradores-temporales.update');

Route::delete('/estudiante/borradores-temporales/{tramite}', [BorradorTemporalController::class, 'destroy'])
    ->where('tramite', '[a-z0-9_-]+')->name('borradores-temporales.destroy');

// PDF firmado del paso 4 mientras la solicitud todavía no existe: uno por trámite, en la sesión.
Route::post('/estudiante/firmados-temporales/{tramite}', [FirmadoTemporalController::class, 'store'])
    ->where('tramite', '[a-z0-9_-]+')->middleware('throttle:20,1')->name('firmados-temporales.store');

Route::get('/estudiante/firmados-temporales/{tramite}', [FirmadoTemporalController::class, 'show'])
    ->where('tramite', '[a-z0-9_-]+')->name('firmados-temporales.show');

Route::delete('/estudiante/firmados-temporales/{tramite}', [FirmadoTemporalController::class, 'destroy'])
    ->where('tramite', '[a-z0-9_-]+')->name('firmados-temporales.destroy');

// Archivos privados con enlace firmado (ArchivoService::urlTemporal y <x-visor-documento>).
Route::get('/archivos/ver', [ArchivoController::class, 'ver'])
    ->middleware('signed')->name('archivos.ver');
