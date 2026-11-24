<?php

namespace App\Enums;

enum NivelCarrera: string
{
    case GRADO = 'grado';
    case POSGRADO = 'posgrado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::GRADO => 'Grado',
            self::POSGRADO => 'Posgrado',
        };
    }

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
