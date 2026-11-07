<?php

namespace App\Services\Solicitudes;

use App\Services\Academico\CatalogoAcademicoService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Facultades y carreras de los selectores del paso 1, leídas del catálogo
 * académico de G1 (contratos C01–C02). El estudiante sigue escribiendo su
 * facultad y carrera; aquí solo se limitan a las que existen y están activas.
 */
final class CatalogoFacultades
{
    /**
     * @return array<string, list<string>> nombre de facultad => nombres de sus carreras
     */
    public static function todas(): array
    {
        return once(function () {
            $catalogo = app(CatalogoAcademicoService::class);
            $carreras = $catalogo->carrerasPorFacultad()->groupBy('facultad_id');

            return $catalogo->facultades()->sortBy('nombre')
                ->mapWithKeys(fn ($facultad) => [
                    $facultad->nombre => $carreras->get($facultad->id, collect())->pluck('nombre')->values()->all(),
                ])
                ->all();
        });
    }

    /** @param array<string, list<string>>|null $catalogo un subconjunto, p. ej. las inscripciones del estudiante */
    public static function restricciones(mixed $facultad, ?array $catalogo = null): array
    {
        $catalogo ??= self::todas();
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
