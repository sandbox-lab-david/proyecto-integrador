<?php

namespace App\Services\Solicitudes;

use App\Models\DocumentoGenerado;
use App\Models\Solicitud;
use App\Services\Archivos\ArchivoService;
use App\Services\Archivos\RespaldosTemporales;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Word que efectivamente se descargó y la versión y huella de sus datos.
 * Si se descargó en PDF, ese PDF se conserva junto al Word.
 * Con un borrador en la cuenta (RF-2.12) es la fila de «documentos_generados»
 * de ese borrador; si no, vive en la sesión.
 */
class DocumentosTemporales
{
    private const SESION = 'documentos_temporales';

    public function __construct(
        private readonly Session $sesion,
        private readonly RespaldosTemporales $respaldos,
        private readonly BorradoresSolicitud $borradores,
    ) {}

    public function guardar(string $tramite, string $word, array $datos, ?string $pdf = null): void
    {
        $clave = ContextoCorreccion::clave($tramite);
        $borrador = $this->borradores->enCuentaDe($tramite);
        $carpeta = $borrador === null ? $this->respaldos->carpeta() : BorradoresSolicitud::carpeta($borrador);
        $nombre = $carpeta.'/'.Str::uuid();
        $ruta = $nombre.'.docx';
        $rutaPdf = $pdf === null ? null : $nombre.'.pdf';
        $disco = Storage::disk(ArchivoService::DISCO);
        if (! $disco->put($ruta, $word) || ($rutaPdf !== null && ! $disco->put($rutaPdf, $pdf))) {
            $disco->delete(array_filter([$ruta, $rutaPdf]));
            throw new RuntimeException('No se pudo conservar el documento generado.');
        }
        $this->quitar($tramite);
        $documento = ['version' => (string) Str::uuid(), 'hash_datos' => $this->huella($datos)];

        if ($borrador !== null) {
            // Un borrador tiene un solo Word: el de la última descarga.
            $borrador->documentosGenerados()->create(['version' => 1, 'ruta_docx' => $ruta, 'ruta_pdf' => $rutaPdf]);
            $this->borradores->anotarArchivos($borrador, ['documento' => $documento]);

            return;
        }

        $this->sesion->put(self::SESION.'.'.$clave, ['ruta' => $ruta, 'ruta_pdf' => $rutaPdf, 'datos' => $datos] + $documento);
    }

    /**
     * @return array{ruta: string, ruta_pdf: ?string, version: string, hash_datos: string}|null
     */
    public function buscar(string $tramite): ?array
    {
        if (($borrador = $this->borradores->enCuentaDe($tramite)) !== null) {
            $generado = $this->generadoDe($borrador, $tramite);
            $documento = $this->borradores->archivos($borrador)['documento'] ?? null;

            return $generado !== null && $documento !== null
                ? ['ruta' => $generado->ruta_docx, 'ruta_pdf' => $generado->ruta_pdf] + $documento : null;
        }

        $documento = $this->sesion->get(self::SESION.'.'.ContextoCorreccion::clave($tramite));

        return $documento === null ? null : $documento + ['ruta_pdf' => null];
    }

    /**
     * El Word del borrador en la cuenta. Si la sesión descargó uno antes de
     * que existiera el borrador, pasa a él, con su misma versión; si el
     * borrador ya tenía uno, el de la sesión es más viejo y se descarta.
     */
    public function generadoDe(Solicitud $borrador, string $tramite): ?DocumentoGenerado
    {
        $generado = $borrador->documentosGenerados()->latest('id')->first();
        $sesion = $this->sesion->get(self::SESION.'.'.$tramite);

        if ($sesion !== null) {
            $this->sesion->forget(self::SESION.'.'.$tramite);
            $disco = Storage::disk(ArchivoService::DISCO);
            $carpeta = BorradoresSolicitud::carpeta($borrador);
            $destino = $carpeta.'/'.basename($sesion['ruta']);
            $pdf = $sesion['ruta_pdf'] ?? null;

            if ($generado === null && $disco->exists($sesion['ruta']) && $disco->move($sesion['ruta'], $destino)) {
                // Si el PDF no se puede mover, el Word sigue valiendo sin él.
                $destinoPdf = $pdf !== null && $disco->exists($pdf) && $disco->move($pdf, $carpeta.'/'.basename($pdf))
                    ? $carpeta.'/'.basename($pdf) : null;
                $generado = $borrador->documentosGenerados()->create(['version' => 1, 'ruta_docx' => $destino, 'ruta_pdf' => $destinoPdf]);
                $this->borradores->anotarArchivos($borrador, [
                    'documento' => ['version' => $sesion['version'], 'hash_datos' => $sesion['hash_datos']],
                ]);
            } else {
                $disco->delete(array_filter([$sesion['ruta'], $pdf]));
            }
        }

        return $generado;
    }

    public function huella(array $datos): string
    {
        $contenido = array_intersect_key($datos, array_flip(['tramite', ...array_keys(DatosFormularioSolicitud::PERSONALES), 'materias', 'campos']));
        $ordenar = function (array $valor) use (&$ordenar): array {
            if (! array_is_list($valor)) ksort($valor);
            foreach ($valor as &$item) {
                if (is_array($item)) $item = $ordenar($item);
            }
            return $valor;
        };
        return hash('sha256', json_encode($ordenar($contenido), JSON_THROW_ON_ERROR));
    }

    /** Quita el Word, su PDF y el PDF firmado de esa versión. */
    public function quitar(string $tramite): void
    {
        $disco = Storage::disk(ArchivoService::DISCO);

        if (($borrador = $this->borradores->enCuentaDe($tramite)) !== null) {
            $generados = $borrador->documentosGenerados()->with('documentoFirmado')->get();
            DB::transaction(function () use ($generados) {
                foreach ($generados as $generado) {
                    $generado->documentoFirmado?->delete();
                    $generado->delete();
                }
            });
            foreach ($generados as $generado) {
                $disco->delete(array_filter([$generado->ruta_docx, $generado->ruta_pdf, $generado->documentoFirmado?->ruta]));
            }
            $this->borradores->anotarArchivos($borrador, ['documento' => null, 'firmado' => null]);
        }

        if ($documento = $this->sesion->get(self::SESION.'.'.ContextoCorreccion::clave($tramite))) {
            $disco->delete(array_filter([$documento['ruta'], $documento['ruta_pdf'] ?? null]));
        }
        $this->sesion->forget(self::SESION.'.'.ContextoCorreccion::clave($tramite));
    }
}
