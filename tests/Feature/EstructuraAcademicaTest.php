<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\Curso;
use App\Models\Facultad;
use App\Models\Malla;
use App\Models\Materia;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\Solicitud;
use App\Models\SolicitudMateria;
use App\Models\TipoTramite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EstructuraAcademicaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@uees.edu.ec')->first();
    }

    public function test_el_administrador_abre_todas_las_pantallas(): void
    {
        $malla = Malla::first();

        foreach ([
            route('administracion.facultades'),
            route('administracion.carreras'),
            route('administracion.mallas'),
            route('administracion.mallas.ver', $malla),
            route('administracion.periodos'),
            route('administracion.materias'),
            route('administracion.paralelos'),
            route('administracion.facultades.editar', Facultad::first()),
            route('administracion.carreras.editar', Carrera::first()),
            route('administracion.mallas.editar', $malla),
            route('administracion.periodos.editar', Periodo::first()),
            route('administracion.materias.editar', Materia::first()),
            route('administracion.paralelos.editar', Curso::first()),
        ] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_otros_roles_no_entran(): void
    {
        $asistente = User::where('email', 'asistente@uees.edu.ec')->first();

        $this->actingAs($asistente)->get(route('administracion.facultades'))->assertForbidden();
        $this->actingAs($asistente)->post(route('administracion.carreras.store'), [])->assertForbidden();
    }

    public function test_la_ruta_vieja_de_cursos_ya_no_existe(): void
    {
        $this->actingAs($this->admin)->get('/cursos')->assertNotFound();
    }

    public function test_crear_y_editar_una_facultad(): void
    {
        $this->actingAs($this->admin)->post(route('administracion.facultades.store'), [
            'codigo' => 'POS', 'nombre' => 'Posgrados', 'correo_notificaciones' => 'posgrados@uees.edu.ec',
        ])->assertRedirect(route('administracion.facultades'));

        $facultad = Facultad::where('codigo', 'POS')->first();
        $this->assertTrue((bool) $facultad->activo);

        $this->actingAs($this->admin)->put(route('administracion.facultades.update', $facultad), [
            'codigo' => 'POS', 'nombre' => 'Escuela de Posgrados',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Escuela de Posgrados', $facultad->fresh()->nombre);

        $this->actingAs($this->admin)->post(route('administracion.facultades.store'), [
            'codigo' => 'POS', 'nombre' => 'Repetida',
        ])->assertSessionHasErrors('codigo');
    }

    public function test_no_se_desactiva_una_facultad_con_carreras_activas(): void
    {
        $facultad = Facultad::where('codigo', 'DER')->first();

        $this->actingAs($this->admin)->patch(route('administracion.facultades.estado', $facultad))->assertSessionHas('error');
        $this->assertTrue((bool) $facultad->fresh()->activo);

        $facultad->carreras()->update(['activo' => false]);

        $this->actingAs($this->admin)->patch(route('administracion.facultades.estado', $facultad))->assertSessionHas('exito');
        $this->assertFalse((bool) $facultad->fresh()->activo);

        $this->actingAs($this->admin)->patch(route('administracion.carreras.estado', $facultad->carreras()->first()))->assertSessionHas('error');
    }

    public function test_crear_una_carrera_con_sus_modalidades(): void
    {
        $modalidades = Modalidad::whereIn('nombre', ['Presencial', 'Híbrida'])->pluck('id')->all();

        $this->actingAs($this->admin)->post(route('administracion.carreras.store'), [
            'facultad_id' => Facultad::where('codigo', 'ING')->value('id'),
            'codigo' => 'MEC',
            'nombre' => 'Ingeniería Mecatrónica',
            'nivel' => 'grado',
            'modalidades' => $modalidades,
            'admite_tercer_registro' => '1',
        ])->assertSessionHasNoErrors();

        $carrera = Carrera::where('codigo', 'MEC')->first();
        $this->assertEqualsCanonicalizing($modalidades, $carrera->modalidades->pluck('id')->all());
        $this->assertTrue((bool) $carrera->admite_tercer_registro);

        $this->actingAs($this->admin)->post(route('administracion.carreras.store'), [
            'facultad_id' => Facultad::where('codigo', 'ING')->value('id'),
            'codigo' => 'OTRA', 'nombre' => 'Sin modalidad', 'nivel' => 'grado',
        ])->assertSessionHasErrors('modalidades');
    }

    public function test_no_se_quita_una_modalidad_con_estudiantes(): void
    {
        $computacion = Carrera::where('codigo', 'COMP')->first();
        $enLinea = Modalidad::where('nombre', 'En línea')->value('id');
        $presencial = Modalidad::where('nombre', 'Presencial')->value('id');
        $datos = ['facultad_id' => $computacion->facultad_id, 'codigo' => 'COMP', 'nombre' => $computacion->nombre, 'nivel' => 'grado', 'admite_tercer_registro' => '1'];

        $this->actingAs($this->admin)->put(route('administracion.carreras.update', $computacion), $datos + ['modalidades' => [$enLinea]])
            ->assertSessionHasErrors('modalidades');

        $this->actingAs($this->admin)->put(route('administracion.carreras.update', $computacion), $datos + ['modalidades' => [$presencial]])
            ->assertSessionHasNoErrors();

        $this->assertSame(['Presencial'], $computacion->fresh()->modalidades->pluck('nombre')->all());
    }

    public function test_solo_hay_una_malla_vigente_por_carrera(): void
    {
        $computacion = Carrera::where('codigo', 'COMP')->first();
        $anterior = $computacion->mallas()->where('vigente', true)->first();

        $this->actingAs($this->admin)->post(route('administracion.mallas.store'), [
            'carrera_id' => $computacion->id, 'version' => '2026', 'vigente' => '1',
        ]);

        $nueva = $computacion->mallas()->where('version', '2026')->first();
        $this->assertTrue((bool) $nueva->vigente);
        $this->assertFalse((bool) $anterior->fresh()->vigente);

        $this->actingAs($this->admin)->patch(route('administracion.mallas.vigente', $anterior));
        $this->assertSame([$anterior->id], $computacion->mallas()->where('vigente', true)->pluck('id')->all());

        $this->actingAs($this->admin)->post(route('administracion.mallas.store'), [
            'carrera_id' => $computacion->id, 'version' => '2026',
        ])->assertSessionHasErrors('version');
    }

    public function test_agregar_y_quitar_materias_de_una_malla(): void
    {
        $malla = Malla::whereHas('carrera', fn ($q) => $q->where('codigo', 'MED'))->first();
        $materia = Materia::where('codigo', 'GEN101')->first();

        $this->actingAs($this->admin)->post(route('administracion.mallas.materias.store', $malla), ['materia_id' => $materia->id, 'nivel' => 3])
            ->assertSessionHasNoErrors();
        $this->assertSame(3, (int) $malla->materias()->find($materia->id)->pivot->nivel);

        $this->actingAs($this->admin)->post(route('administracion.mallas.materias.store', $malla), ['materia_id' => $materia->id, 'nivel' => 1])
            ->assertSessionHasErrors('materia_id');

        $this->actingAs($this->admin)->delete(route('administracion.mallas.materias.destroy', [$malla, $materia]));
        $this->assertFalse($malla->materias()->where('materias.id', $materia->id)->exists());
    }

    public function test_editar_y_activar_periodos(): void
    {
        $anterior = Periodo::where('codigo', 'ORD-1-2026')->first();
        $actual = Periodo::where('codigo', 'ORD-2-2026')->first();

        $this->actingAs($this->admin)->put(route('administracion.periodos.update', $actual), [
            'codigo' => 'ORD-2-2026', 'nombre' => 'Ordinario II 2026', 'tipo' => 'ordinario',
            'fecha_inicio' => '2026-09-07', 'fecha_fin' => '2026-12-23', 'semana_primer_parcial' => 9, 'activo' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame(9, (int) $actual->fresh()->semana_primer_parcial);

        $this->actingAs($this->admin)->patch(route('administracion.periodos.activar', $anterior));
        $this->assertSame(['ORD-1-2026'], Periodo::where('activo', true)->pluck('codigo')->all());
    }

    public function test_desactivar_una_materia_la_saca_de_los_formularios(): void
    {
        $materia = Materia::where('codigo', 'FIS101')->first();

        $this->actingAs($this->admin)->patch(route('administracion.materias.estado', $materia));
        $this->assertFalse((bool) $materia->fresh()->activo);

        $this->actingAs($this->admin)->get(route('administracion.paralelos'))
            ->assertViewHas('materias', fn ($materias) => ! $materias->contains('id', $materia->id));

        $this->actingAs($this->admin)->put(route('administracion.materias.update', $materia), [
            'codigo' => 'FIS101', 'nombre' => 'Física General', 'creditos' => 3,
        ])->assertSessionHasNoErrors();
        $this->assertSame('Física General', $materia->fresh()->nombre);
    }

    public function test_editar_y_borrar_paralelos(): void
    {
        $curso = Curso::whereHas('materia', fn ($q) => $q->where('codigo', 'COM101'))->where('paralelo', 'B1')->first();

        $this->actingAs($this->admin)->put(route('administracion.paralelos.update', $curso), [
            'materia_id' => $curso->materia_id, 'periodo_id' => $curso->periodo_id, 'paralelo' => 'A1', 'cupo' => 30,
        ])->assertSessionHasErrors('paralelo');

        $this->actingAs($this->admin)->put(route('administracion.paralelos.update', $curso), [
            'materia_id' => $curso->materia_id, 'periodo_id' => $curso->periodo_id, 'paralelo' => 'B1', 'cupo' => 30,
        ])->assertSessionHasNoErrors();
        $this->assertNull($curso->fresh()->profesor_id);
        $this->assertSame(30, (int) $curso->fresh()->cupo);

        $usado = Curso::where('id', '!=', $curso->id)->first();
        $solicitud = Solicitud::factory()->create([
            'carrera_estudiante_id' => CarreraEstudiante::first()->id,
            'tipo_tramite_id' => TipoTramite::create(['codigo' => 'retiro_materia', 'nombre' => 'Retiro de materia', 'slug' => 'retiro-materia', 'contenido' => []])->id,
            'periodo_id' => $usado->periodo_id,
            'estado_solicitud_id' => DB::table('estados_solicitud')->value('id'),
        ]);
        SolicitudMateria::factory()->create(['solicitud_id' => $solicitud->id, 'curso_id' => $usado->id]);

        $this->actingAs($this->admin)->delete(route('administracion.paralelos.destroy', $usado))->assertSessionHas('error');
        $this->assertNotNull($usado->fresh());

        $this->actingAs($this->admin)->delete(route('administracion.paralelos.destroy', $curso))->assertSessionHas('exito');
        $this->assertNull($curso->fresh());
    }

    public function test_buscar_y_filtrar_en_las_listas(): void
    {
        $this->actingAs($this->admin)->get(route('administracion.materias', ['buscar' => 'program']))
            ->assertSee('COM101')->assertSee('COM201')->assertDontSee('MAT101');

        $ingenieria = Facultad::where('codigo', 'ING')->value('id');
        $this->actingAs($this->admin)->get(route('administracion.carreras', ['facultad' => $ingenieria]))
            ->assertViewHas('carreras', fn ($carreras) => $carreras->total() === 5);

        $this->actingAs($this->admin)->get(route('administracion.carreras'))
            ->assertViewHas('carreras', fn ($carreras) => $carreras->count() === 15 && $carreras->total() === 38);
    }
}
