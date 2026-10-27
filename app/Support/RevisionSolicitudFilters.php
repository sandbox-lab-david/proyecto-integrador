<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RevisionSolicitudFilters
{
    public const ESTADOS = [
        'Enviado por estudiante',
        'Revisado',
        'Apto para consejo',
        'Agendado',
        'Aprobado para ejecución',
        'Ejecutado',
        'Observado',
        'Rechazado',
    ];

    public static function validate(Request $request): array
    {
        $tramites = \Illuminate\Support\Facades\DB::table('solicitudes_revision')
            ->whereNotNull('tramite')->distinct()->pluck('tramite')->all();

        $validated = $request->validate([
            'filtrar' => ['nullable', 'boolean'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255'],
            'carrera' => ['nullable', 'string', 'max:255'],
            'fecha' => ['nullable', 'date_format:d/m/Y'],
            'tramite' => ['nullable', Rule::in($tramites)],
            'estado' => ['nullable', Rule::in(self::ESTADOS)],
        ]);

        foreach (['nombre', 'codigo', 'carrera', 'fecha', 'tramite', 'estado'] as $key) {
            $validated[$key] = trim((string) ($validated[$key] ?? ''));
        }

        $validated['filtrar'] = $request->boolean('filtrar');

        return $validated;
    }

    public static function validateWithDateRange(Request $request): array
    {
        $tramites = \Illuminate\Support\Facades\DB::table('solicitudes_revision')
            ->whereNotNull('tramite')->distinct()->pluck('tramite')->all();

        $validated = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255'],
            'carrera' => ['nullable', 'string', 'max:255'],
            'tramite' => ['nullable', Rule::in($tramites)],
            'estado' => ['nullable', Rule::in(self::ESTADOS)],
        ]);

        foreach (['desde', 'hasta', 'nombre', 'codigo', 'carrera', 'tramite', 'estado'] as $key) {
            $validated[$key] = trim((string) ($validated[$key] ?? ''));
        }

        return $validated;
    }

    public static function apply(Builder $query, array $filters, string $alias = ''): Builder
    {
        self::applyMetadata($query, $filters, $alias);

        $column = fn (string $name): string => $alias === '' ? $name : $alias.'.'.$name;
        if ($filters['fecha'] !== '') {
            $date = Carbon::createFromFormat('!d/m/Y', $filters['fecha'])->format('Y-m-d');
            $query->whereDate($column('created_at'), $date);
        }

        return $query;
    }

    public static function applyMetadata(Builder $query, array $filters, string $alias = ''): Builder
    {
        $column = fn (string $name): string => $alias === '' ? $name : $alias.'.'.$name;
        $search = function (string $field, string $value) use ($query, $column): void {
            $columnName = $column($field);
            $normalizedColumn = "translate(lower({$columnName}), 'áéíóúüñ', 'aeiouun')";
            $normalizedValue = Str::lower(Str::ascii($value));
            $query->whereRaw($normalizedColumn.' LIKE ?', ['%'.$normalizedValue.'%']);
        };

        if (($filters['nombre'] ?? '') !== '') {
            $search('estudiante', $filters['nombre']);
        }
        if (($filters['codigo'] ?? '') !== '') {
            $search('codigo_siac', $filters['codigo']);
        }
        if (($filters['carrera'] ?? '') !== '') {
            $search('carrera', $filters['carrera']);
        }
        if (($filters['tramite'] ?? '') !== '') {
            $search('tramite', $filters['tramite']);
        }
        if (($filters['estado'] ?? '') !== '') {
            $search('estado', $filters['estado']);
        }

        return $query;
    }

    public static function hasMetadata(array $filters): bool
    {
        foreach (['nombre', 'codigo', 'carrera', 'tramite', 'estado'] as $key) {
            if (($filters[$key] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }

    public static function hasAny(array $filters): bool
    {
        foreach (['nombre', 'codigo', 'carrera', 'fecha', 'tramite', 'estado'] as $key) {
            if ($filters[$key] !== '') {
                return true;
            }
        }

        return false;
    }
}
