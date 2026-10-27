<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\RevisionSolicitudFilters;

class ResolucionesConsejoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = RevisionSolicitudFilters::validateWithDateRange($request);

        $consulta = DB::table('agendas_consejo_m3 as a')
            ->leftJoin('agenda_solicitud_m3 as ax', 'ax.agenda_id', '=', 'a.id')
            ->leftJoin('solicitudes_revision as s', 's.id', '=', 'ax.solicitud_revision_id')
            ->leftJoin('resoluciones_m3 as r', 'r.solicitud_revision_id', '=', 's.id')
            ->select('a.id', 'a.numero', 'a.fecha_sesion', 'a.estado')
            ->selectRaw('COUNT(s.id) as total_solicitudes')
            ->selectRaw("SUM(CASE WHEN s.estado = 'Enviado por estudiante' THEN 1 ELSE 0 END) as total_enviadas")
            ->selectRaw("SUM(CASE WHEN s.estado = 'Revisado' THEN 1 ELSE 0 END) as total_revisadas")
            ->selectRaw("SUM(CASE WHEN s.estado = 'Apto para consejo' THEN 1 ELSE 0 END) as total_aptas_consejo")
            ->selectRaw("SUM(CASE WHEN s.estado = 'Agendado' THEN 1 ELSE 0 END) as total_agendadas")
            ->selectRaw("SUM(CASE WHEN s.estado = 'Aprobado para ejecución' THEN 1 ELSE 0 END) as total_aprobadas_ejecucion")
            ->selectRaw("SUM(CASE WHEN s.estado = 'Ejecutado' THEN 1 ELSE 0 END) as total_ejecutadas")
            ->selectRaw("SUM(CASE WHEN s.estado = 'Observado' THEN 1 ELSE 0 END) as total_observadas")
            ->selectRaw("SUM(CASE WHEN s.estado = 'Rechazado' THEN 1 ELSE 0 END) as total_rechazadas")
            ->selectRaw('SUM(CASE WHEN s.id IS NOT NULL AND r.id IS NULL THEN 1 ELSE 0 END) as total_pendientes_resolucion')
            ->groupBy('a.id', 'a.numero', 'a.fecha_sesion', 'a.estado');

        if ($filtros['desde'] !== '') {
            $consulta->whereDate('a.fecha_sesion', '>=', $filtros['desde']);
        }
        if ($filtros['hasta'] !== '') {
            $consulta->whereDate('a.fecha_sesion', '<=', $filtros['hasta']);
        }
        RevisionSolicitudFilters::applyMetadata($consulta, $filtros, 's');

        $agendas = $consulta->orderByDesc('a.fecha_sesion')->get();
        $tramitesDisponibles = DB::table('solicitudes_revision')->whereNotNull('tramite')->distinct()->orderBy('tramite')->pluck('tramite');
        $estadosDisponibles = RevisionSolicitudFilters::ESTADOS;

        return view('revision.resoluciones', compact('agendas', 'filtros', 'tramitesDisponibles', 'estadosDisponibles'));
    }

    public function porAgenda(int $agenda)
    {
        $agenda = DB::table('agendas_consejo_m3')->where('id', $agenda)->first();
        abort_unless($agenda, 404);

        $casos = DB::table('agenda_solicitud_m3 as ax')
            ->join('solicitudes_revision as s', 's.id', '=', 'ax.solicitud_revision_id')
            ->leftJoin('resoluciones_m3 as r', 'r.solicitud_revision_id', '=', 's.id')
            ->where('ax.agenda_id', $agenda->id)
            ->select('s.id as solicitud_id', 's.codigo_solicitud', 's.codigo_siac', 's.estudiante', 's.carrera', 's.tramite', 's.materia', 's.motivo', 's.estado as estado_solicitud', 'r.id as resolucion_id', 'r.numero_resolucion', 'r.decision')
            ->orderBy('s.id')->get();

        return view('revision.resoluciones', compact('agenda', 'casos'));
    }

    public function documento(Request $request)
    {
        $filtros = RevisionSolicitudFilters::validateWithDateRange($request);

        $consulta = DB::table('agendas_consejo_m3')->orderBy('fecha_sesion')->orderBy('numero');
        if ($filtros['desde'] !== '') {
            $consulta->whereDate('fecha_sesion', '>=', $filtros['desde']);
        }
        if ($filtros['hasta'] !== '') {
            $consulta->whereDate('fecha_sesion', '<=', $filtros['hasta']);
        }

        $agendasDocumentadas = $consulta->get()->map(function ($agenda) use ($filtros) {
            $casos = DB::table('agenda_solicitud_m3 as ax')
                ->join('solicitudes_revision as s', 's.id', '=', 'ax.solicitud_revision_id')
                ->leftJoin('resoluciones_m3 as r', 'r.solicitud_revision_id', '=', 's.id')
                ->where('ax.agenda_id', $agenda->id)
                ->select('s.id', 's.codigo_solicitud', 's.codigo_siac', 's.estudiante', 's.carrera', 's.tramite', 's.materia', 's.motivo', 's.estado', 'r.numero_resolucion', 'r.decision');
            RevisionSolicitudFilters::applyMetadata($casos, $filtros, 's');
            $casos = $casos->orderBy('s.carrera')->orderBy('s.tramite')->orderBy('s.id')->get();

            if (RevisionSolicitudFilters::hasMetadata($filtros) && $casos->isEmpty()) {
                return null;
            }

            $agenda->solicitudesPorCarrera = $casos->groupBy(fn ($caso) => $caso->carrera ?: 'Carrera no especificada');

            return $agenda;
        })->filter()->values();

        return view('revision.documento-agenda', compact('agendasDocumentadas', 'filtros'));
    }

    public function guardar(Request $request, int $agenda, int $solicitud)
    {
        $datos = $request->validate([
            'decision' => ['required', 'in:Aprobado,Rechazado'],
        ]);

        $agendaExiste = DB::table('agendas_consejo_m3')->where('id', $agenda)->exists();
        $expediente = DB::table('agenda_solicitud_m3 as ax')
            ->join('solicitudes_revision as s', 's.id', '=', 'ax.solicitud_revision_id')
            ->where('ax.agenda_id', $agenda)->where('s.id', $solicitud)
            ->select('s.id', 's.codigo_solicitud')->first();
        abort_unless($agendaExiste && $expediente, 404);

        DB::transaction(function () use ($datos, $agenda, $solicitud, $expediente) {
            $resolucionId = DB::table('resoluciones_m3')->where('solicitud_revision_id', $solicitud)->value('id');
            if ($resolucionId) {
                DB::table('resoluciones_m3')->where('id', $resolucionId)->update([
                    'agenda_id' => $agenda,
                    'decision' => $datos['decision'],
                    'updated_at' => now(),
                ]);
                return;
            }

            $resolucionId = DB::table('resoluciones_m3')->insertGetId([
                'agenda_id' => $agenda,
                'solicitud_revision_id' => $solicitud,
                'numero_resolucion' => 'PENDIENTE-'.$solicitud,
                'decision' => $datos['decision'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $numero = 'RES-M3-'.now()->format('Y').'-'.str_pad((string) $resolucionId, 6, '0', STR_PAD_LEFT);
            DB::table('resoluciones_m3')->where('id', $resolucionId)->update([
                'numero_resolucion' => $numero,
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('revision.agendas.resoluciones', $agenda)
            ->with('success', 'Resolución guardada para el expediente '.$expediente->codigo_solicitud.'.');
    }

    public function actualizarEstado(Request $request, int $id)
    {
        $datos = $request->validate([
            'accion' => ['required', 'in:aprobar_ejecucion,ejecutar'],
        ]);

        [$estadoActual, $estadoNuevo, $mensaje] = match ($datos['accion']) {
            'aprobar_ejecucion' => ['Agendado', 'Aprobado para ejecución', 'Solicitud aprobada para ejecución.'],
            'ejecutar' => ['Aprobado para ejecución', 'Ejecutado', 'Solicitud marcada como ejecutada.'],
        };

        $actualizadas = DB::table('solicitudes_revision')
            ->where('id', $id)
            ->where('estado', $estadoActual)
            ->update(['estado' => $estadoNuevo, 'updated_at' => now()]);

        if ($actualizadas !== 1) {
            return back()->withErrors(['estado' => 'La solicitud ya no está en el estado requerido para esta acción.']);
        }

        return redirect()->route('revision.resoluciones')->with('success', $mensaje);
    }

}
