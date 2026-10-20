<?php

namespace App\Services\Solicitudes;

use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Restricciones comunes al borrador, a la descarga y al envío. */
class ReglasCampoSolicitud
{
    // Hora institucional utilizada únicamente por las validaciones del módulo 2.
    private const ZONA_HORARIA = 'America/Guayaquil';

    public static function zonaHoraria(): string
    {
        return self::ZONA_HORARIA;
    }

    /** El máximo genérico del borrador es de caracteres, incluso en campos numéricos. */
    public static function limiteTexto(int $maximo): Closure
    {
        return static function (string $atributo, mixed $valor, Closure $fallar) use ($maximo): void {
            if (is_string($valor) && mb_strlen($valor) > $maximo) {
                $fallar('El campo :attribute no debe superar '.$maximo.' caracteres.');
            }
        };
    }

    /** Compara los datos cronológicos solo cuando sus formatos individuales son válidos. */
    public static function validarCronologia(Validator $validador, string $tramite): void
    {
        $datos = $validador->getData();
        if ($tramite === 'tercer-registro' && is_array($datos['materias'] ?? null)) {
            foreach ($datos['materias'] as $indice => $materia) {
                $inicio = 'materias.'.$indice.'.datos.registro_1_anio';
                $fin = 'materias.'.$indice.'.datos.registro_2_anio';
                $primero = data_get($datos, $inicio);
                $segundo = data_get($datos, $fin);
                if (is_string($primero) && is_string($segundo)
                    && ! $validador->errors()->has($inicio) && ! $validador->errors()->has($fin)
                    && is_numeric($primero) && is_numeric($segundo) && (int) $segundo < (int) $primero) {
                    $validador->errors()->add($fin, 'El año del segundo registro no puede ser anterior al del primero.');
                }
            }
        }

        if ($tramite === 'recalificacion') {
            $fecha = data_get($datos, 'campos.publicacion_fecha');
            $hora = data_get($datos, 'campos.publicacion_hora');
            if (! is_string($fecha) || ! is_string($hora)
                || $validador->errors()->has('campos.publicacion_fecha')
                || $validador->errors()->has('campos.publicacion_hora')) return;

            $publicacion = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', $fecha.' '.$hora,
                new \DateTimeZone(self::zonaHoraria()));
            if ($publicacion !== false && $publicacion > now()) {
                $validador->errors()->add('campos.publicacion_hora',
                    'La fecha y hora de publicación no pueden estar en el futuro.');
            }
        }
    }

    public static function atributos(array $tramite): array
    {
        $atributos = [];
        foreach (['campos' => 'campos', 'campos_materia' => 'materias.*.datos'] as $grupo => $prefijo) {
            foreach ($tramite[$grupo] ?? [] as $campo) {
                $atributos[$prefijo.'.'.$campo['nombre']] = mb_strtolower($campo['etiqueta']);
            }
        }
        return $atributos;
    }

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
            'fecha' => ['date_format:Y-m-d', 'before_or_equal:'.now(self::zonaHoraria())->toDateString()],
            'hora' => ['date_format:H:i'],
            default => ['max:'.($campo['max_length'] ?? ($campo['tipo'] === 'texto_largo' ? 2000 : 255))],
        };
    }
}
