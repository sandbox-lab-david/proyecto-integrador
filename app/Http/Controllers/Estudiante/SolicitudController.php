<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudiante\GenerarDocumentoRequest;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Solicitudes\BorradoresTemporales;
use App\Services\Solicitudes\SeguimientoSolicitudes;
use App\Services\Solicitudes\SolicitudesSinTerminar;
use App\Services\Solicitudes\SolicitudService;
use App\Services\Solicitudes\TramitesSimulados;
use App\Services\Solicitudes\DocumentoRecuperacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    /**
     * «Mis solicitudes» (RF-2.13). Hoy lista lo que la sesión tiene sin
     * terminar; las guardadas aparecen cuando G1 y G3 publiquen sus servicios.
     */
    public function index(SolicitudesSinTerminar $sinTerminar): View
    {
        return view('estudiante.mis-solicitudes', [
            'sinTerminar' => $sinTerminar->todas(),
            // null: el seguimiento todavía no se puede consultar y la vista lo dice.
            'solicitudes' => SeguimientoSolicitudes::disponible()
                ? app(SeguimientoSolicitudes::class)->delEstudianteActual()
                : null,
        ]);
    }

    /**
     * Seguimiento de una solicitud guardada: estado, línea de tiempo,
     * observaciones y resolución final.
     */
    public function show(int $solicitud, SolicitudService $solicitudes): View
    {
        abort_unless(SeguimientoSolicitudes::disponible(), 404);

        $expediente = $solicitudes->detalle($solicitud);

        Gate::authorize('view', $expediente);

        return view('estudiante.solicitud', [
            'solicitud' => $expediente,
            'observaciones' => app(SeguimientoSolicitudes::class)
                ->observaciones($expediente),
        ]);
    }

    public function documento(
        GenerarDocumentoRequest $request,
        TramitesSimulados $tramites,
        DocumentoRecuperacion $documentos
    ) {
        $datos = $request->validated();

        $datos['materia'] = collect($tramites->materias())
            ->firstWhere('id', $datos['materia_id']);

        abort_unless(
            $datos['materia'],
            422,
            'Selecciona una materia del catálogo de prueba.'
        );

        try {
            $documento = $documentos->generarWord($datos);
        } catch (\RuntimeException $error) {
            report($error);

            return response()->json([
                'message' => $error->getMessage(),
            ], 503);
        }

        return response($documento, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="solicitud-recuperacion.docx"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function create(
        Request $request,
        TramitesSimulados $tramites,
        RespaldosTemporales $respaldos,
        BorradoresTemporales $borradores,
        FirmadosTemporales $firmados,
    ): View {
        $codigoTramite = (string) (
            $request->route('tramite') ?? $request->query('tramite')
        );

        $tramite = $tramites->buscar($codigoTramite);

        abort_if($tramite === null, 404);

        return view('estudiante.formulario-solicitud', [
            'codigoTramite' => $codigoTramite,
            'tramite' => $tramite,
            'materias' => $tramites->materias(),
            'respaldosSubidos' => $respaldos->delTramite($codigoTramite),
            'borrador' => $borradores->buscar($codigoTramite),
            'firmado' => $firmados->delTramite($codigoTramite),
        ]);
    }
}