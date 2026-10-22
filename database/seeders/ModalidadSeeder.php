<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Modalidad;
use Illuminate\Database\Seeder;

class ModalidadSeeder extends Seeder
{
    public const MODALIDADES = ['Presencial', 'Semipresencial', 'En línea', 'Híbrida', 'A distancia', 'Dual'];

    public const TAMBIEN_EN_LINEA = ['COMP', 'ADM'];

    public function run(): void
    {
        foreach (self::MODALIDADES as $nombre) {
            Modalidad::create(['nombre' => $nombre]);
        }

        $presencial = Modalidad::where('nombre', 'Presencial')->value('id');
        $enLinea = Modalidad::where('nombre', 'En línea')->value('id');

        foreach (Carrera::all() as $carrera) {
            $carrera->modalidades()->attach($presencial);

            if (in_array($carrera->codigo, self::TAMBIEN_EN_LINEA, true)) {
                $carrera->modalidades()->attach($enLinea);
            }
        }
    }
}
