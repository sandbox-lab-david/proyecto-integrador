<?php

namespace App\Services\Chatbot;

class ChatbotCatalogoService
{
    protected string $kbPath;
    protected ?array $catalogoCache = null;

    protected array $categoriasDefinidas = [
        [
            'id' => 'evaluaciones',
            'nombre' => 'Evaluaciones',
            'descripcion' => 'Recuperación, gracia, supletorio, suficiencia, recalificación',
        ],
        [
            'id' => 'homologacion',
            'nombre' => 'Homologación',
            'descripcion' => 'Reconocer materias aprobadas y alcance',
        ],
        [
            'id' => 'cambios_academicos',
            'nombre' => 'Cambios académicos',
            'descripcion' => 'Carrera, modalidad o malla',
        ],
        [
            'id' => 'registro',
            'nombre' => 'Registro',
            'descripcion' => 'Fuera de plazo, tercer registro, incompleto',
        ],
        [
            'id' => 'retiros_continuidad',
            'nombre' => 'Retiros y continuidad',
            'descripcion' => 'Retiros y reincorporación',
        ],
        [
            'id' => 'apoyo_academico',
            'nombre' => 'Apoyo académico',
            'descripcion' => 'Ayudantía de cátedra, person to person',
        ],
    ];

    protected array $mapeoCategorias = [
        'examen_recuperacion' => 'evaluaciones',
        'examen_gracia' => 'evaluaciones',
        'examen_supletorio' => 'evaluaciones',
        'examen_suficiencia' => 'evaluaciones',
        'recalificacion' => 'evaluaciones',
        'homologacion' => 'homologacion',
        'alcance_homologacion' => 'homologacion',
        'cambio_carrera' => 'cambios_academicos',
        'cambio_modalidad' => 'cambios_academicos',
        'cambio_malla' => 'cambios_academicos',
        'registro_extemporaneo' => 'registro',
        'tercer_registro' => 'registro',
        'incompleto' => 'registro',
        'retiro_materia' => 'retiros_continuidad',
        'retiro_carrera' => 'retiros_continuidad',
        'retiro_universidad' => 'retiros_continuidad',
        'retiro_extemporaneo' => 'retiros_continuidad',
        'reincorporacion_carrera' => 'retiros_continuidad',
        'ayudante_catedra' => 'apoyo_academico',
        'person_to_person' => 'apoyo_academico',
    ];

    public function __construct(?string $kbPath = null)
    {
        $this->kbPath = $kbPath ?? base_path('prolog/kb/hechos.pl');
    }

    public function obtenerCatalogo(): array
    {
        if ($this->catalogoCache !== null) {
            return $this->catalogoCache;
        }

        $tramites = $this->parsearTramites();
        $camposPorTramite = $this->parsearCamposFormulario();

        $preguntas = [];
        $intenciones = [
            ['sufijo' => 'descripcion', 'intencion' => 'consultar_descripcion', 'texto' => '¿Qué es este trámite?'],
            ['sufijo' => 'requisitos', 'intencion' => 'consultar_requisitos', 'texto' => '¿Qué requisitos tiene?'],
            ['sufijo' => 'documentos', 'intencion' => 'consultar_documentos', 'texto' => '¿Qué documentos necesito?'],
            ['sufijo' => 'costo', 'intencion' => 'consultar_costo', 'texto' => '¿Cuánto cuesta?'],
            ['sufijo' => 'plazo', 'intencion' => 'consultar_plazo', 'texto' => '¿Hasta cuándo puedo pedirlo?'],
            ['sufijo' => 'elegibilidad', 'intencion' => 'evaluar_elegibilidad', 'texto' => '¿Puedo solicitar este trámite?'],
        ];

        foreach ($tramites as $t) {
            $tId = $t['id'];
            $camposForm = $camposPorTramite[$tId] ?? [];

            foreach ($intenciones as $item) {
                $esElegibilidad = ($item['intencion'] === 'evaluar_elegibilidad');
                $preguntas[] = [
                    'id' => "{$tId}__{$item['sufijo']}",
                    'tramite_id' => $tId,
                    'texto' => $item['texto'],
                    'intencion' => $item['intencion'],
                    'origenes_permitidos' => $esElegibilidad ? ['manual', 'sesion'] : ['ninguno'],
                    'campos' => $esElegibilidad ? $camposForm : [],
                ];
            }
        }

        $this->catalogoCache = [
            'version' => 2,
            'categorias' => $this->categoriasDefinidas,
            'tramites' => $tramites,
            'preguntas' => $preguntas,
        ];

        return $this->catalogoCache;
    }

