<?php

namespace App\Modules\Chatbot\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chatbot\Http\Requests\ChatbotMensajeRequest;
use App\Modules\Chatbot\Services\ChatbotCatalogoService;
use App\Modules\Chatbot\Services\ChatbotOrchestratorService;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    /**
     * Retorna el catálogo completo generado a partir de la base de conocimiento Prolog.
     * Incluye categorías, trámites y preguntas con sus especificaciones de campos.
     */
    public function catalogo(ChatbotCatalogoService $catalogoService): JsonResponse
    {
        $catalogo = $catalogoService->obtenerCatalogo();
        return response()->json($catalogo, 200);
    }

    /**
     * Procesa una consulta informativa o evaluación de elegibilidad
     * delegando la lógica al orquestador del chatbot.
     */
    public function mensaje(
        ChatbotMensajeRequest $request,
        ChatbotOrchestratorService $orchestrator
    ): JsonResponse {
        $datosValidados = $request->validated();
        $respuesta = $orchestrator->procesarMensaje($datosValidados);
        return response()->json($respuesta, 200);
    }

    /**
     * Endpoint de historial para el frontend.
     */
    public function historial(): JsonResponse
    {
        return response()->json([
            'mensajes' => [],
        ], 200);
    }
}
