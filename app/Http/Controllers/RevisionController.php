<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\RevisionSolicitudFilters;
use Illuminate\Support\Facades\DB;

class RevisionController extends Controller
{
    public function bandejaAsistente(Request $request)
    {
        $filtros = RevisionSolicitudFilters::validate($request);
        $filtrosAplicados = $filtros['filtrar'];
        $solicitudes = collect();

        if ($filtrosAplicados) {
            $query = DB::table('solicitudes_revision')
                ->select('id', 'estudiante', 'codigo_siac as codigo', 'carrera', 'tramite', 'estado');
            RevisionSolicitudFilters::apply($query, $filtros);
            $solicitudes = $query->get();
        }

        $tramitesDisponibles = DB::table('solicitudes_revision')->whereNotNull('tramite')->distinct()->orderBy('tramite')->pluck('tramite');
        $estadosDisponibles = RevisionSolicitudFilters::ESTADOS;

        return view('revision.asistente', compact('solicitudes', 'filtros', 'tramitesDisponibles', 'estadosDisponibles', 'filtrosAplicados'));
    }

    public function detalleCaso($id)
    {
        $solicitud = DB::table('solicitudes_revision')
            ->select('id', 'codigo_solicitud as codigo', 'estudiante', 'codigo_siac as codigo_estudiante', 'carrera', 'tramite', 'materia', 'motivo', 'estado')
            ->where('id', $id)
            ->first();

        if (!$solicitud) {
            abort(404, 'La solicitud no fue encontrada.');
        }

        if (isset($solicitud->documentos) && is_string($solicitud->documentos)) {
            $solicitud->documentos = json_decode($solicitud->documentos);
        }

        return view('revision.detalle', compact('solicitud'));
    }

    public function guardarRevision(Request $request, $id)
    {
        // Verificar el estado actual para evitar modificaciones si ya está finalizado
        $solicitudActual = DB::table('solicitudes_revision')->where('id', $id)->first();
        if ($solicitudActual && $solicitudActual->estado !== 'Enviado por estudiante') {
            return redirect()->route('revision.detalle', $id)
                ->with('error', 'Este caso ya fue procesado y no puede ser modificado.');
        }

        $datos = $request->validate(['accion' => ['required', 'in:aprobar,observar,rechazar']]);
        $estado = match ($datos['accion']) {
            'aprobar' => 'Revisado',
            'observar' => 'Observado',
            'rechazar' => 'Rechazado',
        };

        DB::table('solicitudes_revision')->where('id', $id)->update([
            'estado' => $estado,
            'updated_at' => now(),
        ]);

        $mensaje = match ($datos['accion']) {
            'aprobar' => 'Caso revisado y enviado a la bandeja del Director.',
            'observar' => 'La solicitud quedó marcada como Observado.',
            'rechazar' => 'La solicitud quedó marcada como Rechazado.',
        };

        return redirect()->route('revision.asistente')->with('success', $mensaje);
    }

    public function bandejaDirector(Request $request)
    {
        $filtros = RevisionSolicitudFilters::validate($request);
        $filtrosEfectivos = $filtros;
        if (! $filtros['filtrar']) {
            $filtrosEfectivos['estado'] = 'Revisado';
            $filtros['estado'] = 'Revisado';
        }

        $query = DB::table('solicitudes_revision')
            ->select('id', 'codigo_solicitud', 'codigo_siac', 'estudiante', 'carrera', 'tramite', 'estado', 'created_at');
        RevisionSolicitudFilters::apply($query, $filtrosEfectivos);
        $solicitudes = $query->orderBy('created_at')->get();
        $tramitesDisponibles = DB::table('solicitudes_revision')->whereNotNull('tramite')->distinct()->orderBy('tramite')->pluck('tramite');
        $estadosDisponibles = RevisionSolicitudFilters::ESTADOS;

        return view('revision.director', compact('solicitudes', 'filtros', 'tramitesDisponibles', 'estadosDisponibles'));
    }

    public function guardarDecisionDirector(Request $request, $id)
    {
        $datos = $request->validate(['accion' => ['required', 'in:aprobar,rechazar'], 'observacion' => ['nullable', 'string', 'max:2000']]);
        $caso = DB::table('solicitudes_revision')->where('id', $id)->first();
        abort_unless($caso && $caso->estado === 'Revisado', 404);
        $estado = $datos['accion'] === 'aprobar' ? 'Apto para consejo' : 'Rechazado';
        DB::table('solicitudes_revision')->where('id', $id)->update([
            'estado' => $estado, 'observacion_director' => $datos['observacion'] ?? null, 'updated_at' => now(),
        ]);
        return redirect()->route('revision.director', $request->only(['filtrar', 'nombre', 'codigo', 'carrera', 'fecha', 'tramite', 'estado']))
            ->with('success', 'Decisión registrada: '.$estado.'.');
    }
}
