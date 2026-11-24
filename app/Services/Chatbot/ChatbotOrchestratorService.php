<?php

namespace App\Services\Chatbot;

use App\Services\Chatbot\Prolog\PrologClient;
use App\Services\Solicitudes\ReglasCampoSolicitud;

class ChatbotOrchestratorService
{
    public function __construct(protected PrologClient $prologClient, protected ChatbotCatalogoService $catalogoService) {}

    public function procesarMensaje(array $datos): array
    {
        if (isset($datos['texto'])) {
            if (preg_match('/mis solicitudes|mi solicitud|estado.*solicitud|seguimiento|c[oó]mo va mi/u', mb_strtolower($datos['texto']))) {
                return $this->base(null) + ['tipo' => 'estado_solicitud', 'titulo' => 'Mis solicitudes', 'mensaje' => 'Consulta el estado y las observaciones de tus trámites en Mis solicitudes.', 'acciones' => [['tipo' => 'url', 'etiqueta' => 'Ver mis solicitudes', 'url' => '/estudiante/solicitudes']]];
            }
            $detectado = $this->detectarTramiteEnTexto($datos['texto']);
            $id = $detectado ?? ($datos['tramite_id'] ?? null);
            if ($id === null || $id === 'ambiguo') {
                return $this->base(null) + ['tipo' => 'no_entendido', 'titulo' => 'Elige el trámite', 'mensaje' => 'Selecciona un trámite del catálogo para consultar sus requisitos, documentos y plazos.', 'acciones' => []];
            }
            $intencion = $this->inferirIntencion($datos['texto']);
            if ($id === 'examen_recuperacion' && preg_match('/gpa|365|comparar|calcular/u', mb_strtolower($datos['texto']))) {
                $intencion = 'comprobar_referencias';
            }
            if (preg_match('/qu[eé] (me falta|debo subir|archivos faltan)|verificar (archivos|documentos)|faltan.*(archivo|documento)/u', mb_strtolower($datos['texto']))) {
                $intencion = 'comprobar_referencias';
            }
            $sufijo = $intencion === 'comprobar_referencias' ? 'referencias' : str_replace('consultar_', '', $intencion);
            $datos = ['version' => 3, 'tramite_id' => $id, 'intencion' => $intencion, 'pregunta_id' => $id.'__'.$sufijo, 'datos_usuario' => []];
        }
        $id = $datos['tramite_id'];
        $intencion = $datos['intencion'];
        $esComprobacion = $intencion === 'comprobar_referencias';
        if ($esComprobacion && ! auth()->check() && ! (app()->isLocal() && request()->routeIs('chatbot.pruebas.mensaje'))) {
            return $this->base($id) + ['tipo' => 'sesion_requerida', 'titulo' => 'Inicia sesión', 'mensaje' => 'Inicia sesión para comparar tus datos. Puedes consultar los requisitos sin completar esta comprobación.', 'acciones' => [['tipo' => 'url', 'etiqueta' => 'Iniciar sesión', 'url' => '/login']]];
        }
        $perfil = array_filter($datos['datos_usuario'] ?? [], fn ($v) => $v !== null && $v !== '');
        if (isset($perfil['gpa_general'])) {
            $perfil['gpa_general'] = (float) $perfil['gpa_general'];
        }
        if (isset($perfil['documentos_subidos']) && is_array($perfil['documentos_subidos'])) {
            $perfil['documentos_subidos'] = array_values($perfil['documentos_subidos']);
        }
        $entrada = ['version' => 3, 'tramite_id' => $id, 'intencion' => $intencion];
        $r = $esComprobacion
            ? $this->prologClient->evaluar($entrada + ['fecha' => now(ReglasCampoSolicitud::zonaHoraria())->toDateString(), 'origen_datos' => 'manual', 'perfil' => $perfil])
            : $this->prologClient->consultar($entrada);
        $respuesta = array_replace($this->base($id), $r);
        $respuesta['tipo'] = $esComprobacion ? 'referencias' : str_replace('consultar_', '', $intencion);
        $respuesta['titulo'] = match ($intencion) {
            'consultar_descripcion' => 'Sobre este trámite','consultar_requisitos' => 'Requisitos del trámite','consultar_documentos' => 'Documentos para tu solicitud','consultar_costo' => 'Costo del trámite','consultar_plazo' => 'Plazos del trámite',default => 'Referencias para tu trámite'
        };
        $verifDoc = collect($r['comprobaciones'] ?? [])->firstWhere('codigo', 'verificacion_documentos');
        $respuesta['mensaje'] = $verifDoc['texto'] ?? ($esComprobacion ? 'Puedes usar estas referencias para orientarte. La facultad revisa tu solicitud.' : 'Aquí tienes la información del trámite.');
        $respuesta['meta'] = ['kb_version' => $r['kb_version'], 'origen_datos' => $esComprobacion ? 'manual' : 'ninguno', 'verificacion' => 'declarada'];
        $slug = $this->catalogoService->obtenerIdentificadorAgenda($id);
        $codigo = ['examen_recuperacion' => 'recuperacion', 'examen_gracia' => 'gracia', 'examen_supletorio' => 'supletorio', 'examen_suficiencia' => 'suficiencia', 'ayudante_catedra' => 'ayudante'][$id] ?? $slug;
        $respuesta['acciones'] = [['tipo' => 'iniciar_solicitud', 'etiqueta' => 'Iniciar solicitud', 'url' => '/estudiante/solicitudes/nueva?tramite='.$codigo], ['tipo' => 'url', 'etiqueta' => 'Ver ficha del trámite', 'url' => '/estudiante/tramites/'.($slug === 'recalificacion' ? 'recalificacion-examen' : $slug)]];
        $respuesta['sugerencias'] = array_column(array_values(array_filter($this->catalogoService->obtenerCatalogo()['preguntas'], fn ($p) => $p['tramite_id'] === $id && $p['id'] !== ($datos['pregunta_id'] ?? ''))), 'id');
        if ($esComprobacion && empty($perfil) && ! ($datos['mostrar_referencias'] ?? false)) {
            $respuesta['estado'] = 'requiere_datos';
            $respuesta['campos_pendientes'] = $this->catalogoService->obtenerCamposDeTramite($id);
            $referencias = $this->catalogoService->referenciasRecuperacion();
            $respuesta['mensaje'] = "La referencia de GPA general es {$referencias['gpa_general_minimo']} y el intervalo es de {$referencias['intervalo_recuperacion_dias']} días desde el último examen. Si quieres, introduce uno o ambos datos para calcular la comparación. También puedes continuar sin completarlos.";
        }

        return $respuesta;
    }

