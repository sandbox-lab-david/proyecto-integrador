<?php

namespace App\Services\Solicitudes;

use Illuminate\Validation\Rule;

/** Restricciones comunes al borrador, a la descarga y al envío. */
class ReglasCampoSolicitud
{
    public static function tipo(array $campo): array
    {
        return match ($campo['tipo']) {
            'numero' => array_values(array_filter([
                'numeric',
                isset($campo['min']) ? 'min:'.$campo['min'] : null,
                isset($campo['max']) ? 'max:'.$campo['max'] : null,
                ($campo['entero'] ?? false) ? 'integer' : null,
            ])),
            'lista' => [Rule::in(array_keys($campo['opciones']))],
            'fecha' => ['date_format:Y-m-d', 'before_or_equal:today'],
            'hora' => ['date_format:H:i'],
            default => ['max:'.($campo['max_length'] ?? ($campo['tipo'] === 'texto_largo' ? 2000 : 255))],
        };
    }
}
