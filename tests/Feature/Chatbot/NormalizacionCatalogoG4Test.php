<?php

namespace Tests\Feature\Chatbot;

use App\Models\CategoriaTramite;
use App\Models\Facultad;
use App\Models\RevisionCatalogo;
use App\Models\TipoTramite;
use App\Services\Catalogo\CatalogoTramites;
use App\Services\Catalogo\ExportadorProlog;
use App\Services\Catalogo\NormalizadorCatalogo;
use App\Services\Chatbot\ChatbotCatalogoService;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class NormalizacionCatalogoG4Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['chatbot.catalogo.directorio' => sys_get_temp_dir().'/catalogo-g4-'.Str::uuid()]);
        $this->seed(CatalogoInformativoSeeder::class);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory(config('chatbot.catalogo.directorio'));
        parent::tearDown();
    }

    private function tramite(string $codigo = 'examen_recuperacion'): TipoTramite
    {
        return TipoTramite::where('codigo', $codigo)->firstOrFail();
    }

    private function borrador(string $codigo = 'examen_recuperacion'): array
    {
        return app(CatalogoTramites::class)->contenidoBorrador($this->tramite($codigo));
    }

    public function test_los_veinte_tramites_conservan_todos_los_datos_y_campos(): void
    {
        $catalogo = app(CatalogoTramites::class);
        $borrador = $catalogo->borrador();
        $this->assertEquals(collect($catalogo->inicial()['tramites'])->keyBy('id')->all(), collect($borrador['tramites'])->keyBy('id')->all());
        $this->assertSame($catalogo->inicial()['categorias'], $borrador['categorias']);
        $this->assertDatabaseCount('categorias_tramite', 6);
        $this->assertDatabaseCount('campos_tramite', 56);
        $this->assertDatabaseCount('umbrales_tramite', 2);
        $this->assertSame(20, TipoTramite::where('catalogo_normalizado', true)->count());
    }

    public function test_ediciones_y_eliminaciones_no_se_reponen_desde_json_o_seeder(): void
    {
        $tipo = $this->tramite();
        $tipo->requisitos()->delete();
        $tipo->campos()->delete();
        $tipo->documentos()->delete();
        $tipo->umbrales()->delete();
        $this->seed(CatalogoInformativoSeeder::class);
        (new NormalizadorCatalogo)->importar($tipo);
        $borrador = $this->borrador();
        $this->assertSame([], $borrador['requisitos']);
        $this->assertSame([], $borrador['formulario']['campos']);
        $this->assertSame([], $borrador['formulario']['campos_materia']);
        $this->assertSame([], $borrador['documentos']);
        $this->assertSame([], $borrador['parametros']);

        $tipo->requisitos()->create(['texto' => 'Requisito nuevo', 'orden' => 2]);
        $tipo->requisitos()->create(['texto' => 'Requisito prioritario', 'orden' => 1]);
        $tipo->campos()->create(['ambito' => 'general', 'nombre' => 'comentario', 'etiqueta' => 'Comentario',
            'tipo' => 'texto_largo', 'obligatorio' => false, 'orden' => 0, 'configuracion' => ['max_length' => 250]]);
        $this->assertSame(['Requisito prioritario', 'Requisito nuevo'], $this->borrador()['requisitos']);
        $this->assertSame(250, $this->borrador()['formulario']['campos'][0]['max_length']);
        $this->assertNotSame($this->borrador()['requisitos'], $tipo->contenido['requisitos']);
    }

    public function test_categorias_y_umbrales_solo_cambian_al_publicar(): void
    {
        $tipo = $this->tramite();
        $tipo->categoria->update(['nombre' => 'Evaluaciones actualizadas']);
        $tipo->umbrales()->where('codigo', 'gpa_general_minimo')->update(['valor' => 80]);
        $tipo->umbrales()->where('codigo', 'intervalo_recuperacion_dias')->update(['valor' => 180]);
        $servicio = app(ChatbotCatalogoService::class);
        $this->assertSame('Evaluaciones', $servicio->obtenerCatalogo()['categorias'][0]['nombre']);
        $this->assertSame(365, $servicio->referenciasRecuperacion()['intervalo_recuperacion_dias']);
        Http::fake(fn ($r) => Http::response(['version' => 3, 'kb_version' => $r['kb_version']]));
        $revision = app(ExportadorProlog::class)->exportar(true);
        $this->assertTrue($revision->activa);
        $this->assertSame('Evaluaciones actualizadas', $servicio->obtenerCatalogo()['categorias'][0]['nombre']);
        $this->assertEquals(180, $servicio->referenciasRecuperacion()['intervalo_recuperacion_dias']);
        $this->assertEquals(80, $servicio->referenciasRecuperacion()['gpa_general_minimo']);
        $this->assertStringContainsString('180 días', $servicio->obtenerPregunta('examen_recuperacion__referencias')['texto']);
        $this->assertStringContainsString('parametro(examen_recuperacion, gpa_general_minimo, 80)', file_get_contents($revision->ruta_hechos));
    }

    public function test_categorias_editadas_generan_otra_version_y_revisiones_antiguas_siguen_leyendose(): void
    {
        $catalogo = app(CatalogoTramites::class);
        $version = $catalogo->borrador()['version'];
        CategoriaTramite::first()->update(['descripcion' => 'Descripción nueva']);
        $this->assertNotSame($version, $catalogo->borrador()['version']);
        $anterior = $catalogo->inicial();
        unset($anterior['categorias']);
        RevisionCatalogo::create(['version' => 'revision-antigua', 'contenido' => $anterior,
            'ruta_hechos' => base_path('prolog/kb/hechos.pl'), 'activa' => true]);
        $this->assertCount(6, $catalogo->publicado()['categorias']);
        $this->assertSame('Evaluaciones', $catalogo->publicado()['categorias'][0]['nombre']);
    }

    public function test_asociaciones_preparadas_no_filtran_el_catalogo_ni_cambian_el_reconocimiento(): void
    {
        $this->assertDatabaseCount('facultad_tipo_tramite', 0);
        $this->assertDatabaseCount('palabras_clave', 0);
        $tipo = $this->tramite();
        $antes = app(CatalogoTramites::class)->borrador();
        $tipo->facultades()->attach(Facultad::factory()->create()->id);
        $tipo->palabrasClave()->create(['expresion' => 'expresion futura']);
        $this->assertEquals($antes, app(CatalogoTramites::class)->borrador());
        $this->postJson('/chatbot/mensaje', ['version' => 3, 'texto' => 'expresion futura'])
            ->assertOk()->assertJsonPath('tipo', 'no_entendido');
        $this->assertCount(20, app(ChatbotCatalogoService::class)->obtenerCatalogo()['tramites']);
    }

    public function test_umbral_invalido_no_se_publica_y_conserva_revision_activa(): void
    {
        Http::fake(fn ($r) => Http::response(['version' => 3, 'kb_version' => $r['kb_version']]));
        $exportador = app(ExportadorProlog::class);
        $revision = $exportador->exportar(true);
        $tipo = $this->tramite();
        foreach (['gpa_general_minimo' => 101, 'intervalo_recuperacion_dias' => 0.5] as $codigo => $valor) {
            $original = $tipo->umbrales()->where('codigo', $codigo)->first()->valor;
            $tipo->umbrales()->where('codigo', $codigo)->update(['valor' => $valor]);
            try {
                $exportador->exportar(true);
                $this->fail('La referencia inválida no debe publicarse.');
            } catch (\RuntimeException $error) {
                $this->assertStringContainsString('inválida', $error->getMessage());
            }
            $this->assertTrue($revision->fresh()->activa);
            $tipo->umbrales()->where('codigo', $codigo)->update(['valor' => $original]);
        }
        $this->assertSame(1, RevisionCatalogo::where('activa', true)->count());
    }
}
