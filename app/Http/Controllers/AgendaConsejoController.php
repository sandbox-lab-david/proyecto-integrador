<?php

namespace App\Http\Controllers;

use App\Models\CorteAgenda;
use App\Models\PuntoInternoAgenda;
use App\Models\Solicitud;
use App\Services\Academico\AutorizacionService;
use App\Services\Flujo\EstadoSolicitudService;
use App\Services\Solicitudes\SolicitudService;
use App\Support\Estados;
use App\Support\RevisionSolicitudFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaConsejoController extends Controller
{
    public function index(Request $request, AutorizacionService $authz)
    {
        $filtros = RevisionSolicitudFilters::validate($request);
        $query = Solicitud::with(['carreraEstudiante.estudiante', 'carreraEstudiante.carreraModalidad.carrera', 'tipoTramite', 'estadoSolicitud'])
            ->enEstado(Estados::APTA_CONSEJO)->whereDoesntHave('cortesAgenda');
        if (! $authz->tieneRol(auth()->user(), 'admin')) {
            $ids = $authz->facultadesDe(auth()->user()->trabajador)->modelKeys();
            $query->whereHas('carreraEstudiante.carrera', fn ($c) => $c->whereIn('facultad_id', $ids));
        }
        RevisionSolicitudFilters::apply($query, $filtros);
        $aprobadas = $query->latest('created_at')->get();
        foreach ($aprobadas as $s) {
            $this->presentar($s);
        }

        return view('revision.agendas', ['aprobadas' => $aprobadas, 'filtros' => $filtros, 'tramitesDisponibles' => RevisionSolicitudFilters::tramitesDisponibles(), 'estadosDisponibles' => RevisionSolicitudFilters::ESTADOS]);
    }

    public function store(Request $request, AutorizacionService $authz, EstadoSolicitudService $estados, SolicitudService $solicitudService)
    {
        $data = $request->validate(['fecha_sesion' => ['required', 'date', 'after_or_equal:today'], 'solicitudes' => ['required', 'array', 'min:1'], 'solicitudes.*' => ['integer', 'distinct'], 'punto_titulo' => ['nullable', 'string', 'max:255'], 'punto_descripcion' => ['nullable', 'string', 'max:2000']]);
        $solicitudes = Solicitud::with('carreraEstudiante.carreraModalidad.carrera', 'estadoSolicitud')->whereIn('id', $data['solicitudes'])->enEstado(Estados::APTA_CONSEJO)->whereDoesntHave('cortesAgenda')->get();
        abort_unless($solicitudes->count() === count($data['solicitudes']), 422, 'Solo se pueden agendar solicitudes aptas y sin agenda previa.');
        $admin = $authz->tieneRol(auth()->user(), 'admin');
        foreach ($solicitudes as $s) {
            $career = $s->carreraEstudiante->carreraModalidad->carrera;
            abort_unless($admin || $authz->puedeVerFacultad(auth()->user(), (int) $career->facultad_id), 403);
        }
        DB::transaction(function () use ($solicitudes, $data, $estados) {
            foreach ($solicitudes->groupBy(fn ($s) => $s->carreraEstudiante->carreraModalidad->carrera->facultad_id) as $facultyId => $group) {
                $corte = CorteAgenda::create(['facultad_id' => $facultyId, 'fecha_sesion' => $data['fecha_sesion'], 'estado' => 'Agendado']);
                if (filled($data['punto_titulo'] ?? null) || filled($data['punto_descripcion'] ?? null)) {
                    PuntoInternoAgenda::create(['corte_agenda_id' => $corte->id, 'titulo' => $data['punto_titulo'] ?: 'Punto interno', 'descripcion' => $data['punto_descripcion'] ?: '']);
                }
                $corte->solicitudes()->attach($group->modelKeys());
                foreach ($group as $s) {
                    if (! $s->codigo_sistema) {
                        $solicitudService->registrarCodigoSistema($s, $solicitudService->siguienteCodigoSistema());
                    }
                    $estados->transicionar($s, Estados::AGENDADA, auth()->user(), 'Incluida en agenda de Consejo '.$corte->id.'.');
                }
            }
        });

        return redirect()->route('revision.agendas')->with('success', 'Agenda del Consejo creada y solicitudes seleccionadas agendadas.');
    }

    public function rechazarVariasSolicitudes(Request $request, EstadoSolicitudService $estados, AutorizacionService $authz)
    {
        $data = $request->validate(['solicitudes' => ['required', 'array', 'min:1'], 'solicitudes.*' => ['integer', 'distinct']]);
        $solicitudes = Solicitud::with('carreraEstudiante.carreraModalidad.carrera', 'estadoSolicitud')->whereIn('id', $data['solicitudes'])->enEstado(Estados::APTA_CONSEJO)->whereDoesntHave('cortesAgenda')->get();
        abort_unless($solicitudes->count() === count($data['solicitudes']), 422, 'Solo se pueden rechazar solicitudes aptas para Consejo sin agenda previa.');
        foreach ($solicitudes as $s) {
            abort_unless($authz->tieneRol(auth()->user(), 'admin') || $authz->puedeVerFacultad(auth()->user(), (int) $s->carreraEstudiante->carreraModalidad->carrera->facultad_id), 403);
        }
        DB::transaction(fn () => $solicitudes->each(fn ($s) => $estados->transicionar($s, Estados::RECHAZADA, auth()->user(), 'Rechazada por el Consejo antes de agendar.')));

        return redirect()->route('revision.agendas')->with('success', $solicitudes->count().' solicitudes fueron rechazadas.');
    }

    private function presentar(Solicitud $s): void
    {
        $s->setAttribute('codigo_solicitud', $s->codigo);
        $s->setAttribute('estudiante', trim(($s->carreraEstudiante?->estudiante?->nombres ?? '').' '.($s->carreraEstudiante?->estudiante?->apellidos ?? '')));
        $s->setAttribute('carrera', $s->carreraEstudiante?->carreraModalidad?->carrera?->nombre);
        $s->setAttribute('tramite',$s->tipoTramite?->nombre);
        $s->setAttribute('estado',$s->estadoSolicitud?->nombre);
    }
}
