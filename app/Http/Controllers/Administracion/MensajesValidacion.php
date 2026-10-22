<?php

namespace App\Http\Controllers\Administracion;

trait MensajesValidacion
{
    protected function mensajes(array $propios = []): array
    {
        return array_merge([
            'required' => 'Este campo es obligatorio.',
            'unique' => 'Ya existe un registro con este valor.',
            'integer' => 'Escribe un número entero.',
            'exists' => 'Elige una opción válida.',
            'in' => 'Elige una opción válida.',
            'date' => 'Escribe una fecha válida.',
            'after' => 'La fecha de fin debe ser posterior a la de inicio.',
            'email' => 'Escribe un correo válido.',
            'array' => 'Elige una opción válida.',
            'min' => 'El valor es demasiado pequeño.',
            'max' => 'El valor es demasiado largo o grande.',
        ], $propios);
    }

    protected function buscar($consulta, ?string $texto, array $columnas)
    {
        $texto = mb_strtolower(trim((string) $texto));

        if ($texto === '') {
            return $consulta;
        }

        return $consulta->where(function ($q) use ($texto, $columnas) {
            foreach ($columnas as $columna) {
                $q->orWhereRaw("LOWER($columna) LIKE ?", ["%{$texto}%"]);
            }
        });
    }
}
