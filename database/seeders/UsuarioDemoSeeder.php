<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsuarioDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Administrador G1',
            'email' => 'admin@uees.edu.ec',
            'password' => Hash::make('password123'),
            'tipo' => 'trabajador',
            'activo' => true,
        ]);

        User::factory()->count(10)->create([
            'tipo' => 'estudiante',
        ]);
    }
}
