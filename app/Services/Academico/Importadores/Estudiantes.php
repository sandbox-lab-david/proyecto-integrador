<?php

namespace App\Services\Academico\Importadores;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Str;

class Estudiantes extends Importador
{
    public function titulo(): string
    {
        return 'Estudiantes';
    }

    public function columnas(): array
    {
        return [
            'codigo' => true,
            'cedula' => false,
            'nombres' => true,
            'apellidos' => true,
            'correo' => true,
            'celular' => false,
            'carrera_codigo' => true,
            'modalidad' => true,
            'malla_version' => false,
        ];
    }

    protected function reglas(): array
    {
        return [
            'codigo' => 'required|size:10',
            'cedula' => 'nullable|max:20',
            'nombres' => 'required|max:255',
            'apellidos' => 'required|max:255',
            'correo' => 'required|email|ends_with:@uees.edu.ec|max:255',
            'celular' => 'nullable|max:20',
            'carrera_codigo' => 'required',
            'modalidad' => 'required',
            'malla_version' => 'nullable|max:20',
        ];
    }

    public function clave(array $fila): string
    {
        return (string) $fila['codigo'];
    }

    public function existe(array $fila): bool
    {
        return Estudiante::where('codigo', $fila['codigo'])->exists();
    }

    protected function validarRelaciones(array $fila): array
    {
        $errores = [];
        $carrera = Carrera::where('codigo', $fila['carrera_codigo'])->first();

        if (! $carrera) {
            return ["No existe la carrera con código {$fila['carrera_codigo']}."];
        }

        if (! $this->carreraModalidad($carrera, $fila['modalidad'])) {
            $errores[] = "La carrera {$carrera->codigo} no se ofrece en la modalidad {$fila['modalidad']}.";
        }

        if ($fila['malla_version'] && ! $carrera->mallas()->where('version', $fila['malla_version'])->exists()) {
            $errores[] = "La carrera {$carrera->codigo} no tiene la malla {$fila['malla_version']}.";
        }

        $estudiante = Estudiante::where('codigo', $fila['codigo'])->first();
        $correo = mb_strtolower($fila['correo']);

        $otraCuenta = User::where('email', $correo)->where('id', '!=', $estudiante?->user_id ?? 0)->first();
        if ($otraCuenta && ($otraCuenta->tipo !== 'estudiante' || $otraCuenta->estudiante)) {
            $errores[] = "El correo {$correo} ya pertenece a otra persona.";
        }

        if ($fila['cedula'] && Estudiante::where('cedula', $fila['cedula'])->where('codigo', '!=', $fila['codigo'])->exists()) {
            $errores[] = "La cédula {$fila['cedula']} ya pertenece a otro estudiante.";
        }

        return $errores;
    }

    public function guardar(array $fila): void
    {
        $correo = mb_strtolower($fila['correo']);
        $nombre = "{$fila['nombres']} {$fila['apellidos']}";
        $estudiante = Estudiante::where('codigo', $fila['codigo'])->first();

        $usuario = $estudiante?->user ?? User::where('email', $correo)->first() ?? new User([
            'password' => Str::password(32),
            'tipo' => 'estudiante',
            'activo' => true,
        ]);
        $usuario->fill(['name' => $nombre, 'email' => $correo])->save();

        $estudiante ??= new Estudiante(['codigo' => $fila['codigo']]);
        $estudiante->fill([
            'user_id' => $usuario->id,
            'cedula' => $fila['cedula'],
            'nombres' => $fila['nombres'],
            'apellidos' => $fila['apellidos'],
            'correo' => $correo,
            'celular' => $fila['celular'],
        ])->save();

        $carrera = Carrera::where('codigo', $fila['carrera_codigo'])->first();
        $carreraModalidad = $this->carreraModalidad($carrera, $fila['modalidad']);
        $malla = $fila['malla_version']
            ? $carrera->mallas()->where('version', $fila['malla_version'])->first()
            : $carrera->mallas()->where('vigente', true)->first();

        $inscripcion = CarreraEstudiante::where('estudiante_id', $estudiante->id)
            ->whereIn('carrera_modalidad_id', CarreraModalidad::where('carrera_id', $carrera->id)->select('id'))
            ->first() ?? new CarreraEstudiante([
                'estudiante_id' => $estudiante->id,
                'fecha_inscripcion' => now()->toDateString(),
                'estado' => 'activo',
            ]);

        $inscripcion->fill([
            'carrera_modalidad_id' => $carreraModalidad->id,
            'malla_id' => $malla?->id ?? $inscripcion->malla_id,
        ])->save();
    }

    private function carreraModalidad(Carrera $carrera, string $modalidad): ?CarreraModalidad
    {
        return CarreraModalidad::where('carrera_id', $carrera->id)
            ->with('modalidad')
            ->get()
            ->first(fn ($cm) => $this->normalizar($cm->modalidad?->nombre) === $this->normalizar($modalidad));
    }
}
