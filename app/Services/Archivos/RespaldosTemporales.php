<?php

namespace App\Services\Archivos;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Respaldos que el estudiante sube en el paso 3 antes de que exista su
 * solicitud. Se guardan en una carpeta propia de la sesión y solo esa sesión
 * puede verlos o quitarlos. Cuando la solicitud se pueda guardar, de aquí
 * saldrán las filas de «respaldos».
 */
class RespaldosTemporales
{
    public const MAXIMO_POR_SESION = 30;

    private const CARPETA = 'respaldos-temporales';

    private const SESION = 'respaldos_temporales';

    /** Horas que se conserva una carpeta que nadie volvió a usar. */
    private const HORAS_DE_VIDA = 24;

    public function __construct(
        private readonly ArchivoService $archivos,
        private readonly Session $sesion,
    ) {}

    /**
     * @return array{id: string, nombre_original: string, mime: string, tamano_bytes: int}
     *
     * @throws ArchivoRechazado
     */
    public function agregar(UploadedFile $archivo, string $tramite): array
    {
        if (count($this->todos()) >= self::MAXIMO_POR_SESION) {
            throw new ArchivoRechazado(
                'Ya subiste '.self::MAXIMO_POR_SESION.' archivos. Quita alguno para agregar otro.'
            );
        }

        $guardado = $this->archivos->guardar($archivo, $this->carpeta());
        $id = pathinfo($guardado['ruta'], PATHINFO_FILENAME);

        $this->sesion->put(self::SESION.'.archivos.'.$id, $guardado + ['tramite' => $tramite]);

        return $this->publico($id, $guardado);
    }

    /**
     * @return array{ruta: string, mime: string, tamano_bytes: int, nombre_original: string, tramite: string}|null
     */
    public function buscar(string $id): ?array
    {
        return $this->todos()[$id] ?? null;
    }

    /**
     * Lo que la sesión ya subió para un trámite, para volver a mostrarlo si
     * la página se recarga.
     *
     * @return list<array{id: string, nombre_original: string, mime: string, tamano_bytes: int}>
     */
    public function delTramite(string $tramite): array
    {
        $propios = array_filter($this->todos(), fn (array $respaldo) => $respaldo['tramite'] === $tramite);

        return array_map($this->publico(...), array_keys($propios), $propios);
    }

    public function quitar(string $id): void
    {
        $respaldo = $this->buscar($id);

        if ($respaldo === null) {
            return;
        }

        $this->archivos->eliminar($respaldo['ruta']);
        $this->sesion->forget(self::SESION.'.archivos.'.$id);
    }

    /**
     * @return array<string, array{ruta: string, mime: string, tamano_bytes: int, nombre_original: string, tramite: string}>
     */
    private function todos(): array
    {
        return $this->sesion->get(self::SESION.'.archivos', []);
    }

    /** Sin la ruta: el navegador solo conoce el id. */
    private function publico(string $id, array $respaldo): array
    {
        return [
            'id' => $id,
            'nombre_original' => $respaldo['nombre_original'],
            'mime' => $respaldo['mime'],
            'tamano_bytes' => $respaldo['tamano_bytes'],
        ];
    }

    /** Carpeta temporal de la sesión; la crea la primera vez que se pide. */
    public function carpeta(): string
    {
        $carpeta = $this->sesion->get(self::SESION.'.carpeta');

        if ($carpeta === null) {
            // Cada sesión nueva aprovecha para borrar lo que otras dejaron abandonado.
            $this->borrarAbandonados();

            $carpeta = self::CARPETA.'/'.Str::uuid();
            $this->sesion->put(self::SESION.'.carpeta', $carpeta);
        }

        return $carpeta;
    }

    private function borrarAbandonados(): void
    {
        $disco = Storage::disk(ArchivoService::DISCO);
        $limite = now()->subHours(self::HORAS_DE_VIDA)->getTimestamp();

        foreach ($disco->directories(self::CARPETA) as $carpeta) {
            $enUso = collect($disco->files($carpeta))
                ->contains(fn (string $ruta) => $disco->lastModified($ruta) > $limite);

            if (! $enUso) {
                $disco->deleteDirectory($carpeta);
            }
        }
    }
}
