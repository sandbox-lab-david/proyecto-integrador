<?php
namespace App\Services\Academico;

use App\Enums\EstadoInscripcion;
use App\Models\Estudiante;
use Illuminate\Support\Facades\Auth;
use App\Models\CarreraEstudiante;
use Illuminate\Database\Eloquent\Collection;

class EstudianteService
{
    // C11
    public function estudianteActual()
    {
        $user = Auth::user();

        if ($user && $user->tipo === 'estudiante') {
            return $user->estudiante;
        }

        return null;
    }

    // C12
    public function perfil(int $estudianteId): array
    {
        $estudiante = Estudiante::findOrFail($estudianteId);

        return [
            'id' => $estudiante->id,
            'codigo' => $estudiante->codigo,
            'cedula' => $estudiante->cedula,
            'nombres' => $estudiante->nombres,
            'apellidos' => $estudiante->apellidos,
            'nombre_completo' => trim("{$estudiante->nombres} {$estudiante->apellidos}"),
            'correo' => $estudiante->correo,
            'celular' => $estudiante->celular,
            'carreras' => $this->carrerasActivas($estudiante->id)->load('carreraModalidad.carrera.facultad')->map(fn (CarreraEstudiante $inscripcion) => [
                'carrera_estudiante_id' => $inscripcion->id,
                'estado' => $inscripcion->estado,
                'fecha_inscripcion' => $inscripcion->fecha_inscripcion?->toDateString(),
                'facultad' => $this->resumen($inscripcion->carreraModalidad?->carrera?->facultad, ['id', 'codigo', 'nombre']),
                'carrera' => $this->resumen($inscripcion->carreraModalidad?->carrera, ['id', 'codigo', 'nombre', 'nivel', 'admite_tercer_registro']),
                'modalidad' => $this->resumen($inscripcion->carreraModalidad?->modalidad, ['id', 'nombre']),
                'malla' => $this->resumen($inscripcion->malla, ['id', 'version', 'vigente']),
            ])->values()->all(),
        ];
    }

    private function resumen($modelo, array $campos): ?array
    {
        return $modelo?->only($campos);
    }

    // C13
    public function carrerasActivas(int $estudianteId): Collection
    {
        return CarreraEstudiante::with(['carreraModalidad.carrera', 'carreraModalidad.modalidad', 'malla'])
                                ->where('estudiante_id', $estudianteId)
                                ->where('estado', EstadoInscripcion::ACTIVO->value)
                                ->get();
    }

    // C14
    public function actualizarContacto(Estudiante $estudiante, string $celular): void
    {
        $estudiante->update(['celular' => $celular]);
    }
}
