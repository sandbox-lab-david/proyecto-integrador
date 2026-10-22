<?php

namespace App\Enums;

enum EstadoInscripcion: string
{
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
    case EGRESADO = 'egresado';
    case RETIRADO = 'retirado';

    public function etiqueta(): string
    {
        return ucfirst($this->value);
    }

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
