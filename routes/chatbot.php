<?php

use Illuminate\Support\Facades\Route;

Route::view('/chatbot', 'chatbot.index')->name('chatbot.index');
Route::view('/chatbot/demo', 'chatbot.demo')->name('chatbot.demo');
Route::get('/chatbot/reporte', [\App\Modules\Chatbot\Http\Controllers\ChatbotController::class, 'reporte'])
    ->name('chatbot.reporte')
    ->middleware(['auth', 'verified', 'rol:admin']);

// Endpoints web del Chatbot (sesión, CSRF, middleware web)
Route::controller(\App\Modules\Chatbot\Http\Controllers\ChatbotController::class)->group(function () {
    Route::get('/chatbot/catalogo', 'catalogo')->name('chatbot.catalogo');
    Route::post('/chatbot/mensaje', 'mensaje')->name('chatbot.mensaje')->middleware('throttle:60,1');
    Route::get('/chatbot/historial', 'historial')->name('chatbot.historial');
    Route::delete('/chatbot/historial', 'limpiarHistorial')->name('chatbot.historial.limpiar');
});
