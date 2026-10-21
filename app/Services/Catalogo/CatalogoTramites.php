<?php

namespace App\Services\Catalogo;

use App\Models\RevisionCatalogo;
use App\Models\TipoTramite;
use Illuminate\Support\Facades\Schema;

/** Los borradores en BD solo se hacen públicos tras validar y publicar una revisión. */
class CatalogoTramites
{
    public function publicado(): array
    {
        if (Schema::hasTable('revisiones_catalogo')) {
            $activas = RevisionCatalogo::where('activa', true)->get();
            $revision = $activas->isEmpty() ? null : $activas->sole()->contenido;
            if ($revision !== null) {
                return $revision;
            }
        }

        return $this->inicial();
    }

    public function inicial(): array
    {
        return json_decode(file_get_contents(database_path('catalogo/inicial.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function borrador(): array
    {
        $tramites = TipoTramite::orderBy('codigo')->get()->map(function ($tipo) {
            $contenido = $tipo->contenido;
            $contenido['documentos'] = $tipo->documentos()->orderBy('id')->get()->map(fn ($d) => array_replace($d->contenido, ['id' => $d->codigo, 'nombre' => $d->nombre, 'obligacion' => $d->obligacion, 'obligatorio' => $d->obligacion === 'obligatorio']))->all();
            $contenido['formulario']['documentos'] = array_map(fn ($d) => array_intersect_key($d, array_flip(['id', 'nombre', 'obligatorio'])), $contenido['documentos']);

            return array_replace($contenido, ['id' => $tipo->codigo, 'nombre' => $tipo->nombre, 'slug' => $tipo->slug]);
        })->all();
        if (count($tramites) !== 20) {
            throw new \RuntimeException('El catálogo debe contener los 20 trámites revisados.');
        }

        return ['version' => 'catalogo-'.substr(hash('sha256', json_encode($tramites, JSON_THROW_ON_ERROR)), 0, 16), 'tramites' => $tramites];
    }

    public function buscar(string $codigo): ?array
    {
        $alias = ['recuperacion' => 'examen_recuperacion', 'gracia' => 'examen_gracia', 'supletorio' => 'examen_supletorio', 'suficiencia' => 'examen_suficiencia', 'ayudante' => 'ayudante_catedra', 'recalificacion-examen' => 'recalificacion', 'reincorporacion' => 'reincorporacion_carrera'];
        $codigo = $alias[$codigo] ?? $codigo;
        foreach ($this->publicado()['tramites'] as $t) {
            if ($t['id'] === $codigo || $t['slug'] === $codigo) {
                return $t;
            }
        }

        return null;
    }
}
