<?php

namespace Tests\Feature;

use App\Services\Solicitudes\PrevalidacionSolicitud;
use App\Services\Solicitudes\RequisitosSolicitud;
use App\Services\Solicitudes\TramitesSimulados;

/** Pruebas anteriores aisladas del servidor externo de G4. */
trait RequisitosPrologPrueba
{
    use SesionEstudiantePrueba;

    protected bool $identificadoresPrologPrueba = false;

    protected function setUpRequisitosPrologPrueba(): void
    {
        $this->partialMock(RequisitosSolicitud::class)->shouldReceive('consultar')
            ->andReturnUsing(fn (string $codigo) => [
                'disponible' => true, 'items' => ['Requisito del motor de prueba'],
                'documentos' => $this->documentosMotorPrueba($codigo),
                'aviso' => 'Aviso del motor de prueba', 'kb_version' => 'prueba',
            ]);
        // La comprobación informativa del envío tiene sus propias pruebas (PrevalidacionSolicitudTest).
        $this->partialMock(PrevalidacionSolicitud::class)->shouldReceive('registrar');
    }

    protected function usarIdentificadoresProlog(): bool
    {
        return $this->identificadoresPrologPrueba;
    }

    protected function documentosMotorPrueba(string $codigo): array
    {
        $documentos = app(TramitesSimulados::class)->buscar($codigo)['documentos'] ?? [];
        if ($this->usarIdentificadoresProlog()) {
            $tramite = app(\App\Services\Catalogo\CatalogoTramites::class)->buscar($codigo)['id'];
            $documentos = array_map(fn ($d) => array_replace($d, [
                'id' => $tramite.'_'.str_replace('-', '_', $d['id']),
            ]), $documentos);
        }

        return $documentos;
    }
}
