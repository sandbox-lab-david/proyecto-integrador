<?php

namespace App\Services\Academico\Importadores;

use App\Models\Facultad;
use App\Models\Rol;
use App\Models\Trabajador;
use Illuminate\Support\Facades\DB;

class Docentes extends Importador
{
    public function titulo(): string
    {
        return 'Docentes';
    }

    public function columnas(): array
    {
        return [
            'codigo' => true,
            'nombres' => true,
            'apellidos' => true,
            'correo' => true,
            'facultad_codigo' => false,
        ];
    }

    protected function reglas(): array
    {
        return [
            'codigo' => 'required|max:20',
            'nombres' => 'required|max:255',
            'apellidos' => 'required|max:255',
            'correo' => 'required|email|ends_with:@uees.edu.ec|max:255',
            'facultad_codigo' => 'nullable',
        ];
    }

    public function clave(array $fila): string
    {
        return mb_strtoupper($fila['codigo']);
    }

    public function existe(array $fila): bool
    {
        return Trabajador::where('codigo', $fila['codigo'])->exists();
    }

    protected function validarRelaciones(array $fila): array
    {
        $errores = [];

        if ($fila['facultad_codigo'] && ! Facultad::where('codigo', $fila['facultad_codigo'])->exists()) {
            $errores[] = "No existe la facultad con código {$fila['facultad_codigo']}.";
        }

        if (Trabajador::where('correo', mb_strtolower($fila['correo']))->where('codigo', '!=', $fila['codigo'])->exists()) {
            $errores[] = "El correo {$fila['correo']} ya pertenece a otra persona del personal.";
        }

        return $errores;
    }

    public function guardar(array $fila): void
    {
        $docente = Trabajador::firstOrNew(['codigo' => $fila['codigo']], ['activo' => true]);
        $docente->fill([
            'nombres' => $fila['nombres'],
            'apellidos' => $fila['apellidos'],
            'correo' => mb_strtolower($fila['correo']),
        ])->save();

        if (! Trabajador::conRol('docente')->whereKey($docente->id)->exists()) {
            DB::table('rol_trabajador')->insert([
                'trabajador_id' => $docente->id,
                'rol' => 'docente',
                'rol_id' => Rol::where('codigo', 'docente')->value('id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($fila['facultad_codigo']) {
            $docente->facultades()->syncWithoutDetaching([Facultad::where('codigo', $fila['facultad_codigo'])->value('id')]);
        }
    }
}
