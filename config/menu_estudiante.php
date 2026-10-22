<?php

// Menú propio de G2 para el contrato C37 publicado por G1.
return [
    'Solicitudes académicas' => [
        ['texto' => 'Catálogo de trámites', 'ruta' => 'estudiante.catalogo',
            'activo' => ['estudiante.catalogo', 'estudiante.tramites.*', 'estudiante.solicitudes.*', 'solicitudes.create']],
        ['texto' => 'Mis solicitudes', 'ruta' => 'solicitudes.index',
            'activo' => ['solicitudes.index', 'solicitudes.show', 'solicitudes.edit']],
    ],
];
