<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\DirectorCarrera;
use App\Models\Facultad;
use App\Models\Trabajador;
use App\Models\User;
use App\Services\Academico\AutorizacionService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@uees.edu.ec')->first();
    }

    public function test_el_administrador_abre_las_pantallas_de_personas(): void
    {
        foreach ([
            route('administracion.personal'),
            route('administracion.personal', ['rol' => 'docente']),
            route('administracion.personal.editar', Trabajador::where('codigo', 'AST-001')->first()),
            route('administracion.directores'),
            route('administracion.cuentas'),
            route('administracion.roles'),
        ] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }

        $this->actingAs($this->admin)->get('/users')->assertNotFound();
        $this->actingAs($this->admin)->get('/directores_carrera')->assertNotFound();
    }

    public function test_la_tabla_de_directores_se_llama_como_en_el_excel(): void
    {
        $this->assertTrue(Schema::hasTable('directores_carrera'));
        $this->assertFalse(Schema::hasTable('director_carreras'));
    }

    public function test_registrar_un_docente_sin_acceso(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('administracion.personal.store'), [
            'nombres' => 'Rosa', 'apellidos' => 'Mena', 'correo' => 'rmena@uees.edu.ec', 'codigo' => 'DOC-004',
            'roles' => ['docente'],
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->post(route('administracion.personal.store'), [
            'nombres' => 'Sin', 'apellidos' => 'Código', 'correo' => 'sincodigo@uees.edu.ec', 'roles' => ['docente'],
        ])->assertSessionHasErrors('codigo');

        $docente = Trabajador::where('correo', 'rmena@uees.edu.ec')->first();
        $this->assertNull($docente->user_id);
        $this->assertTrue(Trabajador::conRol('docente')->whereKey($docente->id)->exists());
        Notification::assertNothingSent();
    }

    public function test_registrar_una_asistente_con_acceso_le_envia_el_enlace(): void
    {
        Notification::fake();
        $ingenieria = Facultad::where('codigo', 'ING')->value('id');

        $this->actingAs($this->admin)->post(route('administracion.personal.store'), [
            'codigo' => 'AST-002', 'nombres' => 'Lucía', 'apellidos' => 'Torres', 'correo' => 'ltorres@uees.edu.ec', 'roles' => ['asistente'], 'acceso' => '1',
        ])->assertSessionHasErrors('facultades');

        $this->actingAs($this->admin)->post(route('administracion.personal.store'), [
            'codigo' => 'AST-002', 'nombres' => 'Lucía', 'apellidos' => 'Torres', 'correo' => 'ltorres@uees.edu.ec', 'roles' => ['asistente'],
            'facultades' => [$ingenieria], 'acceso' => '1',
        ])->assertSessionHasNoErrors()->assertSessionHas('exito', fn ($m) => str_contains($m, 'enlace'));

        $usuario = User::where('email', 'ltorres@uees.edu.ec')->first();
        $this->assertSame('trabajador', $usuario->tipo);
        Notification::assertSentTo($usuario, ResetPassword::class);

        $autorizacion = app(AutorizacionService::class);
        $this->assertTrue($autorizacion->tieneRol($usuario, 'asistente'));
        $this->assertTrue($autorizacion->puedeVerFacultad($usuario, $ingenieria));
    }

    public function test_editar_roles_facultades_y_quitar_el_acceso(): void
    {
        Notification::fake();
        $asistente = Trabajador::where('codigo', 'AST-001')->first();
        $salud = Facultad::where('codigo', 'SAL')->value('id');

        $this->actingAs($this->admin)->put(route('administracion.personal.update', $asistente), [
            'codigo' => 'AST-001', 'nombres' => 'Ana', 'apellidos' => 'Asistente', 'correo' => 'asistente@uees.edu.ec',
            'roles' => ['asistente', 'consulta'], 'facultades' => [$salud],
        ])->assertSessionHasNoErrors();

        $usuario = $asistente->user->fresh();
        $this->assertFalse($usuario->activo);
        $this->assertSame([$salud], $asistente->facultades()->pluck('facultades.id')->all());
        $this->assertTrue(app(AutorizacionService::class)->tieneRol(User::find($usuario->id)->forceFill(['activo' => true]), 'consulta'));
    }

    public function test_el_administrador_no_se_quita_a_si_mismo_el_acceso(): void
    {
        $yo = $this->admin->trabajador;
        $datos = ['codigo' => 'ADM-001', 'nombres' => 'Administrador', 'apellidos' => 'Académico', 'correo' => 'admin@uees.edu.ec', 'acceso' => '1'];

        $this->actingAs($this->admin)->put(route('administracion.personal.update', $yo), $datos + ['roles' => ['consulta']])
            ->assertSessionHasErrors('roles');
        $this->actingAs($this->admin)->put(route('administracion.personal.update', $yo), ['acceso' => null] + $datos + ['roles' => ['admin']])
            ->assertSessionHasErrors('acceso');
        $this->actingAs($this->admin)->patch(route('administracion.personal.estado', $yo))->assertSessionHas('error');
        $this->actingAs($this->admin)->patch(route('administracion.cuentas.estado', $this->admin))->assertSessionHas('error');

        $this->assertTrue($this->admin->fresh()->activo);
    }

    public function test_desactivar_a_una_persona_le_impide_entrar(): void
    {
        $director = Trabajador::where('codigo', 'DIR-001')->first();

        $this->actingAs($this->admin)->patch(route('administracion.personal.estado', $director));
        $this->assertFalse((bool) $director->fresh()->activo);
        $this->assertFalse($director->user->fresh()->activo);

        auth()->logout();
        $this->post('/login', ['email' => 'director@uees.edu.ec', 'password' => 'password123'])
            ->assertSessionHasErrors(['email' => 'Tu cuenta está desactivada. Comunícate con la administración académica.']);
        $this->assertGuest();
    }

    public function test_cuentas_desactivar_y_enviar_enlace(): void
    {
        Notification::fake();
        $estudiante = User::where('email', 'estudiante@uees.edu.ec')->first();

        $this->actingAs($this->admin)->get(route('administracion.cuentas', ['tipo' => 'estudiante']))
            ->assertSee('estudiante@uees.edu.ec')->assertDontSee('asistente@uees.edu.ec');

        $this->actingAs($this->admin)->post(route('administracion.cuentas.enlace', $estudiante))->assertSessionHas('exito');
        Notification::assertSentTo($estudiante, ResetPassword::class);

        $this->actingAs($this->admin)->patch(route('administracion.cuentas.estado', $estudiante));
        $this->assertFalse($estudiante->fresh()->activo);

        $this->actingAs($this->admin)->post(route('administracion.cuentas.enlace', $estudiante))->assertSessionHas('error');
    }

    public function test_el_enlace_permite_crear_una_contrasena_nueva(): void
    {
        Notification::fake();
        $estudiante = User::where('email', 'estudiante@uees.edu.ec')->first();

        $this->actingAs($this->admin)->post(route('administracion.cuentas.enlace', $estudiante));
        auth()->logout();

        Notification::assertSentTo($estudiante, ResetPassword::class, function ($aviso) use ($estudiante) {
            $this->post('/reset-password', [
                'token' => $aviso->token, 'email' => $estudiante->email,
                'password' => 'NuevaClave2026', 'password_confirmation' => 'NuevaClave2026',
            ])->assertSessionHasNoErrors();

            return true;
        });

        $this->post('/login', ['email' => 'estudiante@uees.edu.ec', 'password' => 'NuevaClave2026']);
        $this->assertAuthenticated();
    }

    public function test_asignar_un_director_cierra_la_etapa_del_anterior(): void
    {
        Notification::fake();
        $computacion = Carrera::where('codigo', 'COMP')->first();
        $anterior = DirectorCarrera::where('carrera_id', $computacion->id)->first();

        $this->actingAs($this->admin)->post(route('administracion.personal.store'), [
            'codigo' => 'DIR-002', 'nombres' => 'Pedro', 'apellidos' => 'Salas', 'correo' => 'psalas@uees.edu.ec', 'roles' => ['director', 'docente'], 'acceso' => '1',
        ]);
        $nuevo = Trabajador::where('correo', 'psalas@uees.edu.ec')->first();

        $this->actingAs($this->admin)->post(route('administracion.directores.store'), [
            'carrera_id' => $computacion->id, 'trabajador_id' => Trabajador::where('codigo', 'DOC-001')->value('id'), 'fecha_inicio' => '2026-11-01',
        ])->assertSessionHasErrors('trabajador_id');

        $this->actingAs($this->admin)->post(route('administracion.directores.store'), [
            'carrera_id' => $computacion->id, 'trabajador_id' => $nuevo->id, 'fecha_inicio' => '2026-11-01',
        ])->assertSessionHasNoErrors();

        $this->assertSame('2026-10-31', $anterior->fresh()->fecha_fin->toDateString());

        $this->travelTo('2026-11-05');
        $this->assertSame('psalas@uees.edu.ec', app(AutorizacionService::class)->directorDeCarrera($computacion->id)->correo);
        $this->assertTrue(app(AutorizacionService::class)->puedeVerCarrera($nuevo->user, $computacion->id));
        $this->assertFalse(app(AutorizacionService::class)->puedeVerCarrera(User::where('email', 'director@uees.edu.ec')->first(), $computacion->id));

        $this->actingAs($this->admin)->post(route('administracion.directores.store'), [
            'carrera_id' => $computacion->id, 'trabajador_id' => $nuevo->id, 'fecha_inicio' => '2026-10-15',
        ])->assertSessionHasErrors('fecha_inicio');
    }

    public function test_terminar_la_etapa_de_un_director(): void
    {
        $asignacion = DirectorCarrera::first();

        $this->actingAs($this->admin)->patch(route('administracion.directores.terminar', $asignacion), ['fecha_fin' => '2025-12-31'])
            ->assertSessionHasErrors('fecha_fin');

        $this->actingAs($this->admin)->patch(route('administracion.directores.terminar', $asignacion), ['fecha_fin' => '2026-10-21'])
            ->assertSessionHas('exito');

        $this->assertSame('2026-10-21', $asignacion->fresh()->fecha_fin->toDateString());
    }

    public function test_roles_en_solo_lectura_e_inicio_cuenta_solo_docentes(): void
    {
        $this->actingAs($this->admin)->get(route('administracion.roles'))
            ->assertSee('asistente')->assertSee('Director de Carrera')->assertDontSee('Guardar');

        $this->actingAs($this->admin)->get(route('administracion.inicio'))
            ->assertViewHas('conteos', fn ($c) => $c['Docentes'] === 3);
    }
}
