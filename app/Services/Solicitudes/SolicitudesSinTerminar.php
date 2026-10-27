<?php

namespace App\Services\Solicitudes;

use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use Illuminate\Support\Str;

/**
 * Lo empezado y sin enviar, un renglón por trámite: los borradores guardados
 * en la cuenta (BorradoresSolicitud), los que todavía están en la sesión y las
 * correcciones en curso, cada uno con los respaldos y el PDF firmado que esta
 * sesión tiene de ese trámite. «Mis solicitudes» (RF-2.13) y el catálogo los
 * ofrecen para continuar o descartar.
 */
class SolicitudesSinTerminar
{
    public function __construct(
        private readonly BorradoresTemporales $borradores,
        private readonly BorradoresSolicitud $enCuenta,
        private readonly TramitesSimulados $tramites,
        private readonly RespaldosTemporales $respaldos,
        private readonly FirmadosTemporales $firmados,
    ) {}

    /**
     * De la guardada más recientemente a la más antigua.
     *
     * @return list<array{codigo: string, nombre: string, paso: int, guardado_at: string, respaldos: int, firmado: ?string, en_cuenta: bool}>
     */
    public function todas(): array
    {
        app(CorreccionesGuardadas::class)->restaurarPendientes();
        $sinTerminar = [];
        $guardados = [];

        foreach ($this->enCuenta->enCuenta(auth()->user()) as $solicitud) {
            $codigo = $solicitud->detalle['tramite'] ?? null;
            $tramite = is_string($codigo) ? $this->tramites->buscar($codigo) : null;
            if ($tramite === null || isset($guardados[$codigo])) continue;
            $guardados[$codigo] = true;
            $sinTerminar[] = [
                'codigo' => $codigo,
                'nombre' => Str::ucfirst($tramite['nombre']),
                'paso' => (int) ($solicitud->detalle['paso'] ?? 1),
                'guardado_at' => $solicitud->updated_at->toIso8601String(),
                'respaldos' => count($this->respaldos->delTramite($codigo)),
                'firmado' => $this->firmados->delTramite($codigo)['nombre_original'] ?? null,
                'en_cuenta' => true,
            ];
        }

        foreach ($this->borradores->todos() as $codigo => $borrador) {
            // El borrador de la cuenta manda sobre una copia vieja de la sesión.
            if (isset($guardados[$codigo])) continue;
            $claveTemporal = $codigo;
            $enlaces = [];
            if (preg_match('/^correccion-(\d+)--([a-z0-9_-]+)$/', $codigo, $partes)) {
                $expediente = \App\Models\Solicitud::find((int) $partes[1]);
                if (! $expediente || ! \Illuminate\Support\Facades\Gate::allows('update', $expediente)) continue;
                $codigo = $partes[2];
                $enlaces = [
                    'url' => route('solicitudes.edit', $expediente),
                    'descartar_url' => route('borradores-temporales.destroy', ['tramite' => $codigo, 'solicitud_correccion' => $expediente->getKey()]),
                ];
            }
            $tramite = $this->tramites->buscar($codigo);

            if ($tramite !== null) {
                $sinTerminar[] = $enlaces + [
                    'codigo' => $codigo,
                    'nombre' => ($enlaces ? 'Corrección de '.$expediente->codigo.' — ' : '').Str::ucfirst($tramite['nombre']),
                    'paso' => $borrador['paso'],
                    'guardado_at' => $borrador['guardado_at'],
                    'respaldos' => count($this->respaldos->delTramite($claveTemporal)),
                    'firmado' => $this->firmados->delTramite($claveTemporal)['nombre_original'] ?? null,
                    'en_cuenta' => ! empty($borrador['en_cuenta']),
                ];
            }
        }

        usort($sinTerminar, fn (array $a, array $b) => strcmp($b['guardado_at'], $a['guardado_at']));

        return $sinTerminar;
    }
}
