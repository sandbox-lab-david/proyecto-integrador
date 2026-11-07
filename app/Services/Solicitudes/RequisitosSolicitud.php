<?php

namespace App\Services\Solicitudes;

use App\Services\Chatbot\Prolog\PrologClient;
use App\Services\Chatbot\Prolog\PrologException;
use App\Services\Catalogo\CatalogoTramites;
use Illuminate\Validation\ValidationException;

/** Adaptador de lectura de G2: consume el puente de G4 sin modificar el motor. */
class RequisitosSolicitud
{
    public function consultar(string $codigo): array
    {
        $tramite = app(CatalogoTramites::class)->buscar($codigo);
        abort_unless($tramite, 404);
        $entrada = ['version' => 3, 'tramite_id' => $tramite['id']];
        $motor = app(PrologClient::class);
        $requisitos = $motor->consultar($entrada + ['intencion' => 'consultar_requisitos']);
        $documentos = $motor->consultar($entrada + ['intencion' => 'consultar_documentos']);
        if ($requisitos['kb_version'] !== $documentos['kb_version']) {
            throw PrologException::respuestaInvalida('la versión del motor cambió durante la consulta');
        }
        $ids = array_column($documentos['documentos'], 'id');
        if (count(array_unique($ids)) !== count($ids) || in_array('', $ids, true)) {
            throw PrologException::respuestaInvalida('identificadores de documentos vacíos o repetidos');
        }
        return [
            'disponible' => true,
            'items' => $requisitos['items'],
            'documentos' => $documentos['documentos'],
            'aviso' => implode("\n", array_unique(array_filter([$requisitos['aviso'], $documentos['aviso']]))),
            'kb_version' => $documentos['kb_version'],
        ];
    }

    public function paraVista(string $codigo): array
    {
        try {
            return $this->consultar($codigo);
        } catch (PrologException $error) {
            report($error);
            return ['disponible' => false, 'items' => [], 'documentos' => [], 'aviso' => null,
                'error' => 'No se pudieron consultar los requisitos del trámite. Recarga la página para volver a intentarlo. No puedes enviar la solicitud hasta consultarlos.'];
        }
    }

    public function verificar(string $codigo, array $archivos, array $asignaciones): array
    {
        // Siempre vuelve al motor en el servidor; nunca confía en la lista del navegador.
        $consulta = $this->consultar($codigo);
        $documentos = $consulta['documentos'];
        if (array_diff(array_keys($asignaciones), array_column($archivos, 'id'))) {
            throw ValidationException::withMessages(['requisitos' => 'Hay archivos que no pertenecen a esta solicitud.']);
        }
        foreach ($archivos as $archivo) {
            if (! in_array($asignaciones[$archivo['id']] ?? null, array_column($documentos, 'id'), true)) {
                throw ValidationException::withMessages(['requisitos' => 'Asocia cada archivo con un documento vigente del trámite.']);
            }
        }
        foreach ($documentos as $documento) {
            if ($documento['obligatorio'] && ! in_array($documento['id'], array_values($asignaciones), true)) {
                throw ValidationException::withMessages(['requisitos' => 'Falta adjuntar: '.$documento['nombre']]);
            }
        }
        return $consulta;
    }
}
