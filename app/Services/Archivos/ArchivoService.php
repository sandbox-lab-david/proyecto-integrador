<?php

namespace App\Services\Archivos;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Guarda y entrega archivos del storage privado (contratos C48–C49).
 *
 * Las rutas que devuelve y recibe son relativas al disco privado; nunca son
 * una URL pública.
 */
class ArchivoService
{
    public const DISCO = 'local';

    /** Minutos que sirve un enlace de urlTemporal(). */
    private const MINUTOS_ENLACE = 60;

    /** Tipos que el navegador muestra sin descargarlos. */
    private const SE_MUESTRAN = ['application/pdf', 'image/jpeg', 'image/png'];

    /**
     * Valida el archivo por su contenido y lo guarda con un nombre propio.
     *
     * @param  list<string>  $mimes  Extensiones aceptadas.
     * @return array{ruta: string, mime: string, tamano_bytes: int, nombre_original: string}
     *
     * @throws ArchivoRechazado si el tipo o el tamaño no se aceptan.
     */
    public function guardar(UploadedFile $f, string $carpeta, array $mimes = ['pdf', 'jpg', 'png'], int $maxKb = 10240): array
    {
        if (! $f->isValid()) {
            throw new ArchivoRechazado('El archivo no se pudo subir. Inténtalo de nuevo.');
        }

        if ($f->getSize() > $maxKb * 1024) {
            throw new ArchivoRechazado('El archivo supera el máximo de '.$this->tamanoLegible($maxKb).'.');
        }

        // El tipo sale del contenido, no del nombre ni de lo que declara el navegador.
        $extension = $this->extension((string) $f->guessExtension());
        $aceptadas = array_map($this->extension(...), $mimes);

        if (! in_array($extension, $aceptadas, true)) {
            throw new ArchivoRechazado(
                'El archivo no es un '.collect($aceptadas)->map(fn (string $e) => strtoupper($e))->join(', ', ' o ').' válido.'
            );
        }

        $mime = (string) $f->getMimeType();
        $tamano = (int) $f->getSize();
        $ruta = $f->storeAs(trim($carpeta, '/'), Str::uuid().'.'.$extension, self::DISCO);

        if ($ruta === false) {
            throw new RuntimeException('No se pudo guardar el archivo en el storage privado.');
        }

        return [
            'ruta' => $ruta,
            'mime' => $mime,
            'tamano_bytes' => $tamano,
            'nombre_original' => mb_substr($this->nombreSeguro($f->getClientOriginalName()), 0, 255),
        ];
    }

    /**
     * Descarga un archivo del storage privado. Quien llama ya comprobó que
     * el usuario puede verlo.
     */
    public function descargar(string $ruta, ?string $nombre = null): StreamedResponse
    {
        return $this->disco()->download($this->existente($ruta), $this->nombreSeguro($nombre));
    }

    /**
     * Igual que descargar(), pero para verlo en el navegador. Lo que no es
     * PDF, JPG o PNG se descarga.
     */
    public function mostrar(string $ruta, ?string $nombre = null): StreamedResponse
    {
        $ruta = $this->existente($ruta);

        if (! in_array($this->disco()->mimeType($ruta), self::SE_MUESTRAN, true)) {
            return $this->descargar($ruta, $nombre);
        }

        return $this->disco()->response($ruta, $this->nombreSeguro($nombre), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Enlace firmado que abre el archivo durante una hora, para usarlo en un
     * <img>, un <iframe> o un enlace. Solo debe generarse en una página cuyo
     * usuario ya está autorizado a ver el archivo.
     */
    public function urlTemporal(string $ruta, ?string $nombre = null, bool $descargar = false): string
    {
        return URL::temporarySignedRoute('archivos.ver', now()->addMinutes(self::MINUTOS_ENLACE), array_filter([
            'ruta' => $ruta,
            'nombre' => $nombre,
            'descargar' => $descargar ? 1 : null,
        ]));
    }

    public function eliminar(string $ruta): void
    {
        $this->disco()->delete($ruta);
    }

    private function disco(): Filesystem
    {
        return Storage::disk(self::DISCO);
    }

    private function existente(string $ruta): string
    {
        abort_if($ruta === '' || str_contains($ruta, '..') || ! $this->disco()->exists($ruta), 404);

        return $ruta;
    }

    private function extension(string $extension): string
    {
        $extension = strtolower(ltrim($extension, '.'));

        return $extension === 'jpeg' ? 'jpg' : $extension;
    }

    /** Sin separadores de carpeta: no se aceptan en el nombre de una descarga. */
    private function nombreSeguro(?string $nombre): ?string
    {
        if ($nombre === null || trim($nombre) === '') {
            return null;
        }

        return str_replace(['/', '\\'], '-', trim($nombre));
    }

    private function tamanoLegible(int $kb): string
    {
        return $kb >= 1024 ? round($kb / 1024, 1).' MB' : $kb.' KB';
    }
}
