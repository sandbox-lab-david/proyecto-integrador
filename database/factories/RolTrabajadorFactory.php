<?php

namespace Database\Factories;

use App\Models\RolTrabajador;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolTrabajadorFactory extends Factory
{
    protected $model = RolTrabajador::class;

    public function definition(): array
    {
        return [
            'trabajador_id' => 1,
            'rol_id' => 1,
        ];
    }
}