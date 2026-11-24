<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Malla;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MallaController extends Controller
{
    use MensajesValidacion;

    public function index(Request $request, ?Malla $malla = null)
    {
        $mallas = Malla::with('carrera')
            ->withCount('materias')
            ->when($request->filled('buscar'), fn ($q) => $q->whereHas('carrera', fn ($c) => $this->buscar($c, $request->input('buscar'), ['codigo', 'nombre'])))
            ->when($request->filled('carrera'), fn ($q) => $q->where('carrera_id', $request->input('carrera')))
            ->orderBy(Carrera::select('nombre')->whereColumn('carreras.id', 'mallas.carrera_id'))
            ->orderByDesc('version')
            ->paginate(15)
            ->withQueryString();

        return view('administracion.mallas', [
            'mallas' => $mallas,
            'carreras' => Carrera::orderBy('nombre')->get(),
            'editando' => $malla?->exists ? $malla : null,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $malla = DB::transaction(fn () => $this->guardar(new Malla, $datos));

        return redirect()->route('administracion.mallas.ver', $malla)
            ->with('exito', "Malla {$malla->version} creada. Ahora agrega sus materias.");
    }

    public function update(Request $request, Malla $malla)
    {
        $datos = $this->validar($request, $malla);

        DB::transaction(fn () => $this->guardar($malla, $datos));

        return redirect()->route('administracion.mallas')
            ->with('exito', "Malla {$malla->version} actualizada.");
    }

    public function marcarVigente(Malla $malla)
    {
        DB::transaction(fn () => $this->guardar($malla, ['vigente' => true]));

        return back()->with('exito', "La malla {$malla->version} es ahora la vigente de {$malla->carrera->nombre}.");
    }

    public function show(Malla $malla)
    {
        $malla->load(['carrera', 'materias' => fn ($q) => $q->orderBy('malla_materia.nivel')->orderBy('codigo')]);

        return view('administracion.malla', [
            'malla' => $malla,
            'niveles' => $malla->materias->groupBy('pivot.nivel'),
            'disponibles' => Materia::where('activo', true)->whereNotIn('id', $malla->materias->pluck('id'))->orderBy('codigo')->get(),
        ]);
    }

    public function agregarMateria(Request $request, Malla $malla)
    {
        $datos = $request->validate([
            'materia_id' => ['required', 'exists:materias,id', Rule::unique('malla_materia')->where('malla_id', $malla->id)],
            'nivel' => 'required|integer|min:1|max:20',
        ], $this->mensajes(['unique' => 'Esa materia ya está en esta malla.']));

        $malla->materias()->attach($datos['materia_id'], ['nivel' => $datos['nivel']]);

        return back()->with('exito', 'Materia agregada a la malla.');
    }

    public function quitarMateria(Malla $malla, Materia $materia)
    {
        $malla->materias()->detach($materia->id);

        return back()->with('exito', "{$materia->codigo} se quitó de la malla.");
    }

    private function validar(Request $request, ?Malla $malla = null): array
    {
        $datos = $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'version' => [
                'required', 'string', 'max:20',
                Rule::unique('mallas')->where('carrera_id', $request->input('carrera_id'))->ignore($malla),
            ],
        ], $this->mensajes(['unique' => 'Esa carrera ya tiene una malla con esta versión.']));

        $datos['vigente'] = $request->boolean('vigente');

        return $datos;
    }

    private function guardar(Malla $malla, array $datos): Malla
    {
        $malla->fill($datos)->save();

        if ($malla->vigente) {
            Malla::where('carrera_id', $malla->carrera_id)->whereKeyNot($malla->id)->update(['vigente' => false]);
        }

        return $malla;
    }
}
