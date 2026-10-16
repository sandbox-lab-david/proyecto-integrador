<?php

namespace App\Services\Chatbot;

use App\Services\Chatbot\Prolog\PrologClient;
use App\Services\Chatbot\Prolog\PrologException;
use App\Models\MensajeChat;
use App\Models\User;

/**
 * Fachada pública del motor Prolog para uso de otros módulos (G2, G3 y chat interno).
 * Implementa los tres contratos definidos en el Excel (C72, C73 y C74):
 *   - C72: evaluar(Solicitud|string, bool|array, ...)
 *   - C73: ultimaEvaluacion(int|string, ?string)
 *   - C74: consultar(string, string|User|null)
 *
 * Los grupos G2/G3 deben depender exclusivamente de esta clase y NO de PrologClient
 * directamente, asegurando desacoplamiento del módulo Chatbot.
 */
class PrologService
{
    public function __construct(
        protected PrologClient $client,
        protected ChatbotCatalogoService $catalogoService,
    ) {}

    /**
     * C74: Consulta información de un trámite o procesa una consulta de texto.
     *
     * Admite dos modos de uso:
     *   1. Específico por trámite e intención: consultar('examen_recuperacion', 'consultar_requisitos')
     *   2. Texto libre (contrato C74 del Excel): consultar('¿Cuáles son los requisitos de recuperación?', $usuario)
     *
     * @param  string                  $tramiteOTexto Identificador del trámite o texto de la consulta
     * @param  string|User|null        $intencionOUsuario Intención (consultar_*) o usuario autenticado
     * @return array                   Respuesta validada del motor Prolog o respuesta estructurada del chat
     * @throws PrologException         Si el motor Prolog no está disponible o devuelve un error
     */
    public function consultar(string $tramiteOTexto, string|User|null $intencionOUsuario = null): array
    {
        // Si el segundo argumento es una intención reconocida (o empieza con 'consultar_')
        if (is_string($intencionOUsuario) && str_starts_with($intencionOUsuario, 'consultar_')) {
            return $this->client->consultar([
                'version'    => 2,
                'tramite_id' => $tramiteOTexto,
                'intencion'  => $intencionOUsuario,
            ]);
        }

        // Si se invoca en modo C74 (texto libre + usuario opcional)
        if (app()->bound(ChatbotOrchestratorService::class)) {
            $orchestrator = app(ChatbotOrchestratorService::class);
            return $orchestrator->procesarMensaje([
                'version'      => 2,
                'texto'        => $tramiteOTexto,
                'tramite_id'   => null,
                'origen_datos' => $intencionOUsuario ? 'sesion' : 'ninguno',
            ]);
        }

        // Fallback predeterminado a consulta de descripción
        return $this->client->consultar([
            'version'    => 2,
            'tramite_id' => $tramiteOTexto,
            'intencion'  => is_string($intencionOUsuario) ? $intencionOUsuario : 'consultar_descripcion',
        ]);
    }

