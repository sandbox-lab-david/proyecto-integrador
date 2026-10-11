<?php
namespace App\Services\Academico;

use App\Models\Estudiante;
use Illuminate\Support\Facades\Auth;

class EstudianteService
{
    
    //=== C11: Devuelve el objeto Estudiante del usuario que inició sesión.
    public function estudianteActual()
    {   
    /*
    // = STUB =
    return (object)[
            'id' => 101, 
            'nombres' => 'Juan', 
            'apellidos' => 'Pérez', 
            'codigo' => '2023123456'
        ];
    */


        // Obtiene el usuario de la sesión actual de Laravel
        $user = Auth::user(); 
        
        // Verifica que el usuario exista y que sea de tipo 'estudiante'
        if ($user && $user->tipo === 'estudiante') {
            // Retorna la relación 'estudiante' que definiste en tu modelo User
            return $user->estudiante; 
        }
        
        return null; // Si es un profesor o no ha iniciado sesión, devuelve nulo
        
    }

    //===C14 
    public function actualizarContacto($estudiante, string $celular): void
    {
        
    }
}