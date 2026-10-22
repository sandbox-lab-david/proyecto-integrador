<?php

namespace App\Services\Academico\Importadores;

use App\Models\CategoriaMateria;
use App\Models\Materia;

class Materias extends Importador
{
    public function titulo(): string
    {
        return 'Materias';
    }

    public function columnas(): array
    {
        return [
            'codigo' => true,
            'nombre' => true,
            'creditos' => true,
            'categoria' => false,
        ];
    }

    protected function reglas(): array
    {
        return [
            'codigo' => 'required|max:20',
            'nombre' => 'required|max:255',
            'creditos' => 'required|integer|min:0|max:20',
            'categoria' => 'nullable|max:100',
        ];
    }

    public function clave(array $fila): string
    {
        return mb_strtoupper($fila['codigo']);
    }

    public function existe(array $fila): bool
    {
        return Materia::where('codigo', $fila['codigo'])->exists();
    }

    protected function validarRelaciones(array $fila): array
    {
        if ($fila['categoria'] && ! $this->categoria($fila['categoria'])) {
            return ["No existe la categoría {$fila['categoria']}."];
        }

        return [];
    }

    public function guardar(array $fila): void
    {
        $materia = Materia::firstOrNew(['codigo' => $fila['codigo']], ['activo' => true]);
        $materia->fill([
            'nombre' => $fila['nombre'],
            'creditos' => (int) $fila['creditos'],
            'categoria_materia_id' => $fila['categoria'] ? $this->categoria($fila['categoria'])->id : $materia->categoria_materia_id,
        ])->save();
    }

    private function categoria(string $nombre): ?CategoriaMateria
    {
        return CategoriaMateria::all()->first(fn ($c) => $this->normalizar($c->nombre) === $this->normalizar($nombre));
    }
}
