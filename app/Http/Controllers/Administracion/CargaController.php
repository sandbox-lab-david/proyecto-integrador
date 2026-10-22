<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Jobs\ProcesarImportacion;
use App\Models\Importacion;
use App\Services\Academico\ImportacionService;
use App\Services\Academico\Importadores\Plantilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CargaController extends Controller
{
    use MensajesValidacion;

    public function __construct(private ImportacionService $servicio) {}

    public function index()
    {
        return view('administracion.cargas.index', [
            'importadores' => collect(ImportacionService::IMPORTADORES)->map(fn ($clase, $entidad) => $this->servicio->importador($entidad)),
            'pendientes' => Importacion::where('estado', 'pendiente')->where('user_id', auth()->id())->latest()->get(),
        ]);
    }

    public function subir(Request $request)
    {
        $datos = $request->validate([
            'entidad' => 'required|in:'.implode(',', array_keys(ImportacionService::IMPORTADORES)),
            'archivo' => 'required|file|extensions:xlsx,xls,csv|max:10240',
        ], $this->mensajes([
            'entidad.required' => 'Elige qué vas a importar.',
            'archivo.required' => 'Elige el archivo.',
            'archivo.extensions' => 'El archivo debe ser .xlsx, .xls o .csv.',
            'archivo.max' => 'El archivo no puede pasar de 10 MB.',
        ]));

        $archivo = $request->file('archivo');
        $nombre = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'archivo';
        $ruta = $archivo->storeAs('importaciones/'.Str::uuid(), $nombre.'.'.strtolower($archivo->getClientOriginalExtension()), 'local');

        $importacion = Importacion::create([
            'entidad' => $datos['entidad'],
            'archivo_ruta' => $ruta,
            'user_id' => auth()->id(),
            'estado' => 'pendiente',
        ]);

        return redirect()->route('cargas.vista-previa', $importacion);
    }

    public function vistaPrevia(Importacion $importacion)
    {
        if ($importacion->estado !== 'pendiente') {
            return redirect()->route('cargas.detalle', $importacion);
        }

        $analisis = $this->servicio->analizar($importacion);
        $importacion->update(['total_filas' => $analisis['resumen']['total']]);

        return view('administracion.cargas.preview', [
            'importacion' => $importacion,
            'importador' => $this->servicio->importador($importacion->entidad),
            'analisis' => $analisis,
            'filas' => collect($analisis['filas'])->sortBy(fn ($f) => $f['estado'] === 'error' ? 0 : 1)->take(200),
        ]);
    }

    public function procesar(Importacion $importacion)
    {
        abort_unless($importacion->estado === 'pendiente', 409, 'Esta importación ya se procesó.');

        if ($importacion->total_filas > ImportacionService::LIMITE_INMEDIATO) {
            $importacion->update(['estado' => 'procesando']);
            ProcesarImportacion::dispatch($importacion);

            return redirect()->route('cargas.detalle', $importacion)
                ->with('exito', "El archivo tiene {$importacion->total_filas} filas: se está procesando en segundo plano. Actualiza esta página en unos minutos.");
        }

        ProcesarImportacion::dispatchSync($importacion);
        $importacion->refresh();

        return redirect()->route('cargas.detalle', $importacion)
            ->with($importacion->estado === 'completada' ? 'exito' : 'error', $importacion->estado === 'completada'
                ? "Importación terminada: {$importacion->creadas} creadas, {$importacion->actualizadas} actualizadas, {$importacion->rechazadas} rechazadas."
                : 'La importación no se pudo completar. Revisa el detalle.');
    }

    public function cancelar(Importacion $importacion)
    {
        abort_unless($importacion->estado === 'pendiente', 409, 'Solo se puede cancelar una importación que todavía no se procesó.');

        Storage::disk('local')->deleteDirectory(dirname($importacion->archivo_ruta));
        $importacion->delete();

        return redirect()->route('cargas.index')->with('exito', 'Importación cancelada. No se guardó nada.');
    }

    public function historial(Request $request)
    {
        $importaciones = Importacion::with('user')
            ->where('estado', '!=', 'pendiente')
            ->when($request->filled('entidad'), fn ($q) => $q->where('entidad', $request->input('entidad')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('administracion.cargas.historial', [
            'importaciones' => $importaciones,
            'titulos' => collect(ImportacionService::IMPORTADORES)->map(fn ($c, $e) => $this->servicio->importador($e)->titulo()),
        ]);
    }

    public function detalle(Importacion $importacion)
    {
        return view('administracion.cargas.detalle', [
            'importacion' => $importacion->load('user'),
            'importador' => $this->servicio->importador($importacion->entidad),
        ]);
    }

    public function descargarPlantilla(string $tipo)
    {
        $importador = $this->servicio->importador($tipo);

        return Excel::download(new Plantilla(array_keys($importador->columnas())), "plantilla_{$tipo}.xlsx");
    }

    public function descargarEjemplo(string $tipo)
    {
        $this->servicio->importador($tipo);
        $ruta = database_path("data/ejemplos/{$tipo}.csv");
        abort_unless(is_file($ruta), 404);

        return response()->download($ruta, "ejemplo_{$tipo}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function descargarReporteErrores(Importacion $importacion)
    {
        return response()->streamDownload(function () use ($importacion) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\u{FEFF}");
            fputcsv($salida, ['fila', 'codigo', 'motivo']);
            foreach ($importacion->errores ?? [] as $error) {
                fputcsv($salida, [$error['fila'] ?? '', $error['codigo'] ?? '', $error['motivo'] ?? '']);
            }
            fclose($salida);
        }, "errores_importacion_{$importacion->id}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
