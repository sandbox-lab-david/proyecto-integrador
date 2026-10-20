<?php

namespace App\Services\Solicitudes;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Listas públicas de referencia, independientes de la base de datos de estudiantes. */
final class CatalogoFacultades
{
    public static function todas(): array
    {
        return config('facultades_uees', require config_path('facultades_uees.php'));
    }

    public static function restricciones(mixed $facultad): array
    {
        $catalogo = self::todas();
        $carreras = is_string($facultad) && isset($catalogo[$facultad])
            ? $catalogo[$facultad] : array_merge(...array_values($catalogo));

        return ['facultad' => Rule::in(array_keys($catalogo)), 'carrera' => Rule::in($carreras)];
    }

    public static function normalizar(array $datos): array
    {
        $catalogo = self::todas();
        if (is_string($datos['facultad'] ?? null)) {
            $nombre = preg_replace('/^Facultad de\s+/iu', '', Str::trim($datos['facultad']));
            $datos['facultad'] = self::coincidencia($nombre, array_keys($catalogo));
        }
        if (is_string($datos['carrera'] ?? null)) {
            $datos['carrera'] = self::carrera($datos['carrera']);
        }
        if (is_string($datos['campos']['carrera_destino'] ?? null)) {
            $datos['campos']['carrera_destino'] = self::carrera($datos['campos']['carrera_destino']);
        }
        return $datos;
    }

    private static function carrera(string $valor): string
    {
        // Nombres anteriores usados por los formularios y por admisiones de la UEES.
        $alias = ['computacion' => 'Ingeniería en Computación', 'sistemas (computacion)' => 'Ingeniería en Computación',
            'diseno grafico' => 'Diseño Gráfico Interactivo'];
        $valor = $alias[self::clave($valor)] ?? $valor;
        return self::coincidencia($valor, array_merge(...array_values(self::todas())));
    }

    private static function coincidencia(string $valor, array $opciones): string
    {
        foreach ($opciones as $opcion) {
            if (self::clave($opcion) === self::clave($valor)) return $opcion;
        }
        return $valor;
    }

    private static function clave(string $valor): string
    {
        return mb_strtolower(Str::ascii(preg_replace('/\s+/u', ' ', Str::trim($valor))));
    }
}
