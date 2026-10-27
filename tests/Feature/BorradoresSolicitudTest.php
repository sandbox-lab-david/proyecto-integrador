<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\CarreraModalidad;
use App\Models\Estudiante;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Solicitudes\CatalogoFacultades;
use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\DocumentosTemporales;
use App\Services\Solicitudes\SolicitudService;
use App\Services\Solicitudes\TramitesSimulados;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Borrador en la cuenta (RF-2.12): una fila de solicitudes en estado
 * «borrador» y sin enviada_at, que se retoma desde otra sesión y que el envío
 * convierte en la solicitud.
 */
class BorradoresSolicitudTest extends TestCase
{
    use ArchivosDePrueba, RefreshDatabase, RequisitosPrologPrueba;

    private User $usuario;

    private CarreraEstudiante $inscripcion;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(CatalogoInformativoSeeder::class);
        [$this->usuario, $this->inscripcion] = $this->estudianteInscrito();
        $this->actingAs($this->usuario);
    }

    /**
     * @return array{0: User, 1: CarreraEstudiante}
     */
    private function estudianteInscrito(string $codigo = '2026000001'): array
    {
        $u = User::factory()->create(['tipo' => 'estudiante']);
        $carrera = Carrera::where('codigo', 'COM')->first() ?? Carrera::create([
            'facultad_id' => DB::table('facultades')->insertGetId(['codigo' => 'ING', 'nombre' => 'Ingeniería', 'activo' => true]),
            'codigo' => 'COM', 'nombre' => 'Ingeniería en Computación', 'nivel' => 'grado', 'activo' => true,
        ]);
        Periodo::where('codigo', 'ACTUAL')->exists() || Periodo::create(['codigo' => 'ACTUAL', 'nombre' => 'Período actual', 'tipo' => 'ordinario',
            'fecha_inicio' => today()->subDays(10), 'fecha_fin' => today()->addDays(30), 'semana_primer_parcial' => 4, 'activo' => true]);
        $e = Estudiante::create(['user_id' => $u->id, 'codigo' => $codigo, 'nombres' => 'Estudiante', 'apellidos' => 'Prueba', 'correo' => $u->email]);
        $cm = CarreraModalidad::firstOrCreate(['carrera_id' => $carrera->id, 'modalidad_id' => Modalidad::firstOrCreate(['nombre' => 'Presencial'])->id]);

        return [$u, CarreraEstudiante::create(['estudiante_id' => $e->id, 'carrera_modalidad_id' => $cm->id, 'estado' => 'activo'])];
    }

    private function guardar(array $datos = [], string $tramite = 'recuperacion'): TestResponse
    {
        return $this->putJson(route('borradores-temporales.update', $tramite), $datos + [
            'paso' => 2, 'celular' => '0990000000', 'materias' => [], 'campos' => [], 'requisitos' => [],
        ]);
    }

    public function test_guarda_el_borrador_en_la_cuenta_como_solicitud_sin_enviar(): void
    {
        $this->guardar(['campos' => ['gpa_general' => '71.5']])->assertOk()->assertJson(['en_cuenta' => true]);

        $borrador = Solicitud::sole();
        $this->assertNull($borrador->enviada_at);
        $this->assertSame('borrador', $borrador->estadoSolicitud->codigo);
        $this->assertSame($this->inscripcion->id, $borrador->carrera_estudiante_id);
        $this->assertSame(Periodo::where('codigo', 'ACTUAL')->value('id'), $borrador->periodo_id);
        $this->assertSame('recuperacion', $borrador->detalle['tramite']);
        $this->assertSame('71.50', $borrador->gpa_declarado);
        $this->assertFalse($borrador->declaracion_veracidad);
        $this->assertMatchesRegularExpression('/^SOL-\d{4}-\d{5}$/', $borrador->codigo);
        // Lo guardado en la cuenta no se duplica en la sesión.
        $this->assertSame([], session('borradores_temporales', []));
    }

    public function test_guardar_otra_vez_actualiza_el_mismo_borrador(): void
    {
        $this->guardar(['paso' => 1])->assertOk();
        $codigo = Solicitud::sole()->codigo;

        $this->guardar(['paso' => 3, 'campos' => ['observacion' => 'Segundo guardado']])->assertOk();

        $borrador = Solicitud::sole();
        $this->assertSame($codigo, $borrador->codigo);
        $this->assertSame(3, $borrador->detalle['paso']);
        $this->assertSame('Segundo guardado', $borrador->detalle['campos']['observacion']);
    }

    public function test_cada_tramite_tiene_su_propio_borrador(): void
    {
        $this->guardar([], 'recuperacion')->assertOk();
        $this->guardar([], 'gracia')->assertOk();

        $this->assertSame(['gracia', 'recuperacion'],
            Solicitud::borradores()->get()->pluck('detalle.tramite')->sort()->values()->all());
    }

    public function test_se_retoma_desde_otra_sesion(): void
    {
        $this->guardar(['paso' => 3, 'campos' => ['observacion' => 'Escrito en otra computadora']])->assertOk();

        $this->flushSession();

        $borrador = $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk()->viewData('borrador');
        $this->assertSame('Escrito en otra computadora', $borrador['campos']['observacion']);
        $this->assertTrue($borrador['en_cuenta']);
    }

    public function test_mis_solicitudes_lo_ofrece_sin_terminar_y_no_como_registrada(): void
    {
        $this->guardar(['paso' => 2])->assertOk();
        $borrador = Solicitud::sole();

        $vista = $this->get(route('solicitudes.index'))->assertOk();

        $this->assertSame([['codigo' => 'recuperacion', 'paso' => 2, 'en_cuenta' => true]], array_map(
            fn ($pendiente) => array_intersect_key($pendiente, array_flip(['codigo', 'paso', 'en_cuenta'])),
            $vista->viewData('sinTerminar')
        ));
        $this->assertFalse(collect($vista->viewData('solicitudes'))->contains('id', $borrador->id));
        $this->assertSame(0, app(SolicitudService::class)->delEstudiante($this->inscripcion->estudiante_id)->count());
    }

    public function test_el_seguimiento_de_un_borrador_lleva_al_formulario(): void
    {
        $this->guardar()->assertOk();

        $this->get(route('solicitudes.show', Solicitud::sole()))
            ->assertRedirect(route('solicitudes.create', ['tramite' => 'recuperacion']));
    }

    public function test_otro_estudiante_no_ve_ni_descarta_el_borrador(): void
    {
        $this->guardar(['campos' => ['observacion' => 'Privado']])->assertOk();
        $borrador = Solicitud::sole();
        [$otro] = $this->estudianteInscrito('2026000002');

        $this->actingAs($otro);
        $this->assertNull($this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->viewData('borrador'));
        $this->get(route('solicitudes.show', $borrador))->assertForbidden();
        $this->deleteJson(route('borradores-temporales.destroy', 'recuperacion'))->assertNoContent();

        $this->assertModelExists($borrador);
    }

    public function test_el_personal_no_ve_los_borradores(): void
    {
        $this->guardar()->assertOk();

        $this->assertFalse(User::factory()->create(['tipo' => 'trabajador'])->can('view', Solicitud::sole()));
    }

    public function test_descartar_borra_la_fila_y_sus_respaldos(): void
    {
        $this->guardar()->assertOk();
        $this->postJson(route('respaldos-temporales.store'), ['tramite' => 'recuperacion', 'archivo' => $this->pdf()])->assertCreated();

        $this->deleteJson(route('borradores-temporales.destroy', 'recuperacion'))->assertNoContent();

        $this->assertDatabaseCount('solicitudes', 0);
        $this->assertSame([], app(RespaldosTemporales::class)->delTramite('recuperacion'));
    }

    public function test_sin_inscripcion_activa_el_borrador_queda_en_la_sesion(): void
    {
        $this->inscripcion->update(['estado' => 'retirado']);

        $this->guardar(['campos' => ['observacion' => 'Sin inscripción']])->assertOk()->assertJson(['en_cuenta' => false]);

        $this->assertDatabaseCount('solicitudes', 0);
        $this->assertSame('Sin inscripción', session('borradores_temporales.recuperacion.campos.observacion'));
    }

    public function test_al_poder_guardarse_en_la_cuenta_se_lleva_lo_de_la_sesion(): void
    {
        $this->inscripcion->update(['estado' => 'retirado']);
        $this->guardar(['paso' => 1])->assertOk()->assertJson(['en_cuenta' => false]);

        $this->inscripcion->update(['estado' => 'activo']);
        $this->guardar(['paso' => 2])->assertOk()->assertJson(['en_cuenta' => true]);

        $this->assertSame(2, Solicitud::sole()->detalle['paso']);
        $this->assertSame([], session('borradores_temporales', []));
        $this->assertCount(1, $this->get(route('solicitudes.index'))->viewData('sinTerminar'));
    }

    public function test_un_borrador_de_la_cuenta_sigue_ahi_aunque_falte_el_contexto(): void
    {
        $this->guardar(['paso' => 1])->assertOk();
        $this->inscripcion->update(['estado' => 'retirado']);

        $this->guardar(['paso' => 3])->assertOk()->assertJson(['en_cuenta' => true]);

        $this->assertSame(3, Solicitud::sole()->detalle['paso']);
    }

    public function test_al_enviar_el_borrador_se_convierte_en_la_solicitud(): void
    {
        // Un trámite sin materias, para no depender del catálogo académico de G1.
        $catalogo = app(TramitesSimulados::class);
        $tramite = collect(array_keys(DocumentoSolicitud::PLANTILLAS))->first(fn ($codigo) => $catalogo->buscar($codigo)['max_materias'] === 0);
        $def = $catalogo->buscar($tramite);
        $datos = CatalogoFacultades::normalizar([
            'tramite' => $tramite, 'nombre' => $this->usuario->name, 'correo' => $this->usuario->email,
            'codigo' => '2026000012', 'cedula' => '0912345678', 'celular' => '0991234567', 'facultad' => 'Ingeniería',
            'carrera' => 'Ingeniería en Computación', 'modalidad' => 'Presencial', 'periodo' => 'ACTUAL',
            'campos' => $this->camposDe($def['campos']), 'declaracion' => true, 'materias' => [],
        ]);
        $this->guardar(['paso' => 4, 'campos' => $datos['campos']], $tramite)->assertOk();
        $borrador = Solicitud::sole();

        $temporales = app(DocumentosTemporales::class);
        $temporales->guardar($tramite, app(DocumentoSolicitud::class)->generarWord($datos), $datos);
        $datos['clave_envio'] = $temporales->buscar($tramite)['version'];
        app(FirmadosTemporales::class)->guardar($this->pdf(), $tramite);
        $datos['requisitos'] = [];
        foreach ($def['documentos'] as $doc) {
            if ($doc['obligatorio']) {
                $datos['requisitos'][app(RespaldosTemporales::class)->agregar($this->pdf(), $tramite)['id']] = $doc['id'];
            }
        }

        $this->postJson(route('solicitudes.enviar'), $datos)->assertCreated()->assertJson(['codigo' => $borrador->codigo]);

        $enviada = Solicitud::sole();
        $this->assertSame($borrador->id, $enviada->id);
        $this->assertNotNull($enviada->enviada_at);
        $this->assertTrue($enviada->declaracion_veracidad);
        $this->assertSame('enviada', $enviada->estadoSolicitud->codigo);
        $this->assertSame(0, Solicitud::borradores()->count());
        $this->assertSame([], $this->get(route('solicitudes.index'))->viewData('sinTerminar'));
    }

    /**
     * @param  list<array<string, mixed>>  $campos
     * @return array<string, string>
     */
    private function camposDe(array $campos): array
    {
        $datos = [];
        foreach ($campos as $campo) {
            if (isset($campo['mostrar_si'])) {
                continue;
            }
            $datos[$campo['nombre']] = match ($campo['tipo']) {
                'numero' => str_contains($campo['nombre'], 'anio') ? '2025' : (str_contains($campo['nombre'], 'semana') ? '1' : '80'),
                'fecha' => '2025-01-01', 'hora' => '08:30', 'lista' => (string) array_key_first($campo['opciones']), default => 'Información de prueba',
            };
        }

        return $datos;
    }
}
