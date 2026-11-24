<?php

namespace App\Support;

final class Estados
{
    public const BORRADOR = 'borrador';

    public const ENVIADA = 'enviada';

    public const REVISADA = 'revisada';

    public const OBSERVADA = 'observada';

    public const APTA_CONSEJO = 'apta_consejo';

    public const AGENDADA = 'agendada';

    public const APROBADA_EJECUCION = 'aprobada_ejecucion';

    public const EJECUTADA = 'ejecutada';

    public const RECHAZADA = 'rechazada';

    public static function codigoDesdeEtiqueta(string $label): string
    {
        return match ($label) {
            'Borrador' => self::BORRADOR, 'Enviado por estudiante' => self::ENVIADA, 'Revisado' => self::REVISADA,
            'Observado' => self::OBSERVADA, 'Apto para consejo' => self::APTA_CONSEJO,
            'Agendado' => self::AGENDADA, 'Aprobado para ejecución' => self::APROBADA_EJECUCION,
            'Ejecutado' => self::EJECUTADA, 'Rechazado' => self::RECHAZADA,
            default => $label,
        };
    }
}
