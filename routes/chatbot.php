<?php

use Illuminate\Support\Facades\Route;

Route::view('/chatbot', 'chatbot.index')->name('chatbot.index');
Route::view('/chatbot/demo', 'chatbot.demo')->name('chatbot.demo');
Route::view('/chatbot/reporte', 'chatbot.reporte', ['preguntas' => []])->name('chatbot.reporte');

// Endpoints funcionales del Chatbot (Web y API)
Route::controller(\App\Modules\Chatbot\Http\Controllers\ChatbotController::class)->group(function () {
    Route::get('/chatbot/catalogo', 'catalogo')->name('chatbot.catalogo');
    Route::post('/chatbot/mensaje', 'mensaje')->name('chatbot.mensaje');
    Route::get('/chatbot/historial', 'historial')->name('chatbot.historial');

    // Rutas con prefijo /api para integración externa
    Route::prefix('api/chatbot')->group(function () {
        Route::get('/catalogo', 'catalogo');
        Route::post('/mensaje', 'mensaje');
        Route::get('/historial', 'historial');
    });
});
