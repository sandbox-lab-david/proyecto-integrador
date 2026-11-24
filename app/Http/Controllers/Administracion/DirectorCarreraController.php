<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\DirectorCarrera;
use App\Models\Trabajador;
use App\Services\Solicitudes\ReglasCampoSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DirectorCarreraController extends Controller
{
    use MensajesValidacion;

    public function index(Request $request)
    {
        $hoy = now(ReglasCampoSolicitud::zonaHoraria())->toDateString();

        $asignaciones = DirectorCarrera::with(['carrera', 'trabajador'])
            ->when($request->filled('buscar'), fn ($q) => $q->whereHas('carrera', fn ($c) => $this->buscar($c, $request->input('buscar'), ['codigo', 'nombre'])))
            ->when($request->input('estado') === 'vigentes', fn ($q) => $q->where(fn ($v) => $v->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $hoy)))
            ->orderByRaw('CASE WHEN fecha_fin IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('fecha_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('administracion.directores', [
            'asignaciones' => $asignaciones,
            'carreras' => Carrera::where('activo', true)->orderBy('nombre')->get(),
            'directores' => Trabajador::conRol('director')->where('activo', true)->orderBy('apellidos')->get(),
            'hoy' => $hoy,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'trabajador_id' => ['required', function ($atributo, $valor, $falla) {
                if (! Trabajador::conRol('director')->where('activo', true)->whereKey($valor)->exists()) {
                    $falla('Elige a una persona activa con el rol de director.');
                }
            }],
            'fecha_inicio' => 'required|date',
        ], $this->mensajes());

        $inicio = Carbon::parse($datos['fecha_inicio']);

        $posterior = DirectorCarrera::where('carrera_id', $datos['carrera_id'])->whereDate('fecha_inicio', '>=', $inicio)->exists();
        if ($posterior) {
            return back()->withInput()->withErrors(['fecha_inicio' => 'Esta carrera ya tiene un director que empieza en esa fecha o después.']);
        }

        DB::transaction(function () use ($datos, $inicio) {
            DirectorCarrera::where('carrera_id', $datos['carrera_id'])
                ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $inicio))
                ->update(['fecha_fin' => $inicio->copy()->subDay()->toDateString()]);

            DirectorCarrera::create($datos);
        });

        $director = Trabajador::find($datos['trabajador_id']);
        $carrera = Carrera::find($datos['carrera_id']);

        return redirect()->route('administracion.directores')
            ->with('exito', "{$director->nombres} {$director->apellidos} dirige {$carrera->nombre} desde el {$inicio->format('d/m/Y')}.");
    }

    public function terminar(Request $request, DirectorCarrera $asignacion)
    {
        $datos = $request->validate([
            'fecha_fin' => 'required|date|after_or_equal:'.$asignacion->fecha_inicio->toDateString(),
        ], $this->mensajes(['after_or_equal' => 'La fecha de fin no puede ser anterior al inicio.']));

        $asignacion->update($datos);

        return back()->with('exito', "Se cerró la etapa de {$asignacion->trabajador->nombres} {$asignacion->trabajador->apellidos} en {$asignacion->carrera->nombre}.");
    }
}
