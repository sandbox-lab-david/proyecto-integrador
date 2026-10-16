<?php

namespace App\Modules\Chatbot\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\MensajeChat;
use App\Modules\Chatbot\Http\Requests\ChatbotMensajeRequest;
use App\Services\Chatbot\ChatbotCatalogoService;
use App\Services\Chatbot\ChatbotOrchestratorService;
use Illuminate\Contracts\View\View;
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
     * Endpoint de historial para el frontend.
     * Lee los mensajes almacenados en la conversación activa.
     */
    public function historial(Request $request): JsonResponse
    {
        $conversacion = $this->obtenerConversacionActiva($request, crearSiNoExiste: false);

        if (!$conversacion) {
            return response()->json(['mensajes' => []], 200);
        }

        try {
            $mensajes = $conversacion->mensajes()
                ->orderBy('id', 'asc')
                ->get()
                ->map(function (MensajeChat $m) {
                    if ($m->emisor === 'usuario') {
                        return [
                            'emisor' => 'usuario',
                            'texto'  => $m->texto,
                        ];
                    }

                    return [
                        'emisor'    => 'bot',
                        'texto'     => $m->texto,
                        'respuesta' => $m->payload ?? [
                            'version' => 2,
                            'estado'  => 'respondido',
                            'tipo'    => $m->intencion ?? 'descripcion',
                            'mensaje' => $m->texto,
                        ],
                    ];
                });

            return response()->json(['mensajes' => $mensajes], 200);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['mensajes' => []], 200);
        }
    }

    /**
     * Borra el historial de la conversación actual.
     * El botón "Nueva consulta" del frontend llama a DELETE /chatbot/historial.
     */
    public function limpiarHistorial(Request $request): JsonResponse
    {
        try {
            $conversacion = $this->obtenerConversacionActiva($request, crearSiNoExiste: false);
            if ($conversacion) {
                $conversacion->delete();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(null, 204);
    }

    /**
     * Vista de reporte con las preguntas no resueltas por el chatbot.
     * Protegida con rol:admin.
     */
    public function reporte(): View
    {
        try {
            $noResueltas = MensajeChat::where('emisor', 'usuario')
                ->where('resuelta', false)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get();

            $preguntas = $noResueltas->map(fn (MensajeChat $m) => [
                'texto'   => $m->texto,
                'tramite' => $m->tramite_id,
                'fecha'   => $m->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $preguntas = collect([]);
        }

        return view('chatbot.reporte', ['preguntas' => $preguntas]);
    }

    /**
     * Obtiene la conversación activa de la sesión o usuario actual.
     */
    protected function obtenerConversacionActiva(Request $request, bool $crearSiNoExiste = true): ?Conversacion
    {
        try {
            $userId = $request->user()?->id;
            $sessionId = $request->session()->getId();

            // 1. Buscar por usuario autenticado si existe
            if ($userId) {
                $conversacion = Conversacion::where('user_id', $userId)->latest('updated_at')->first();
                if ($conversacion) {
                    return $conversacion;
                }
            }

            // 2. Buscar por session_id
            if ($sessionId) {
                $conversacion = Conversacion::where('session_id', $sessionId)->latest('updated_at')->first();
                if ($conversacion) {
                    if ($userId && !$conversacion->user_id) {
                        $conversacion->update(['user_id' => $userId]);
                    }
                    return $conversacion;
                }
            }

            if (!$crearSiNoExiste) {
                return null;
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
