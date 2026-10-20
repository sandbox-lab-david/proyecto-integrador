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
use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\DocumentosTemporales;
use App\Services\Solicitudes\IntegracionEnvio;
use App\Services\Solicitudes\EnvioSolicitud;
use App\Services\Solicitudes\DatosFormularioSolicitud;
use App\Services\Solicitudes\CatalogoFacultades;
use App\Http\Requests\Estudiante\EnviarSolicitudRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    public function enviar(EnviarSolicitudRequest $request, TramitesSimulados $catalogo, EnvioSolicitud $envio)
    {
        if (! app()->bound(IntegracionEnvio::class)) {
            return response()->json(['message' => 'El envío está pendiente de integrar autenticación, catálogo académico y estados de revisión. Tus datos y archivos siguen guardados temporalmente.'], 503);
        }
        abort_unless($request->user(), 401, 'Inicia sesión para enviar una solicitud.');
        $datos = $request->validated();
        $solicitud = $envio->enviar($request->user(), $datos);
        return response()->json(['codigo' => $solicitud->codigo, 'url' => route('solicitudes.index')], 201);
    }

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
        DocumentoSolicitud $documentos,
        DocumentosTemporales $temporales,
        FirmadosTemporales $firmados,
    ) {
        $datos = $request->validated();


        try {
            $documento = $documentos->generarWord($datos);
            $contenidoDescarga = $documento;
            $formato = $datos['formato'] ?? 'docx';
            if ($formato === 'pdf') {
                $carpeta = storage_path('app/private/descargas/'.\Illuminate\Support\Str::uuid());
                \Illuminate\Support\Facades\File::ensureDirectoryExists($carpeta);
                $rutaPdf = null;
                try {
                    file_put_contents($carpeta.'/documento.docx', $documento);
                    $rutaPdf = app(\App\Services\Documentos\WordService::class)->convertirAPdf($carpeta.'/documento.docx');
                    $contenidoDescarga = file_get_contents($rutaPdf);
                } finally {
                    \Illuminate\Support\Facades\File::deleteDirectory($carpeta);
                    if ($rutaPdf) {
                        \Illuminate\Support\Facades\File::deleteDirectory(dirname($rutaPdf));
                    }
                }
            }
            $temporales->guardar($datos['tramite'], $documento, $datos);
            // Cada descarga es una versión nueva, incluso si solo cambió la fecha.
            $firmados->quitar($datos['tramite']);
        } catch (\RuntimeException $error) {
            report($error);

            return response()->json([
                'message' => $error->getMessage(),
            ], 503);
        }

        return response($contenidoDescarga, 200, [
            'Content-Type' => $formato === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="solicitud-'.$datos['tramite'].'.'.$formato.'"',
            'Cache-Control' => 'private, no-store',
            'X-Firmado-Conservado' => $firmados->buscar($datos['tramite']) ? '1' : '0',
            'X-Documento-Version' => $temporales->buscar($datos['tramite'])['version'],
        ]);
    }

    public function create(
        Request $request,
        TramitesSimulados $tramites,
        RespaldosTemporales $respaldos,
        BorradoresTemporales $borradores,
        FirmadosTemporales $firmados,
        DocumentosTemporales $temporales,
    ): View {
        $codigoTramite = (string) (
            $request->route('tramite') ?? $request->query('tramite')
        );

        $tramite = $tramites->buscar($codigoTramite);

        abort_if($tramite === null, 404);

        return view('estudiante.formulario-solicitud', [
            'codigoTramite' => $codigoTramite,
            'tramite' => $tramite,
            'datosCuenta' => DatosFormularioSolicitud::cuenta($request->user()),
            'camposPersonales' => DatosFormularioSolicitud::PERSONALES,
            'camposMateriaManual' => DatosFormularioSolicitud::MATERIA,
            'catalogoFacultades' => CatalogoFacultades::todas(),
            'respaldosSubidos' => $respaldos->delTramite($codigoTramite),
            'borrador' => ($borrador = $borradores->buscar($codigoTramite)) ? CatalogoFacultades::normalizar($borrador) : null,
            'firmado' => $firmados->delTramite($codigoTramite),
            'versionDocumento' => $temporales->buscar($codigoTramite)['version'] ?? null,
            'envioDisponible' => app()->bound(IntegracionEnvio::class) && $request->user() !== null,
        ]);
    }
}
