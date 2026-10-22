<?php

namespace App\Services\Academico\Importadores;

use App\Models\Carrera;
use App\Models\Malla;
use App\Models\Materia;
use Illuminate\Support\Facades\DB;

class Mallas extends Importador
{
    public function titulo(): string
    {
        return 'Mallas';
    }

    public function columnas(): array
    {
        return [
            'carrera_codigo' => true,
            'version' => true,
            'vigente' => false,
            'materia_codigo' => true,
            'nivel' => true,
        ];
    }

    protected function reglas(): array
    {
        return [
            'carrera_codigo' => 'required',
            'version' => 'required|max:20',
            'vigente' => 'nullable|max:5',
            'materia_codigo' => 'required',
            'nivel' => 'required|integer|min:1|max:20',
        ];
    }

    public function clave(array $fila): string
    {
        return mb_strtoupper("{$fila['carrera_codigo']}|{$fila['version']}|{$fila['materia_codigo']}");
    }

    public function existe(array $fila): bool
    {
        $malla = $this->malla($fila);

        return $malla && $malla->materias()->where('materias.codigo', $fila['materia_codigo'])->exists();
    }

    protected function validarRelaciones(array $fila): array
    {
        $errores = [];

        if (! Carrera::where('codigo', $fila['carrera_codigo'])->exists()) {
            $errores[] = "No existe la carrera con código {$fila['carrera_codigo']}.";
        }

        if (! Materia::where('codigo', $fila['materia_codigo'])->exists()) {
            $errores[] = "No existe la materia con código {$fila['materia_codigo']}. Impórtala primero.";
        }

        return $errores;
    }

    public function guardar(array $fila): void
    {
        $carrera = Carrera::where('codigo', $fila['carrera_codigo'])->first();
        $malla = Malla::firstOrCreate(['carrera_id' => $carrera->id, 'version' => $fila['version']], ['vigente' => false]);

        if ($this->siNo($fila['vigente'], false) && ! $malla->vigente) {
            Malla::where('carrera_id', $carrera->id)->update(['vigente' => false]);
            $malla->update(['vigente' => true]);
        }

        $materiaId = Materia::where('codigo', $fila['materia_codigo'])->value('id');
        $actual = DB::table('malla_materia')->where('malla_id', $malla->id)->where('materia_id', $materiaId);

        if ($actual->exists()) {
            $actual->update(['nivel' => (int) $fila['nivel'], 'updated_at' => now()]);
        } else {
            $malla->materias()->attach($materiaId, ['nivel' => (int) $fila['nivel']]);
        }
    }

    private function malla(array $fila): ?Malla
    {
        return Malla::where('version', $fila['version'])
            ->whereHas('carrera', fn ($q) => $q->where('codigo', $fila['carrera_codigo']))
            ->first();
    }
}
