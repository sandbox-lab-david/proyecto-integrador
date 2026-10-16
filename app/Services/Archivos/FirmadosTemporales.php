<?php

namespace App\Services\Archivos;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\UploadedFile;

/**
 * PDF firmado que el estudiante sube después de confirmar (RF-2.11) mientras
 * la solicitud todavía no existe: uno por trámite, en la carpeta temporal de
 * la sesión. Cuando la solicitud se pueda guardar será una fila de
 * «documentos_firmados» y subirlo pasará el caso a Enviado.
 */
class FirmadosTemporales
{
    private const SESION = 'firmados_temporales';

    public function __construct(
        private readonly ArchivoService $archivos,
        private readonly RespaldosTemporales $respaldos,
        private readonly Session $sesion,
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
        $guardado = $this->archivos->guardar($archivo, $this->respaldos->carpeta(), ['pdf']);

        // El anterior se borra solo cuando el nuevo ya quedó guardado.
        $this->quitar($tramite);
        $this->sesion->put(self::SESION.'.'.$tramite, $guardado);

        return $this->publico($guardado);
    }

    /**
     * @return array{ruta: string, mime: string, tamano_bytes: int, nombre_original: string}|null
     */
    public function buscar(string $tramite): ?array
    {
        return $this->sesion->get(self::SESION, [])[$tramite] ?? null;
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
        $firmado = $this->buscar($tramite);

        if ($firmado === null) {
            return;
        }

        $this->archivos->eliminar($firmado['ruta']);
        $this->sesion->forget(self::SESION.'.'.$tramite);
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
