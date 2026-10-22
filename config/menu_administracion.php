<?php

return [
    'Módulo' => [
        ['texto' => 'Inicio', 'ruta' => 'administracion.inicio', 'activo' => 'administracion.inicio'],
    ],
    'Estructura académica' => [
        ['texto' => 'Periodos académicos', 'ruta' => 'administracion.periodos', 'activo' => 'administracion.periodos*'],
        ['texto' => 'Materias', 'ruta' => 'administracion.materias', 'activo' => 'administracion.materias*'],
        ['texto' => 'Paralelos y docentes', 'ruta' => 'administracion.paralelos', 'activo' => 'administracion.paralelos*'],
    ],
    'Datos' => [
        ['texto' => 'Importaciones SIAC', 'ruta' => 'cargas.index', 'activo' => 'cargas.*'],
    ],
];
