<?php

namespace App\Services\Chatbot;

use App\Models\User;
use App\Services\Academico\AutorizacionService;

class VisibilidadChatbot
{
    public function __construct(private AutorizacionService $autorizacion)
    {
    }

    public function puedeVer(?User $usuario): bool
    {
        if (! $usuario) {
            return true;
        }

        $roles = config('chatbot.roles_visibles', []);

        if (in_array($usuario->tipo, $roles, true)) {
            return true;
        }

        $rolesTrabajador = array_values(array_diff($roles, ['estudiante']));

        return $rolesTrabajador !== [] && $this->autorizacion->tieneRol($usuario, $rolesTrabajador);
    }
}
