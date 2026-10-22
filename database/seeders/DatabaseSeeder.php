<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Grupo 1
        $this->call([
            RolSeeder::class,
            FacultadSeeder::class,
            CarreraSeeder::class,
            ModalidadSeeder::class,
            CategoriaMateriaSeeder::class,
            MallaSeeder::class,
            PeriodoSeeder::class,
            UsuarioDemoSeeder::class,
            CursoSeeder::class,
        ]);

        // Grupo 3
        $this->call([
            SolicitudRevisionSeeder::class,
        ]);
    }
}