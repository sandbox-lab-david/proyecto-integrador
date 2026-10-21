<?php

namespace App\Services\Catalogo;

use App\Models\RevisionCatalogo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Process\Process;

class ExportadorProlog
{
    public function generar(array $catalogo): string
    {
        $texto = fn ($s) => json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $atom = function ($s) {
            if (! is_string($s) || ! preg_match('/^[a-z][a-z0-9_]*$/D', $s)) {
                throw new RuntimeException('Identificador Prolog inválido.');
            }

return $s;
        };
        $salida = ":- encoding(utf8).\n% Generado desde el catálogo; no contiene datos personales.\n";
        foreach (['tramite/2', 'descripcion/2', 'requisito/3', 'documento_def/5', 'tramite_documento/4', 'costo/3', 'costo_texto/2', 'plazo_texto/2', 'parametro/3', 'pendiente_validacion/2', 'observacion_conocimiento/2'] as $pred) {
            $salida .= ":- discontiguous {$pred}.\n:- multifile {$pred}.\n";
        }
        $salida .= 'kb_version('.$texto($catalogo['version']).").\n";
        $ids = [];
        foreach ($catalogo['tramites'] as $t) {
            $id = $atom($t['id']);
            if (isset($ids[$id])) {
                throw new RuntimeException('Trámite duplicado.');
            }
            $ids[$id] = true;
            $docs = [];
            $salida .= 'tramite('.$id.', '.$texto($t['nombre']).").\n";
            $salida .= 'descripcion('.$id.', '.$texto($t['descripcion']).").\n";
            foreach ($t['requisitos'] as $i => $r) {
                $salida .= 'requisito('.$id.', '.($i + 1).', '.$texto($r).").\n";
            }
            foreach ($t['documentos'] as $i => $d) {
                if (! in_array($d['obligacion'], ['obligatorio', 'opcional', 'condicional'], true)) {
                    throw new RuntimeException('Obligación de documento inválida.');
                }
                if ($d['obligatorio'] !== ($d['obligacion'] === 'obligatorio')) {
                    throw new RuntimeException('Obligación contradictoria.');
                }
                $doc = $id.'_'.str_replace('-', '_', $d['id']);
                $atom($doc);
                if (isset($docs[$doc])) {
                    throw new RuntimeException('Identificador documental duplicado.');
                }
                $docs[$doc] = true;
                $salida .= 'documento_def('.$doc.', '.$texto($d['nombre']).', '.$texto($d['descripcion']).', '.$texto($d['formato']).', '.($d['url'] === null ? 'sin_url' : $texto($d['url'])).").\n";
                $salida .= 'tramite_documento('.$id.', '.($i + 1).', '.$doc.', '.$d['obligacion'].").\n";
            }
            foreach (['costo_texto', 'plazo_texto'] as $k) {
                $salida .= $k.'('.$id.', '.$texto($t[$k]).").\n";
            }
            foreach ($t['parametros'] as $k => $v) {
                if (! is_numeric($v) || $v < 0) {
                    throw new RuntimeException('Parámetro inválido.');
                }
                $salida .= 'parametro('.$id.', '.$atom($k).', '.(float) $v.").\n";
            }
        }

        return $salida;
    }

    public function validar(string $ruta): void
    {
        $script = base_path('prolog/pruebas/validar_catalogo.pl');
        $proceso = new Process(['swipl', '-q', '-s', $script, '--', $ruta, base_path('prolog/kb/reglas.pl')]);
        $proceso->setTimeout(20);
        $proceso->mustRun();
        if (trim($proceso->getOutput()) !== 'catalogo_valido') {
            throw new RuntimeException('El candidato no superó las pruebas de Prolog.');
        }
    }

    public function exportar(bool $publicar = false): RevisionCatalogo
    {
        return Cache::lock('publicacion-catalogo', 60)->block(5, function () use ($publicar) {
            $catalogo = DB::transaction(function () {
                if (DB::getDriverName() === 'pgsql') {
                    DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
                }

                return app(CatalogoTramites::class)->borrador();
            });
            $raiz = config('chatbot.catalogo.directorio', storage_path('prolog'));
            $dir = $raiz.'/revisiones/'.$catalogo['version'];
            if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
                throw new RuntimeException('No se pudo crear la revisión.');
            }
            $ruta = $dir.'/hechos.pl';
            $contenido = $this->generar($catalogo);
            if (file_exists($ruta) && file_get_contents($ruta) !== $contenido) {
                throw new RuntimeException('Una revisión publicada es inmutable.');
            }
            if (! file_exists($ruta) && file_put_contents($ruta, $contenido) === false) {
                throw new RuntimeException('No se pudo escribir el candidato.');
            }
            $this->validar($ruta);
            $revision = RevisionCatalogo::firstOrCreate(['version' => $catalogo['version']], ['contenido' => $catalogo, 'ruta_hechos' => $ruta]);
            if (! $publicar) {
                return $revision;
            }
            $anterior = RevisionCatalogo::where('activa', true)->first();
            $rutaAnterior = $anterior?->ruta_hechos ?? base_path('prolog/kb/hechos.pl');
            try {
                $this->recargar($ruta, $revision->version);
                DB::transaction(function () use ($revision) {
                    RevisionCatalogo::where('activa', true)->update(['activa' => false]);
                    $revision->update(['activa' => true]);
                });
                // El motor usa este puntero en el próximo arranque. Escritura por rename.
                $puntero = $raiz.'/activa';
                if (file_put_contents($puntero.'.tmp', $ruta) === false || ! rename($puntero.'.tmp', $puntero)) {
                    throw new RuntimeException('No se pudo guardar la revisión para el siguiente arranque.');
                }
            } catch (\Throwable $e) {
                $this->recargar($rutaAnterior, $anterior?->version ?? app(CatalogoTramites::class)->inicial()['version']);
                DB::transaction(function () use ($revision, $anterior) {
                    $revision->update(['activa' => false]);
                    if ($anterior) {
                        $anterior->update(['activa' => true]);
                    }
                });
                throw $e;
            }

            return $revision->fresh();
        });
    }

    private function recargar(string $ruta, string $version): void
    {
        $r = Http::baseUrl(config('chatbot.prolog.url'))->withHeaders(['X-Api-Key' => config('chatbot.prolog.api_key')])->timeout(15)->post('/recargar', ['version' => 3, 'ruta' => $ruta, 'kb_version' => $version]);
        if (! $r->successful() || $r->json('kb_version') !== $version || $r->json('version') !== 3) {
            throw new RuntimeException('El motor no confirmó la revisión solicitada.');
        }
    }
}
