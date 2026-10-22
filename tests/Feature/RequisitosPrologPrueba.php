<?php

namespace Tests\Feature;

use App\Services\Solicitudes\RequisitosSolicitud;
use App\Services\Solicitudes\TramitesSimulados;

/** Pruebas anteriores aisladas del servidor externo de G4. */
trait RequisitosPrologPrueba
{
    use SesionEstudiantePrueba;

    protected function setUpRequisitosPrologPrueba(): void
    {
        $this->partialMock(RequisitosSolicitud::class)->shouldReceive('consultar')
            ->andReturnUsing(fn (string $codigo) => [
                'disponible' => true, 'items' => ['Requisito del motor de prueba'],
                'documentos' => app(TramitesSimulados::class)->buscar($codigo)['documentos'] ?? [],
                'aviso' => 'Aviso del motor de prueba', 'kb_version' => 'prueba',
            ]);
    }
}
