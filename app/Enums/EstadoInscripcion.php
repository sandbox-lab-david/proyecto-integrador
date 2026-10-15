<?php

namespace App\Enums;

enum EstadoInscripcion: string
{
    case INSCRITO = 'inscrito';
    case RETIRADO = 'retirado';
    case PENDIENTE = 'pendiente';
    case APROBADO = 'aprobado';
}