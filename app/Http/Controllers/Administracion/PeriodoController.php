<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PeriodoController extends Controller
{
    use MensajesValidacion;

    public function index(Request $request, ?Periodo $periodo = null)
    {
        $periodos = $this->buscar(Periodo::query(), $request->input('buscar'), ['codigo', 'nombre'])
            ->orderByDesc('fecha_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('administracion.periodos', [
            'periodos' => $periodos,
            'editando' => $periodo?->exists ? $periodo : null,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        DB::transaction(fn () => $this->guardar(new Periodo, $datos));

        return redirect()->route('administracion.periodos')
            ->with('exito', "Periodo {$datos['nombre']} creado.");
    }

    public function update(Request $request, Periodo $periodo)
    {
        $datos = $this->validar($request, $periodo);

        DB::transaction(fn () => $this->guardar($periodo, $datos));

        return redirect()->route('administracion.periodos')
            ->with('exito', "Periodo {$periodo->nombre} actualizado.");
    }

    public function activar(Periodo $periodo)
    {
        DB::transaction(fn () => $this->guardar($periodo, ['activo' => true]));

        return back()->with('exito', "{$periodo->nombre} es ahora el periodo activo.");
    }

    private function validar(Request $request, ?Periodo $periodo = null): array
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:30', Rule::unique('periodos', 'codigo')->ignore($periodo)],
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:ordinario,extraordinario,intensivo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'semana_primer_parcial' => 'required|integer|min:1|max:20',
        ], $this->mensajes(['unique' => 'Ya existe un periodo con este código.']));

        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }

    private function guardar(Periodo $periodo, array $datos): Periodo
    {
        if (! empty($datos['activo'])) {
            Periodo::where('activo', true)->whereKeyNot($periodo->id)->update(['activo' => false]);
        }

        $periodo->fill($datos)->save();

        return $periodo;
    }
}