    public function obtenerCamposDeTramite(string $tramiteId): array
    {
        $campos = $this->parsearCamposFormulario();
        return $campos[$tramiteId] ?? [];
    }

    /**
     * Busca una pregunta por su identificador en el catálogo.
     */
    public function obtenerPregunta(string $preguntaId): ?array
    {
        $catalogo = $this->obtenerCatalogo();
        foreach ($catalogo['preguntas'] as $p) {
            if ($p['id'] === $preguntaId) {
                return $p;
            }
        }
        return null;
    }

    /**
     * API-04: Devuelve los nombres de campos permitidos para un trámite, incluyendo
     * los nombres base de hechos.pl y sus alias aceptados por el sistema.
     *
     * @return array<int, string>
     */
    public function obtenerNombresCamposValidosPorTramite(string $tramiteId): array
    {
        $campos = $this->obtenerCamposDeTramite($tramiteId);
        $permitidos = [];

        $mapaAlias = [
            'subjects'             => ['materia_id', 'subjects'],
            'period_gpa'           => ['promedio', 'gpa', 'period_gpa', 'nota', 'grade'],
            'gpa_general'          => ['promedio', 'gpa', 'gpa_general', 'nota', 'grade'],
            'subject_grade'        => ['promedio', 'nota', 'subject_grade', 'grade'],
            'current_grade'        => ['promedio', 'nota', 'current_grade', 'grade'],
            'attendance'           => ['asistencia', 'attendance'],
            'attended_as_listener' => ['listener', 'attended_as_listener'],
            'new_code'             => ['new_code'],
            'last_recovery_date'   => ['fecha_ultima_recuperacion', 'last_recovery_date'],
            'reason'               => ['reason', 'motivo'],
        ];

        foreach ($campos as $c) {
            $nom = $c['nombre'];
            $permitidos[] = $nom;
            if (isset($mapaAlias[$nom])) {
                $permitidos = array_merge($permitidos, $mapaAlias[$nom]);
            }
        }

        return array_values(array_unique($permitidos));
    }

