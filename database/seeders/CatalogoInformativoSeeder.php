<?php

namespace Database\Seeders;

use App\Models\DocumentoRequerido;
use App\Models\EstadoSolicitud;
use App\Models\TipoTramite;
use App\Services\Catalogo\CatalogoTramites;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Importación inicial idempotente: nunca sobrescribe un catálogo ya editado. */
class CatalogoInformativoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (['borrador' => 'Borrador', 'enviada' => 'Enviada'] as $codigo => $nombre) {
                EstadoSolicitud::firstOrCreate(['codigo' => $codigo], ['nombre' => $nombre]);
            }
            foreach (app(CatalogoTramites::class)->inicial()['tramites'] as $t) {
                $tipo = TipoTramite::firstOrCreate(['codigo' => $t['id']], ['nombre' => $t['nombre'], 'slug' => $t['slug'], 'contenido' => $t]);
                foreach ($t['documentos'] as $d) {
                    DocumentoRequerido::firstOrCreate(['tipo_tramite_id' => $tipo->id, 'codigo' => $d['id']], ['nombre' => $d['nombre'], 'obligacion' => $d['obligacion'], 'contenido' => $d]);
                }
            }
        });
    }
}
