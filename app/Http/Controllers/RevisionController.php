<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevisionController extends Controller
{
    public function bandejaAsistente(Request $request)
    {
        $filtroEstado = $request->get('estado');
        $filtroTramite = $request->get('tramite');

        $query = DB::table('solicitudes');

        if ($filtroEstado) {
            $query->where('estado', $filtroEstado);
        }
        if ($filtroTramite) {
            $query->where('tramite', $filtroTramite);
        }

        $solicitudes = $query->get()->map(function($item) {
            if (isset($item->documentos) && is_string($item->documentos)) {
                $item->documentos = json_decode($item->documentos);
            }
            return $item;
        });

        $tramitesDisponibles = DB::table('solicitudes')->distinct()->pluck('tramite');
        $estadosDisponibles = DB::table('solicitudes')->distinct()->pluck('estado');

        return view('revision.asistente', compact('solicitudes', 'filtroEstado', 'filtroTramite', 'tramitesDisponibles', 'estadosDisponibles'));
    }

    public function detalleCaso($id)
    {
        $solicitud = DB::table('solicitudes')->where('id', $id)->first();

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
        $solicitudActual = DB::table('solicitudes')->where('id', $id)->first();
        if ($solicitudActual && in_array($solicitudActual->estado, ['Aprobado', 'Rechazado'])) {
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

        // Actualizar estado en PostgreSQL
        DB::table('solicitudes')->where('id', $id)->update([
            'estado' => $nuevoEstado
        ]);

        return redirect()->route('revision.asistente')
            ->with('success', 'La solicitud ha sido actualizada a estado: ' . $nuevoEstado);
    }
}