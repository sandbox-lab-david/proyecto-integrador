<?php

return [
    'Módulo' => [
        ['texto' => 'Inicio', 'ruta' => 'administracion.inicio', 'activo' => 'administracion.inicio'],
    ],
    'Estructura académica' => [
        ['texto' => 'Facultades', 'ruta' => 'administracion.facultades', 'activo' => 'administracion.facultades*', 'descripcion' => 'Facultades de la UEES y su correo de notificaciones.'],
        ['texto' => 'Carreras', 'ruta' => 'administracion.carreras', 'activo' => 'administracion.carreras*', 'descripcion' => 'Carreras de cada facultad, sus modalidades y si admiten tercer registro.'],
        ['texto' => 'Mallas', 'ruta' => 'administracion.mallas', 'activo' => 'administracion.mallas*', 'descripcion' => 'Versiones del plan de estudios de cada carrera y sus materias por nivel.'],
        ['texto' => 'Periodos académicos', 'ruta' => 'administracion.periodos', 'activo' => 'administracion.periodos*', 'descripcion' => 'Crea periodos y define cuál está activo.'],
        ['texto' => 'Materias', 'ruta' => 'administracion.materias', 'activo' => 'administracion.materias*', 'descripcion' => 'Registra materias con su código, créditos y categoría.'],
        ['texto' => 'Paralelos y docentes', 'ruta' => 'administracion.paralelos', 'activo' => 'administracion.paralelos*', 'descripcion' => 'Abre paralelos de una materia en un periodo y asigna su docente.'],
    ],
    'Personas' => [
        ['texto' => 'Personal', 'ruta' => 'administracion.personal', 'activo' => 'administracion.personal*', 'descripcion' => 'Asistentes, directores, docentes y demás personal, con sus roles y facultades.'],
        ['texto' => 'Directores de carrera', 'ruta' => 'administracion.directores', 'activo' => 'administracion.directores*', 'descripcion' => 'Quién dirige cada carrera y desde cuándo.'],
        ['texto' => 'Cuentas de acceso', 'ruta' => 'administracion.cuentas', 'activo' => 'administracion.cuentas*', 'descripcion' => 'Activa o desactiva cuentas y envía enlaces para crear la contraseña.'],
        ['texto' => 'Roles', 'ruta' => 'administracion.roles', 'activo' => 'administracion.roles', 'descripcion' => 'Los cinco roles del sistema y cuántas personas tiene cada uno.'],
    ],
    'Datos' => [
        ['texto' => 'Importaciones SIAC', 'ruta' => 'cargas.index', 'activo' => 'cargas.*', 'descripcion' => 'Sube archivos del SIAC, revisa la vista previa y consulta el historial.'],
    ],
];
