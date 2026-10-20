<?php

namespace App\Services\Chatbot;

use App\Services\Chatbot\Prolog\PrologClient;

class ChatbotOrchestratorService
{
    public function __construct(
        protected PrologClient $prologClient,
        protected ChatbotCatalogoService $catalogoService,
    ) {}

    /**
     * Punto de entrada desde el controlador.
     * Acepta tanto el modo botón (contrato v2 completo) como el modo texto
     * libre ({version, texto, tramite_id}). En modo texto libre se infiere
     * la intención y, si no se reconoce, se devuelve no_entendido sin
     * llamar al motor Prolog.
     */
    public function procesarMensaje(array $datos): array
    {
        // Modo texto libre: normalizar a estructura equivalente al modo botón
        if (isset($datos['texto']) && !isset($datos['intencion'])) {
            if ($this->esConsultaEstadoSolicitud($datos['texto'])) {
                return $this->respuestaEstadoSolicitud($datos);
            }

            $tramiteDetectado = $datos['tramite_id'] ?? $this->detectarTramiteEnTexto($datos['texto']);
            $intencionInferida = $this->inferirIntencion($datos['texto']);

            if ($intencionInferida === null || $tramiteDetectado === null) {
                return $this->respuestaNoEntendido($tramiteDetectado, $datos['texto']);
            }

            // API-01: Si en texto libre se infiere una evaluación personal, exigir sesión
            $pruebaLocal = config('app.env') === 'local'
                && request()->routeIs('chatbot.pruebas.mensaje');
            if ($intencionInferida === 'evaluar_elegibilidad' && !auth()->check() && !$pruebaLocal) {
                return $this->respuestaRequiereSesion($tramiteDetectado);
            }

            $datos = [
                'version'       => $datos['version'],
                'pregunta_id'   => $tramiteDetectado . '__' . str_replace('consultar_', '', $intencionInferida),
                'tramite_id'    => $tramiteDetectado,
                'intencion'     => $intencionInferida,
                'origen_datos'  => 'ninguno',
                'datos_usuario' => [],
            ];
        }

        $intencion = $datos['intencion'];
        $tramiteId = $datos['tramite_id'];
        $perfil = $datos['datos_usuario'] ?? [];

        // API-02: Eliminar atribución de procedencia basada en el cliente.
        // Todo dato enviado en el cuerpo de la petición HTTP es estrictamente 'manual' y 'declarada'.
        $esEvaluacion = ($intencion === 'evaluar_elegibilidad');
        $origen = $esEvaluacion ? 'manual' : 'ninguno';
        $verificacion = 'declarada';

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
                        'origen_datos' => 'ninguno',
                        'verificacion' => 'declarada',
                    ],
                ];
            }
        }

        // Llamada al motor Prolog según la intención
        if ($intencion === 'evaluar_elegibilidad') {
            $perfilProlog = $this->normalizarPerfilParaProlog($perfil);
            $respuestaProlog = $this->prologClient->evaluar([
                'version'      => 2,
                'tramite_id'   => $tramiteId,
                'intencion'    => $intencion,
                'fecha'        => now()->toDateString(),
                'origen_datos' => 'manual',
                'perfil'       => $perfilProlog,
            ]);

            // API-02: Garantizar que evaluaciones manuales conserven advertencia de validez declarativa
            $avisoDeclarativo = 'Los datos evaluados fueron ingresados manualmente y tienen carácter declarativo. Para una resolución oficial, los requisitos serán validados contra los registros institucionales de la facultad.';
            $avisoExistente = $respuestaProlog['aviso'] ?? null;
            $respuestaProlog['aviso'] = $avisoExistente ? "{$avisoExistente} {$avisoDeclarativo}" : $avisoDeclarativo;
        } else {
            $respuestaProlog = $this->prologClient->consultar([
                'version'    => 2,
                'tramite_id' => $tramiteId,
                'intencion'  => $intencion,
            ]);
        }

        return $this->formatearRespuestaPublica($datos, $respuestaProlog, $origen, $verificacion);
    }

    protected function formatearRespuestaPublica(
        array $peticionOriginal,
        array $respuestaProlog,
        string $origen = 'ninguno',
        string $verificacion = 'declarada'
    ): array {
        $intencion = $peticionOriginal['intencion'];
        $tramiteId = $peticionOriginal['tramite_id'];
        $resultado = $respuestaProlog['resultado'] ?? null;
        $costo = $respuestaProlog['costo'] ?? null;
        $plazo = $respuestaProlog['plazo'] ?? null;
        $aviso = $respuestaProlog['aviso'] ?? null;

        // API-05: Representar explícitamente información desconocida, parcial o sujeta a revisión
        [$titulo, $mensaje] = match ($intencion) {
            'consultar_descripcion' => ['Descripción del trámite', 'Aquí tienes la información general del trámite.'],
            'consultar_requisitos' => ['Requisitos del trámite', 'Estos son los requisitos que debes cumplir.'],
            'consultar_documentos' => ['Documentos solicitados', 'Revisa la documentación y respaldos que debes presentar.'],
            'consultar_costo' => $costo !== null
                ? ['Costo del trámite', 'Información sobre aranceles del trámite.']
                : ['Costo por confirmar', 'El arancel o costo de este trámite se encuentra por confirmar con la Facultad y Secretaría.'],
            'consultar_plazo' => $plazo !== null
                ? ['Plazos y fechas límite', 'Fechas y plazos reglamentarios para solicitar el trámite.']
                : ['Plazos por confirmar', 'Las fechas límite y plazos reglamentarios de este trámite se encuentran por confirmar con la Facultad.'],
            'evaluar_elegibilidad' => match ($resultado['estado'] ?? null) {
                'cumple' => [
                    'Cumples los requisitos',
                    $verificacion === 'institucional'
                        ? 'Según los registros institucionales verificados, cumples con las condiciones reglamentarias para este trámite.'
                        : 'Con los datos declarados ingresados cumples preliminarmente las condiciones reglamentarias evaluadas. Este resultado es de carácter orientativo y no definitivo; la Facultad validará los registros oficiales al procesar tu solicitud.'
                ],
                'no_cumple' => ['No cumples los requisitos', 'Según la información evaluada, no alcanzas las condiciones reglamentarias para este trámite.'],
                'requiere_revision' => ['Revisión por la Facultad requerida', $this->determinarMensajeRevision($resultado)],
                default => ['Resultado de elegibilidad', 'Hemos evaluado tu información contra la normativa reglamentaria.'],
            },
            default => ['Información de la consulta', 'Aquí tienes la información solicitada.'],
        };

        // API-05: Si el costo o plazo fue consultado pero no está definido, asegurar un aviso explícito
        if ($intencion === 'consultar_costo' && $costo === null && empty($aviso)) {
            $costoTexto = $this->catalogoService->obtenerCostoTexto($tramiteId);
            $aviso = $costoTexto ?? 'El valor o arancel no está definido en el reglamento actual y debe ser confirmado directamente con la Facultad.';
        } elseif ($intencion === 'consultar_plazo' && $plazo === null && empty($aviso)) {
            $plazoTexto = $this->catalogoService->obtenerPlazoTexto($tramiteId);
            $aviso = $plazoTexto ?? 'Las fechas están sujetas al calendario académico y deben ser confirmadas con la Facultad.';
        }

        // Generar sugerencias de preguntas siguientes para el mismo trámite
        $sugerencias = array_values(array_filter([
            "{$tramiteId}__descripcion",
            "{$tramiteId}__requisitos",
            "{$tramiteId}__documentos",
            "{$tramiteId}__costo",
            "{$tramiteId}__plazo",
            "{$tramiteId}__elegibilidad",
        ], fn ($pId) => $pId !== ($peticionOriginal['pregunta_id'] ?? '')));

        // Construir acciones contextuales hacia el portal del estudiante
        $acciones = [];
        $slugAgenda = $this->catalogoService->obtenerIdentificadorAgenda($tramiteId);
        if ($slugAgenda !== null) {
            // Tabla de equivalencias entre hechos.pl y el formulario del estudiante (G2)
            $mapaCodigoFormulario = [
                'ayudante_catedra'    => 'ayudante',
                'examen_suficiencia'  => 'suficiencia',
                'examen_supletorio'   => 'supletorio',
                'examen_recuperacion' => 'recuperacion',
                'examen_gracia'       => 'gracia',
            ];
            $codigoFormulario = $mapaCodigoFormulario[$tramiteId] ?? $slugAgenda;

            $urlInicio = match ($codigoFormulario) {
                'recuperacion' => '/estudiante/solicitudes/recuperacion/nueva',
                'gracia'       => '/estudiante/solicitudes/gracia/nueva',
                'supletorio'   => '/estudiante/solicitudes/supletorio/nueva',
                default        => '/estudiante/solicitudes/nueva?tramite=' . $codigoFormulario,
            };
            $slugFicha = ($slugAgenda === 'recalificacion') ? 'recalificacion-examen' : $slugAgenda;

            $acciones[] = [
                'tipo'     => 'iniciar_solicitud',
                'etiqueta' => 'Iniciar solicitud en el portal',
                'url'      => $urlInicio,
            ];

            $acciones[] = [
                'tipo'     => 'url',
                'etiqueta' => 'Ver ficha informativa del trámite',
                'url'      => '/estudiante/tramites/' . $slugFicha,
            ];
        }

        $documentos = array_map(function ($d) {
            if (!is_array($d)) {
                return $d;
            }
            if (!isset($d['obligacion'])) {
                $d['obligacion'] = ($d['obligatorio'] ?? false) ? 'obligatorio' : 'por_confirmar';
            }
            return $d;
        }, $respuestaProlog['documentos'] ?? []);

        if (is_array($resultado)) {
            // El atributo con_datos_verificados debe ser false también cuando el origen sea 'sesion' o 'manual'.
            // Hoy los datos no provienen del SIAC institucional y no están verificados.
            $resultado['con_datos_verificados'] = ($origen === 'institucional' && $verificacion === 'institucional');
            if (isset($resultado['motivos']) && is_array($resultado['motivos'])) {
                $resultado['motivos'] = array_map(function ($m) {
                    if (is_string($m)) {
                        return ['codigo' => 'general', 'texto' => $m];
                    }
                    return $m;
                }, $resultado['motivos']);
            }
        }

        return [
            'version' => 2,
            'estado'  => 'respondido',
            'tipo'    => match ($intencion) {
                'evaluar_elegibilidad' => 'elegibilidad',
                default => str_replace('consultar_', '', $intencion),
            },
            'tramite_id'       => $tramiteId,
            'titulo'           => $titulo,
            'mensaje'          => $mensaje,
            'items'            => $respuestaProlog['items'] ?? [],
            'documentos'       => $documentos,
            'costo'            => $costo,
            'plazo'            => $plazo,
            'resultado'        => $resultado,
            'aviso'            => $aviso,
            'campos_pendientes'=> [],
            'acciones'         => $acciones,
            'sugerencias'      => $sugerencias,
            'meta'             => [
                'kb_version'   => $respuestaProlog['kb_version'] ?? null,
                'origen_datos' => $origen,
                'verificacion' => $verificacion,
            ],
        ];
    }

    protected function determinarMensajeRevision(?array $resultado): string
    {
        $codigos = array_column($resultado['motivos'] ?? [], 'codigo');
        if (in_array('condiciones_no_evaluadas', $codigos, true)) {
            return 'Solo se evaluó una parte de los requisitos con los datos declarados. Las condiciones restantes y la resolución definitiva deben ser confirmadas por la Facultad.';
        }
        if (in_array('evaluacion_no_disponible', $codigos, true)) {
            return 'Este trámite no cuenta con evaluación automática. Esta respuesta no determina cumplimiento definitivo ni rechazo; tu caso será revisado directamente por la Facultad.';
        }
        return 'Tu caso requiere revisión y autorización individual por parte de la Facultad según la normativa vigente.';
    }

    /**
     * Intenta mapear el texto libre del usuario a una de las intenciones
     * del contrato v2 buscando palabras clave. Devuelve null si no hay
     * coincidencia suficiente para proceder con confianza.
     */
    protected function inferirIntencion(string $texto): ?string
    {
        $texto = mb_strtolower($texto);

        $mapa = [
            'consultar_descripcion' => [
                'qué es', 'que es', 'descripcion', 'descripción', 'información general',
                'informacion general', 'de qué trata', 'de que trata', 'explica', 'cuéntame', 'cuentame',
            ],
            'consultar_requisitos' => [
                'requisito', 'condicion', 'condición', 'necesito para', 'puedo aplicar',
                'se necesita', 'exige', 'piden',
            ],
            'consultar_documentos' => [
                'documento', 'papel', 'archivo', 'adjunto', 'qué debo presentar',
                'que debo presentar', 'que necesito llevar', 'qué necesito llevar',
            ],
            'consultar_costo' => [
                'costo', 'precio', 'cuánto cuesta', 'cuanto cuesta', 'valor', 'arancel', 'pago',
            ],
            'consultar_plazo' => [
                'plazo', 'fecha', 'hasta cuándo', 'hasta cuando', 'límite', 'limite', 'vencimiento', 'cuándo puedo', 'cuando puedo',
            ],
            'evaluar_elegibilidad' => [
                'puedo', 'califico', 'soy elegible', 'aplico', 'me corresponde', 'cumplo',
                'tengo derecho', 'elegibilidad',
            ],
        ];

        foreach ($mapa as $intencion => $palabras) {
            foreach ($palabras as $clave) {
                if (str_contains($texto, $clave)) {
                    return $intencion;
                }
            }
        }

        return null;
    }

    /**
     * Intenta detectar el trámite mencionado en el texto libre.
     */
    protected function detectarTramiteEnTexto(string $texto): ?string
    {
        $texto = mb_strtolower($texto);

        $mapa = [
            'examen_recuperacion'     => ['recuperacion', 'recuperación'],
            'examen_gracia'           => ['gracia'],
            'examen_supletorio'       => ['supletorio'],
            'examen_suficiencia'      => ['suficiencia'],
            'recalificacion'          => ['recalificacion', 'recalificación'],
            'alcance_homologacion'    => ['alcance de homologacion', 'alcance de homologación'],
            'homologacion'            => ['homologacion', 'homologación'],
            'cambio_carrera'          => ['cambio de carrera', 'cambiar de carrera'],
            'cambio_modalidad'        => ['cambio de modalidad', 'cambiar de modalidad'],
            'cambio_malla'            => ['cambio de malla', 'cambiar de malla', 'pensum', 'pénsum'],
            'registro_extemporaneo'   => ['registro extemporaneo', 'registro extemporáneo', 'fuera de plazo'],
            'tercer_registro'         => ['tercer registro', 'tercera matricula', 'tercera matrícula'],
            'incompleto'              => ['incompleto'],
            'retiro_extemporaneo'     => ['retiro extemporaneo', 'retiro extemporáneo'],
            'retiro_materia'          => ['retiro de materia', 'retirar materia'],
            'retiro_carrera'          => ['retiro de carrera', 'retirar carrera'],
            'retiro_universidad'      => ['retiro de la universidad', 'retirar de la universidad'],
            'reincorporacion_carrera' => ['reincorporacion', 'reincorporación'],
            'ayudante_catedra'        => ['ayudante', 'ayudantia', 'ayudantía'],
            'person_to_person'        => ['person to person', 'tutoria', 'tutoría'],
        ];

        foreach ($mapa as $tramiteId => $claves) {
            foreach ($claves as $clave) {
                if (str_contains($texto, $clave)) {
                    return $tramiteId;
                }
            }
        }

        return null;
    }

    /**
     * Respuesta estándar cuando el chatbot no entiende el texto libre.
     * No llama al motor Prolog.
     */
    protected function respuestaNoEntendido(?string $tramiteId, string $textoOriginal): array
    {
        $sugerencias = $tramiteId ? [
            "{$tramiteId}__descripcion",
            "{$tramiteId}__requisitos",
            "{$tramiteId}__documentos",
            "{$tramiteId}__costo",
            "{$tramiteId}__plazo",
        ] : [];

        return [
            'version'          => 2,
            'estado'           => 'respondido',
            'tipo'             => 'no_entendido',
            'tramite_id'       => $tramiteId,
            'titulo'           => 'No entendí tu consulta',
            'mensaje'          => $tramiteId
                ? 'No pude identificar qué información necesitas sobre este trámite. Puedes usar los botones de abajo para consultar descripción, requisitos, documentos, costo o plazo.'
                : 'No entendí tu pregunta. Elige una categoría o escribe el nombre del trámite, por ejemplo «supletorio» o «retiro de materia».',
            'items'            => [],
            'documentos'       => [],
            'costo'            => null,
            'plazo'            => null,
            'resultado'        => null,
            'aviso'            => null,
            'campos_pendientes'=> [],
            'acciones'         => [],
            'sugerencias'      => $sugerencias,
            'meta' => [
                'kb_version'   => null,
                'origen_datos' => 'ninguno',
                'verificacion' => 'declarada',
            ],
        ];
    }

    /**
     * API-01: Respuesta cuando un usuario no autenticado solicita evaluar elegibilidad en texto libre.
     */
    protected function respuestaRequiereSesion(?string $tramiteId): array
    {
        return [
            'version'           => 2,
            'estado'            => 'requiere_datos',
            'tipo'              => 'elegibilidad',
            'tramite_id'        => $tramiteId,
            'titulo'            => 'Inicio de sesión requerido',
            'mensaje'           => 'Para realizar una evaluación personal de elegibilidad debes iniciar sesión con tu cuenta institucional.',
            'items'             => [],
            'documentos'        => [],
            'costo'             => null,
            'plazo'             => null,
            'resultado'         => null,
            'aviso'             => null,
            'campos_pendientes' => [],
            'acciones'          => [
                [
                    'tipo'     => 'url',
                    'etiqueta' => 'Iniciar sesión con cuenta institucional',
                    'url'      => '/login',
                ],
            ],
            'sugerencias'       => $tramiteId ? [
                "{$tramiteId}__descripcion",
                "{$tramiteId}__requisitos",
                "{$tramiteId}__documentos",
            ] : [],
            'meta'              => [
                'kb_version'   => null,
                'origen_datos' => 'ninguno',
                'verificacion' => 'declarada',
            ],
        ];
    }

    protected function normalizarPerfilParaProlog(array $perfil): array
    {
        $normalizado = [];

        // Mapeo de alias de calificaciones/promedio al campo 'promedio' esperado por servidor.pl
        $promedio = $perfil['promedio']
            ?? $perfil['gpa']
            ?? $perfil['period_gpa']
            ?? $perfil['subject_grade']
            ?? $perfil['gpa_general']
            ?? $perfil['current_grade']
            ?? $perfil['nota']
            ?? $perfil['grade']
            ?? null;
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

    /**
     * Determina si el texto corresponde a una consulta sobre el estado de solicitudes.
     */
    protected function esConsultaEstadoSolicitud(string $texto): bool
    {
        $texto = mb_strtolower($texto);
        $patrones = [
            'estado de mi solicitud',
            'estado de mis solicitudes',
            'mis solicitudes',
            'mi solicitud',
            'como va mi tramite',
            'cómo va mi trámite',
            'como va mi solicitud',
            'cómo va mi solicitud',
            'consultar estado',
            'ver mis solicitudes',
            'seguimiento de solicitud',
        ];

        foreach ($patrones as $p) {
            if (str_contains($texto, $p)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Respuesta estructurada con tipo 'estado_solicitud' para el frontend.
     */
    protected function respuestaEstadoSolicitud(array $datos): array
    {
        $usuario = auth()->user();
        $solicitudes = [];

        if ($usuario) {
            $titulo = 'Tus solicitudes registradas';
            $mensaje = 'Aquí tienes el estado de tus trámites en curso:';

            if (class_exists(\App\Models\Solicitud::class)) {
                try {
                    $solicitudes = \App\Models\Solicitud::where('user_id', $usuario->id)
                        ->latest('created_at')
                        ->limit(5)
                        ->get()
                        ->map(fn ($s) => [
                            'id'      => $s->id,
                            'tramite' => $s->tipoTramite->nombre ?? $s->tramite ?? ('Solicitud #' . $s->id),
                            'estado'  => $s->estado ?? 'En revisión',
                            'color'   => match (mb_strtolower($s->estado ?? '')) {
                                'aprobada', 'aprobado'  => 'verde',
                                'rechazada', 'rechazado' => 'rojo',
                                default                 => 'ambar',
                            },
                            'url' => '/estudiante/solicitudes/' . $s->id,
                        ])
                        ->toArray();
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            if (empty($solicitudes)) {
                $mensaje = 'No tienes solicitudes activas registradas en este momento.';
            }
        } else {
            $titulo = 'Estado de solicitudes';
            $mensaje = 'Para consultar el estado de tus trámites en curso, inicia sesión con tu cuenta institucional en el portal.';
        }

        return [
            'version'           => 2,
            'estado'            => 'respondido',
            'tipo'              => 'estado_solicitud',
            'tramite_id'        => null,
            'titulo'            => $titulo,
            'mensaje'           => $mensaje,
            'items'             => [],
            'documentos'        => [],
            'costo'             => null,
            'plazo'             => null,
            'resultado'         => null,
            'aviso'             => null,
            'campos_pendientes' => [],
            'solicitudes'       => $solicitudes,
            'acciones'          => [
                [
                    'tipo'     => 'url',
                    'etiqueta' => 'Ver mis solicitudes en el portal',
                    'url'      => '/estudiante/solicitudes',
                ],
            ],
            'sugerencias'       => [],
            'meta'              => [
                'kb_version'   => null,
                'origen_datos' => $usuario ? 'sesion' : 'ninguno',
                'verificacion' => 'declarada',
            ],
        ];
    }
}
