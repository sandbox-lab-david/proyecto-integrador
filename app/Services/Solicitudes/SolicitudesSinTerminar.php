<?php

namespace App\Services\Solicitudes;

use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use Illuminate\Support\Str;

/**
 * Lo que la sesión tiene empezado y sin enviar, un renglón por trámite: el
 * borrador con sus respaldos y su PDF firmado. Es lo único que «Mis
 * solicitudes» (RF-2.13) puede listar mientras las solicitudes no se guarden;
 * después serán filas de «solicitudes» sin enviar y esta clase se podrá borrar.
 */
class SolicitudesSinTerminar
{
    public function __construct(
        private readonly BorradoresTemporales $borradores,
        private readonly TramitesSimulados $tramites,
        private readonly RespaldosTemporales $respaldos,
        private readonly FirmadosTemporales $firmados,
    ) {}

    /**
     * De la guardada más recientemente a la más antigua.
     *
     * @return list<array{codigo: string, nombre: string, paso: int, guardado_at: string, respaldos: int, firmado: ?string}>
     */
    public function todas(): array
    {
        $sinTerminar = [];

        foreach ($this->borradores->todos() as $codigo => $borrador) {
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
                ];
            }
        }

        usort($sinTerminar, fn (array $a, array $b) => strcmp($b['guardado_at'], $a['guardado_at']));

        return $sinTerminar;
    }
}
