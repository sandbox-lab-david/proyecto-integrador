<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Borrador del formulario (RF-2.12): mientras la solicitud todavía no se
 * puede guardar, lo escrito queda en la sesión, uno por trámite.
 */
class BorradoresTemporalesTest extends TestCase
{
    use ArchivosDePrueba;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function guardar(array $datos = [], string $tramite = 'recuperacion'): TestResponse
    {
        return $this->putJson(route('borradores-temporales.update', $tramite), $datos + [
            'paso' => 2,
            'celular' => '0990000000',
            'materias' => [],
            'campos' => [],
            'requisitos' => [],
        ]);
    }

    private function subir(string $tramite = 'recuperacion'): string
    {
        return $this->postJson(route('respaldos-temporales.store'), [
            'tramite' => $tramite,
            'archivo' => $this->pdf(),
        ])->json('id');
    }

    private function borradorDe(string $tramite): ?array
    {
        return $this->get(route('solicitudes.create', ['tramite' => $tramite]))
            ->assertOk()
            ->viewData('borrador');
    }

    public function test_guarda_el_borrador_y_el_formulario_lo_recupera(): void
    {
        $this->guardar([
            'paso' => 3,
            'materias' => [['id' => 'calculo-demo', 'datos' => ['nota' => '58.5', 'estado' => 'reprobada']]],
            'campos' => ['gpa_periodo' => '71', 'observacion' => 'Texto que no debe perderse'],
        ])->assertOk()->assertJsonStructure(['guardado_at']);

        $borrador = $this->borradorDe('recuperacion');

        $this->assertSame(3, $borrador['paso']);
        $this->assertSame('0990000000', $borrador['celular']);
        $this->assertSame(
            [['id' => 'calculo-demo', 'datos' => ['nota' => '58.5', 'estado' => 'reprobada']]],
            $borrador['materias']
        );
        $this->assertSame('Texto que no debe perderse', $borrador['campos']['observacion']);
    }

    public function test_el_formulario_sin_borrador_empieza_vacio(): void
    {
        $this->assertNull($this->borradorDe('recuperacion'));
    }

    public function test_admite_un_borrador_incompleto(): void
    {
        $this->guardar(['paso' => 1, 'celular' => '', 'campos' => ['gpa_periodo' => '']])->assertOk();

        $borrador = $this->borradorDe('recuperacion');

        $this->assertNull($borrador['celular']);
        $this->assertNull($borrador['campos']['gpa_periodo']);
    }

    public function test_solo_guarda_los_campos_que_define_el_tramite(): void
    {
        $this->guardar([
            'materias' => [['id' => 'calculo-demo', 'datos' => ['nota' => '60', 'inventado' => 'x']]],
            'campos' => ['observacion' => 'ok', 'inventado' => 'x'],
        ])->assertOk();

        $borrador = $this->borradorDe('recuperacion');

        $this->assertSame(['nota' => '60'], $borrador['materias'][0]['datos']);
        $this->assertSame(['observacion' => 'ok'], $borrador['campos']);
    }

    public function test_rechaza_lo_que_el_tramite_no_admite(): void
    {
        $this->guardar(['paso' => 5])->assertJsonValidationErrors('paso');

        $this->guardar(['materias' => [['id' => 'no-existe']]])->assertJsonValidationErrors('materias.0.id');

        // Recuperación admite una sola materia.
        $this->guardar(['materias' => [['id' => 'calculo-demo'], ['id' => 'fisica-demo']]])
            ->assertJsonValidationErrors('materias');

        $this->guardar(['campos' => ['observacion' => str_repeat('a', 2001)]])
            ->assertJsonValidationErrors('campos.observacion');

        $this->assertNull($this->borradorDe('recuperacion'));
    }

    public function test_un_tramite_que_no_existe_no_tiene_borrador(): void
    {
        $this->guardar([], 'inventado')->assertNotFound();
    }

    public function test_cada_tramite_tiene_su_propio_borrador(): void
    {
        $this->guardar(['campos' => ['observacion' => 'de recuperación']]);

        $this->assertNull($this->borradorDe('gracia'));
    }

    public function test_otra_sesion_no_ve_el_borrador(): void
    {
        $this->guardar();

        $this->flushSession();

        $this->assertNull($this->borradorDe('recuperacion'));
    }

    public function test_recuerda_a_que_requisito_corresponde_cada_respaldo(): void
    {
        $id = $this->subir();

        $this->guardar(['requisitos' => [
            $id => 'registro-calificaciones',
            '11111111-1111-4111-8111-111111111111' => 'otro',
        ]])->assertOk();

        // El segundo no es un archivo de esta sesión.
        $this->assertSame([$id => 'registro-calificaciones'], $this->borradorDe('recuperacion')['requisitos']);

        $this->guardar(['requisitos' => [$id => 'requisito-de-otro-tramite']])
            ->assertJsonValidationErrors("requisitos.{$id}");
    }

    public function test_descartar_borra_el_borrador_y_sus_respaldos(): void
    {
        $this->subir('recuperacion');
        $this->subir('gracia');
        $this->guardar();
        $this->guardar(['campos' => ['motivo' => 'sigue']], 'gracia');

        $this->deleteJson(route('borradores-temporales.destroy', 'recuperacion'))->assertNoContent();

        $this->assertNull($this->borradorDe('recuperacion'));
        $this->assertSame('sigue', $this->borradorDe('gracia')['campos']['motivo']);
        $this->assertCount(1, Storage::disk('local')->allFiles('respaldos-temporales'));
    }

    public function test_el_catalogo_ofrece_continuar_los_borradores(): void
    {
        $this->get(route('estudiante.catalogo'))->assertOk()->assertDontSee('Tienes solicitudes sin terminar');

        $this->guardar(['paso' => 3]);

        $this->get(route('estudiante.catalogo'))
            ->assertOk()
            ->assertSee('Tienes solicitudes sin terminar')
            ->assertSee('Examen de recuperación')
            ->assertSee('Paso 3 de 4')
            ->assertSee(route('solicitudes.create', ['tramite' => 'recuperacion']), false);
    }
}
