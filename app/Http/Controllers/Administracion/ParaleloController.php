<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\SolicitudMateria;
use App\Services\Academico\CatalogoAcademicoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParaleloController extends Controller
{
    use MensajesValidacion;

    public function index(Request $request, CatalogoAcademicoService $catalogo, ?Curso $curso = null)
    {
        $editando = $curso?->exists ? $curso : null;

        $cursos = Curso::with(['materia', 'periodo', 'profesor'])
            ->when($request->filled('buscar'), fn ($q) => $q->whereHas('materia', fn ($m) => $this->buscar($m, $request->input('buscar'), ['codigo', 'nombre'])))
            ->when($request->filled('periodo'), fn ($q) => $q->where('periodo_id', $request->input('periodo')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $materias = Materia::where('activo', true)
            ->when($editando, fn ($q) => $q->orWhere('id', $editando->materia_id))
            ->orderBy('nombre')
            ->get();

        $docentes = $catalogo->docentes();
        if ($editando?->profesor && ! $docentes->contains('id', $editando->profesor_id)) {
            $docentes->push($editando->profesor);
        }

        return view('administracion.paralelos', [
            'cursos' => $cursos,
            'materias' => $materias,
            'periodos' => Periodo::orderByDesc('fecha_inicio')->get(),
            'docentes' => $docentes->sortBy('apellidos'),
            'editando' => $editando,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        Curso::create($datos);

        return redirect()->route('administracion.paralelos')
            ->with('exito', "Paralelo {$datos['paralelo']} creado.");
    }

    public function update(Request $request, Curso $curso)
    {
        $curso->update($this->validar($request, $curso));

        return redirect()->route('administracion.paralelos')
            ->with('exito', "Paralelo {$curso->paralelo} actualizado.");
    }

    public function destroy(Curso $curso)
    {
        if (SolicitudMateria::where('curso_id', $curso->id)->exists()) {
            return back()->with('error', "No se puede borrar el paralelo {$curso->paralelo} porque ya hay solicitudes que lo usan.");
        }

        $curso->delete();

        return redirect()->route('administracion.paralelos')
            ->with('exito', "Paralelo {$curso->paralelo} borrado.");
    }

    private function validar(Request $request, ?Curso $curso = null): array
    {
        return $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'periodo_id' => 'required|exists:periodos,id',
            'profesor_id' => 'nullable|exists:trabajadores,id',
            'paralelo' => [
                'required', 'string', 'max:20',
                Rule::unique('cursos')->where(fn ($q) => $q
                    ->where('materia_id', $request->input('materia_id'))
                    ->where('periodo_id', $request->input('periodo_id')))
                    ->ignore($curso),
            ],
            'cupo' => 'nullable|integer|min:1|max:200',
        ], $this->mensajes(['unique' => 'Esa materia ya tiene un paralelo con ese nombre en el periodo elegido.'])) + ['profesor_id' => null, 'cupo' => null];
    }
}
