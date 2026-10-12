<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CargaController extends Controller
{
    /**
     * Muestra el formulario inicial de carga y descarga de plantillas.
     */
    public function index()
    {
        return view('administracion.cargas.index');
    }

    /**
     * Procesa temporalmente el archivo subido para generar la vista previa.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'tipo_carga' => 'required|string',
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $tipoCarga = $request->input('tipo_carga');
        $file = $request->file('archivo_excel');

        // Guardar archivo temporalmente para procesar en la previsualización
        $path = $file->storeAs('temp_imports', Str::uuid() . '.' . $file->getClientOriginalExtension());

        // Ejemplo de estructura procesada (puedes adaptarlo con Maatwebsite/Excel o League/CSV)
        // Aquí simulamos la lectura y validación de las filas del archivo
        $filasProcesadas = [
            [
                'num_fila' => 2,
                'codigo' => 'EST-001',
                'nombre' => 'Juan Pérez',
                'estado' => 'valido', // 'valido', 'duplicado', 'error'
                'observacion' => 'Listo para registrar',
            ],
            [
                'num_fila' => 3,
                'codigo' => 'EST-002',
                'nombre' => 'María López',
                'estado' => 'duplicado',
                'observacion' => 'El código ya existe en el sistema',
            ],
            [
                'num_fila' => 4,
                'codigo' => 'INVALIDO',
                'nombre' => 'Carlos Ruiz',
                'estado' => 'error',
                'observacion' => 'Formato de correo electrónico o cédula incorrecto',
            ],
        ];

        // Resumen de las filas analizadas
        $resumen = [
            'correctas' => collect($filasProcesadas)->where('estado', 'valido')->count(),
            'duplicados' => collect($filasProcesadas)->where('estado', 'duplicado')->count(),
            'errores' => collect($filasProcesadas)->where('estado', 'error')->count(),
        ];

        $batchId = Str::uuid()->toString();

        return view('administracion.cargas.preview', [
            'tipoCarga' => $tipoCarga,
            'filas' => $filasProcesadas,
            'resumen' => $resumen,
            'batchId' => $batchId,
        ]);
    }

    /**
     * Confirma la inserción definitiva de los registros válidos en la base de datos.
     */
    public function procesar(Request $request)
    {
        $batchId = $request->input('batch_id');

        // Aquí ejecutas la lógica para insertar únicamente las filas válidas
        // en las tablas correspondientes según el tipo de carga.

        return redirect()->route('cargas.historial')
            ->with('success', 'La carga de datos se procesó correctamente.');
    }

    /**
     * Muestra la tabla con el historial de cargas realizadas.
     */
    public function historial()
    {
        // Si ya tienen una tabla/modelo de historial (ej: CargaHistorial), pueden usar:
        // $historial = CargaHistorial::with('user')->latest()->get();
        
        $historial = []; // Reemplazar con la consulta Eloquent cuando la tabla esté definida

        return view('administracion.cargas.historial', compact('historial'));
    }

    /**
     * Permite descargar las plantillas .xlsx vacías.
     */
    public function descargarPlantilla($tipo)
    {
        $fileName = "plantilla_{$tipo}.xlsx";
        $filePath = "plantillas/{$fileName}";

        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        }

        return back()->with('error', "La plantilla para {$tipo} aún no está disponible.");
    }

    /**
     * Genera y descarga el archivo con las filas que dieron error.
     */
    public function descargarReporteErrores($id)
    {
        // Lógica para recuperar el reporte de errores de la carga $id
        $fileName = "reporte_errores_carga_{$id}.csv";

        return response()->streamDownload(function () {
            echo "Fila,Codigo,Error\n";
            echo "4,INVALIDO,Formato de correo electrónico o cédula incorrecto\n";
        }, $fileName);
    }
}