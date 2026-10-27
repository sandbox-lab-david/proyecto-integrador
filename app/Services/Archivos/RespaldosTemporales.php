<?php

namespace App\Services\Archivos;

use App\Models\Respaldo;
use App\Models\Solicitud;
use App\Services\Solicitudes\BorradoresSolicitud;
use App\Services\Solicitudes\ContextoCorreccion;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Respaldos que el estudiante sube en el paso 3 antes de enviar la solicitud.
 *
 * Si el trámite tiene un borrador en la cuenta (RF-2.12), cada archivo es una
 * fila de «respaldos» de ese borrador, en su carpeta, y se retoma desde
 * cualquier navegador. Si no (una corrección, o una cuenta que todavía no
 * puede guardar el borrador), se guarda en una carpeta propia de la sesión y
 * solo esa sesión puede verlo o quitarlo; cuando aparece el borrador, lo de
 * la sesión pasa a él. En los dos casos el navegador solo conoce el uuid del
 * archivo.
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
        private readonly BorradoresSolicitud $borradores,
    ) {}

    /**
     * @return array{id: string, nombre_original: string, mime: string, tamano_bytes: int}
     *
     * @throws ArchivoRechazado
     */
    public function agregar(UploadedFile $archivo, string $tramite, ?string $requisito = null): array
    {
        $clave = ContextoCorreccion::clave($tramite);
        $borrador = $this->borradores->enCuentaDe($tramite);
        $subidos = $borrador === null ? count($this->todos()) : count($this->delTramite($tramite));
        if ($subidos >= self::MAXIMO_POR_SESION) {
            throw new ArchivoRechazado(
                'Ya subiste '.self::MAXIMO_POR_SESION.' archivos. Quita alguno para agregar otro.'
            );
        }

        if ($borrador !== null) {
            $guardado = $this->archivos->guardar($archivo, BorradoresSolicitud::carpeta($borrador));
            $fila = $this->crearFila($borrador, $guardado, $requisito);

            return $this->publico($this->idDe($fila->ruta), $this->deFila($fila, $tramite));
        }

        $guardado = $this->archivos->guardar($archivo, $this->carpeta());
        $id = $this->idDe($guardado['ruta']);

        if ($requisito !== null) {
            $guardado['requisito'] = $requisito;
        }

        $this->sesion->put(self::SESION.'.archivos.'.$id, $guardado + ['tramite' => $clave]);

        return $this->publico($id, $guardado);
    }

    /**
     * @return array{ruta: string, mime: string, tamano_bytes: int, nombre_original: string, tramite: string}|null
     */
    public function buscar(string $id): ?array
    {
        if (($respaldo = $this->todos()[$id] ?? null) !== null) {
            return $respaldo;
        }

        $fila = $this->filaPropia($id);

        return $fila === null ? null : $this->deFila($fila, $fila->solicitud->detalle['tramite'] ?? '');
    }

    /**
     * Lo ya subido para un trámite, para volver a mostrarlo si la página se
     * recarga o el borrador se retoma.
     *
     * @return list<array{id: string, nombre_original: string, mime: string, tamano_bytes: int}>
     */
    public function delTramite(string $tramite): array
    {
        if (($borrador = $this->borradores->enCuentaDe($tramite)) !== null) {
            $this->adoptar($borrador, $tramite);

            return $borrador->respaldos()->with('documentoRequerido')->orderBy('id')->get()
                ->map(fn (Respaldo $fila) => $this->publico($this->idDe($fila->ruta), $this->deFila($fila, $tramite)))
                ->all();
        }

        $clave = ContextoCorreccion::clave($tramite);
        $propios = array_filter($this->todos(), fn (array $respaldo) => $respaldo['tramite'] === $clave);

        return array_map($this->publico(...), array_keys($propios), $propios);
    }

    public function quitar(string $id): void
    {
        if (($respaldo = $this->todos()[$id] ?? null) !== null) {
            $this->archivos->eliminar($respaldo['ruta']);
            $this->sesion->forget(self::SESION.'.archivos.'.$id);

            return;
        }

        if (($fila = $this->filaPropia($id)) !== null) {
            $fila->delete();
            $this->archivos->eliminar($fila->ruta);
        }
    }

    /**
     * @return array<string, array{ruta: string, mime: string, tamano_bytes: int, nombre_original: string, tramite: string}>
     */
    private function todos(): array
    {
        return $this->sesion->get(self::SESION.'.archivos', []);
    }

    /** Respaldo de un borrador en la cuenta del usuario actual, por el uuid de su archivo. */
    private function filaPropia(string $id): ?Respaldo
    {
        if (! Str::isUuid($id) || ($borradores = $this->borradores->idsEnCuenta()) === []) {
            return null;
        }

        return Respaldo::with('documentoRequerido', 'solicitud')
            ->whereIn('solicitud_id', $borradores)
            ->where('ruta', 'like', '%/'.$id.'.%')
            ->first();
    }

    /**
     * @param  array{ruta: string, mime: string, tamano_bytes: int, nombre_original: string}  $guardado
     */
    private function crearFila(Solicitud $borrador, array $guardado, ?string $requisito): Respaldo
    {
        // El requisito es el código del documento en el catálogo de G4.
        $documento = $requisito === null ? null
            : $borrador->tipoTramite?->documentos()->where('codigo', $requisito)->value('id');

        return $borrador->respaldos()->create([
            'documento_requerido_id' => $documento,
            'nombre_original' => $guardado['nombre_original'],
            'ruta' => $guardado['ruta'],
            'mime' => $guardado['mime'],
            'tamano_bytes' => $guardado['tamano_bytes'],
        ]);
    }

    /**
     * La fila con la misma forma que un respaldo de la sesión.
     *
     * @return array{ruta: string, mime: string, tamano_bytes: int, nombre_original: string, tramite: string}
     */
    private function deFila(Respaldo $fila, string $tramite): array
    {
        return [
            'ruta' => $fila->ruta,
            'mime' => $fila->mime,
            'tamano_bytes' => $fila->tamano_bytes,
            'nombre_original' => $fila->nombre_original,
            'tramite' => $tramite,
            ...($fila->documentoRequerido ? ['requisito' => $fila->documentoRequerido->codigo] : []),
        ];
    }

    /**
     * Lo que la sesión subió para el trámite antes de que existiera su
     * borrador en la cuenta pasa a ese borrador, con el mismo uuid.
     */
    private function adoptar(Solicitud $borrador, string $tramite): void
    {
        $disco = Storage::disk(ArchivoService::DISCO);

        foreach ($this->todos() as $id => $respaldo) {
            if ($respaldo['tramite'] !== $tramite) {
                continue;
            }

            $destino = BorradoresSolicitud::carpeta($borrador).'/'.basename($respaldo['ruta']);
            if ($disco->exists($respaldo['ruta']) && $disco->move($respaldo['ruta'], $destino)) {
                $this->crearFila($borrador, ['ruta' => $destino] + $respaldo, $respaldo['requisito'] ?? null);
            }

            $this->sesion->forget(self::SESION.'.archivos.'.$id);
        }
    }

    private function idDe(string $ruta): string
    {
        return pathinfo($ruta, PATHINFO_FILENAME);
    }

    /** Sin la ruta: el navegador solo conoce el id. */
    private function publico(string $id, array $respaldo): array
    {
        return [
            'id' => $id,
            'nombre_original' => $respaldo['nombre_original'],
            'mime' => $respaldo['mime'],
            'tamano_bytes' => $respaldo['tamano_bytes'],
            ...(isset($respaldo['requisito']) ? ['requisito' => $respaldo['requisito']] : []),
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
