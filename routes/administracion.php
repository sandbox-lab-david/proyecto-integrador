<?php

use App\Http\Controllers\Administracion\MateriaController;
use App\Http\Controllers\Administracion\ParaleloController;
use App\Http\Controllers\Administracion\PeriodoController;
use Illuminate\Support\Facades\Route;

Route::get('/periodos', [PeriodoController::class, 'index']);
Route::get('/materias', [MateriaController::class, 'index']);
Route::get('/paralelos', [ParaleloController::class, 'index']);
