<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SoloEntornoLocal;

Route::view('/chatbot', 'chatbot.index')->name('chatbot.index');
Route::view('/chatbot/demo', 'chatbot.demo')->name('chatbot.demo');

Route::middleware(SoloEntornoLocal::class)->group(function () {
    Route::view('/chatbot/pruebas', 'chatbot.pruebas')->name('chatbot.pruebas');
    Route::post('/chatbot/pruebas/mensaje', [\App\Modules\Chatbot\Http\Controllers\ChatbotController::class, 'mensaje'])
        ->middleware('throttle:60,1')
        ->name('chatbot.pruebas.mensaje');
});

// Endpoints web del Chatbot (sesión, CSRF, middleware web)
Route::controller(\App\Modules\Chatbot\Http\Controllers\ChatbotController::class)->group(function () {
    Route::get('/chatbot/catalogo', 'catalogo')->name('chatbot.catalogo');
    Route::post('/chatbot/mensaje', 'mensaje')->name('chatbot.mensaje')->middleware('throttle:60,1');
});
