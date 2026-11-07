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

        $this->call(CatalogoInformativoSeeder::class);

        // Contratos compartidos del flujo; las solicitudes de prueba no se crean aquí.
        $this->call(EstadosSolicitudSeeder::class);
    }
}
