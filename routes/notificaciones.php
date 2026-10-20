<?php

use App\Http\Controllers\NotificacionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:60,1,notificaciones:'])->prefix('notificaciones')->name('notificaciones.')->group(function () {
    Route::get('/', [NotificacionController::class, 'index'])->name('index');
    Route::get('/resumen', [NotificacionController::class, 'resumen'])->name('resumen');
    Route::post('/leer-todas', [NotificacionController::class, 'leerTodas'])->name('leer-todas');
    Route::post('/{notificacion}/leer', [NotificacionController::class, 'leer'])->whereUuid('notificacion')->name('leer');
});
