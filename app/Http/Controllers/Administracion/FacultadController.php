<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Facultad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacultadController extends Controller
{
    use MensajesValidacion;

    public function index(Request $request, ?Facultad $facultad = null)
    {
        $facultades = $this->buscar(Facultad::withCount(['carreras', 'carreras as carreras_activas_count' => fn ($q) => $q->where('activo', true)]), $request->input('buscar'), ['codigo', 'nombre'])
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('administracion.facultades', [
            'facultades' => $facultades,
            'editando' => $facultad?->exists ? $facultad : null,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $datos['activo'] = true;

        Facultad::create($datos);

        return redirect()->route('administracion.facultades')
            ->with('exito', "Facultad {$datos['nombre']} registrada.");
    }

    public function update(Request $request, Facultad $facultad)
    {
        $facultad->update($this->validar($request, $facultad));

        return redirect()->route('administracion.facultades')
            ->with('exito', "Facultad {$facultad->nombre} actualizada.");
    }

    public function cambiarEstado(Facultad $facultad)
    {
        if ($facultad->activo && $facultad->carreras()->where('activo', true)->exists()) {
            return back()->with('error', "No se puede desactivar {$facultad->nombre} porque tiene carreras activas. Desactiva primero sus carreras.");
        }

        $facultad->update(['activo' => ! $facultad->activo]);

        return back()->with('exito', "Facultad {$facultad->nombre} ".($facultad->activo ? 'activada.' : 'desactivada.'));
    }

    private function validar(Request $request, ?Facultad $facultad = null): array
    {
        return $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('facultades', 'codigo')->ignore($facultad)],
            'nombre' => 'required|string|max:255',
            'correo_notificaciones' => 'nullable|email|max:255',
        ], $this->mensajes(['unique' => 'Ya existe una facultad con este código.'])) + ['correo_notificaciones' => null];
    }
}
