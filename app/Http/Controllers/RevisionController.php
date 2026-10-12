<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;

class RevisionController extends Controller
{
    public function bandejaAsistente(Request $request)
    {
        $filtroEstado = $request->get('estado');
        $filtroTramite = $request->get('tramite');

        $query = Solicitud::query();

        if ($filtroEstado) {
            $query->where('estado', $filtroEstado);
        }
        if ($filtroTramite) {
            $query->where('tramite', $filtroTramite);
        }

        $solicitudes = $query->get();

        $tramitesDisponibles = Solicitud::distinct()->pluck('tramite');
        $estadosDisponibles = Solicitud::distinct()->pluck('estado');

        return view('revision.asistente', compact('solicitudes', 'filtroEstado', 'filtroTramite', 'tramitesDisponibles', 'estadosDisponibles'));
    }

    public function detalleCaso($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        return view('revision.detalle', compact('solicitud'));
    }

    public function guardarRevision(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if (in_array($solicitud->estado, ['Aprobado', 'Rechazado'])) {
            return redirect()->route('revision.detalle', $id)
                ->with('error', 'Este caso ya fue procesado y no puede ser modificado.');
        }

        $accion = $request->input('accion');
        $nuevoEstado = 'En revisión';

        if ($accion === 'aprobar') {
            $nuevoEstado = 'Aprobado';
        } elseif ($accion === 'rechazar') {
            $nuevoEstado = 'Rechazado';
        } elseif ($accion === 'observado') {
            $nuevoEstado = 'Observado';
        }

        $solicitud->update([
            'estado' => $nuevoEstado
        ]);

        return redirect()->route('revision.asistente')
            ->with('success', 'La solicitud ha sido actualizada a estado: ' . $nuevoEstado);
    }
}