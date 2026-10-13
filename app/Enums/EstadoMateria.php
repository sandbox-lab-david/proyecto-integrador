<?php

namespace App\Enums;

/**
 * Estado académico que el estudiante declara para cada materia (contrato C59).
 *
 * Son los mismos valores en el formulario, en la revisión (G3) y en los hechos de Prolog (G4).
 */
enum EstadoMateria: string
{
    case Aprobada = 'aprobada';
    case Reprobada = 'reprobada';
    case SemestreActual = 'semestre_actual';
    case Retirada = 'retirada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Aprobada => 'Aprobada',
            self::Reprobada => 'Reprobada',
            self::SemestreActual => 'Semestre actual',
            self::Retirada => 'Retirada',
        };
    }
}
