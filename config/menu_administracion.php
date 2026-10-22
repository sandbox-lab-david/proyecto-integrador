<?php

return [
    'Módulo' => [
        ['texto' => 'Inicio', 'ruta' => 'administracion.inicio', 'activo' => 'administracion.inicio'],
    ],
    'Estructura académica' => [
        ['texto' => 'Facultades', 'ruta' => 'administracion.facultades', 'activo' => 'administracion.facultades*'],
        ['texto' => 'Carreras', 'ruta' => 'administracion.carreras', 'activo' => 'administracion.carreras*'],
        ['texto' => 'Mallas', 'ruta' => 'administracion.mallas', 'activo' => 'administracion.mallas*'],
        ['texto' => 'Periodos académicos', 'ruta' => 'administracion.periodos', 'activo' => 'administracion.periodos*'],
        ['texto' => 'Materias', 'ruta' => 'administracion.materias', 'activo' => 'administracion.materias*'],
        ['texto' => 'Paralelos y docentes', 'ruta' => 'administracion.paralelos', 'activo' => 'administracion.paralelos*'],
    ],
    'Personas' => [
        ['texto' => 'Personal', 'ruta' => 'administracion.personal', 'activo' => 'administracion.personal*'],
        ['texto' => 'Directores de carrera', 'ruta' => 'administracion.directores', 'activo' => 'administracion.directores*'],
        ['texto' => 'Cuentas de acceso', 'ruta' => 'administracion.cuentas', 'activo' => 'administracion.cuentas*'],
        ['texto' => 'Roles', 'ruta' => 'administracion.roles', 'activo' => 'administracion.roles'],
    ],
    'Datos' => [
        ['texto' => 'Importaciones SIAC', 'ruta' => 'cargas.index', 'activo' => 'cargas.*'],
    ],
];
