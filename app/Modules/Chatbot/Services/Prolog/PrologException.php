<?php

namespace App\Modules\Chatbot\Services\Prolog;

use RuntimeException;
use Throwable;

/**
 * Error tecnico del puente. Nunca representa "no cumple": el servicio que
 * llama debe convertirlo al sobre de error publico (503, 504, 502, 500, 422).
 */
class PrologException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly string $codigo,
        string $mensaje,
        public readonly bool $reintentable = false,
        public readonly array $campos = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($mensaje, $status, $previous);
    }

    public static function noDisponible(?Throwable $e = null): self
    {
        return new self(503, 'motor_no_disponible', 'El motor de reglas no esta disponible.', true, [], $e);
    }

    public static function tiempoAgotado(?Throwable $e = null): self
    {
        return new self(504, 'tiempo_agotado', 'El motor de reglas tardo demasiado en responder.', true, [], $e);
    }

    public static function respuestaInvalida(string $detalle): self
    {
        return new self(502, 'respuesta_motor_invalida', 'Respuesta incompatible del motor: ' . $detalle);
    }
}