<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudiante\GenerarDocumentoRequest;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Solicitudes\BorradoresSolicitud;
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
        abort_if($request->filled('solicitud_correccion'), 422, 'Usa la opción de reenviar la solicitud observada.');
        if (! app()->bound(IntegracionEnvio::class)) {
            return response()->json(['message' => 'El envío está pendiente de integrar autenticación, catálogo académico y estados de revisión. Tus datos y archivos siguen guardados temporalmente.'], 503);
        }
        abort_unless($request->user(), 401, 'Inicia sesión para enviar una solicitud.');
        $datos = $request->validated();
        try {
            $solicitud = $envio->enviar($request->user(), $datos);
        } catch (\App\Services\Chatbot\Prolog\PrologException $error) {
            report($error);
            return response()->json(['message' => 'No se pudieron verificar los requisitos en Prolog. No se envió la solicitud; tus archivos se conservan.'], 503);
        }
        return response()->json(['codigo' => $solicitud->codigo, 'url' => route('solicitudes.index')], 201);
    }

    public function editar(Request $request, int $solicitud, BorradoresTemporales $borradores): View
    {
        $expediente = \App\Models\Solicitud::findOrFail($solicitud);
        Gate::authorize('update', $expediente);
        $datos = $expediente->detalle ?? [];
        $codigo = $datos['tramite'] ?? null;
        abort_unless(is_string($codigo) && app(TramitesSimulados::class)->buscar($codigo), 409, 'No se reconoce el trámite original.');
        $request->query->set('tramite', $codigo);
        $request->query->set('solicitud_correccion', $solicitud);
        if ($borradores->buscar($codigo) === null) {
            $borradores->guardar($codigo, \Illuminate\Support\Arr::only($datos, [
                'nombre', 'codigo', 'cedula', 'facultad', 'carrera', 'modalidad', 'periodo', 'correo', 'celular', 'campos',
            ]) + [
                'paso' => 1, 'requisitos' => [],
                'materias' => array_map(fn ($materia) => \Illuminate\Support\Arr::only($materia, ['id', ...array_keys(DatosFormularioSolicitud::MATERIA), 'datos']), $datos['materias'] ?? []),
            ]);
            $request->session()->put('correcciones_base.'.$solicitud, EnvioSolicitud::revision($expediente));
        }
        return app()->call([$this, 'create'], ['request' => $request]);
    }

    public function reenviar(EnviarSolicitudRequest $request, int $solicitud, TramitesSimulados $catalogo, EnvioSolicitud $envio)
    {
        $expediente = \App\Models\Solicitud::findOrFail($solicitud);
        Gate::authorize('update', $expediente);
        abort_unless((int) $request->input('solicitud_correccion') === $solicitud, 422);
        if (! app()->bound(IntegracionEnvio::class)) {
            return response()->json(['message' => 'El reenvío está pendiente del adaptador de estados de revisión. Tu corrección sigue guardada como borrador.'], 503);
        }
        $datos = $request->validated();
        try {
            $guardada = $envio->enviar($request->user(), $datos, $expediente, $request->session()->get('correcciones_base.'.$solicitud));
        } catch (\App\Services\Chatbot\Prolog\PrologException $error) {
            report($error);
            return response()->json(['message' => 'No se pudieron verificar los requisitos en Prolog. La corrección no se envió.'], 503);
        }
        $request->session()->forget('correcciones_base.'.$solicitud);
        return response()->json(['codigo' => $guardada->codigo, 'url' => route('solicitudes.index')]);
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
                : app(\App\Services\Solicitudes\SolicitudesPropias::class)->consultar(auth()->user()),
        ]);
    }

    /**
     * Seguimiento de una solicitud guardada: estado, línea de tiempo,
     * observaciones y resolución final.
     */
    public function show(int $solicitud, SolicitudService $solicitudes): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless(SeguimientoSolicitudes::disponible(), 404);

        $borrador = \App\Models\Solicitud::borradores()->find($solicitud);
        if ($borrador !== null) {
            // Un borrador no tiene seguimiento: se retoma en el formulario.
            Gate::authorize('view', $borrador);

            return redirect()->route('solicitudes.create', ['tramite' => $borrador->detalle['tramite'] ?? '']);
        }

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
                $carpeta = sys_get_temp_dir().DIRECTORY_SEPARATOR.'uees-descarga-'.\Illuminate\Support\Str::uuid();
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
            // El PDF descargado se conserva con el Word y pasa al expediente al enviar.
            $temporales->guardar($datos['tramite'], $documento, $datos, $formato === 'pdf' ? $contenidoDescarga : null);
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
        BorradoresSolicitud $borradores,
        FirmadosTemporales $firmados,
        DocumentosTemporales $temporales,
        \App\Services\Solicitudes\PerfilFormularioSolicitud $perfil,
    ): View {
        $codigoTramite = (string) (
            $request->route('tramite') ?? $request->query('tramite')
        );

        $tramite = $tramites->buscar($codigoTramite);

        abort_if($tramite === null, 404);
        $codigoTramite = substr($tramite['ficha'], strrpos($tramite['ficha'], '.') + 1);

        $requisitosMotor = app(\App\Services\Solicitudes\RequisitosSolicitud::class)->paraVista($codigoTramite);
        $tramite['documentos'] = $requisitosMotor['documentos'];
        $tramite['aviso'] = $requisitosMotor['aviso'];
        $tramite['documentos_orientativos'] = false;

        $expedienteCorreccion = null;
        $observacionesCorreccion = null;
        if ($request->filled('solicitud_correccion')) {
            $expedienteCorreccion = \App\Models\Solicitud::findOrFail((int) $request->input('solicitud_correccion'));
            Gate::authorize('update', $expedienteCorreccion);
            if (SeguimientoSolicitudes::disponible()
                && method_exists(\App\Services\Flujo\EstadoSolicitudService::class, 'lineaDeTiempo')) {
                $observacionesCorreccion = app(SeguimientoSolicitudes::class)->observaciones($expedienteCorreccion);
            }
        }

        $borrador = $borradores->buscar($codigoTramite);
        if ($borrador) {
            $borrador = CatalogoFacultades::normalizar($borrador);
            $borrador = array_replace($borrador, app(\App\Services\Solicitudes\MateriasSolicitud::class)
                ->entrada($borrador + ['origen_materias' => 'catalogo']));
        }

        $datosCuenta = DatosFormularioSolicitud::cuenta($request->user());
        if ($borrador) {
            $borrador = array_replace($borrador, $datosCuenta, $perfil->fijos($borrador));
        }
        $datosFijos = $perfil->fijos([]);

        return view('estudiante.formulario-solicitud', [
            'solicitudCorreccion' => $expedienteCorreccion,
            'observacionesCorreccion' => $observacionesCorreccion,
            'requisitosMotor' => $requisitosMotor,
            'codigoTramite' => $codigoTramite,
            'tramite' => $tramite,
            'datosCuenta' => $datosCuenta,
            'datosIniciales' => array_replace($perfil->iniciales(), $datosFijos),
            'datosFijos' => $datosFijos,
            'modalidadesMatricula' => $perfil->modalidades(),
            'camposPersonales' => DatosFormularioSolicitud::PERSONALES,
            'camposMateriaManual' => DatosFormularioSolicitud::MATERIA,
            'materias' => app(\App\Services\Solicitudes\MateriasSolicitud::class)->todas(),
            'catalogoFacultades' => $perfil->facultades(),
            'respaldosSubidos' => $respaldos->delTramite($codigoTramite),
            'borrador' => $borrador,
            'firmado' => $firmados->delTramite($codigoTramite),
            'versionDocumento' => $temporales->buscar($codigoTramite)['version'] ?? null,
            'envioDisponible' => app()->bound(IntegracionEnvio::class) && $request->user() !== null,
        ]);
    }
}
