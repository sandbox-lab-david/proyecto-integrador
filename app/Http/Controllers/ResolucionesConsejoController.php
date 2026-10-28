<?php

namespace App\Http\Controllers;

use App\Models\CorteAgenda;
use App\Models\Resolucion;
use App\Models\Solicitud;
use App\Services\Flujo\AgendaWordService;
use App\Services\Flujo\EstadoSolicitudService;
use App\Services\Flujo\ResolucionService;
use App\Services\Notificaciones\NotificacionService;
use App\Services\Academico\AutorizacionService;
use App\Support\Estados;
use App\Support\RevisionSolicitudFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResolucionesConsejoController extends Controller
{
    public function index(Request $request, AutorizacionService $authz)
    {
        $filtros = RevisionSolicitudFilters::validateWithDateRange($request);
        $agendas = collect();
        if ($filtros['filtrar']) {
            $query = CorteAgenda::with(['solicitudes.estadoSolicitud', 'resoluciones'])->whereHas('solicitudes', fn ($q) => $this->aplicarFiltrosSolicitud($q, $filtros));
            if ($filtros['desde'] !== '') {
                $query->whereDate('fecha_sesion', '>=', $filtros['desde']);
            }
            if ($filtros['hasta'] !== '') {
                $query->whereDate('fecha_sesion', '<=', $filtros['hasta']);
            }
            if ($authz->tieneRol(auth()->user(), 'asistente')) {
                $facultades = $authz->facultadesDe(auth()->user()->trabajador)->modelKeys();
                $query->whereIn('facultad_id', $facultades);
            }
            $agendas = $query->orderByDesc('fecha_sesion')->get();
            foreach ($agendas as $agenda) {
                $agenda->numero = 'AG-'.$agenda->id;
                $itemsQuery = $agenda->solicitudes()->with('estadoSolicitud');
                RevisionSolicitudFilters::apply($itemsQuery->getQuery(), $filtros);
                $items = $itemsQuery->get();
                $agenda->setRelation('solicitudes', $items);
                $agenda->total_solicitudes = $items->count();
                $map = [Estados::ENVIADA => 'total_enviadas', Estados::REVISADA => 'total_revisadas', Estados::APTA_CONSEJO => 'total_aptas_consejo', Estados::AGENDADA => 'total_agendadas', Estados::APROBADA_EJECUCION => 'total_aprobadas_ejecucion', Estados::EJECUTADA => 'total_ejecutadas', Estados::OBSERVADA => 'total_observadas', Estados::RECHAZADA => 'total_rechazadas'];
                foreach ($map as $code => $attribute) {
                    $agenda->{$attribute} = $items->filter(fn ($s) => $s->estadoSolicitud?->codigo === $code)->count();
                }
                $agenda->total_pendientes_resolucion = $items->filter(fn ($s) => ! $s->resolucion || $s->resolucion->decision === 'Guardar')->count();
            }
        }

        return view('revision.resoluciones', ['agendas' => $agendas, 'filtros' => $filtros, 'filtrosAplicados' => $filtros['filtrar'], 'tramitesDisponibles' => RevisionSolicitudFilters::tramitesDisponibles(), 'estadosDisponibles' => RevisionSolicitudFilters::ESTADOS]);
    }

    public function porAgenda(int $agenda, ResolucionService $resoluciones, AutorizacionService $authz)
    {
        $corte = CorteAgenda::with(['puntosInternos', 'solicitudes.carreraEstudiante.estudiante', 'solicitudes.carreraEstudiante.carreraModalidad.carrera', 'solicitudes.tipoTramite', 'solicitudes.estadoSolicitud', 'solicitudes.historial', 'solicitudes.resolucion'])->findOrFail($agenda);
        if ($authz->tieneRol(auth()->user(), 'asistente')) {
            abort_unless($authz->puedeVerFacultad(auth()->user(), (int) $corte->facultad_id), 403);
        }
        $corte->numero = 'AG-'.$corte->id;
        $casos = $corte->solicitudes->map(function ($s) use ($resoluciones) {
            $student = $s->carreraEstudiante?->estudiante;
            $career = $s->carreraEstudiante?->carreraModalidad?->carrera;
            $resolucion = $resoluciones->resolucionDe($s);

            return (object) ['solicitud_id' => $s->id, 'codigo_solicitud' => $s->codigo, 'codigo_siac' => $student?->codigo, 'codigo_sistema' => $s->codigo_sistema, 'estudiante' => trim(($student?->nombres ?? '').' '.($student?->apellidos ?? '')), 'carrera' => $career?->nombre, 'tramite' => $s->tipoTramite?->nombre, 'materia' => null, 'motivo' => data_get($s->detalle, 'motivo'), 'estado_solicitud' => $s->estadoSolicitud?->nombre, 'resolucion' => $resolucion, 'numero_resolucion' => $resolucion?->numero_resolucion, 'decision' => $resolucion?->decision, 'estado_codigo' => $s->estadoSolicitud?->codigo];
        });
        $agenda = $corte;

        return view('revision.resoluciones', compact('agenda', 'casos'));
    }

    public function documento(Request $request, AgendaWordService $word, AutorizacionService $authz)
    {
        $filtros = RevisionSolicitudFilters::validateWithDateRange($request);
        $query = CorteAgenda::query()->whereHas('solicitudes', fn ($q) => $this->aplicarFiltrosSolicitud($q, $filtros));
        if ($filtros['desde'] !== '') {
            $query->whereDate('fecha_sesion', '>=', $filtros['desde']);
        }
        if ($filtros['hasta'] !== '') {
            $query->whereDate('fecha_sesion', '<=', $filtros['hasta']);
        }
        if ($authz->tieneRol(auth()->user(), 'asistente')) {
            $query->whereIn('facultad_id', $authz->facultadesDe(auth()->user()->trabajador)->modelKeys());
        }

        return $word->generarDocumento($query->pluck('id')->all(), $filtros);
    }

    public function documentoAgenda(int $agenda, AgendaWordService $word, AutorizacionService $authz)
    {
        $corte = CorteAgenda::findOrFail($agenda);
        if ($authz->tieneRol(auth()->user(), 'asistente')) {
            abort_unless($authz->puedeVerFacultad(auth()->user(), (int) $corte->facultad_id), 403);
        }

        return $word->generarAgendaWord($corte->id);
    }

    public function exportar(Request $request, AutorizacionService $authz)
    {
        $filtros = RevisionSolicitudFilters::validateWithDateRange($request);
        $query = CorteAgenda::with(['solicitudes.estadoSolicitud', 'resoluciones'])->whereHas('solicitudes', fn ($q) => $this->aplicarFiltrosSolicitud($q, $filtros));
        if ($filtros['desde'] !== '') {
            $query->whereDate('fecha_sesion', '>=', $filtros['desde']);
        }
        if ($filtros['hasta'] !== '') {
            $query->whereDate('fecha_sesion', '<=', $filtros['hasta']);
        }
        if ($authz->tieneRol(auth()->user(), 'asistente')) {
            $query->whereIn('facultad_id', $authz->facultadesDe(auth()->user()->trabajador)->modelKeys());
        }
        $rows = $query->orderBy('fecha_sesion')->get();
        foreach ($rows as $agenda) {
            $filtered = $agenda->solicitudes()->with('resolucion', 'estadoSolicitud');
            RevisionSolicitudFilters::apply($filtered->getQuery(), $filtros);
            $agenda->setRelation('solicitudes', $filtered->get());
        }
        $filename = 'agendas-consejo-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Agenda', 'Fecha de sesión', 'Días transcurridos', 'Solicitudes', 'Pendientes de resolución', 'Facultad']);
            foreach ($rows as $agenda) {
                fputcsv($out, ['AG-'.$agenda->id, $agenda->fecha_sesion->format('d/m/Y'), (int) $agenda->fecha_sesion->diffInDays(now()), $agenda->solicitudes->count(), $agenda->solicitudes->filter(fn ($s) => ! $s->resolucion || $s->resolucion->decision === 'Guardar')->count(), $agenda->facultad_id]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function guardar(Request $request, int $agenda, int $solicitud, EstadoSolicitudService $estados, NotificacionService $notificaciones)
    {
        $data = $request->validate(['decision' => ['required', 'in:Aprobado,Rechazado,Guardar'], 'observacion' => ['nullable', 'string', 'max:2000']]);
        $corte = CorteAgenda::findOrFail($agenda);
        $s = $corte->solicitudes()->with('estadoSolicitud')->whereKey($solicitud)->firstOrFail();
        abort_unless($s->estadoSolicitud?->codigo === Estados::AGENDADA, 409, 'La resolución solo se puede registrar mientras el expediente está agendado.');
        $user = auth()->user();
        DB::transaction(function () use ($data, $corte, $s, $user, $estados) {
            $resolution = Resolucion::where('solicitud_id', $s->id)->lockForUpdate()->first();
            if (! $resolution) {
                do {
                    $numero = 'RES-'.now()->format('Y').'-'.strtoupper(Str::random(10));
                } while (Resolucion::where('numero_resolucion', $numero)->exists());
                $resolution = Resolucion::create(['solicitud_id' => $s->id, 'corte_agenda_id' => $corte->id, 'numero_resolucion' => $numero, 'fecha_resolucion' => now()->toDateString(), 'decision' => $data['decision'], 'observacion' => $data['observacion'] ?? null, 'registrada_por' => $user->id]);
            } else {
                $resolution->update(['corte_agenda_id' => $corte->id, 'decision' => $data['decision'], 'observacion' => $data['observacion'] ?? null, 'fecha_resolucion' => now()->toDateString(), 'registrada_por' => $user->id]);
            }
            if ($data['decision'] === 'Aprobado') {
                $estados->transicionar($s, Estados::APROBADA_EJECUCION, $user, $data['observacion'] ?? 'Resolución del Consejo: aprobado.');
            }
            if ($data['decision'] === 'Rechazado') {
                $estados->transicionar($s, Estados::RECHAZADA, $user, $data['observacion'] ?? 'Resolución del Consejo: rechazado.');
            }
        });
        if ($data['decision'] === 'Guardar') {
            $notificaciones->notificarResolucionGuardada($s->fresh(['carreraEstudiante']), $data['observacion'] ?? null);
        }

        return redirect()->route('revision.agendas.resoluciones', $agenda)->with('success', 'Resolución guardada para el expediente '.$s->codigo.'.');
    }

    public function actualizarEstado(Request $request, int $id, EstadoSolicitudService $estados)
    {
        $data = $request->validate(['accion' => ['required', 'in:ejecutar']]);
        $s = Solicitud::with('estadoSolicitud')->findOrFail($id);
        abort_unless($s->estadoSolicitud?->codigo === Estados::APROBADA_EJECUCION, 409);
        $estados->transicionar($s, Estados::EJECUTADA, auth()->user(), 'Ejecución registrada.');

        return redirect()->route('revision.resoluciones')->with('success','La solicitud quedó marcada como ejecutada.');
    }

    private function aplicarFiltrosSolicitud($query,array $filtros): void
    {
        RevisionSolicitudFilters::apply($query,$filtros);
    }
}
