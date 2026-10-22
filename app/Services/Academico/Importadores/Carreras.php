<?php

namespace App\Services\Academico\Importadores;

use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\Modalidad;

class Carreras extends Importador
{
    public function titulo(): string
    {
        return 'Carreras';
    }

    public function columnas(): array
    {
        return [
            'facultad_codigo' => true,
            'codigo' => true,
            'nombre' => true,
            'nivel' => false,
            'admite_tercer_registro' => false,
            'modalidades' => true,
        ];
    }

    protected function reglas(): array
    {
        return [
            'facultad_codigo' => 'required',
            'codigo' => 'required|max:20',
            'nombre' => 'required|max:255',
            'nivel' => 'nullable|in:grado,posgrado,Grado,Posgrado',
            'admite_tercer_registro' => 'nullable|max:5',
            'modalidades' => 'required',
        ];
    }

    public function clave(array $fila): string
    {
        return mb_strtoupper($fila['codigo']);
    }

    public function existe(array $fila): bool
    {
        return Carrera::where('codigo', $fila['codigo'])->exists();
    }

    protected function validarRelaciones(array $fila): array
    {
        $errores = [];

        if (! Facultad::where('codigo', $fila['facultad_codigo'])->exists()) {
            $errores[] = "No existe la facultad con código {$fila['facultad_codigo']}.";
        }

        $conocidas = Modalidad::pluck('nombre')->map(fn ($n) => $this->normalizar($n));
        foreach ($this->lista($fila['modalidades']) as $modalidad) {
            if (! $conocidas->contains($this->normalizar($modalidad))) {
                $errores[] = "No existe la modalidad {$modalidad}.";
            }
        }

        return $errores;
    }

    public function guardar(array $fila): void
    {
        $carrera = Carrera::firstOrNew(['codigo' => $fila['codigo']], ['activo' => true]);
        $carrera->fill([
            'facultad_id' => Facultad::where('codigo', $fila['facultad_codigo'])->value('id'),
            'nombre' => $fila['nombre'],
            'nivel' => mb_strtolower($fila['nivel'] ?? $carrera->nivel ?? 'grado'),
            'admite_tercer_registro' => $this->siNo($fila['admite_tercer_registro'], $carrera->exists ? (bool) $carrera->admite_tercer_registro : true),
        ])->save();

        $buscadas = collect($this->lista($fila['modalidades']))->map(fn ($m) => $this->normalizar($m));
        $ids = Modalidad::all()->filter(fn ($m) => $buscadas->contains($this->normalizar($m->nombre)))->pluck('id');
        $nuevas = $ids->diff($carrera->modalidades()->pluck('modalidades.id'));

        $carrera->modalidades()->attach($nuevas->mapWithKeys(fn ($id) => [$id => ['created_at' => now(), 'updated_at' => now()]])->all());
    }
}
