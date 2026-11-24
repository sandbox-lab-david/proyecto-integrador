<?php

namespace App\Services\Catalogo;

use App\Models\CategoriaTramite;
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
                // Las revisiones v3 anteriores no incluían las categorías.
                return $revision + ['categorias' => (new NormalizadorCatalogo)->categoriasIniciales()];
            }
        }

        return $this->inicial();
    }

    public function inicial(): array
    {
        return json_decode(file_get_contents(database_path('catalogo/inicial.json')), true, 512, JSON_THROW_ON_ERROR)
            + ['categorias' => (new NormalizadorCatalogo)->categoriasIniciales()];
    }

    public function borrador(): array
    {
        $tramites = TipoTramite::with(['categoria', 'requisitos', 'campos', 'umbrales', 'documentos' => fn ($q) => $q->orderBy('id')])
            ->orderBy('codigo')->get()->map(fn ($tipo) => $this->contenidoBorrador($tipo))->all();
        if (count($tramites) !== 20) {
            throw new \RuntimeException('El catálogo debe contener los 20 trámites revisados.');
        }

        $categorias = CategoriaTramite::orderBy('orden')->orderBy('id')->get()
            ->map(fn ($c) => ['id' => $c->codigo, 'nombre' => $c->nombre, 'descripcion' => $c->descripcion])->all();
        foreach ($tramites as $tramite) {
            if (! in_array($tramite['categoria_id'] ?? null, array_column($categorias, 'id'), true)) {
                throw new \RuntimeException('El trámite debe tener una categoría registrada.');
            }
        }

        $datos = ['tramites' => $tramites, 'categorias' => $categorias];

        return ['version' => 'catalogo-'.substr(hash('sha256', json_encode($datos, JSON_THROW_ON_ERROR)), 0, 16)] + $datos;
    }

    /** Mantiene el contrato de los consumidores y permite recuperar el JSON al revertir. */
    public function contenidoBorrador(TipoTramite $tipo): array
    {
        $contenido = $tipo->contenido;
        if ($tipo->catalogo_normalizado) {
            if (! $tipo->categoria) {
                throw new \RuntimeException('El trámite normalizado debe tener una categoría.');
            }
            $contenido['categoria_id'] = $tipo->categoria->codigo;
            $contenido['requisitos'] = $tipo->requisitos->pluck('texto')->all();
            $contenido['parametros'] = $tipo->umbrales->mapWithKeys(fn ($u) => [$u->codigo => $u->valor])->all();
            foreach (['campos' => 'general', 'campos_materia' => 'materia'] as $clave => $ambito) {
                $contenido['formulario'][$clave] = $tipo->campos->where('ambito', $ambito)->values()
                    ->map(fn ($campo) => $campo->definicion())->all();
            }
        }
        $tipo->loadMissing('documentos');
        $contenido['documentos'] = $tipo->documentos->sortBy('id')->values()->map(fn ($d) => array_replace($d->contenido, [
            'id' => $d->codigo, 'nombre' => $d->nombre, 'obligacion' => $d->obligacion, 'obligatorio' => $d->obligacion === 'obligatorio',
        ]))->all();
        $contenido['formulario']['documentos'] = array_map(fn ($d) => array_intersect_key($d, array_flip(['id', 'nombre', 'obligatorio'])), $contenido['documentos']);

        return array_replace($contenido, ['id' => $tipo->codigo, 'nombre' => $tipo->nombre, 'slug' => $tipo->slug]);
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
