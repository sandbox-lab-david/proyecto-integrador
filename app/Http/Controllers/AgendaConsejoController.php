<?php

namespace App\Http\Controllers;

use App\Support\RevisionSolicitudFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaConsejoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = RevisionSolicitudFilters::validate($request);
        $aprobadasQuery = DB::table('solicitudes_revision')
            ->where('estado', 'Apto para consejo')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('agenda_solicitud_m3')
                    ->whereColumn('agenda_solicitud_m3.solicitud_revision_id', 'solicitudes_revision.id');
            });
        RevisionSolicitudFilters::apply($aprobadasQuery, $filtros);
        $aprobadas = $aprobadasQuery->orderBy('created_at')->get();

        $tramitesDisponibles = DB::table('solicitudes_revision')->whereNotNull('tramite')->distinct()->orderBy('tramite')->pluck('tramite');
        $estadosDisponibles = RevisionSolicitudFilters::ESTADOS;

        return view('revision.agendas', compact('aprobadas', 'filtros', 'tramitesDisponibles', 'estadosDisponibles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_sesion' => ['required', 'date', 'after_or_equal:today'],
            'solicitudes' => ['required', 'array', 'min:1'],
            'solicitudes.*' => ['integer', 'distinct'],
        ]);
        $ids = collect($data['solicitudes'])->unique()->values();
        $casos = DB::table('solicitudes_revision')->whereIn('id', $ids)->where('estado', 'Apto para consejo')->count();
        if ($casos !== $ids->count()) {
            return back()->withErrors(['solicitudes' => 'Solo se pueden agendar solicitudes aptas para Consejo.'])->withInput();
        }

        DB::transaction(function () use ($data, $ids) {
            $id = DB::table('agendas_consejo_m3')->insertGetId([
                'numero' => 'AG-'.now()->format('Ymd-His'), 'fecha_sesion' => $data['fecha_sesion'],
                'estado' => 'Agendado', 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($ids as $solicitudId) {
                DB::table('agenda_solicitud_m3')->insert(['agenda_id' => $id, 'solicitud_revision_id' => $solicitudId, 'created_at' => now(), 'updated_at' => now()]);
            }
            DB::table('solicitudes_revision')->whereIn('id', $ids)->update([
                'estado' => 'Agendado',
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('revision.agendas', $request->only(['filtrar', 'nombre', 'codigo', 'carrera', 'fecha', 'tramite', 'estado']))
            ->with('success', 'Agenda del Consejo creada; las solicitudes seleccionadas quedaron agendadas.');
    }

    public function rechazarSolicitud(Request $request, int $id)
    {
        $actualizadas = DB::table('solicitudes_revision')
            ->where('id', $id)
            ->where('estado', 'Apto para consejo')
            ->whereNotExists(function ($query) use ($id) {
                $query->selectRaw('1')->from('agenda_solicitud_m3')
                    ->whereColumn('agenda_solicitud_m3.solicitud_revision_id', 'solicitudes_revision.id')
                    ->where('agenda_solicitud_m3.solicitud_revision_id', $id);
            })
            ->update(['estado' => 'Rechazado', 'updated_at' => now()]);

        abort_unless($actualizadas === 1, 404);

        return redirect()->route('revision.agendas', $request->only(['filtrar', 'nombre', 'codigo', 'carrera', 'fecha', 'tramite', 'estado']))
            ->with('success', 'La solicitud fue rechazada y ya no aparece entre las solicitudes aptas para agenda.');
    }

    public function rechazarVariasSolicitudes(Request $request)
    {
        $data = $request->validate([
            'solicitudes' => ['required', 'array', 'min:1'],
            'solicitudes.*' => ['required', 'integer', 'distinct'],
        ]);
        $ids = collect($data['solicitudes'])->unique()->values();

        $aptasSinAgenda = DB::table('solicitudes_revision as s')
            ->whereIn('s.id', $ids)
            ->where('s.estado', 'Apto para consejo')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('agenda_solicitud_m3 as ax')
                    ->whereColumn('ax.solicitud_revision_id', 's.id');
            });

        if ($aptasSinAgenda->count() !== $ids->count()) {
            return back()->withErrors(['solicitudes' => 'Solo se pueden rechazar solicitudes aptas para Consejo y que aún no tengan agenda.'])->withInput();
        }

        DB::table('solicitudes_revision')->whereIn('id', $ids)
            ->where('estado', 'Apto para consejo')
            ->update(['estado' => 'Rechazado', 'updated_at' => now()]);

        return redirect()->route('revision.agendas', $request->only(['filtrar', 'nombre', 'codigo', 'carrera', 'fecha', 'tramite', 'estado']))
            ->with('success', $ids->count().' solicitudes fueron rechazadas.');
    }
}
