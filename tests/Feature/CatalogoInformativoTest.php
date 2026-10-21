<?php

namespace Tests\Feature;

use App\Models\DocumentoRequerido;
use App\Models\RevisionCatalogo;
use App\Models\TipoTramite;
use App\Services\Catalogo\CatalogoTramites;
use App\Services\Catalogo\ExportadorProlog;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class CatalogoInformativoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['chatbot.catalogo.directorio' => sys_get_temp_dir().'/catalogo-test-'.Str::uuid()]);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory(config('chatbot.catalogo.directorio'));
        parent::tearDown();
    }

    public function test_los_veinte_tramites_comparten_documentos_y_sus_vistas_son_accesibles(): void
    {
        $this->seed(CatalogoInformativoSeeder::class);
        $this->assertDatabaseCount('tipos_tramite', 20);
        foreach (app(CatalogoTramites::class)->publicado()['tramites'] as $t) {
            $slug = $t['slug'] === 'recalificacion' ? 'recalificacion-examen' : $t['slug'];
            $this->get('/estudiante/tramites/'.$slug)->assertOk()->assertSee($t['nombre'])->assertDontSee('Agenda Anny')->assertDontSee('Por confirmar');
            $this->get('/estudiante/solicitudes/nueva?tramite='.$t['slug'])->assertOk();
            $this->assertSame(array_column($t['documentos'], 'id'), array_column($t['formulario']['documentos'], 'id'));
            $this->assertSame(array_column($t['documentos'], 'obligatorio'), array_column($t['formulario']['documentos'], 'obligatorio'));
        }
    }

    public function test_importacion_no_sobrescribe_ediciones_y_borrador_no_se_publica_automaticamente(): void
    {
        $this->seed(CatalogoInformativoSeeder::class);
        $tipo = TipoTramite::first();
        $tipo->update(['nombre' => 'Nombre editado']);
        $this->seed(CatalogoInformativoSeeder::class);
        $this->assertSame('Nombre editado', $tipo->fresh()->nombre);
        $this->assertNotSame('Nombre editado', app(CatalogoTramites::class)->buscar($tipo->codigo)['nombre']);
    }

    public function test_hechos_distribuidos_son_la_exportacion_exacta_del_catalogo_inicial(): void
    {
        $this->assertSame(file_get_contents(base_path('prolog/kb/hechos.pl')), app(ExportadorProlog::class)->generar(app(CatalogoTramites::class)->inicial()));
    }

    public function test_publicar_valida_el_candidato_y_espera_confirmacion_del_motor(): void
    {
        $this->seed(CatalogoInformativoSeeder::class);
        Http::fake(fn ($r) => Http::response(['version' => 3, 'kb_version' => $r['kb_version'], 'estado' => 'listo']));
        $r = app(ExportadorProlog::class)->exportar(true);
        $this->assertTrue($r->activa);
        $this->assertSame($r->version, app(CatalogoTramites::class)->publicado()['version']);
        Http::assertSent(fn ($p) => str_ends_with($p->url(), '/recargar') && is_file($p['ruta']));
        // El puntero del test no debe afectar el arranque fuera de esta BD desechable.
        @unlink(config('chatbot.catalogo.directorio').'/activa');
    }

    public function test_publicacion_fallida_conserva_la_version_anterior(): void
    {
        $this->seed(CatalogoInformativoSeeder::class);
        $anterior = RevisionCatalogo::create(['version' => 'anterior', 'contenido' => ['version' => 'anterior', 'tramites' => []], 'ruta_hechos' => base_path('prolog/kb/hechos.pl'), 'activa' => true]);
        $llamadas = 0;
        Http::fake(function ($r) use (&$llamadas) {
            $llamadas++;

            return Http::response(['version' => 3, 'kb_version' => $llamadas === 1 ? 'otra' : $r['kb_version'], 'estado' => 'listo']);
        });
        try {
            app(ExportadorProlog::class)->exportar(true);
            $this->fail('Debe fallar la activación');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('no confirmó', $e->getMessage());
        }
        $this->assertTrue($anterior->fresh()->activa);
        $this->assertSame(1, RevisionCatalogo::where('activa', true)->count());
    }

    public function test_no_se_publica_una_obligacion_documental_contradictoria(): void
    {
        $this->seed(CatalogoInformativoSeeder::class);
        $doc = DocumentoRequerido::first();
        $doc->update(['obligacion' => 'no_definida']);
        Http::fake();
        $this->expectException(\RuntimeException::class);
        app(ExportadorProlog::class)->exportar(true);
        Http::assertNothingSent();
    }
}
