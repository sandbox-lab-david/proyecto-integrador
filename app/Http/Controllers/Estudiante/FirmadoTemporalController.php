<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\Archivos\ArchivoRechazado;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Solicitudes\TramitesSimulados;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * PDF firmado del paso 4 mientras la solicitud todavía no existe (RF-2.11).
 */
class FirmadoTemporalController extends Controller
{
    public function __construct(private readonly FirmadosTemporales $firmados) {}

    public function store(Request $request, TramitesSimulados $tramites, string $tramite): JsonResponse
    {
        abort_if($tramites->buscar($tramite) === null, 404);

        $limiteServidor = round(UploadedFile::getMaxFilesize() / (1024 * 1024), 1);

        $request->validate([
            'archivo' => ['required', 'file'],
        ], [
            'archivo.required' => 'Selecciona el PDF firmado.',
            'archivo.file' => 'Selecciona el PDF firmado.',
            // PHP lo descartó antes de llegar a la aplicación (upload_max_filesize).
            'archivo.uploaded' => "El servidor no aceptó el archivo: hoy solo recibe hasta {$limiteServidor} MB.",
        ]);

        try {
            $firmado = $this->firmados->guardar($request->file('archivo'), $tramite);
        } catch (ArchivoRechazado $error) {
            throw ValidationException::withMessages(['archivo' => $error->getMessage()]);
        }

        return response()->json($firmado, 201);
    }

    /**
     * Vista previa del PDF, lista para insertarse en la página.
     */
    public function show(string $tramite): View
    {
        $firmado = $this->firmados->buscar($tramite);

        abort_if($firmado === null, 404);

        return view('estudiante.partials.visor-respaldo', ['respaldo' => $firmado]);
    }

    public function destroy(string $tramite): Response
    {
        $this->firmados->quitar($tramite);

        return response()->noContent();
    }
}
