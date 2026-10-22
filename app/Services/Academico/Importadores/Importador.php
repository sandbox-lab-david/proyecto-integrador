<?php

namespace App\Services\Academico\Importadores;

use Illuminate\Support\Facades\Validator;

abstract class Importador
{
    abstract public function titulo(): string;

    abstract public function columnas(): array;

    abstract protected function reglas(): array;

    abstract public function clave(array $fila): string;

    abstract public function existe(array $fila): bool;

    abstract public function guardar(array $fila): void;

    public function obligatorias(): array
    {
        return array_keys(array_filter($this->columnas()));
    }

    public function validar(array $fila): array
    {
        $errores = Validator::make($fila, $this->reglas(), $this->mensajes())->errors()->all();

        return $errores ?: $this->validarRelaciones($fila);
    }

    protected function validarRelaciones(array $fila): array
    {
        return [];
    }

    protected function mensajes(): array
    {
        return [
            'required' => 'Falta :attribute.',
            'email' => ':attribute no es un correo válido.',
            'ends_with' => ':attribute debe terminar en @uees.edu.ec.',
            'integer' => ':attribute debe ser un número entero.',
            'size' => ':attribute debe tener :size caracteres.',
            'max' => ':attribute es demasiado largo o grande.',
            'min' => ':attribute es demasiado pequeño.',
            'in' => ':attribute tiene un valor no permitido.',
        ];
    }

    protected function siNo(?string $valor, bool $porDefecto): bool
    {
        if ($valor === null) {
            return $porDefecto;
        }

        return in_array($this->normalizar($valor), ['si', 's', '1', 'true', 'x'], true);
    }

    protected function normalizar(?string $texto): string
    {
        $texto = mb_strtolower(trim((string) $texto));

        return strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }

    protected function lista(?string $valor): array
    {
        return collect(preg_split('/[;,|]/', (string) $valor))->map(fn ($v) => trim($v))->filter()->values()->all();
    }
}
