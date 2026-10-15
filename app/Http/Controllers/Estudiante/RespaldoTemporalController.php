<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\Archivos\ArchivoRechazado;
use App\Services\Archivos\RespaldosTemporales;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Respaldos del paso 3 del formulario mientras la solicitud todavía no existe.
 */
class RespaldoTemporalController extends Controller
{
    public function __construct(private readonly RespaldosTemporales $respaldos) {}

    public function store(Request $request): JsonResponse
    {
        $limiteServidor = round(UploadedFile::getMaxFilesize() / (1024 * 1024), 1);

        $request->validate([
            'tramite' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_-]+$/'],
            'archivo' => ['required', 'file'],
        ], [
            'tramite.*' => 'No se reconoce el trámite de esta solicitud.',
            'archivo.required' => 'Selecciona un archivo.',
            'archivo.file' => 'Selecciona un archivo.',
            // PHP lo descartó antes de llegar a la aplicación (upload_max_filesize).
            'archivo.uploaded' => "El servidor no aceptó el archivo: hoy solo recibe hasta {$limiteServidor} MB.",
        ]);

        try {
            $respaldo = $this->respaldos->agregar($request->file('archivo'), $request->input('tramite'));
        } catch (ArchivoRechazado $error) {
            throw ValidationException::withMessages(['archivo' => $error->getMessage()]);
        }

        return response()->json($respaldo, 201);
    }

    /**
     * Vista previa del archivo, lista para insertarse en la página.
     */
    public function show(string $id): View
    {
        $respaldo = $this->respaldos->buscar($id);

        abort_if($respaldo === null, 404);

        return view('estudiante.partials.visor-respaldo', ['respaldo' => $respaldo]);
    }

    public function destroy(string $id): Response
    {
        $this->respaldos->quitar($id);

        return response()->noContent();
    }
}
