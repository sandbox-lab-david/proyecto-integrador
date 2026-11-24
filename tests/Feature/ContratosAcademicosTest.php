<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\Malla;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Trabajador;
use App\Models\User;
use App\Services\Academico\AutorizacionService;
use App\Services\Academico\CatalogoAcademicoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContratosAcademicosTest extends TestCase
{
    use RefreshDatabase;

    private Facultad $ingenieria;

    private Facultad $salud;

    private Carrera $computacion;

    private Malla $malla;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ingenieria = Facultad::create(['codigo' => 'ING', 'nombre' => 'Ingeniería', 'activo' => true]);
        $this->salud = Facultad::create(['codigo' => 'SAL', 'nombre' => 'Ciencias de la Salud', 'activo' => true]);

        $this->computacion = Carrera::create(['facultad_id' => $this->ingenieria->id, 'codigo' => 'COMP', 'nombre' => 'Ingeniería en Computación', 'nivel' => 'grado', 'activo' => true]);
        Carrera::create(['facultad_id' => $this->ingenieria->id, 'codigo' => 'CIVIL', 'nombre' => 'Ingeniería Civil', 'nivel' => 'grado', 'activo' => false]);
        Carrera::create(['facultad_id' => $this->salud->id, 'codigo' => 'MED', 'nombre' => 'Medicina', 'nivel' => 'grado', 'activo' => true]);

        $presencial = Modalidad::create(['nombre' => 'Presencial']);
        $enLinea = Modalidad::create(['nombre' => 'En línea']);
        Modalidad::create(['nombre' => 'Dual']);
        $this->computacion->modalidades()->attach([$presencial->id, $enLinea->id]);

        $this->malla = Malla::create(['carrera_id' => $this->computacion->id, 'version' => '2024', 'vigente' => true]);
        $calculo = Materia::create(['codigo' => 'MAT101', 'nombre' => 'Cálculo I', 'creditos' => 4, 'activo' => true]);
        $programacion = Materia::create(['codigo' => 'COM101', 'nombre' => 'Programación I', 'creditos' => 4, 'activo' => true]);
        $retirada = Materia::create(['codigo' => 'VIE001', 'nombre' => 'Materia antigua', 'creditos' => 2, 'activo' => false]);
        Materia::create(['codigo' => 'OTR001', 'nombre' => 'Materia de otra malla', 'creditos' => 3, 'activo' => true]);
        $this->malla->materias()->attach([
            $programacion->id => ['nivel' => 2],
            $calculo->id => ['nivel' => 1],
            $retirada->id => ['nivel' => 1],
        ]);

        Periodo::create(['codigo' => 'ORD-1-2026', 'nombre' => 'Ordinario I 2026', 'tipo' => 'ordinario', 'fecha_inicio' => '2026-05-04', 'fecha_fin' => '2026-08-28', 'semana_primer_parcial' => 8, 'activo' => false]);
        Periodo::create(['codigo' => 'ORD-2-2026', 'nombre' => 'Ordinario II 2026', 'tipo' => 'ordinario', 'fecha_inicio' => '2026-09-07', 'fecha_fin' => '2026-12-18', 'semana_primer_parcial' => 8, 'activo' => true]);
    }

    private function trabajador(string $rol, bool $activo = true, ?Facultad $facultad = null): Trabajador
    {
        $usuario = User::factory()->create(['tipo' => 'trabajador', 'activo' => true]);
        $trabajador = Trabajador::create([
            'user_id' => $usuario->id,
            'codigo' => 'T-'.$usuario->id,
            'nombres' => ucfirst($rol),
            'apellidos' => 'Prueba '.$usuario->id,
            'correo' => $usuario->email,
            'activo' => $activo,
        ]);
        DB::table('rol_trabajador')->insert(['trabajador_id' => $trabajador->id, 'rol' => $rol]);
        if ($facultad) {
            $trabajador->facultades()->attach($facultad->id);
        }

        return $trabajador;
    }

    private function catalogo(): CatalogoAcademicoService
    {
        return app(CatalogoAcademicoService::class);
    }

    private function autorizacion(): AutorizacionService
    {
        return app(AutorizacionService::class);
    }

    public function test_c02_carreras_por_facultad_respeta_solo_activas(): void
    {
        $this->assertSame(['Ingeniería en Computación'], $this->catalogo()->carrerasPorFacultad($this->ingenieria->id)->pluck('nombre')->all());
        $this->assertCount(2, $this->catalogo()->carrerasPorFacultad($this->ingenieria->id, false));
        $this->assertCount(2, $this->catalogo()->carrerasPorFacultad());
    }

    public function test_c03_modalidades_de_una_carrera(): void
    {
        $this->assertSame(['En línea', 'Presencial'], $this->catalogo()->modalidadesDeCarrera($this->computacion->id)->pluck('nombre')->all());
    }

    public function test_c05_materias_de_la_malla_activas_por_nivel_y_con_busqueda(): void
    {
        $materias = $this->catalogo()->materiasDeMalla($this->malla->id);
        $this->assertSame(['MAT101', 'COM101'], $materias->pluck('codigo')->all());
        $this->assertSame(1, (int) $materias->first()->nivel);

        $this->assertSame(['COM101'], $this->catalogo()->materiasDeMalla($this->malla->id, 'progra')->pluck('codigo')->all());
        $this->assertSame(['MAT101'], $this->catalogo()->materiasDeMalla($this->malla->id, 'mat1')->pluck('codigo')->all());
    }

    public function test_c06_periodos_respeta_solo_activos(): void
    {
        $this->assertSame(['ORD-2-2026', 'ORD-1-2026'], $this->catalogo()->periodos()->pluck('codigo')->all());
        $this->assertSame(['ORD-2-2026'], $this->catalogo()->periodos(true)->pluck('codigo')->all());
    }

    public function test_c10_docentes_solo_con_rol_docente_y_activos(): void
    {
        $docente = $this->trabajador('docente');
        $this->trabajador('docente', false);
        $this->trabajador('asistente');

        $this->assertSame([$docente->id], $this->catalogo()->docentes()->pluck('id')->all());
        $this->assertCount(2, $this->catalogo()->docentes(false));
    }

    public function test_c16_facultades_de_un_trabajador(): void
    {
        $asistente = $this->trabajador('asistente', true, $this->ingenieria);
        $asistente->facultades()->attach($this->salud->id);

        $this->assertSame(['Ciencias de la Salud', 'Ingeniería'], $this->autorizacion()->facultadesDe($asistente)->pluck('nombre')->all());
    }

    public function test_c19_asistentes_de_una_facultad(): void
    {
        $asistente = $this->trabajador('asistente', true, $this->ingenieria);
        $this->trabajador('asistente', true, $this->salud);
        $this->trabajador('asistente', false, $this->ingenieria);
        $this->trabajador('director', true, $this->ingenieria);

        $this->assertSame([$asistente->id], $this->autorizacion()->asistentesDeFacultad($this->ingenieria->id)->pluck('id')->all());
    }

    public function test_c20_puede_ver_facultad(): void
    {
        $asistente = $this->trabajador('asistente', true, $this->ingenieria);
        $admin = $this->trabajador('admin');

        $this->assertTrue($this->autorizacion()->puedeVerFacultad($asistente->user, $this->ingenieria->id));
        $this->assertFalse($this->autorizacion()->puedeVerFacultad($asistente->user, $this->salud->id));
        $this->assertTrue($this->autorizacion()->puedeVerFacultad($admin->user, $this->salud->id));

        $asistente->update(['activo' => false]);
        $this->assertFalse($this->autorizacion()->puedeVerFacultad($asistente->user->fresh(), $this->ingenieria->id));
    }

    public function test_la_api_interna_pide_sesion(): void
    {
        foreach (['/api/interno/carreras?facultad_id=1', '/api/interno/periodos', '/api/interno/periodos/actual', '/api/interno/materias?malla_id=1'] as $url) {
            $this->getJson($url)->assertUnauthorized();
        }
    }

    public function test_c32_api_carreras(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson('/api/interno/carreras?facultad_id='.$this->ingenieria->id)
            ->assertOk()
            ->assertExactJson([['id' => $this->computacion->id, 'nombre' => 'Ingeniería en Computación']]);

        $this->getJson('/api/interno/carreras')->assertUnprocessable()->assertJsonValidationErrors(['facultad_id']);
    }

    public function test_c33_api_periodos_y_periodo_actual(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson('/api/interno/periodos')->assertOk()->assertJsonCount(2)->assertJsonPath('0.codigo', 'ORD-2-2026');
        $this->getJson('/api/interno/periodos/actual')->assertOk()->assertJsonPath('codigo', 'ORD-2-2026');

        Periodo::query()->update(['activo' => false]);
        $this->getJson('/api/interno/periodos/actual')->assertNotFound();
    }

    public function test_c34_api_materias(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson('/api/interno/materias?malla_id='.$this->malla->id)
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.codigo', 'MAT101')
            ->assertJsonStructure([['id', 'codigo', 'nombre']]);

        $this->getJson('/api/interno/materias?malla_id='.$this->malla->id.'&q=progra')->assertOk()->assertJsonCount(1);
        $this->getJson('/api/interno/materias')->assertUnprocessable()->assertJsonValidationErrors(['malla_id']);
    }
}
