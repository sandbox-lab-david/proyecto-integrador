<?php

namespace App\Modules\Chatbot\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\MensajeChat;
use App\Modules\Chatbot\Http\Requests\ChatbotMensajeRequest;
use App\Services\Chatbot\ChatbotCatalogoService;
use App\Services\Chatbot\ChatbotOrchestratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * delegando la lógica al orquestador del chatbot y persistiendo
     * la interacción en las tablas conversaciones y mensajes_chat.
     */
    public function mensaje(
        ChatbotMensajeRequest $request,
        ChatbotOrchestratorService $orchestrator
    ): JsonResponse {
        $inicio = hrtime(true);
        $datosValidados = $request->validated();

        $conversacion = $this->obtenerConversacionActiva($request);

        // Determinar texto visible para el mensaje del usuario
        $textoUsuario = $datosValidados['texto']
            ?? $this->resolverTextoPregunta($datosValidados['pregunta_id'] ?? null, $datosValidados['tramite_id'] ?? null)
            ?? 'Consulta sobre trámite';

        $mensajeUsuario = null;
        if ($conversacion) {
            try {
                $mensajeUsuario = MensajeChat::create([
                    'conversacion_id' => $conversacion->id,
                    'emisor'          => 'usuario',
                    'texto'           => $textoUsuario,
                    'intencion'       => $datosValidados['intencion'] ?? null,
                    'tramite_id'      => $datosValidados['tramite_id'] ?? null,
                    'resuelta'        => true,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Procesar en el orquestador
        $respuesta = $orchestrator->procesarMensaje($datosValidados);
        $tiempoMs = (int) round((hrtime(true) - $inicio) / 1e6);

        $esNoEntendido = ($respuesta['tipo'] ?? '') === 'no_entendido';
        $resuelta = !$esNoEntendido;

        // Si el bot no entendió la pregunta, marcamos la pregunta del usuario como no resuelta
        if ($esNoEntendido && $mensajeUsuario) {
            try {
                $mensajeUsuario->update(['resuelta' => false]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Guardar respuesta del bot con payload completo
        if ($conversacion) {
            try {
                MensajeChat::create([
                    'conversacion_id' => $conversacion->id,
                    'emisor'          => 'bot',
                    'texto'           => $respuesta['mensaje'] ?? 'Información solicitada.',
                    'intencion'       => $respuesta['tipo'] ?? null,
                    'tramite_id'      => $respuesta['tramite_id'] ?? $datosValidados['tramite_id'] ?? null,
                    'resuelta'        => $resuelta,
                    'tiempo_ms'       => $tiempoMs,
                    'payload'         => $respuesta,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json($respuesta, 200);
    }

    /**
     * Agrupa métricas por sesión y propietario, sin transferir conversaciones
     * anónimas a una cuenta ni mezclar cuentas que compartan navegador.
     */
    protected function obtenerConversacionActiva(Request $request): ?Conversacion
    {
        try {
            $userId = $request->user()?->id;
            $sessionId = $request->hasSession() ? $request->session()->getId() : null;

            if ($sessionId) {
                $query = Conversacion::where('session_id', $sessionId);
                if ($userId === null) {
                    $query->whereNull('user_id');
                } else {
                    $query->where('user_id', $userId);
                }
                $conversacion = $query->latest('updated_at')->first();
                if ($conversacion) {
                    return $conversacion;
                }
            }

            return Conversacion::create([
                'user_id'    => $userId,
                'session_id' => $sessionId,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * Reconstruye una etiqueta legible para el mensaje de usuario cuando pulsa un botón.
     */
    protected function resolverTextoPregunta(?string $preguntaId, ?string $tramiteId): ?string
    {
        if (!$preguntaId) {
            return null;
        }

        $sufijo = str_contains($preguntaId, '__') ? explode('__', $preguntaId)[1] : $preguntaId;

        $etiquetas = [
            'descripcion'  => '¿Qué es este trámite?',
            'requisitos'   => '¿Qué requisitos tiene?',
            'documentos'   => '¿Qué documentos necesito?',
            'costo'        => '¿Cuánto cuesta?',
            'plazo'        => '¿Hasta cuándo puedo pedirlo?',
            'elegibilidad' => '¿Puedo solicitar este trámite?',
        ];

        return $etiquetas[$sufijo] ?? null;
    }
}
