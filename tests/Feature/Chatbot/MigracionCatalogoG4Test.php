<?php

namespace Tests\Feature\Chatbot;

use App\Models\TipoTramite;
use App\Services\Catalogo\CatalogoTramites;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MigracionCatalogoG4Test extends TestCase
{
    public function test_migracion_y_reversion_conservan_el_borrador_en_una_bd_aislada(): void
    {
        $conexionAnterior = DB::getDefaultConnection();
        config(['database.connections.catalogo_migracion_g4' => array_replace(config('database.connections.sqlite'), [
            'database' => ':memory:', 'url' => null, 'foreign_key_constraints' => true,
        ])]);
        DB::setDefaultConnection('catalogo_migracion_g4');
        try {
            Artisan::call('migrate', ['--database' => 'catalogo_migracion_g4', '--force' => true]);
            $this->seed(CatalogoInformativoSeeder::class);
            $documentos = DB::table('documentos_requeridos')->pluck('tipo_tramite_id', 'id')->all();
            $migracion = require database_path('migrations/2026_10_28_000001_normalizar_catalogo_g4.php');
            $migracion->down();
            $tipo = $this->tramite();
            $contenido = $tipo->contenido;
            $contenido['requisitos'] = ['Texto editado en la BD existente'];
            $contenido['formulario']['campos'][0]['etiqueta'] = 'Etiqueta editada';
            $contenido['parametros']['gpa_general_minimo'] = 80;
            $tipo->update(['contenido' => $contenido]);
            $migracion->up();
            $this->assertEquals($contenido, $this->borrador());
            $tipo = $this->tramite();
            $tipo->requisitos()->first()->update(['texto' => 'Edición después de migrar']);
            $tipo->campos()->where('nombre', $contenido['formulario']['campos'][0]['nombre'])->update(['etiqueta' => 'Nueva etiqueta']);
            $antes = $this->borrador();
            $migracion->down();
            $this->assertEquals($antes, $this->tramite()->contenido);
            $this->assertSame($documentos, DB::table('documentos_requeridos')->pluck('tipo_tramite_id', 'id')->all());
            $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
            $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
            $migracion->up();
            $this->assertEquals($antes, $this->borrador());
            $this->assertDatabaseCount('campos_tramite', 56);
        } finally {
            DB::setDefaultConnection($conexionAnterior);
            DB::purge('catalogo_migracion_g4');
        }
    }

    private function tramite(): TipoTramite
    {
        return TipoTramite::where('codigo', 'examen_recuperacion')->firstOrFail();
    }

    private function borrador(): array
    {
        return app(CatalogoTramites::class)->contenidoBorrador($this->tramite());
    }
}
