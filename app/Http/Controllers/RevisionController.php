<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\Solicitud;
use App\Services\Academico\AutorizacionService;
use App\Services\Chatbot\PrologService;
use App\Services\Flujo\EstadoSolicitudService;
use App\Services\Solicitudes\RequisitosSolicitud;
use App\Services\Solicitudes\SolicitudService;
use App\Support\Estados;
use App\Support\RevisionSolicitudFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RevisionController extends Controller
{
    public function bandejaAsistente(Request $request, AutorizacionService $authz)
    {
        $filtros = RevisionSolicitudFilters::validate($request);
        $facultades = auth()->user()->trabajador ? $authz->facultadesDe(auth()->user()->trabajador) : collect();
        $solicitudes = collect();
        if ($filtros['filtrar']) {
            $query = Solicitud::with(['carreraEstudiante.estudiante', 'carreraEstudiante.carreraModalidad.carrera', 'tipoTramite', 'estadoSolicitud'])
                ->enEstado(Estados::ENVIADA);
            if (! $authz->tieneRol(auth()->user(), 'admin')) {
                $query->whereHas('carreraEstudiante.carrera', fn ($c) => $c->whereIn('facultad_id', $facultades->modelKeys()));
            }
            RevisionSolicitudFilters::apply($query, $filtros);
            $solicitudes = $query->latest('created_at')->get()->map(fn ($s) => $this->presentar($s));
        }

        return view('revision.asistente', ['solicitudes' => $solicitudes, 'filtros' => $filtros, 'tramitesDisponibles' => RevisionSolicitudFilters::tramitesDisponibles(), 'estadosDisponibles' => RevisionSolicitudFilters::ESTADOS, 'filtrosAplicados' => $filtros['filtrar']]);
    }

    public function detalleCaso(int $id, AutorizacionService $authz, SolicitudService $solicitudes, RequisitosSolicitud $requisitos, PrologService $prolog, EstadoSolicitudService $estados)
    {
        $solicitud = $solicitudes->detalle($id);
        $usuario = auth()->user();
        abort_unless($this->puedeVer($usuario, $solicitud, $authz), 403);
        $solicitud = $this->presentar($solicitud);
        $lineaTiempo = $estados->lineaDeTiempo($solicitud, true);
        $validacionProlog = null;
        $requisitosProlog = null;
        try {
            $requisitosProlog = $requisitos->paraVista($solicitud->tipoTramite->codigo);
            $validacionProlog = $prolog->evaluar($solicitud);
        } catch (\Throwable $e) {
            report($e);
        }

        return view('revision.detalle', compact('solicitud', 'lineaTiempo', 'requisitosProlog', 'validacionProlog'));
    }

    public function guardarRevision(Request $request, int $id, AutorizacionService $authz, EstadoSolicitudService $estados)
    {
        $data = $request->validate(['accion' => ['required', 'in:aprobar,observar,rechazar'], 'observaciones' => ['nullable', 'string', 'max:2000'], 'check_documento' => ['nullable', 'boolean'], 'check_datos' => ['nullable', 'boolean'], 'check_requisitos' => ['nullable', 'boolean']]);
        $solicitud = Solicitud::with(['carreraEstudiante.carreraModalidad.carrera', 'estadoSolicitud'])->findOrFail($id);
        $usuario = auth()->user();
        abort_unless($authz->tieneRol($usuario, 'admin') || ($usuario->trabajador && $authz->puedeVerFacultad($usuario, (int) $solicitud->carreraEstudiante->carreraModalidad->carrera->facultad_id)), 403);
        $destino = match ($data['accion']) {
            'aprobar' => Estados::REVISADA,'observar' => Estados::OBSERVADA,'rechazar' => Estados::RECHAZADA
        };
        $trabajadorId = $usuario->trabajador?->id;
        abort_unless($trabajadorId, 403);
        DB::transaction(function () use ($solicitud, $data, $destino, $usuario, $trabajadorId, $estados) {
            Revision::create(['solicitud_id' => $solicitud->id, 'trabajador_id' => $trabajadorId, 'documento_legible' => (bool) ($data['check_documento'] ?? false), 'datos_coinciden' => (bool) ($data['check_datos'] ?? false), 'requisitos_completos' => (bool) ($data['check_requisitos'] ?? false), 'observaciones_internas' => $data['observaciones'] ?? null, 'decision' => $destino]);
            $estados->transicionar($solicitud, $destino, $usuario, $data['observaciones'] ?? null);
        });

        return redirect()->route('revision.asistente')->with('success', 'La decisión fue registrada y la solicitud quedó '.$this->label($destino).'.');
    }

    public function verDocumento(int $id, int $documento, AutorizacionService $authz, SolicitudService $solicitudes)
    {
        $solicitud = $solicitudes->detalle($id);
        $usuario = auth()->user();
        abort_unless($this->puedeVer($usuario, $solicitud, $authz), 403);
        $respaldo = $solicitud->respaldos->firstWhere('id', $documento);
        abort_unless($respaldo && Storage::disk('local')->exists($respaldo->ruta), 404);

        return Storage::disk('local')->response($respaldo->ruta, $respaldo->nombre_original, ['Content-Type' => $respaldo->mime ?: 'application/octet-stream', 'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $respaldo->nombre_original).'"']);
    }

    public function bandejaDirector(Request $request, AutorizacionService $authz)
    {
        $filtros = RevisionSolicitudFilters::validate($request);
        $solicitudes = collect();
        if ($filtros['filtrar']) {
            $query = Solicitud::with(['carreraEstudiante.estudiante', 'carreraEstudiante.carreraModalidad.carrera', 'tipoTramite', 'estadoSolicitud'])->enEstado(Estados::REVISADA);
            if (! $authz->tieneRol(auth()->user(), 'admin')) {
                $trabajador = auth()->user()->trabajador;
                $ids = $trabajador ? $authz->carrerasQueDirige($trabajador)->modelKeys() : [];
                $query->whereHas('carreraEstudiante.carreraModalidad', fn ($q) => $q->whereIn('carrera_id', $ids));
            }
            RevisionSolicitudFilters::apply($query, $filtros);
            $solicitudes = $query->latest('created_at')->get()->map(fn ($s) => $this->presentar($s));
        }

        return view('revision.director', ['solicitudes' => $solicitudes, 'filtros' => $filtros, 'filtrosAplicados' => $filtros['filtrar'], 'tramitesDisponibles' => RevisionSolicitudFilters::tramitesDisponibles(), 'estadosDisponibles' => RevisionSolicitudFilters::ESTADOS]);
    }

    public function guardarDecisionDirector(Request $request, int $id, AutorizacionService $authz, EstadoSolicitudService $estados)
    {
        $data = $request->validate(['accion' => ['required', 'in:aprobar,rechazar'], 'observacion' => ['nullable', 'string', 'max:2000']]);
        $solicitud = Solicitud::with(['carreraEstudiante.carreraModalidad.carrera', 'estadoSolicitud'])->findOrFail($id);
        $usuario = auth()->user();
        abort_unless($authz->tieneRol($usuario, 'admin') || ($usuario->trabajador && $authz->puedeVerCarrera($usuario, (int) $solicitud->carreraEstudiante->carreraModalidad->carrera_id)), 403);
        $destino = $data['accion'] === 'aprobar' ? Estados::APTA_CONSEJO : Estados::RECHAZADA;
        abort_unless($usuario->trabajador, 403);
        DB::transaction(function () use ($solicitud, $destino, $usuario, $data, $estados) {
            Revision::create(['solicitud_id' => $solicitud->id, 'trabajador_id' => $usuario->trabajador->id, 'observaciones_internas' => $data['observacion'] ?? null, 'decision' => $destino]);
            $estados->transicionar($solicitud, $destino, $usuario, $data['observacion'] ?? null);
        });

        return redirect()->route('revision.director', $request->only(['filtrar', 'nombre', 'codigo', 'carrera', 'fecha', 'tramite', 'estado']))->with('success', 'Decisión registrada: '.$this->label($destino).'.');
    }

    private function presentar(Solicitud $s): Solicitud
    {
        $student = $s->carreraEstudiante?->estudiante;
        $career = $s->carreraEstudiante?->carreraModalidad?->carrera;
        $s->setAttribute('codigo_solicitud', $s->codigo);
        $s->setAttribute('codigo_siac', $student?->codigo);
        $s->setAttribute('estudiante', trim(($student?->nombres ?? '').' '.($student?->apellidos ?? '')));
        $s->setAttribute('carrera', $career?->nombre);
        $s->setAttribute('tramite', $s->tipoTramite?->nombre);
        $s->setAttribute('estado', $s->estadoSolicitud?->nombre);
        $s->setAttribute('motivo', data_get($s->detalle, 'motivo', data_get($s->detalle, 'descripcion', '')));
        $s->setAttribute('fecha', $s->enviada_at?->format('d/m/Y') ?? $s->created_at?->format('d/m/Y'));

        return $s;
    }

    private function label(string $code): string
    {
        return match ($code) {
            Estados::REVISADA => 'Revisado',Estados::OBSERVADA => 'Observado',Estados::APTA_CONSEJO => 'Apto para consejo',Estados::RECHAZADA => 'Rechazado',default => $code
        };
    }

    private function puedeVer($usuario, Solicitud $s, AutorizacionService $authz): bool
    {
        if ($authz->tieneRol($usuario, 'admin')) {
            return true;
        }
        $careerId = (int) $s->carreraEstudiante->carreraModalidad->carrera_id;

        return ($authz->tieneRol($usuario, 'asistente') && $authz->puedeVerFacultad($usuario,(int) $s->carreraEstudiante->carreraModalidad->carrera->facultad_id))
            || ($authz->tieneRol($usuario,'director') && $authz->puedeVerCarrera($usuario,$careerId));
    }
}
