<?php

namespace App\Services\Academico;

use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\DirectorCarrera;
use App\Services\Solicitudes\ReglasCampoSolicitud;
use Illuminate\Support\Facades\DB;

class AutorizacionService
{
    // ==== C17: Carreras que dirige
    public function carrerasQueDirige($trabajadorId)
    {
        return DirectorCarrera::where('trabajador_id', $trabajadorId)
            ->with('carrera')
            ->get()
            ->pluck('carrera');
    }

    // ==== C18: Encuentra qué profesor/trabajador es el director actual de una carrera.
    public function directorDeCarrera(int $carreraId)
    {

        /*
        // = STUB =
            return (object)[
                'id' => 50,
                'nombres' => 'Dra. María',
                'apellidos' => 'Gómez',
                'correo' => 'mgomez@uees.edu.ec'
            ];
        */

        // Busca en la tabla Trabajadores aquel que tenga una relación vigente de director con la carrera indicada
        return Trabajador::whereHas('directoresCarrera', function ($query) use ($carreraId) {
            $query->where('carrera_id', $carreraId)
                ->whereNull('fecha_fin'); // Si fecha_fin es nula, significa que sigue en el cargo
        })->first();

    }

    // ==== C16: Facultades donde está asignado un trabajador
    public function facultadesDe(Trabajador $trabajador): Collection
    {
        return $trabajador->facultades()->orderBy('nombre')->get();
    }

    // ===C19: Asistentes activas de una facultad
    public function asistentesDeFacultad(int $facultadId): Collection
    {
        return Trabajador::conRol('asistente')
            ->where('activo', true)
            ->whereHas('facultades', fn ($q) => $q->where('facultades.id', $facultadId))
            ->orderBy('apellidos')
            ->get();
    }

    // ==== C15
    public function tieneRol(User $u, string|array $codigos): bool
    {
        $codigos = (array) $codigos;
        $trabajador = $u->trabajador;

        if (! $trabajador) {
            return false;
        }

        if (! $u->activo || ! $trabajador->activo) {
            return false;
        }

        return DB::table('rol_trabajador')
            ->leftJoin('roles', 'roles.id', '=', 'rol_trabajador.rol_id')
            ->where('rol_trabajador.trabajador_id', $trabajador->id)
            ->where(fn ($query) => $query->whereIn('rol_trabajador.rol', $codigos)
                ->orWhereIn('roles.codigo', $codigos))
            ->exists();
    }

    // ==== C20
    public function puedeVerCarrera(User $usuario, int $carreraId): bool
    {
        $trabajador = $usuario->trabajador;
        if (! $usuario->activo || ! $trabajador?->activo) {
            return false;
        }

        if ($this->tieneRol($usuario, 'admin')) {
            return true;
        }

        $hoy = now(ReglasCampoSolicitud::zonaHoraria())->toDateString();

        return DB::table('director_carreras')
            ->where('trabajador_id', $trabajador->id)->where('carrera_id', $carreraId)
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $hoy))->exists();
    }

    // ==== C20: Es administrador o trabaja en esa facultad
    public function puedeVerFacultad(User $usuario, int $facultadId): bool
    {
        $trabajador = $usuario->trabajador;
        if (! $usuario->activo || ! $trabajador?->activo) {
            return false;
        }

        if ($this->tieneRol($usuario, 'admin')) {
            return true;
        }

        return $trabajador->facultades()->where('facultades.id', $facultadId)->exists();
    }
}
