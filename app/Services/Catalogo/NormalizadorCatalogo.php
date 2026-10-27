<?php

namespace App\Services\Catalogo;

use App\Models\CategoriaTramite;
use App\Models\TipoTramite;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Importa una sola vez el borrador existente; nunca repone relaciones eliminadas. */
class NormalizadorCatalogo
{
    public function categoriasIniciales(): array
    {
        return json_decode(file_get_contents(database_path('catalogo/categorias.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function prepararCategorias(): void
    {
        foreach ($this->categoriasIniciales() as $orden => $categoria) {
            CategoriaTramite::firstOrCreate(['codigo' => $categoria['id']], [
                'nombre' => $categoria['nombre'], 'descripcion' => $categoria['descripcion'], 'orden' => $orden,
            ]);
        }
    }

    public function importar(TipoTramite $tipo): void
    {
        DB::transaction(function () use ($tipo) {
            $tipo = TipoTramite::lockForUpdate()->findOrFail($tipo->id);
            if ($tipo->catalogo_normalizado) {
                return;
            }
            $contenido = $tipo->contenido;
            if (! isset($contenido['categoria_id'], $contenido['requisitos'], $contenido['formulario'], $contenido['parametros'])) {
                throw new InvalidArgumentException("El trámite {$tipo->codigo} no tiene un borrador completo para normalizar.");
            }
            $categoria = CategoriaTramite::firstOrCreate(['codigo' => $contenido['categoria_id']], [
                'nombre' => $contenido['categoria_id'], 'descripcion' => '', 'orden' => CategoriaTramite::count(),
            ]);
            foreach ($contenido['requisitos'] as $orden => $texto) {
                $tipo->requisitos()->create(['texto' => $texto, 'orden' => $orden]);
            }
            foreach (['campos' => 'general', 'campos_materia' => 'materia'] as $clave => $ambito) {
                foreach ($contenido['formulario'][$clave] ?? [] as $orden => $campo) {
                    $tipo->campos()->create([
                        'ambito' => $ambito, 'nombre' => $campo['nombre'], 'etiqueta' => $campo['etiqueta'],
                        'tipo' => $campo['tipo'], 'obligatorio' => $campo['obligatorio'], 'orden' => $orden,
                        'configuracion' => array_diff_key($campo, array_flip(['nombre', 'etiqueta', 'tipo', 'obligatorio'])),
                    ]);
                }
            }
            $orden = 0;
            foreach ($contenido['parametros'] as $codigo => $valor) {
                $tipo->umbrales()->create(['codigo' => $codigo, 'valor' => $valor, 'orden' => $orden++]);
            }
            $tipo->update(['categoria_tramite_id' => $categoria->id, 'catalogo_normalizado' => true]);
        });
    }
}
