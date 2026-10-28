<?php

namespace App\Services\Flujo;

use App\Models\CorteAgenda;
use App\Support\RevisionSolicitudFilters;
use Illuminate\Support\Carbon;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class AgendaWordService
{
    public function generarAgendaWord(int $corteId)
    {
        return $this->generarDocumento([$corteId]);
    }

    public function generarDocumento(array $corteIds, array $filtros = [])
    {
        $cortes = CorteAgenda::with(['puntosInternos', 'solicitudes.carreraEstudiante.estudiante', 'solicitudes.carreraEstudiante.carreraModalidad.carrera', 'solicitudes.tipoTramite', 'solicitudes.estadoSolicitud', 'solicitudes.resolucion'])->whereIn('id', $corteIds)->orderBy('fecha_sesion')->orderBy('id')->get();
        abort_if($cortes->isEmpty(), 404, 'No hay agendas para esos filtros.');
        if ($filtros) {
            foreach ($cortes as $corte) {
                $solicitudes = $corte->solicitudes()->with(['carreraEstudiante.estudiante', 'carreraEstudiante.carreraModalidad.carrera', 'tipoTramite', 'estadoSolicitud', 'resolucion']);
                RevisionSolicitudFilters::apply($solicitudes->getQuery(), $filtros);
                $corte->setRelation('solicitudes', $solicitudes->get());
            }
        }
        $doc = new PhpWord;
        $section = $doc->addSection();
        $section->addTitle('AGENDAS DEL CONSEJO DE FACULTAD', 1);
        foreach ($cortes as $corte) {
            $section->addTitle('Agenda '.$corte->id.' · Sesión: '.Carbon::parse($corte->fecha_sesion)->format('d/m/Y'), 2);
            $section->addText('Estado: '.$corte->estado);
            if ($corte->asistentes) {
                $section->addText('Asistentes: '.$corte->asistentes);
            }
            foreach ($corte->puntosInternos as $punto) {
                $section->addText('Punto interno: '.$punto->titulo, ['bold' => true]);
                $section->addText($punto->descripcion);
            }
            $section->addTextBreak(1);
            $section->addTitle('Expedientes incluidos', 3);
            foreach ($corte->solicitudes as $index => $s) {
                $estudiante = $s->carreraEstudiante?->estudiante;
                $carrera = $s->carreraEstudiante?->carreraModalidad?->carrera;
                $section->addText(($index + 1).'. Expediente '.$s->codigo.' · CAFI: '.($s->codigo_sistema ?: 'Pendiente'));
                $section->addText('Estudiante: '.trim(($estudiante?->nombres ?? '').' '.($estudiante?->apellidos ?? '')));
                $section->addText('Carrera: '.($carrera?->nombre ?? '—').' · Trámite: '.($s->tipoTramite?->nombre ?? '—'));
                $section->addText('Estado: '.($s->estadoSolicitud?->nombre ?? '—'));
                $section->addTextBreak(1);
            }
        }
        $name = 'agendas-consejo-'.now()->format('Ymd-His').'.docx';
        $directory = storage_path('app/private/generated-agendas');
        if (! is_dir($directory)) {
            mkdir($directory, 0750, true);
        }
        $path = $directory.'/'.$name;
        IOFactory::createWriter($doc, 'Word2007')->save($path);

        return response()->download($path, $name)->deleteFileAfterSend(true);
    }
}
