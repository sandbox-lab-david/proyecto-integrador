<?php

use Illuminate\Support\Facades\Route;

Route::view('/chatbot', 'chatbot.index')->name('chatbot.index');

Route::view('/chatbot/reporte', 'chatbot.reporte', ['preguntas' => []])->name('chatbot.reporte');
