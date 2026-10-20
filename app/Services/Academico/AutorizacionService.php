<?php
namespace App\Services\Academico;

use App\Models\Trabajador;
use App\Models\User;
use App\Models\DirectorCarrera;
use App\Models\Carrera;

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
        return Trabajador::whereHas('directoresCarrera', function($query) use ($carreraId) {
            $query->where('carrera_id', $carreraId)
                  ->whereNull('fecha_fin'); // Si fecha_fin es nula, significa que sigue en el cargo
        })->first();
        
    }

    //===C19 
    public function asistentesDeFacultad(int $facultadId)
    {
        
        return collect([]);
    }

    // ==== C15
    public function tieneRol(User $u, string|array $codigos): bool
    {
        $codigos = (array) $codigos;
        $trabajador = $u->trabajador;

        if (! $trabajador) { 
            return false; 
        }
        
        return $trabajador->roles()->whereIn('codigo', $codigos)->exists();
    }

    // ==== C20: Puede ver carrera
    public function puedeVerCarrera($user, $carreraId)
    {
        // C20: se usa tieneRol (C15) en lugar de hasRole, que no existe en User
        if ($this->tieneRol($user, 'admin')) {
            return true;
        }

        $trabajadorId = $user->trabajador->id ?? null;
        if (!$trabajadorId) {
            return false;
        }

        return DirectorCarrera::where('trabajador_id', $trabajadorId)
            ->where('carrera_id', $carreraId)
            ->exists();
    }
}