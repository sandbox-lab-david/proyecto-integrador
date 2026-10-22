<?php

namespace App\Services\Academico\Importadores;

use App\Models\Curso;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Trabajador;

class Paralelos extends Importador
{
    public function titulo(): string
    {
        return 'Paralelos';
    }

    public function columnas(): array
    {
        return [
            'materia_codigo' => true,
            'periodo_codigo' => true,
            'paralelo' => true,
            'docente_codigo' => false,
            'cupo' => false,
        ];
    }

    protected function reglas(): array
    {
        return [
            'materia_codigo' => 'required',
            'periodo_codigo' => 'required',
            'paralelo' => 'required|max:10',
            'docente_codigo' => 'nullable',
            'cupo' => 'nullable|integer|min:1|max:200',
        ];
    }

    public function clave(array $fila): string
    {
        return mb_strtoupper("{$fila['materia_codigo']}|{$fila['periodo_codigo']}|{$fila['paralelo']}");
    }

    public function existe(array $fila): bool
    {
        return (bool) $this->curso($fila);
    }

    protected function validarRelaciones(array $fila): array
    {
        $errores = [];

        if (! Materia::where('codigo', $fila['materia_codigo'])->exists()) {
            $errores[] = "No existe la materia con código {$fila['materia_codigo']}.";
        }

        if (! Periodo::where('codigo', $fila['periodo_codigo'])->exists()) {
            $errores[] = "No existe el periodo con código {$fila['periodo_codigo']}.";
        }

        if ($fila['docente_codigo'] && ! Trabajador::where('codigo', $fila['docente_codigo'])->exists()) {
            $errores[] = "No existe el docente con código {$fila['docente_codigo']}. Impórtalo primero.";
        }

        return $errores;
    }

    public function guardar(array $fila): void
    {
        $curso = $this->curso($fila) ?? new Curso([
            'materia_id' => Materia::where('codigo', $fila['materia_codigo'])->value('id'),
            'periodo_id' => Periodo::where('codigo', $fila['periodo_codigo'])->value('id'),
            'paralelo' => $fila['paralelo'],
        ]);

        $curso->fill([
            'profesor_id' => $fila['docente_codigo'] ? Trabajador::where('codigo', $fila['docente_codigo'])->value('id') : null,
            'cupo' => $fila['cupo'] !== null ? (int) $fila['cupo'] : null,
        ])->save();
    }

    private function curso(array $fila): ?Curso
    {
        return Curso::where('paralelo', $fila['paralelo'])
            ->whereHas('materia', fn ($q) => $q->where('codigo', $fila['materia_codigo']))
            ->whereHas('periodo', fn ($q) => $q->where('codigo', $fila['periodo_codigo']))
            ->first();
    }
}
