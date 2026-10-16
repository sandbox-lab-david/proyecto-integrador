<?php

namespace App\Services\Solicitudes;

/**
 * Datos de prueba del formulario de solicitudes.
 *
 * Reemplaza temporalmente a TramiteCatalogoService (G4) y a
 * CatalogoAcademicoService (G1). Cuando esos servicios existan,
 * el controlador los usará y esta clase se podrá borrar.
 */
class TramitesSimulados
{
    private const ESTADOS_MATERIA = [
        'aprobada' => 'Aprobada',
        'reprobada' => 'Reprobada',
        'semestre_actual' => 'Semestre actual',
        'retirada' => 'Retirada',
    ];

    public function buscar(string $codigo): ?array
    {
        return $this->tramites()[$codigo] ?? $this->otrosTramites()[$codigo] ?? null;
    }

    private function otrosTramites(): array
    {
        // Configuración de demostración: el catálogo definitivo corresponde a G4.
        $campo = fn ($nombre, $etiqueta, $obligatorio = true, $tipo = 'texto') => [
            'nombre' => $nombre, 'etiqueta' => $etiqueta,
            'obligatorio' => $obligatorio, 'tipo' => $tipo,
        ];
        $motivo = $campo('motivo', 'Motivo de la solicitud', true, 'texto_largo');
        $definiciones = [
            'alcance-homologacion' => ['Alcance de homologación', 10, [$motivo, $campo('documento_faltante', 'Documento que faltó')]],
            'ayudante' => ['Ayudante de cátedra', 10, [$campo('periodo', 'Periodo'), $campo('anio', 'Año'), $campo('gpa_general', 'GPA general', false), $campo('motivacion', 'Motivación', false, 'texto_largo')]],
            'cambio-carrera' => ['Cambio de carrera', 0, [$campo('carrera_destino', 'Carrera de destino'), $campo('nuevo_codigo', '¿Solicitas nuevo código estudiantil? (sí/no)', false), $motivo]],
            'cambio-malla' => ['Cambio de malla / pénsum', 0, [$campo('pensum_actual', 'Pénsum actual'), $campo('malla_destino', 'Malla de destino'), $campo('motivo', 'Motivo', false, 'texto_largo')]],
            'cambio-modalidad' => ['Cambio de modalidad', 0, [$campo('modalidad_actual', 'Modalidad actual'), $campo('modalidad_destino', 'Modalidad solicitada'), $campo('carrera_destino', 'Carrera de destino', false), $motivo]],
            'suficiencia' => ['Examen de suficiencia', 10, [$campo('tipo_respaldo', 'Tipo de respaldo'), $campo('fundamento', 'Fundamento', true, 'texto_largo')]],
            'homologacion' => ['Homologación', 10, [$campo('institucion', 'Institución de procedencia'), $campo('carrera_destino', 'Carrera de destino'), $campo('informacion', 'Información adicional', false, 'texto_largo')]],
            'incompleto' => ['Incompleto', 10, [$campo('tipo_causa', 'Tipo de causa'), $campo('detalle', 'Detalle de la causa', true, 'texto_largo')]],
            'person-to-person' => ['Person to Person', 10, [$campo('motivo', 'Motivo', false, 'texto_largo')]],
            'recalificacion' => ['Recalificación de examen', 10, [$campo('evaluacion', 'Evaluación'), $campo('publicacion_fecha', 'Fecha de publicación de la nota', true, 'fecha'), $campo('publicacion_hora', 'Hora de publicación de la nota'), $campo('reclamo', 'Puntos del reclamo', true, 'texto_largo')]],
            'registro-extemporaneo' => ['Registro extemporáneo', 10, [$campo('oyente', '¿Asististe como oyente? (sí/no)'), $motivo]],
            'reincorporacion' => ['Reincorporación a carrera', 0, [$campo('ultimo_periodo', 'Último periodo cursado', false), $motivo]],
            'retiro-carrera' => ['Retiro de carrera', 0, [$motivo]],
            'retiro-materia' => ['Retiro de materia', 10, [$campo('semana', 'Semana del periodo', false), $campo('motivo', 'Motivo', false, 'texto_largo')]],
            'retiro-universidad' => ['Retiro de universidad', 0, [$motivo]],
            'retiro-extemporaneo' => ['Retiro extemporáneo', 10, [$campo('motivo', 'Motivo excepcional', true, 'texto_largo')]],
            'tercer-registro' => ['Tercer registro', 10, []],
        ];
        $resultado = [];
        foreach ($definiciones as $codigo => [$nombre, $maximo, $campos]) {
            $porMateria = [];
            if ($codigo === 'tercer-registro') {
                foreach ([1, 2] as $registro) {
                    $porMateria[] = $campo('registro_'.$registro.'_anio', 'Año del registro '.$registro);
                    $porMateria[] = $campo('registro_'.$registro.'_periodo', 'Periodo del registro '.$registro);
                    $porMateria[] = $campo('registro_'.$registro.'_nota', 'Nota del registro '.$registro, true, 'numero') + ['min' => 0, 'max' => 100];
                }
            }
            if ($codigo === 'ayudante') {
                $porMateria[] = $campo('calificacion', 'Calificación en la materia', false, 'numero') + ['min' => 0, 'max' => 100];
            }
            $resultado[$codigo] = [
                'nombre' => $nombre, 'ficha' => 'estudiante.tramites.'.$codigo,
                'instrucciones' => 'Completa los datos de este trámite. La información será revisada por la facultad.',
                'max_materias' => $maximo, 'campos_materia' => $porMateria,
                'campos' => $campos,
                'documentos' => [['id' => 'otro', 'nombre' => 'Documento de apoyo (requisitos pendientes de confirmación)', 'obligatorio' => false]],
            ];
        }

        return $resultado;
    }

