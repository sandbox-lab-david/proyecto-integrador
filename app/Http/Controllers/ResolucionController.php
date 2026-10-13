<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resolucion;
use App\Models\Solicitud;
use App\Models\HistorialSolicitud;

class ResolucionController extends Controller
{
    public function index()
    {
        $resoluciones = Resolucion::with(['solicitud', 'corteAgenda'])->get();
        return view('resoluciones.index', compact('resoluciones'));
    }

    public function store(Request $request, $solicitudId)
    {
        $solicitud = Solicitud::findOrFail($solicitudId);

        $resolucion = Resolucion::create([
            'solicitud_id' => $solicitud->id,
            'corte_agenda_id' => $request->input('corte_agenda_id'),
            'numero_resolucion' => $request->input('numero_resolucion'),
            'resultado' => $request->input('resultado'),
            'observaciones' => $request->input('observaciones'),
            'fecha_emision' => now(),
        ]);

        HistorialSolicitud::create([
            'solicitud_id' => $solicitud->id,
            'estado_anterior' => 'En Consejo',
            'estado_nuevo' => 'Resuelto - ' . ucfirst($request->input('resultado')),
            'user_id' => auth()->id() ?? 1,
            'comentario' => 'Resolución N° ' . $request->input('numero_resolucion')
        ]);

        return redirect()->back()->with('success', 'Resolución registrada con éxito.');
    }
}