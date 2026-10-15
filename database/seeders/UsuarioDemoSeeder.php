<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
<<<<<<< HEAD
use App\Models\User;
=======
use Illuminate\Support\Facades\Hash;
>>>>>>> d3c3bd4331bca2767d4e5be21fd044363ca7f3a5

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