    private function base(?string $id): array
    {
        return ['version' => 3, 'estado' => 'respondido', 'tramite_id' => $id, 'items' => [], 'documentos' => [], 'costo' => null, 'plazo' => null, 'resultado' => null, 'comprobaciones' => [], 'aviso' => null, 'campos_pendientes' => [], 'sugerencias' => [], 'meta' => ['kb_version' => null, 'origen_datos' => 'ninguno', 'verificacion' => 'declarada']];
    }

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
            'consultar_requisitos_personales' => [
                'puedo', 'califico', 'soy elegible', 'aplico', 'me corresponde', 'cumplo',
                'tengo derecho', 'elegibilidad',
            ],
        ];

        foreach ($mapa as $intencion => $palabras) {
            foreach ($palabras as $clave) {
                if (str_contains($texto, $clave)) {
                    return $intencion === 'consultar_requisitos_personales' ? 'consultar_requisitos' : $intencion;
                }
            }
        }

        return 'consultar_descripcion';
    }

    /**
     * Intenta detectar el trámite mencionado en el texto libre.
     */
    protected function detectarTramiteEnTexto(string $texto): ?string
    {
        $texto = mb_strtolower($texto);

        $mapa = [
            'examen_recuperacion' => ['recuperacion', 'recuperación'],
            'examen_gracia' => ['gracia'],
            'examen_supletorio' => ['supletorio'],
            'examen_suficiencia' => ['suficiencia'],
            'recalificacion' => ['recalificacion', 'recalificación'],
            'alcance_homologacion' => ['alcance de homologacion', 'alcance de homologación'],
            'homologacion' => ['homologacion', 'homologación', 'homologar'],
            'cambio_carrera' => ['cambio de carrera', 'cambiar de carrera'],
            'cambio_modalidad' => ['cambio de modalidad', 'cambiar de modalidad'],
            'cambio_malla' => ['cambio de malla', 'cambiar de malla', 'pensum', 'pénsum'],
            'registro_extemporaneo' => ['registro extemporaneo', 'registro extemporáneo', 'fuera de plazo'],
            'tercer_registro' => ['tercer registro', 'tercera matricula', 'tercera matrícula'],
            'incompleto' => ['incompleto'],
            'retiro_extemporaneo' => ['retiro extemporaneo', 'retiro extemporáneo'],
            'retiro_materia' => ['retiro de materia', 'retirar materia', 'retirar una materia'],
            'retiro_carrera' => ['retiro de carrera', 'retirar carrera'],
            'retiro_universidad' => ['retiro de la universidad', 'retirar de la universidad'],
            'reincorporacion_carrera' => ['reincorporacion', 'reincorporación'],
            'ayudante_catedra' => ['ayudante', 'ayudantia', 'ayudantía'],
            'person_to_person' => ['person to person', 'tutoria', 'tutoría'],
        ];

        $encontrados = [];
        foreach ($mapa as $tramiteId => $claves) {
            foreach ($claves as $clave) {
                if (str_contains($texto, $clave)) {
                    $encontrados[] = $tramiteId;
                    break;
                }
            }
        }
        if (in_array('alcance_homologacion', $encontrados, true)) {
            $encontrados = array_diff($encontrados, ['homologacion']);
        }

        return count($encontrados) === 1 ? array_values($encontrados)[0] : (count($encontrados) > 1 ? 'ambiguo' : null);
    }
}
