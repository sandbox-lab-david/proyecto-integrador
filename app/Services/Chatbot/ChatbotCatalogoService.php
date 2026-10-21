<?php

namespace App\Services\Chatbot;

use App\Services\Catalogo\CatalogoTramites;
use App\Services\Solicitudes\ReglasCampoSolicitud;

class ChatbotCatalogoService
{
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

    public function obtenerCatalogo(): array
    {
        $publicado = app(CatalogoTramites::class)->publicado();
        $tramites = array_map(fn ($t) => array_intersect_key($t, array_flip(['id', 'nombre', 'categoria_id'])), $publicado['tramites']);
        $preguntas = [];
        foreach ($tramites as $t) {
            $intenciones = ['descripcion' => '¿Qué es este trámite?', 'requisitos' => '¿Qué requisitos tiene?', 'documentos' => '¿Qué documentos necesito?', 'costo' => '¿Cuánto cuesta?', 'plazo' => '¿Qué plazos debo considerar?'];
            if ($t['id'] === 'examen_recuperacion') {
                $intenciones['referencias'] = 'Comparar mi GPA general y calcular los 365 días (opcional)';
            }
            foreach ($intenciones as $sufijo => $texto) {
                $preguntas[] = ['id' => $t['id'].'__'.$sufijo, 'tramite_id' => $t['id'], 'texto' => $texto,
                    'intencion' => $sufijo === 'referencias' ? 'comprobar_referencias' : 'consultar_'.$sufijo,
                    'origenes_permitidos' => $sufijo === 'referencias' ? ['manual'] : ['ninguno'],
                    'campos' => $sufijo === 'referencias' ? $this->obtenerCamposDeTramite($t['id']) : []];
            }
        }

        return ['version' => 3, 'kb_version' => $publicado['version'], 'categorias' => $this->categoriasDefinidas, 'tramites' => $tramites, 'preguntas' => $preguntas];
    }

    public function obtenerPregunta(string $id): ?array
    {
        foreach ($this->obtenerCatalogo()['preguntas'] as $p) {
            if ($p['id'] === $id) {
                return $p;
            }
        }

        return null;
    }

    public function obtenerCamposDeTramite(string $id): array
    {
        if ($id !== 'examen_recuperacion') {
            return [];
        }

        return [
            ['nombre' => 'gpa_general', 'etiqueta' => 'GPA general', 'tipo' => 'numero', 'requerido' => false, 'min' => 0, 'max' => 100, 'opciones' => []],
            ['nombre' => 'fecha_ultima_recuperacion', 'etiqueta' => 'Fecha del último examen de recuperación', 'tipo' => 'fecha', 'requerido' => false, 'min' => null, 'max' => now(ReglasCampoSolicitud::zonaHoraria())->toDateString(), 'opciones' => []],
        ];
    }

    public function obtenerNombresCamposValidosPorTramite(string $id): array
    {
        return array_column($this->obtenerCamposDeTramite($id), 'nombre');
    }

    public function obtenerIdentificadorAgenda(string $id): ?string
    {
        return app(CatalogoTramites::class)->buscar($id)['slug'] ?? null;
    }

    public function obtenerCostoTexto(string $id): ?string
    {
        return app(CatalogoTramites::class)->buscar($id)['costo_texto'] ?? null;
    }

    public function obtenerPlazoTexto(string $id): ?string
    {
        return app(CatalogoTramites::class)->buscar($id)['plazo_texto'] ?? null;
    }
}
