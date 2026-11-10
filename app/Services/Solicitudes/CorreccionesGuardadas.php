<?php

namespace App\Services\Solicitudes;

use App\Models\Solicitud;
use App\Services\Archivos\ArchivoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/** Copia privada de trabajo de G2. Nunca modifica el expediente observado. */
class CorreccionesGuardadas
{
    public const TABLA = 'borradores_correccion_g2';

    public static function disponible(): bool
    {
        return Schema::hasTable(self::TABLA);
    }

    public static function carpeta(int $usuario, int $solicitud): string
    {
        abort_unless($usuario > 0 && $solicitud > 0, 422);

        return 'correcciones-g2/'.$usuario.'/'.$solicitud;
    }

    public function restaurar(Solicitud $solicitud, string $tramite): void
    {
        $fila = DB::table(self::TABLA)->where('user_id', auth()->id())->where('solicitud_id', $solicitud->id)->first();
        if (! $fila) {
            return;
        }
        $datos = json_decode($fila->contenido, true, 512, JSON_THROW_ON_ERROR);
        $clave = 'correccion-'.$solicitud->id.'--'.$tramite;
        foreach (['borrador' => 'borradores_temporales', 'documento' => 'documentos_temporales', 'firmado' => 'firmados_temporales'] as $parte => $espacio) {
            $valor = $datos[$parte] ?? null;
            if ($parte === 'borrador' && $valor !== null) {
                $valor['en_cuenta'] = true;
            }
            $valor === null ? session()->forget($espacio.'.'.$clave) : session()->put($espacio.'.'.$clave, $valor);
        }
        $archivos = array_filter(session('respaldos_temporales.archivos', []), fn ($a) => ($a['tramite'] ?? null) !== $clave);
        session()->put('respaldos_temporales.archivos', $archivos + ($datos['respaldos'] ?? []));
        session()->put('correcciones_base.'.$solicitud->id, $fila->revision_base);
        if (! hash_equals($fila->revision_base, EnvioSolicitud::revision($solicitud))) {
            request()->attributes->set('correccion_desactualizada', true);
        }
    }

    public function restaurarPendientes(): void
    {
        if (! self::disponible() || ! auth()->user()?->activo) {
            return;
        }
        foreach (DB::table(self::TABLA)->where('user_id', auth()->id())->get() as $fila) {
            $solicitud = Solicitud::find($fila->solicitud_id);
            if ($solicitud && Gate::allows('update', $solicitud)) {
                $this->restaurar($solicitud, $fila->tramite);
            }
        }
    }

    public function guardar(Solicitud $solicitud, string $tramite): bool
    {
        $solicitud = $solicitud->fresh();
        if (! $solicitud || ! Gate::allows('update', $solicitud)) {
            if ($solicitud) {
                $this->descartar($solicitud);
            }

            return false;
        }
        $clave = 'correccion-'.$solicitud->id.'--'.$tramite;
        $datos = [
            'borrador' => session('borradores_temporales.'.$clave),
            'documento' => session('documentos_temporales.'.$clave),
            'firmado' => session('firmados_temporales.'.$clave),
            'respaldos' => array_filter(session('respaldos_temporales.archivos', []), fn ($a) => ($a['tramite'] ?? null) === $clave),
        ];
        if (! $datos['borrador'] && ! $datos['documento'] && ! $datos['firmado'] && ! $datos['respaldos']) {
            $this->descartar($solicitud);

            return false;
        }
        // Adopta archivos de sesiones anteriores. El original se borra solo después de guardar la copia.
        $viejos = [];
        $carpeta = self::carpeta((int) auth()->id(), $solicitud->id);
        $copiar = function (?string $ruta) use ($carpeta, &$viejos): ?string {
            if ($ruta === null || str_starts_with($ruta, $carpeta.'/')) {
                return $ruta;
            }
            if (! str_starts_with($ruta, 'respaldos-temporales/')) {
                throw new RuntimeException('Ruta de corrección no válida.');
            }
            $disco = Storage::disk(ArchivoService::DISCO);
            $destino = $carpeta.'/'.basename($ruta);
            if (! $disco->exists($ruta) || ! $disco->copy($ruta, $destino)) {
                throw new RuntimeException('No se pudo conservar el archivo de la corrección.');
            }
            $viejos[] = $ruta;

            return $destino;
        };
        foreach (['documento', 'firmado'] as $parte) {
            if ($datos[$parte]) {
                $datos[$parte]['ruta'] = $copiar($datos[$parte]['ruta']);
                if (isset($datos[$parte]['ruta_pdf'])) {
                    $datos[$parte]['ruta_pdf'] = $copiar($datos[$parte]['ruta_pdf']);
                }
            }
        }
        foreach ($datos['respaldos'] as &$archivo) {
            $archivo['ruta'] = $copiar($archivo['ruta']);
        }
        unset($archivo);
        if ($datos['borrador']) {
            $datos['borrador']['en_cuenta'] = true;
        }
        DB::table(self::TABLA)->updateOrInsert(['user_id' => auth()->id(), 'solicitud_id' => $solicitud->id], [
            'tramite' => $tramite,
            'revision_base' => session('correcciones_base.'.$solicitud->id, EnvioSolicitud::revision($solicitud)),
            'contenido' => json_encode($datos, JSON_THROW_ON_ERROR), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->restaurar($solicitud, $tramite);
        Storage::disk(ArchivoService::DISCO)->delete($viejos);

        return true;
    }

    private function descartar(Solicitud $solicitud): void
    {
        DB::table(self::TABLA)->where('user_id', auth()->id())->where('solicitud_id', $solicitud->id)->delete();
        Storage::disk(ArchivoService::DISCO)->deleteDirectory(self::carpeta((int) auth()->id(), $solicitud->id));
        session()->forget('correcciones_base.'.$solicitud->id);
    }
}
