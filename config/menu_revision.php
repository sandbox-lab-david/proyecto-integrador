<?php

return [
    'Revisión' => [
        ['texto' => 'Bandeja de solicitudes', 'ruta' => 'revision.asistente', 'activo' => ['revision.asistente', 'revision.detalle'], 'roles' => ['asistente', 'admin']],
        ['texto' => 'Bandeja Director', 'ruta' => 'revision.director', 'activo' => 'revision.director', 'roles' => ['director', 'admin']],
    ],
    'Consejo' => [
        ['texto' => 'Agendas del Consejo', 'ruta' => 'revision.agendas', 'activo' => 'revision.agendas*', 'roles' => ['asistente', 'admin']],
        ['texto' => 'Resoluciones', 'ruta' => 'revision.resoluciones', 'activo' => 'revision.resoluciones*', 'roles' => ['consulta', 'admin']],
    ],
];
