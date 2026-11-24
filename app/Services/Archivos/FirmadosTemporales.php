<?php

namespace App\Services\Archivos;

use App\Models\DocumentoFirmado;
use App\Models\DocumentoGenerado;
use App\Models\Solicitud;
use App\Services\Solicitudes\BorradoresSolicitud;
use App\Services\Solicitudes\ContextoCorreccion;
use App\Services\Solicitudes\DocumentosTemporales;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * PDF firmado que el estudiante sube después de confirmar (RF-2.11), antes de
 * enviar: uno por trámite. Con un borrador en la cuenta (RF-2.12) es la fila
 * de «documentos_firmados» del Word que se descargó; si no, vive en la
 * carpeta temporal de la sesión.
 */
class FirmadosTemporales
{
    private const SESION = 'firmados_temporales';

    public function __construct(
        private readonly ArchivoService $archivos,
        private readonly RespaldosTemporales $respaldos,
        private readonly Session $sesion,
        private readonly DocumentosTemporales $documentos,
        private readonly BorradoresSolicitud $borradores,
    ) {}

    /**
     * Guarda el PDF y reemplaza al que hubiera para ese trámite.
     *
     * @return array{nombre_original: string, mime: string, tamano_bytes: int}
     *
     * @throws ArchivoRechazado
     */
    public function guardar(UploadedFile $archivo, string $tramite): array
    {
        $clave = ContextoCorreccion::clave($tramite);

        if (($borrador = $this->borradores->enCuentaDe($tramite)) !== null) {
            $generado = $this->documentos->generadoDe($borrador, $tramite);
            if ($generado === null) {
                throw new ArchivoRechazado('Descarga el documento antes de subir su PDF firmado.');
            }

            $guardado = $this->archivos->guardar($archivo, BorradoresSolicitud::carpeta($borrador), ['pdf']);
            $this->quitar($tramite);
            $generado->documentoFirmado()->create([
                'ruta' => $guardado['ruta'], 'tamano_bytes' => $guardado['tamano_bytes'], 'subido_por' => auth()->id(),
            ]);
            $this->borradores->anotarArchivos($borrador, ['firmado' => $guardado['nombre_original']]);

            return $this->publico($guardado);
        }

        $guardado = $this->archivos->guardar($archivo, $this->respaldos->carpeta(), ['pdf']);

        // El anterior se borra solo cuando el nuevo ya quedó guardado.
        $this->quitar($tramite);
        $this->sesion->put(self::SESION.'.'.$clave, $guardado + [
            'documento_hash' => $this->documentos->buscar($tramite)['hash_datos'] ?? null,
            'documento_version' => $this->documentos->buscar($tramite)['version'] ?? null,
        ]);

        return $this->publico($guardado);
    }

    /**
     * @return array{ruta: string, mime: string, tamano_bytes: int, nombre_original: string, documento_hash: ?string, documento_version: ?string}|null
     */
    public function buscar(string $tramite): ?array
    {
        if (($borrador = $this->borradores->enCuentaDe($tramite)) !== null) {
            $generado = $this->documentos->generadoDe($borrador, $tramite);
            $this->adoptar($borrador, $tramite, $generado);
            $firmado = $generado?->documentoFirmado()->first();

            if ($firmado === null) {
                return null;
            }

            $archivos = $this->borradores->archivos($borrador);

            return [
                'ruta' => $firmado->ruta,
                'mime' => 'application/pdf',
                'tamano_bytes' => $firmado->tamano_bytes,
                'nombre_original' => $archivos['firmado'] ?? 'documento-firmado.pdf',
                // Un firmado siempre es de la versión del Word al que pertenece.
                'documento_hash' => $archivos['documento']['hash_datos'] ?? null,
                'documento_version' => $archivos['documento']['version'] ?? null,
            ];
        }

        return $this->sesion->get(self::SESION, [])[ContextoCorreccion::clave($tramite)] ?? null;
    }

    /**
     * Lo que el navegador puede saber del firmado: nunca la ruta.
     *
     * @return array{nombre_original: string, mime: string, tamano_bytes: int}|null
     */
    public function delTramite(string $tramite): ?array
    {
        $firmado = $this->buscar($tramite);

        return $firmado === null ? null : $this->publico($firmado);
    }

    public function quitar(string $tramite): void
    {
        if (($borrador = $this->borradores->enCuentaDe($tramite)) !== null) {
            $firmados = DocumentoFirmado::whereIn('documento_generado_id', $borrador->documentosGenerados()->select('id'))->get();
            foreach ($firmados as $firmado) {
                $firmado->delete();
                $this->archivos->eliminar($firmado->ruta);
            }
            $this->borradores->anotarArchivos($borrador, ['firmado' => null]);
        }

        $firmado = $this->sesion->get(self::SESION, [])[ContextoCorreccion::clave($tramite)] ?? null;

        if ($firmado === null) {
            return;
        }

        $this->archivos->eliminar($firmado['ruta']);
        $this->sesion->forget(self::SESION.'.'.ContextoCorreccion::clave($tramite));
    }

    /**
     * El firmado que la sesión subió antes de que existiera el borrador en la
     * cuenta pasa a él si es del mismo Word; si no, ya no sirve y se borra.
     */
    private function adoptar(Solicitud $borrador, string $tramite, ?DocumentoGenerado $generado): void
    {
        $sesion = $this->sesion->get(self::SESION.'.'.$tramite);
        if ($sesion === null) {
            return;
        }

        $this->sesion->forget(self::SESION.'.'.$tramite);
        $disco = Storage::disk(ArchivoService::DISCO);
        $destino = BorradoresSolicitud::carpeta($borrador).'/'.basename($sesion['ruta']);
        $version = $this->borradores->archivos($borrador)['documento']['version'] ?? null;

        if ($generado !== null && $version !== null && ($sesion['documento_version'] ?? null) === $version
            && ! $generado->documentoFirmado()->exists()
            && $disco->exists($sesion['ruta']) && $disco->move($sesion['ruta'], $destino)) {
            $generado->documentoFirmado()->create([
                'ruta' => $destino, 'tamano_bytes' => $sesion['tamano_bytes'], 'subido_por' => auth()->id(),
            ]);
            $this->borradores->anotarArchivos($borrador, ['firmado' => $sesion['nombre_original']]);

            return;
        }

        $disco->delete($sesion['ruta']);
    }

    private function publico(array $firmado): array
    {
        return [
            'nombre_original' => $firmado['nombre_original'],
            'mime' => $firmado['mime'],
            'tamano_bytes' => $firmado['tamano_bytes'],
        ];
    }
}
