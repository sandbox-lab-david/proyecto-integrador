<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Enums\NivelCarrera;
use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\Facultad;
use App\Models\Modalidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CarreraController extends Controller
{
    use MensajesValidacion;

    public function index(Request $request, ?Carrera $carrera = null)
    {
        $carreras = $this->buscar(Carrera::with(['facultad', 'modalidades']), $request->input('buscar'), ['carreras.codigo', 'carreras.nombre'])
            ->when($request->filled('facultad'), fn ($q) => $q->where('facultad_id', $request->input('facultad')))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('administracion.carreras', [
            'carreras' => $carreras,
            'facultades' => Facultad::orderBy('nombre')->get(),
            'modalidades' => Modalidad::orderBy('id')->get(),
            'editando' => $carrera?->exists ? $carrera->load('modalidades') : null,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos) {
            $carrera = Carrera::create($datos['carrera'] + ['activo' => true]);
            $carrera->modalidades()->attach($this->conFechas($datos['modalidades']));
        });

        return redirect()->route('administracion.carreras')
            ->with('exito', "Carrera {$datos['carrera']['nombre']} registrada.");
    }

    public function update(Request $request, Carrera $carrera)
    {
        $datos = $this->validar($request, $carrera);

        $quitadas = $carrera->modalidades()->pluck('modalidades.id')->diff($datos['modalidades']);
        $enUso = CarreraEstudiante::whereIn(
            'carrera_modalidad_id',
            CarreraModalidad::where('carrera_id', $carrera->id)->whereIn('modalidad_id', $quitadas)->select('id')
        )->exists();

        if ($enUso) {
            return back()->withInput()->withErrors([
                'modalidades' => 'No puedes quitar una modalidad que tiene estudiantes inscritos.',
            ]);
        }

        DB::transaction(function () use ($carrera, $datos) {
            $carrera->update($datos['carrera']);
            $nuevas = collect($datos['modalidades'])->diff($carrera->modalidades()->pluck('modalidades.id'));
            $carrera->modalidades()->detach($carrera->modalidades()->pluck('modalidades.id')->diff($datos['modalidades']));
            $carrera->modalidades()->attach($this->conFechas($nuevas->all()));
        });

        return redirect()->route('administracion.carreras')
            ->with('exito', "Carrera {$carrera->nombre} actualizada.");
    }

    public function cambiarEstado(Carrera $carrera)
    {
        if (! $carrera->activo && ! $carrera->facultad?->activo) {
            return back()->with('error', "No se puede activar {$carrera->nombre} porque su facultad está desactivada.");
        }

        $carrera->update(['activo' => ! $carrera->activo]);

        return back()->with('exito', "Carrera {$carrera->nombre} ".($carrera->activo ? 'activada.' : 'desactivada.'));
    }

    private function validar(Request $request, ?Carrera $carrera = null): array
    {
        $datos = $request->validate([
            'facultad_id' => 'required|exists:facultades,id',
            'codigo' => ['required', 'string', 'max:20', Rule::unique('carreras', 'codigo')->ignore($carrera)],
            'nombre' => 'required|string|max:255',
            'nivel' => ['required', Rule::enum(NivelCarrera::class)],
            'modalidades' => 'required|array|min:1',
            'modalidades.*' => 'integer|exists:modalidades,id',
        ], $this->mensajes([
            'unique' => 'Ya existe una carrera con este código.',
            'modalidades.required' => 'Marca al menos una modalidad.',
            'modalidades.min' => 'Marca al menos una modalidad.',
        ]));

        return [
            'carrera' => [
                'facultad_id' => $datos['facultad_id'],
                'codigo' => $datos['codigo'],
                'nombre' => $datos['nombre'],
                'nivel' => $datos['nivel'],
                'admite_tercer_registro' => $request->boolean('admite_tercer_registro'),
            ],
            'modalidades' => array_map('intval', $datos['modalidades']),
        ];
    }

    private function conFechas(array $ids): array
    {
        return collect($ids)->mapWithKeys(fn ($id) => [$id => ['created_at' => now(), 'updated_at' => now()]])->all();
    }
}
