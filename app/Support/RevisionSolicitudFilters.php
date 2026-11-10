<?php

namespace App\Support;

use App\Models\TipoTramite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class RevisionSolicitudFilters
{
    public const ESTADOS = ['Enviado por estudiante', 'Revisado', 'Observado', 'Apto para consejo', 'Agendado', 'Aprobado para ejecución', 'Ejecutado', 'Rechazado'];

    public const ESTADOS_ASISTENTE = ['Enviado por estudiante'];

    public const ESTADOS_DIRECTOR = ['Revisado'];

    public const ESTADOS_AGENDAS = ['Apto para consejo'];

    public const ESTADOS_RESOLUCIONES = ['Agendado', 'Aprobado para ejecución', 'Ejecutado', 'Rechazado'];

    public static function validate(Request $request, ?array $estadosDisponibles = null): array
    {
        $validated = $request->validate([
            'filtrar' => ['nullable', 'boolean'], 'nombre' => ['nullable', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255'], 'carrera' => ['nullable', 'string', 'max:255'],
            'desde' => ['nullable', 'date_format:d/m/Y'],
            'hasta' => ['nullable', 'date_format:d/m/Y', function ($attribute, $value, $fail) use ($request) {
                $desde = $request->input('desde');
                if (filled($value) && filled($desde)) {
                    try {
                        if (Carbon::createFromFormat('!d/m/Y', $value)->lt(Carbon::createFromFormat('!d/m/Y', $desde))) {
                            $fail('La fecha Hasta debe ser igual o posterior a Desde.');
                        }
                    } catch (\Throwable) {
                        // La regla date_format informa las fechas mal escritas.
                    }
                }
            }],
            'tramite' => ['nullable', Rule::exists('tipos_tramite', 'nombre')],
            'estado' => ['nullable', Rule::in($estadosDisponibles ?? self::ESTADOS)],
        ]);
        foreach (['nombre', 'codigo', 'carrera', 'desde', 'hasta', 'tramite', 'estado'] as $key) {
            $validated[$key] = trim((string) ($validated[$key] ?? ''));
        }
        $validated['filtrar'] = $request->boolean('filtrar');

        return $validated;
    }

    public static function validateWithDateRange(Request $request): array
    {
        return self::validate($request);
    }

    public static function apply(Builder $query, array $filters): Builder
    {
        self::applyMetadata($query, $filters);

        $column = \Illuminate\Support\Facades\DB::raw('COALESCE(solicitudes.enviada_at, solicitudes.created_at)');
        if (($filters['desde'] ?? '') !== '') {
            $query->whereDate($column, '>=', Carbon::createFromFormat('!d/m/Y', $filters['desde'])->toDateString());
        }
        if (($filters['hasta'] ?? '') !== '') {
            $query->whereDate($column, '<=', Carbon::createFromFormat('!d/m/Y', $filters['hasta'])->toDateString());
        }

        return $query;
    }

    public static function applyMetadata(Builder $query, array $filters): Builder
    {
        if (($filters['nombre'] ?? '') !== '' || ($filters['codigo'] ?? '') !== '') {
            $query->whereHas('carreraEstudiante.estudiante', function (Builder $student) use ($filters) {
                foreach (['nombre' => ['nombres', 'apellidos'], 'codigo' => ['codigo', 'cedula']] as $filter => $columns) {
                    if (($filters[$filter] ?? '') === '') {
                        continue;
                    }
                    $value = self::fold($filters[$filter]);
                    $student->where(function (Builder $match) use ($columns, $value) {
                        foreach ($columns as $index => $column) {
                            $match->{$index ? 'orWhereRaw' : 'whereRaw'}(self::normalized($column).' LIKE ?', ['%'.$value.'%']);
                        }
                    });
                }
            });
        }
        if (($filters['carrera'] ?? '') !== '') {
            $value = self::fold($filters['carrera']);
            $query->whereHas('carreraEstudiante.carreraModalidad.carrera', fn (Builder $c) => $c->whereRaw(self::normalized('nombre').' LIKE ?', ['%'.$value.'%']));
        }
        if (($filters['tramite'] ?? '') !== '') {
            $query->whereHas('tipoTramite', fn (Builder $t) => $t->where(fn (Builder $match) => $match->where('nombre', $filters['tramite'])->orWhere('codigo', $filters['tramite'])));
        }
        if (($filters['estado'] ?? '') !== '') {
            $code = Estados::codigoDesdeEtiqueta($filters['estado']);
            $query->enEstado($code);
        }
        return $query;
    }

    public static function tramitesDisponibles()
    {
        return TipoTramite::query()->orderBy('nombre')->pluck('nombre');
    }

    public static function hasMetadata(array $filters): bool
    {
        foreach (['nombre', 'codigo', 'carrera', 'tramite', 'estado'] as $k) {
            if (($filters[$k] ?? '') !== '') {
                return true;
            }
        }

return false;
    }

    public static function hasAny(array $filters): bool
    {
        foreach (['nombre', 'codigo', 'carrera', 'desde', 'hasta', 'tramite', 'estado'] as $k) {
            if (($filters[$k] ?? '') !== '') {
                return true;
            }
        }

return false;
    }

    private static function fold(string $value): string
    {
        return mb_strtolower(strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n']));
    }

    private static function normalized(string $column): string
    {
        $expression = 'LOWER('.$column.')';
        foreach (['á' => 'a', 'à' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ë' => 'e', 'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'ñ' => 'n'] as $from => $to) {
            $expression = "REPLACE($expression, '$from', '$to')";
        }

        return $expression;
    }
}
