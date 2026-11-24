<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<Trabajador>
 */
class TrabajadorFactory extends Factory
{
    protected $model = Trabajador::class;

    public function definition(): array
    {
        $numero = fake()->unique()->numberBetween(1, 99999);

        return [
            'user_id' => null,
            'codigo' => sprintf('TRB-%05d', $numero),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'correo' => "trabajador{$numero}@uees.edu.ec",
            'activo' => true,
        ];
    }

    public function conRol(string $codigo): static
    {
        return $this->afterCreating(function (Trabajador $trabajador) use ($codigo) {
            $rol = Rol::firstOrCreate(['codigo' => $codigo], ['nombre' => ucfirst($codigo)]);

            DB::table('rol_trabajador')->insert([
                'trabajador_id' => $trabajador->id,
                'rol' => $codigo,
                'rol_id' => $rol->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function conAcceso(): static
    {
        return $this->state(fn (array $datos) => [
            'user_id' => User::factory()->state([
                'name' => "{$datos['nombres']} {$datos['apellidos']}",
                'email' => $datos['correo'],
                'tipo' => 'trabajador',
                'activo' => true,
            ]),
        ]);
    }
}
