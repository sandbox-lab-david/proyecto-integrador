<?php

namespace App\Services\Academico;

use App\Models\Importacion;
use App\Services\Academico\Importadores\Carreras;
use App\Services\Academico\Importadores\Docentes;
use App\Services\Academico\Importadores\Estudiantes;
use App\Services\Academico\Importadores\Importador;
use App\Services\Academico\Importadores\Lector;
use App\Services\Academico\Importadores\Mallas;
use App\Services\Academico\Importadores\Materias;
use App\Services\Academico\Importadores\Paralelos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportacionService
{
    public const LIMITE_INMEDIATO = 500;

    public const IMPORTADORES = [
        'carreras' => Carreras::class,
        'materias' => Materias::class,
        'mallas' => Mallas::class,
        'docentes' => Docentes::class,
        'paralelos' => Paralelos::class,
        'estudiantes' => Estudiantes::class,
    ];

    public function importador(string $entidad): Importador
    {
        abort_unless(isset(self::IMPORTADORES[$entidad]), 404);

        return app(self::IMPORTADORES[$entidad]);
    }

    public function analizar(Importacion $importacion): array
    {
        $importador = $this->importador($importacion->entidad);

        try {
            [$encabezados, $filas] = $this->leer($importacion->archivo_ruta);
        } catch (Throwable) {
            return ['faltantes' => [], 'filas' => [], 'resumen' => $this->resumen([]), 'ilegible' => true];
        }

        $faltantes = array_values(array_diff($importador->obligatorias(), $encabezados));
        if ($faltantes) {
            return ['faltantes' => $faltantes, 'filas' => [], 'resumen' => $this->resumen([]), 'ilegible' => false];
        }

        $vistas = [];
        $resultado = [];

        foreach ($filas as $numero => $fila) {
            $fila = array_merge(array_fill_keys(array_keys($importador->columnas()), null), $fila);
            $motivos = $importador->validar($fila);

            if (! $motivos) {
                $clave = $importador->clave($fila);
                if (isset($vistas[$clave])) {
                    $motivos[] = "Está repetida: es igual a la fila {$vistas[$clave]}.";
                } else {
                    $vistas[$clave] = $numero;
                }
            }

            $resultado[] = [
                'fila' => $numero,
                'datos' => $fila,
                'estado' => $motivos ? 'error' : ($importador->existe($fila) ? 'actualiza' : 'nueva'),
                'motivos' => $motivos,
            ];
        }

        return ['faltantes' => [], 'filas' => $resultado, 'resumen' => $this->resumen($resultado), 'ilegible' => false];
    }

    public function procesar(Importacion $importacion): void
    {
        $importacion->update(['estado' => 'procesando']);
        $importador = $this->importador($importacion->entidad);
        $analisis = $this->analizar($importacion);

        if ($analisis['ilegible'] || $analisis['faltantes']) {
            $importacion->update([
                'estado' => 'fallida',
                'errores' => [['fila' => null, 'codigo' => null, 'motivo' => $analisis['ilegible'] ? 'No se pudo leer el archivo.' : 'Faltan columnas: '.implode(', ', $analisis['faltantes'])]],
            ]);

            return;
        }

        $cuentas = ['creadas' => 0, 'actualizadas' => 0, 'rechazadas' => 0];
        $errores = [];

        foreach ($analisis['filas'] as $fila) {
            if ($fila['estado'] === 'error') {
                $cuentas['rechazadas']++;
                $errores[] = $this->error($fila, $importador, implode(' ', $fila['motivos']));

                continue;
            }

            try {
                DB::transaction(fn () => $importador->guardar($fila['datos']));
                $cuentas[$fila['estado'] === 'nueva' ? 'creadas' : 'actualizadas']++;
            } catch (Throwable $e) {
                $cuentas['rechazadas']++;
                $errores[] = $this->error($fila, $importador, 'No se pudo guardar: '.$e->getMessage());
            }
        }

        $importacion->update($cuentas + [
            'estado' => 'completada',
            'total_filas' => count($analisis['filas']),
            'errores' => $errores ?: null,
        ]);
    }

    public function leer(string $ruta): array
    {
        $contenido = Storage::disk('local')->get($ruta);
        $esCsv = in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['csv', 'txt'], true);
        $primera = strtok((string) $contenido, "\n");
        $lector = new Lector(
            $esCsv && substr_count($primera, ';') > substr_count($primera, ',') ? ';' : ',',
            mb_check_encoding($contenido, 'UTF-8') ? 'UTF-8' : 'ISO-8859-1',
        );

        $hoja = Excel::toArray($lector, $ruta, 'local')[0] ?? [];
        $encabezados = array_map(fn ($e) => $this->encabezado($e), array_shift($hoja) ?? []);

        $filas = [];
        foreach ($hoja as $i => $valores) {
            $fila = [];
            foreach ($encabezados as $j => $columna) {
                if ($columna !== '') {
                    $fila[$columna] = $this->valor($valores[$j] ?? null);
                }
            }

            if (array_filter($fila, fn ($v) => $v !== null)) {
                $filas[$i + 2] = $fila;
            }
        }

        return [array_values(array_filter($encabezados)), $filas];
    }

    private function encabezado($texto): string
    {
        $texto = mb_strtolower(trim((string) $texto));
        $texto = strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', "\u{FEFF}" => '']);

        return trim(preg_replace('/[^a-z0-9]+/', '_', $texto), '_');
    }

    private function valor($valor): ?string
    {
        if (is_float($valor) && floor($valor) === $valor) {
            $valor = (int) $valor;
        }

        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }

    private function resumen(array $filas): array
    {
        $estados = collect($filas)->countBy('estado');

        return [
            'nuevas' => $estados->get('nueva', 0),
            'actualiza' => $estados->get('actualiza', 0),
            'errores' => $estados->get('error', 0),
            'total' => count($filas),
        ];
    }

    private function error(array $fila, Importador $importador, string $motivo): array
    {
        $primera = array_key_first($importador->columnas());

        return ['fila' => $fila['fila'], 'codigo' => $fila['datos'][$primera] ?? null, 'motivo' => $motivo];
    }
}