    /**
     * C72: Evalúa si un estudiante cumple los requisitos de elegibilidad para un trámite.
     *
     * Compatible tanto con el modelo Solicitud (usado por G2 en paso 4 y G3 en revisión)
     * como con llamadas directas por identificador de trámite y perfil array.
     *
     * @param  mixed  $solicitudOTramiteId  Instancia de Solicitud u objeto similar, o string id del trámite
     * @param  mixed  $perfilOConVerificados Array de perfil (promedio, materia...) o booleano de datos verificados
     * @param  string $origenDatos          'manual' | 'sesion'
     * @param  bool   $conDatosVerificados  true si la asistente de facultad ya verificó los datos
     * @return array                        Respuesta validada con resultado.estado y resultado.motivos
     * @throws PrologException              Si el motor Prolog no responde o rechaza la consulta
     */
    public function evaluar(
        mixed $solicitudOTramiteId,
        mixed $perfilOConVerificados = [],
        string $origenDatos = 'manual',
        bool $conDatosVerificados = false
    ): array {
        $tramiteId = 'examen_recuperacion';
        $perfil = [];
        $verificados = $conDatosVerificados;

        // Si se recibe un objeto Solicitud (uso por G2 y G3)
        if (is_object($solicitudOTramiteId)) {
            // Extraer el identificador del trámite desde el modelo
            $tramiteId = $solicitudOTramiteId->tipoTramite->codigo
                ?? $solicitudOTramiteId->tipo_tramite_id
                ?? $solicitudOTramiteId->tramite
                ?? 'examen_recuperacion';

            // Extraer perfil desde el campo detalle y materias asociadas
            $detalle = (array) ($solicitudOTramiteId->detalle ?? []);
            $materias = $solicitudOTramiteId->materias ?? [];
            $primeraMateria = is_iterable($materias) ? (collect($materias)->first() ?? []) : [];

            $perfil = array_merge($detalle, [
                'promedio'   => $detalle['gpa_periodo'] ?? $detalle['promedio'] ?? $primeraMateria['nota'] ?? null,
                'asistencia' => $primeraMateria['asistencia'] ?? $detalle['asistencia'] ?? null,
                'materia_id' => $primeraMateria['materia_id'] ?? $primeraMateria['codigo'] ?? null,
            ]);

            if (is_bool($perfilOConVerificados)) {
                $verificados = $perfilOConVerificados;
            }
            $origenDatos = isset($solicitudOTramiteId->user_id) ? 'sesion' : 'manual';
        } else {
            $tramiteId = (string) $solicitudOTramiteId;
            $perfil = is_array($perfilOConVerificados) ? $perfilOConVerificados : [];
        }

        // Limpiar valores nulos para Prolog
        $perfilFiltrado = array_filter($perfil, fn ($v) => $v !== null && $v !== '');
        $perfilPayload = empty($perfilFiltrado) ? new \stdClass() : $perfilFiltrado;

        return $this->client->evaluar([
            'version'                => 2,
            'tramite_id'             => $tramiteId,
            'intencion'              => 'evaluar_elegibilidad',
            'fecha'                  => now()->toDateString(),
            'origen_datos'           => $origenDatos,
            'perfil'                 => $perfilPayload,
            'con_datos_verificados' => $verificados,
        ]);
    }

    /**
     * C73: Recupera el resultado de la última evaluación de elegibilidad registrada.
     *
     * Usado por G3 para mostrar la pre-validación en la bandeja de detalle de solicitudes,
     * o por el chatbot para recuperar evaluaciones recientes en la sesión.
     *
     * @param  int|string   $solicitudOId Identificador numérico de la solicitud o código de trámite
     * @param  string|null  $sessionToken Token de sesión si la consulta proviene del chatbot
     * @return array|null                 Último resultado o null si aún no existe registro
     */
    public function ultimaEvaluacion(int|string $solicitudOId, ?string $sessionToken = null): ?array
    {
        $query = MensajeChat::where('intencion', 'evaluar_elegibilidad');

        if ($sessionToken) {
            $query->whereHas('conversacion', fn ($q) => $q->where('session_id', $sessionToken));
        }

        if (is_string($solicitudOId)) {
            $query->where('tramite_id', $solicitudOId);
        }

        $ultimo = $query->latest('created_at')->first();

        return $ultimo?->payload;
    }

    /**
     * Devuelve los campos del formulario de elegibilidad para un trámite.
     * Útil para que G2/G3 construyan formularios dinámicos sin acoplarse al CatalogoService.
     *
     * @param  string $tramiteId  Identificador del trámite
     * @return array              Lista de campos con nombre, etiqueta, tipo y obligatoriedad
     */
    public function camposFormulario(string $tramiteId): array
    {
        return $this->catalogoService->obtenerCamposDeTramite($tramiteId);
    }
}
