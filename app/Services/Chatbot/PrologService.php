<?php

namespace App\Services\Chatbot;

use App\Services\Chatbot\Prolog\PrologClient;
use App\Services\Chatbot\Prolog\PrologException;
use App\Models\User;
use InvalidArgumentException;

/**
 * Fachada pública del motor Prolog para uso de otros módulos (G2, G3 y chat interno).
 * Expone consultas y orientaciones del motor (C72 y C74):
 *   - C72: evaluar(Solicitud|string, bool|array, ...)
 *   - C74: consultar(string, string|User|null)
 *
 * No conserva resultados por solicitud ni cambia estados de solicitudes.
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
     * C72: Obtiene una orientación inmediata sobre elegibilidad.
     *
     * Compatible tanto con el modelo Solicitud (usado por G2 en paso 4 y G3 en revisión)
     * como con llamadas directas por identificador de trámite y perfil array.
     *
     * @param  mixed  $solicitudOTramiteId  Instancia de Solicitud u objeto similar, o string id del trámite
     * @param  mixed  $perfilOConVerificados Perfil o indicador histórico; este último no acredita datos
     * @param  string $origenDatos          Argumento histórico; los datos de esta entrada son declarados
     * @param  bool   $conDatosVerificados  Argumento histórico; no se transmite al motor
     * @return array                        Respuesta validada con resultado.estado y resultado.motivos
     * @throws PrologException              Si el motor Prolog no responde o rechaza la consulta
     */
    public function evaluar(
        mixed $solicitudOTramiteId,
        mixed $perfilOConVerificados = [],
        string $origenDatos = 'manual',
        bool $conDatosVerificados = false
    ): array {
        $tramiteId = null;
        $perfil = [];

        // Si se recibe un objeto Solicitud (uso por G2 y G3)
        if (is_object($solicitudOTramiteId)) {
            // Extraer el identificador del trámite desde el modelo
            $tramiteId = $solicitudOTramiteId->tipoTramite->codigo
                ?? $solicitudOTramiteId->tramite
                ?? null;

            // Extraer perfil desde el campo detalle y materias asociadas
            $detalle = (array) ($solicitudOTramiteId->detalle ?? []);
            $materias = $solicitudOTramiteId->materias ?? [];
            $primeraMateria = is_iterable($materias) ? (collect($materias)->first() ?? []) : [];

            $perfil = array_merge($detalle, [
                'promedio'   => $detalle['gpa_periodo'] ?? $detalle['promedio'] ?? $primeraMateria['nota'] ?? null,
                'asistencia' => $primeraMateria['asistencia'] ?? $detalle['asistencia'] ?? null,
                'materia_id' => $primeraMateria['materia_id'] ?? $primeraMateria['codigo'] ?? null,
            ]);

        } else {
            $tramiteId = $solicitudOTramiteId;
            $perfil = is_array($perfilOConVerificados) ? $perfilOConVerificados : [];
        }

        $idsValidos = array_column($this->catalogoService->obtenerCatalogo()['tramites'], 'id');
        if (!is_string($tramiteId) || !in_array($tramiteId, $idsValidos, true)) {
            throw new InvalidArgumentException('El trámite no tiene un identificador válido para el chatbot.');
        }

        // El contrato HTTP del motor acepta solo estos cuatro campos.
        $perfilFiltrado = array_intersect_key($perfil, array_flip([
            'materia_id', 'promedio', 'asistencia', 'tipo_solicitante',
        ]));
        $perfilFiltrado = array_filter($perfilFiltrado, fn ($v) => $v !== null && $v !== '');
        foreach (['promedio', 'asistencia'] as $campo) {
            if (array_key_exists($campo, $perfilFiltrado)) {
                $valor = $perfilFiltrado[$campo];
                if (!is_numeric($valor) || is_bool($valor) || $valor < 0 || $valor > 100) {
                    throw new InvalidArgumentException("El campo {$campo} debe ser un número entre 0 y 100.");
                }
                $perfilFiltrado[$campo] = (float) $valor;
            }
        }
        foreach (['materia_id', 'tipo_solicitante'] as $campo) {
            if (array_key_exists($campo, $perfilFiltrado)) {
                if ($campo === 'materia_id' && is_int($perfilFiltrado[$campo])) {
                    $perfilFiltrado[$campo] = (string) $perfilFiltrado[$campo];
                }
                if (!is_string($perfilFiltrado[$campo]) || trim($perfilFiltrado[$campo]) === '') {
                    throw new InvalidArgumentException("El campo {$campo} debe ser texto no vacío.");
                }
            }
        }
        $perfilPayload = empty($perfilFiltrado) ? new \stdClass() : $perfilFiltrado;

        return $this->client->evaluar([
            'version'      => 2,
            'tramite_id'   => $tramiteId,
            'intencion'    => 'evaluar_elegibilidad',
            'fecha'        => now()->toDateString(),
            'origen_datos' => 'manual',
            'perfil'       => $perfilPayload,
        ]);
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
