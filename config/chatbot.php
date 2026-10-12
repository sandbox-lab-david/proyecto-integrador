<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración del Motor Prolog
    |--------------------------------------------------------------------------
    | Parámetros de conexión HTTP con el servidor en SWI-Prolog (servidor.pl).
    | El contrato JSON v2 requiere comunicación por HTTP/JSON en el puerto 8081.
    */
    'prolog' => [
        'url' => env('PROLOG_API_URL', 'http://127.0.0.1:8081'),
        'api_key' => env('PROLOG_API_KEY', ''),
        'timeout' => (float) env('PROLOG_TIMEOUT', 5.0),
        'connect_timeout' => (float) env('PROLOG_CONNECT_TIMEOUT', 1.0),
        'intentos' => (int) env('PROLOG_INTENTOS', 2),
    ],
];
