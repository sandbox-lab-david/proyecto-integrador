<?php
namespace App\Services\Academico;

use App\Models\Trabajador;

class AutorizacionService
{
    
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
}