<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevisionController extends Controller
{
<<<<<<< HEAD
    /**
     * Muestra la bandeja de solicitudes para la asistente.
     */
    public function index(Request $request)
    {
        $query = DB::table('solicitudes');

        // Filtro opcional por estado si se envía en la petición
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Obtener la lista de solicitudes ordenadas de más reciente a más antigua
        $solicitudes = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('revision.asistente', compact('solicitudes'));
    }

    /**
     * Muestra el detalle del caso y el expediente para su respectiva revisión.
     */
    public function show($id)
=======
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
>>>>>>> 14da9b03ae2b6f3bbe13fb26951d91d301d13bd9
    {
        $solicitud = DB::table('solicitudes')->where('id', $id)->first();

        if (!$solicitud) {
<<<<<<< HEAD
            return redirect()->route('revision.asistente')->with('error', 'La solicitud no existe.');
        }

        // Buscar si ya existe una revisión previa para este caso
        $revision = DB::table('revisiones')->where('solicitud_id', $id)->first();

        return view('revision.detalle', compact('solicitud', 'revision'));
    }

    /**
     * Procesa la decisión de la asistente (Aplica, Requiere corrección o Rechazado).
     */
    public function decidir(Request $request, $id)
    {
        $solicitud = DB::table('solicitudes')->where('id', $id)->first();

        if (!$solicitud) {
            return redirect()->route('revision.asistente')->with('error', 'La solicitud no existe.');
        }

        // Validar si el caso ya se encuentra finalizado
        $estadosFinales = ['Aplica', 'Rechazado', 'Aprobado'];
        if (in_array($solicitud->estado, $estadosFinales)) {
            return redirect()->back()->with('error', 'Este caso ya fue procesado y sus datos se encuentran bloqueados.');
        }

        $decisionInput = $request->input('decision');
        $estadoAnterior = $solicitud->estado;
        $nuevoEstado = $estadoAnterior;
        $decisionRevision = 'devuelta';

        // Mapeo de la decisión a los estados oficiales del proyecto
        switch ($decisionInput) {
            case 'aplica':
                $nuevoEstado = 'Aplica';
                $decisionRevision = 'aplica';
                break;
            case 'correccion':
                $nuevoEstado = 'Requiere corrección';
                $decisionRevision = 'correccion';
                break;
            case 'rechaza':
                $nuevoEstado = 'Rechazado';
                $decisionRevision = 'rechazada';
                break;
            default:
                return redirect()->back()->with('error', 'Decisión no válida.');
        }

        $observaciones = $request->input('observaciones_internas');
        $docLegible = $request->has('check_documento') ? 1 : 0;
        $datosCoincidentes = $request->has('check_datos') ? 1 : 0;
        $reqCompletos = $request->has('check_requisitos') ? 1 : 0;

        // 1. Guardar o actualizar la revisión en la tabla 'revisiones' (usando 'observacion')
        DB::table('revisiones')->updateOrInsert(
            ['solicitud_id' => $solicitud->id],
            [
                'trabajador_id'        => auth()->id() ?? 1,
                'decision'             => $decisionRevision,
                'documento_legible'    => $docLegible,
                'datos_coincidentes'   => $datosCoincidentes,
                'requisitos_completos' => $reqCompletos,
                'observacion'          => $observaciones,
                'updated_at'           => now(),
                'created_at'           => now(),
            ]
        );

        // 2. Actualizar el estado de la solicitud principal
        DB::table('solicitudes')->where('id', $id)->update([
            'estado'     => $nuevoEstado,
            'updated_at' => now(),
        ]);

        // 3. Registrar la bitácora inmutable en 'historial_solicitud' (alineado con 'observacion' y 'observacion_interna')
        DB::table('historial_solicitud')->insert([
            'solicitud_id'        => $solicitud->id,
            'estado_anterior'     => $estadoAnterior,
            'estado_nuevo'        => $nuevoEstado,
            'user_id'             => auth()->id() ?? 1,
            'observacion'         => $observaciones ?? 'Revisión documentaria realizada por asistente.',
            'observacion_interna' => $observaciones,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return redirect()->route('revision.caso.detalle', $id)
            ->with('success', 'La decisión se ha registrado correctamente y el estado cambió a: ' . $nuevoEstado);
=======
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
>>>>>>> 14da9b03ae2b6f3bbe13fb26951d91d301d13bd9
    }
}