    public function materias(): array
    {
        return [
            [
                'id' => 'calculo-demo',
                'codigo' => 'MAT202',
                'nombre' => 'Cálculo II',
                'periodo' => 'Ordinario I 2026',
                'paralelo' => 'A',
                'docente' => 'Docente de prueba A',
            ],
            [
                'id' => 'fisica-demo',
                'codigo' => 'FIS101',
                'nombre' => 'Física I',
                'periodo' => 'Ordinario I 2026',
                'paralelo' => 'B',
                'docente' => 'Docente de prueba B',
            ],
            [
                'id' => 'programacion-demo',
                'codigo' => 'COM203',
                'nombre' => 'Programación II',
                'periodo' => 'Ordinario II 2025',
                'paralelo' => 'A',
                'docente' => 'Docente de prueba C',
            ],
        ];
    }

    /**
     * Cada trámite define sus campos según la tabla 6.3 de los requerimientos.
     *
     * Tipos de campo: texto, texto_largo, numero, fecha, lista.
     * 'campos_materia' se pide por cada materia elegida.
     * 'mostrar_si' muestra el campo solo cuando otro campo tiene un valor.
     */
    private function tramites(): array
    {
        return [
            'recuperacion' => [
                'nombre' => 'examen de recuperación',
                'ficha' => 'estudiante.tramites.recuperacion',
                'instrucciones' => 'Selecciona la materia y declara tus datos académicos. '
                    .'La asistente verificará la información durante la revisión.',
                'aviso' => 'Recuperación admite una sola materia por solicitud. '
                    .'La selección y los datos declarados no representan '
                    .'una aprobación de elegibilidad.',
                'max_materias' => 1,
                'campos_materia' => [
                    [
                        'nombre' => 'estado',
                        'etiqueta' => 'Estado académico declarado',
                        'tipo' => 'lista',
                        'obligatorio' => false,
                        'opciones' => self::ESTADOS_MATERIA,
                    ],
                    [
                        'nombre' => 'nota',
                        'etiqueta' => 'Nota de la materia',
                        'tipo' => 'numero',
                        'obligatorio' => false,
                        'min' => 0,
                        'max' => 100,
                    ],
                    [
                        'nombre' => 'asistencia',
                        'etiqueta' => 'Asistencia (%)',
                        'tipo' => 'numero',
                        'obligatorio' => false,
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'campos' => [
                    [
                        'nombre' => 'gpa_periodo',
                        'etiqueta' => 'GPA del periodo',
                        'tipo' => 'numero',
                        'obligatorio' => false,
                        'min' => 0,
                        'max' => 100,
                        'ayuda' => 'Declara el GPA aplicable al periodo de la materia seleccionada.',
                    ],
                    [
                        'nombre' => 'historial_recuperacion',
                        'etiqueta' => 'Exámenes de recuperación anteriores',
                        'tipo' => 'lista',
                        'obligatorio' => false,
                        'opciones' => [
                            'nunca' => 'Nunca he rendido un examen de recuperación',
                            'con_fecha' => 'He rendido uno y conozco la fecha',
                            'fecha_desconocida' => 'He rendido uno, pero no conozco la fecha',
                            'desconocido' => 'No puedo confirmar esta información',
                        ],
                    ],
                    [
                        'nombre' => 'ultima_recuperacion',
                        'etiqueta' => 'Fecha del último examen de recuperación',
                        'tipo' => 'fecha',
                        'obligatorio' => true,
                        'mostrar_si' => ['campo' => 'historial_recuperacion', 'valor' => 'con_fecha'],
                    ],
                    [
                        'nombre' => 'observacion',
                        'etiqueta' => 'Observación',
                        'tipo' => 'texto_largo',
                        'obligatorio' => false,
                    ],
                ],
                'documentos' => [
                    [
                        'id' => 'registro-calificaciones',
                        'nombre' => 'Registro de calificaciones o evidencia de la materia reprobada',
                        'obligatorio' => true,
                    ],
                    [
                        'id' => 'otro',
                        'nombre' => 'Otro documento de apoyo',
                        'obligatorio' => false,
                    ],
                ],
            ],

            'gracia' => [
                'nombre' => 'examen de gracia',
                'ficha' => 'estudiante.tramites.gracia',
                'instrucciones' => 'Selecciona las materias y explica el motivo de tu solicitud.',
                'max_materias' => 10,
                'campos_materia' => [
                    [
                        'nombre' => 'asistencia',
                        'etiqueta' => 'Asistencia (%)',
                        'tipo' => 'numero',
                        'obligatorio' => false,
                        'min' => 0,
                        'max' => 100,
                    ],
                    [
                        'nombre' => 'nota',
                        'etiqueta' => 'Calificación actual',
                        'tipo' => 'numero',
                        'obligatorio' => false,
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'campos' => [
                    [
                        'nombre' => 'motivo',
                        'etiqueta' => 'Motivo y justificación',
                        'tipo' => 'texto_largo',
                        'obligatorio' => true,
                    ],
                ],
                'documentos' => [
                    [
                        'id' => 'respaldo',
                        'nombre' => 'Documento de respaldo (pendiente de confirmación por la facultad)',
                        'obligatorio' => false,
                    ],
                ],
            ],

            'supletorio' => [
                'nombre' => 'examen supletorio',
                'ficha' => 'estudiante.tramites.supletorio',
                'instrucciones' => 'Selecciona las materias, el parcial que no rendiste '
                    .'y la circunstancia que te impidió presentarlo.',
                'max_materias' => 10,
                'campos_materia' => [],
                'campos' => [
                    [
                        'nombre' => 'parcial',
                        'etiqueta' => 'Parcial no rendido',
                        'tipo' => 'lista',
                        'obligatorio' => true,
                        'opciones' => [
                            'primer_parcial' => 'Primer parcial',
                            'segundo_parcial' => 'Segundo parcial',
                        ],
                    ],
                    [
                        'nombre' => 'circunstancia',
                        'etiqueta' => 'Circunstancia',
                        'tipo' => 'texto_largo',
                        'obligatorio' => true,
                        'ayuda' => 'Describe qué te impidió rendir el examen.',
                    ],
                ],
                'documentos' => [
                    [
                        'id' => 'evidencia-impedimento',
                        'nombre' => 'Evidencia que respalde el impedimento para rendir el examen',
                        'obligatorio' => true,
                    ],
                ],
            ],
        ];
    }
}
