<?php

namespace App\Services\Acceso;

use App\Models\User;
use App\Services\Academico\AutorizacionService;
use Illuminate\Support\Facades\Route;

class ModulosDelUsuario
{
    public const ENTRADAS = [
        ['modulo' => 'Administración académica', 'roles' => ['admin'], 'ruta' => 'administracion.inicio'],
        ['modulo' => 'Revisión y Consejo', 'roles' => ['asistente'], 'ruta' => 'revision.asistente'],
        ['modulo' => 'Revisión y Consejo', 'roles' => ['director'], 'ruta' => 'revision.director'],
        ['modulo' => 'Revisión y Consejo', 'roles' => ['consulta'], 'ruta' => 'revision.resoluciones'],
    ];

    public function __construct(private AutorizacionService $autorizacion)
    {
    }

    public function de(User $usuario): array
    {
        $modulos = [];

        foreach (self::ENTRADAS as $entrada) {
            if (isset($modulos[$entrada['modulo']]) || ! Route::has($entrada['ruta'])) {
                continue;
            }

            if ($this->autorizacion->tieneRol($usuario, $entrada['roles'])) {
                $modulos[$entrada['modulo']] = $entrada['ruta'];
            }
        }

        return $modulos;
    }
}
