<?php

namespace App\Services\Solicitudes;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

/** Datos declarados por el estudiante; no consulta ni importa información del SIAC. */
final class DatosFormularioSolicitud
{
    public const PERSONALES = [
        'nombre' => ['Nombres y apellidos', 255],
        'codigo' => ['Código estudiantil', 30],
        'cedula' => ['Cédula', 30],
        'facultad' => ['Facultad', 150],
        'carrera' => ['Carrera actual', 150],
        'modalidad' => ['Modalidad', 100],
        'periodo' => ['Periodo actual', 100],
        'correo' => ['Correo institucional', 255],
        'celular' => ['Celular', 20],
    ];

    public const MATERIA = [
        'codigo' => ['Código de la materia', 30],
        'nombre' => ['Nombre de la materia', 150],
        'periodo' => ['Periodo de la materia', 100],
        'paralelo' => ['Paralelo', 50],
        'docente' => ['Docente', 150],
    ];

    public const SOLO_DIGITOS = ['codigo', 'cedula', 'celular'];

    public const CAMBIOS = [
        'cambio-carrera' => [
            'actuales' => ['carrera'],
            'destino' => 'campos.carrera_destino',
            'mensaje' => 'La carrera de destino debe ser diferente de tu carrera actual.',
        ],
        'cambio-modalidad' => [
            'actuales' => ['modalidad', 'campos.modalidad_actual'],
            'destino' => 'campos.modalidad_destino',
            'mensaje' => 'La modalidad solicitada debe ser diferente de tu modalidad actual.',
        ],
    ];

    /** Limpia los extremos sin alterar espacios internos, saltos de línea ni tipos inválidos. */
    public static function limpiarEntrada(array $datos): array
    {
        $limpiar = function (mixed $valor) use (&$limpiar): mixed {
            if (is_array($valor)) return array_map($limpiar, $valor);
            if (! is_string($valor)) return $valor;

            $texto = Str::trim($valor);
            return $texto === '' ? null : $texto;
        };

        return array_map($limpiar, array_intersect_key($datos,
            array_flip([...array_keys(self::PERSONALES), 'campos', 'materias'])));
    }

    public static function validarCambio(Validator $validador, string $tramite): void
    {
        $cambio = self::CAMBIOS[$tramite] ?? null;
        if ($cambio === null) return;

        $datos = $validador->getData();
        $destino = self::compararTexto(data_get($datos, $cambio['destino']));
        if ($destino === '') return;

        foreach ($cambio['actuales'] as $ruta) {
            $actual = self::compararTexto(data_get($datos, $ruta));
            // En un borrador puede faltar todavía el valor actual o el destino.
            if ($actual !== '' && $actual === $destino) {
                $validador->errors()->add($cambio['destino'], $cambio['mensaje']);
                return;
            }
        }
    }

    private static function compararTexto(mixed $valor): string
    {
        if (! is_string($valor)) return '';
        return mb_strtolower(preg_replace('/[\s\x{FEFF}]+/u', ' ', Str::trim($valor)) ?? '');
    }

    public static function cuenta(?User $usuario): array
    {
        if (! $usuario) return [];

        return array_filter(['nombre' => $usuario->name, 'correo' => $usuario->email],
            fn ($valor) => is_string($valor) && trim($valor) !== '');
    }

    public static function reglas(bool $borrador, ?string $tramite = null): array
    {
        $presencia = $borrador ? 'nullable' : 'required';
        $reglas = [];
        foreach (self::PERSONALES as $campo => [, $limite]) {
            $reglas[$campo] = [$campo === 'cedula' && $tramite !== 'homologacion' ? 'nullable' : $presencia, 'string', 'max:'.$limite];
        }
        $reglas['correo'][] = 'email';
        foreach (self::SOLO_DIGITOS as $campo) {
            $reglas[$campo][] = 'regex:/\A[0-9]+\z/';
        }
        // El id identifica una fila del formulario; nunca es el id de un curso del SIAC.
        $reglas['materias.*.id'] = ['required', 'string', 'max:80', 'regex:/\A[a-zA-Z0-9_-]+\z/', 'distinct'];
        $reglas['materias.*'] = ['array:id,codigo,nombre,periodo,paralelo,docente,datos'];
        foreach (self::MATERIA as $campo => [, $limite]) {
            $reglas['materias.*.'.$campo] = [$presencia, 'string', 'max:'.$limite];
        }

        return $reglas;
    }

    public static function mensajes(): array
    {
        $mensajes = [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe enviarse como texto.',
            'min' => [
                'numeric' => 'El campo :attribute debe ser mayor o igual que :min.',
                'array' => 'El campo :attribute debe tener al menos :min elementos.',
            ],
            'max' => [
                'string' => 'El campo :attribute no debe superar :max caracteres.',
                'numeric' => 'El campo :attribute debe ser menor o igual que :max.',
                'array' => 'El campo :attribute admite como máximo :max elementos.',
            ],
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'date_format' => 'El campo :attribute debe tener el formato :format.',
            'before_or_equal' => 'El campo :attribute no puede tener una fecha futura.',
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'facultad.in' => 'Selecciona una facultad de la lista.',
            'carrera.in' => 'Selecciona una carrera que pertenezca a la facultad elegida.',
            'materias.*.id.distinct' => 'Hay una materia repetida en la solicitud.',
        ];
        foreach (self::SOLO_DIGITOS as $campo) {
            [$etiqueta, $limite] = self::PERSONALES[$campo];
            $mensajes[$campo.'.regex'] = $etiqueta.': ingresa únicamente dígitos del 0 al 9.';
            $mensajes[$campo.'.max'] = $etiqueta.': se admiten como máximo '.$limite.' dígitos.';
        }

        return $mensajes;
    }

    /** Una fila nueva no permite repetir la misma materia, periodo y paralelo. */
    public static function validarMateriasUnicas(Validator $validador): void
    {
        $materias = $validador->getData()['materias'] ?? [];
        if (! is_array($materias)) return;

        $vistas = [];
        foreach ($materias as $indice => $materia) {
            if (! is_array($materia)) continue;
            $partes = [];
            foreach (['codigo', 'periodo', 'paralelo'] as $campo) {
                $valor = $materia[$campo] ?? null;
                // Los borradores pueden contener filas todavía incompletas.
                if (! is_string($valor)) continue 2;
                $normalizado = preg_replace('/[\s\x{FEFF}]+/u', ' ', $valor);
                if ($normalizado === null || trim($normalizado) === '') continue 2;
                $partes[] = mb_strtolower(trim($normalizado));
            }
            $clave = json_encode($partes, JSON_THROW_ON_ERROR);
            if (isset($vistas[$clave])) {
                $validador->errors()->add('materias.'.$indice.'.codigo',
                    'Esta materia ya está agregada con el mismo periodo y paralelo.');
            }
            $vistas[$clave] = true;
        }
    }
}