    /**
     * Devuelve el slug del portal Agenda Anny para un trámite (ej. "examen-recuperacion").
     * Se usa para construir la URL de acción hacia el G2.
     * Retorna null si el trámite no tiene identificador definido en hechos.pl.
     */
    public function obtenerIdentificadorAgenda(string $tramiteId): ?string
    {
        if (!file_exists($this->kbPath)) {
            return null;
        }
        $contenido = file_get_contents($this->kbPath);
        // Coincide con identificador_agenda(tramite_id, "slug-del-portal").
        if (preg_match('/identificador_agenda\(' . preg_quote($tramiteId, '/') . ',\s*"([^"]+)"\)\./u', $contenido, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Devuelve el texto informativo de costo registrado en hechos.pl para un trámite.
     */
    public function obtenerCostoTexto(string $tramiteId): ?string
    {
        if (!file_exists($this->kbPath)) {
            return null;
        }
        $contenido = file_get_contents($this->kbPath);
        if (preg_match('/costo_texto\(' . preg_quote($tramiteId, '/') . ',\s*"([^"]+)"\)\./u', $contenido, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Devuelve el texto informativo de plazo registrado en hechos.pl para un trámite.
     */
    public function obtenerPlazoTexto(string $tramiteId): ?string
    {
        if (!file_exists($this->kbPath)) {
            return null;
        }
        $contenido = file_get_contents($this->kbPath);
        if (preg_match('/plazo_texto\(' . preg_quote($tramiteId, '/') . ',\s*"([^"]+)"\)\./u', $contenido, $m)) {
            return $m[1];
        }
        return null;
    }

    protected function parsearTramites(): array
    {
        if (!file_exists($this->kbPath)) {
            return [];
        }

        $contenido = file_get_contents($this->kbPath);
        // Coincide con tramite(identificador, "Nombre").
        preg_match_all('/tramite\(([a-z0-9_]+),\s*"([^"]+)"\)\./u', $contenido, $matches, PREG_SET_ORDER);

        $resultado = [];
        foreach ($matches as $m) {
            $id = $m[1];
            $nombre = $m[2];
            $categoriaId = $this->mapeoCategorias[$id] ?? 'evaluaciones';

            $resultado[] = [
                'id' => $id,
                'nombre' => $nombre,
                'categoria_id' => $categoriaId,
            ];
        }

        return $resultado;
    }

    protected function parsearCamposFormulario(): array
    {
        if (!file_exists($this->kbPath)) {
            return [];
        }

        $contenido = file_get_contents($this->kbPath);
        // Coincide con campo_formulario(tramite, nombre_campo, "Etiqueta", obligatoriedad).
        preg_match_all('/campo_formulario\(([a-z0-9_]+),\s*([a-z0-9_]+),\s*"([^"]+)",\s*([a-z0-9_]+)\)\./u', $contenido, $matches, PREG_SET_ORDER);


        $camposPorTramite = [];
        foreach ($matches as $m) {
            $tramiteId = $m[1];
            $nombreCampo = $m[2];
            $etiqueta = $m[3];
            $obligatorio = ($m[4] === 'obligatorio' || $m[4] === 'requerido');

            $tipo = 'texto';
            $min = null;
            $max = null;
            $opciones = [];

            if (str_contains($nombreCampo, 'fecha') || str_contains($nombreCampo, 'date')) {
                $tipo = 'fecha';
            } elseif (
                str_contains($nombreCampo, 'gpa') ||
                str_contains($nombreCampo, 'grade') ||
                str_contains($nombreCampo, 'nota') ||
                str_contains($nombreCampo, 'attendance') ||
                str_contains($nombreCampo, 'promedio')
            ) {
                $tipo = 'numero';
                $min = 0;
                $max = 100;
            } elseif (
                str_contains($nombreCampo, 'listener') ||
                str_contains($nombreCampo, 'new_code') ||
                str_contains($nombreCampo, 'fraude')
            ) {
                $tipo = 'booleano';
            } elseif ($nombreCampo === 'subjects' || $nombreCampo === 'materia_id') {
                $tipo = 'seleccion';
                $opciones = $this->obtenerOpcionesMaterias();
            }

            $camposPorTramite[$tramiteId][] = [
                'nombre' => $nombreCampo,
                'etiqueta' => $etiqueta,
                'tipo' => $tipo,
                'requerido' => $obligatorio,
                'min' => $min,
                'max' => $max,
                'opciones' => $opciones,
            ];
        }

        return $camposPorTramite;
    }

    protected function obtenerOpcionesMaterias(): array
    {
        $opciones = [];

        if (class_exists(\App\Models\Materia::class)) {
            try {
                $materiasBd = \App\Models\Materia::select('codigo', 'nombre')->get();
                foreach ($materiasBd as $m) {
                    $opciones[] = [
                        'valor'    => $m->codigo,
                        'etiqueta' => "{$m->codigo} - {$m->nombre}",
                    ];
                }
            } catch (\Throwable) {
            }
        }

        if (empty($opciones) && class_exists(\App\Services\Solicitudes\TramitesSimulados::class)) {
            try {
                $simulados = app(\App\Services\Solicitudes\TramitesSimulados::class)->materias();
                foreach ($simulados as $m) {
                    $codigo = $m['codigo'] ?? $m['id'];
                    $nombre = $m['nombre'] ?? $codigo;
                    $opciones[] = [
                        'valor'    => $codigo,
                        'etiqueta' => "{$codigo} - {$nombre}",
                    ];
                }
            } catch (\Throwable) {
            }
        }

        return $opciones;
    }
}
