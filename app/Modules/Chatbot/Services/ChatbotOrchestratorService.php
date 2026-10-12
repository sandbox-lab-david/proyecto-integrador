<?php

namespace App\Modules\Chatbot\Services;

use App\Modules\Chatbot\Services\Prolog\HttpPrologClient;
use App\Modules\Chatbot\Services\Prolog\PrologClient;

class ChatbotOrchestratorService
{
    protected PrologClient $prologClient;
    protected ChatbotCatalogoService $catalogoService;

    public function __construct(
        ?PrologClient $prologClient = null,
        ?ChatbotCatalogoService $catalogoService = null
    ) {
        $this->prologClient = $prologClient ?? HttpPrologClient::desdeConfig();
        $this->catalogoService = $catalogoService ?? new ChatbotCatalogoService();
    }

    public function procesarMensaje(array $datos): array
    {
        $intencion = $datos['intencion'];
        $origen = $datos['origen_datos'];
        $tramiteId = $datos['tramite_id'];
        $perfil = $datos['datos_usuario'] ?? [];

        // Si se pide evaluar elegibilidad pero aún no se han enviado datos de formulario
        if ($intencion === 'evaluar_elegibilidad' && empty($perfil)) {
            $camposPendientes = $this->catalogoService->obtenerCamposDeTramite($tramiteId);
            if (!empty($camposPendientes)) {
                return [
                    'version' => 2,
                    'estado' => 'requiere_datos',
                    'tipo' => 'elegibilidad',
                    'tramite_id' => $tramiteId,
                    'titulo' => 'Evaluación de elegibilidad',
                    'mensaje' => 'Para evaluar si cumples con las condiciones de este trámite, por favor ingresa los siguientes datos:',
                    'items' => [],
                    'documentos' => [],
                    'costo' => null,
                    'plazo' => null,
                    'resultado' => null,
                    'aviso' => null,
                    'campos_pendientes' => $camposPendientes,
                    'acciones' => [],
                    'sugerencias' => [],
                    'meta' => [
                        'kb_version' => null,
                        'origen_datos' => $origen,
                        'verificacion' => 'declarada',
                    ],
                ];
            }
        }

        // Llamada al motor Prolog según la intención
        if ($intencion === 'evaluar_elegibilidad') {
            $perfilProlog = $this->normalizarPerfilParaProlog($perfil);
            $respuestaProlog = $this->prologClient->evaluar([
                'version' => 2,
                'tramite_id' => $tramiteId,
                'intencion' => $intencion,
                'fecha' => date('Y-m-d'),
                'origen_datos' => $origen === 'ninguno' ? 'manual' : $origen,
                'perfil' => $perfilProlog,
            ]);
        } else {
            $respuestaProlog = $this->prologClient->consultar([
                'version' => 2,
                'tramite_id' => $tramiteId,
                'intencion' => $intencion,
            ]);
        }

        return $this->formatearRespuestaPublica($datos, $respuestaProlog);
    }

    protected function formatearRespuestaPublica(array $peticionOriginal, array $respuestaProlog): array
    {
        $intencion = $peticionOriginal['intencion'];
        $tramiteId = $peticionOriginal['tramite_id'];
        $resultado = $respuestaProlog['resultado'] ?? null;

        [$titulo, $mensaje] = match ($intencion) {
            'consultar_descripcion' => ['Descripción del trámite', 'Aquí tienes la información general del trámite.'],
            'consultar_requisitos' => ['Requisitos del trámite', 'Estos son los requisitos que debes cumplir.'],
            'consultar_documentos' => ['Documentos solicitados', 'Revisa la documentación y respaldos que debes presentar.'],
            'consultar_costo' => ['Costo del trámite', 'Información sobre aranceles del trámite.'],
            'consultar_plazo' => ['Plazos y fechas límite', 'Fechas y plazos reglamentarios para solicitar el trámite.'],
            'evaluar_elegibilidad' => ['Resultado de elegibilidad', 'Hemos evaluado tu información contra la normativa reglamentaria.'],
            default => ['Información de la consulta', 'Aquí tienes la información solicitada.'],
        };

        // Generar sugerencias de preguntas siguientes para el mismo trámite
        $sugerencias = array_values(array_filter([
            "{$tramiteId}__descripcion",
            "{$tramiteId}__requisitos",
            "{$tramiteId}__documentos",
            "{$tramiteId}__costo",
            "{$tramiteId}__plazo",
            "{$tramiteId}__elegibilidad",
        ], fn ($pId) => $pId !== ($peticionOriginal['pregunta_id'] ?? '')));

        return [
            'version' => 2,
            'estado' => 'respondido',
            'tipo' => str_replace('consultar_', '', $intencion),
            'tramite_id' => $tramiteId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'items' => $respuestaProlog['items'] ?? [],
            'documentos' => $respuestaProlog['documentos'] ?? [],
            'costo' => $respuestaProlog['costo'] ?? null,
            'plazo' => $respuestaProlog['plazo'] ?? null,
            'resultado' => $resultado,
            'aviso' => $respuestaProlog['aviso'] ?? null,
            'campos_pendientes' => [],
            'acciones' => [],
            'sugerencias' => $sugerencias,
            'meta' => [
                'kb_version' => $respuestaProlog['kb_version'] ?? null,
                'origen_datos' => $peticionOriginal['origen_datos'],
                'verificacion' => $peticionOriginal['origen_datos'] === 'sesion' ? 'institucional' : 'declarada',
            ],
        ];
    }

    protected function normalizarPerfilParaProlog(array $perfil): array
    {
        $normalizado = [];

        // Mapeo de alias de calificaciones/promedio al campo 'promedio' esperado por servidor.pl
        $promedio = $perfil['promedio'] ?? $perfil['gpa'] ?? $perfil['period_gpa'] ?? $perfil['nota'] ?? null;
        if ($promedio !== null && is_numeric($promedio)) {
            $normalizado['promedio'] = (float) $promedio;
        }

        if (isset($perfil['materia_id']) && is_string($perfil['materia_id'])) {
            $normalizado['materia_id'] = $perfil['materia_id'];
        } elseif (isset($perfil['subjects']) && is_string($perfil['subjects'])) {
            $normalizado['materia_id'] = $perfil['subjects'];
        }

        if (isset($perfil['asistencia']) && is_numeric($perfil['asistencia'])) {
            $normalizado['asistencia'] = (float) $perfil['asistencia'];
        } elseif (isset($perfil['attendance']) && is_numeric($perfil['attendance'])) {
            $normalizado['asistencia'] = (float) $perfil['attendance'];
        }

        if (isset($perfil['tipo_solicitante']) && is_string($perfil['tipo_solicitante'])) {
            $normalizado['tipo_solicitante'] = $perfil['tipo_solicitante'];
        }

        return $normalizado;
    }
}
