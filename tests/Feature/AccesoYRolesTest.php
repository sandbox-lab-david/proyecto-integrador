<?php

namespace Tests\Feature;

use App\Http\Controllers\Acceso\PanelController;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AccesoYRolesTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioConRol(string $rol): User
    {
        $usuario = User::factory()->create(['tipo' => 'trabajador', 'activo' => true]);
        $trabajador = Trabajador::create([
            'user_id' => $usuario->id,
            'codigo' => 'T-'.$usuario->id,
            'nombres' => 'Persona',
            'apellidos' => 'Prueba',
            'correo' => $usuario->email,
            'activo' => true,
        ]);
        DB::table('rol_trabajador')->insert(['trabajador_id' => $trabajador->id, 'rol' => $rol]);

        return $usuario;
    }

    public function test_sin_sesion_las_paginas_del_grupo_1_mandan_al_login(): void
    {
        foreach (['/administracion', '/periodos', '/materias', '/paralelos', '/administracion/cargas'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_solo_el_administrador_entra_al_grupo_1(): void
    {
        $this->actingAs(User::factory()->create(['tipo' => 'estudiante']));
        $this->get('/periodos')->assertForbidden()->assertSee('No tienes permiso para ver esta página');

        $this->actingAs($this->usuarioConRol('asistente'));
        $this->get('/periodos')->assertForbidden();

        $this->actingAs($this->usuarioConRol('admin'));
        $this->get('/periodos')->assertOk();
    }

    public function test_cada_rol_llega_a_su_lugar_despues_de_iniciar_sesion(): void
    {
        $this->actingAs($this->usuarioConRol('admin'));
        $this->get('/dashboard')->assertRedirect(route('administracion.inicio'));

        $this->actingAs($this->usuarioConRol('asistente'));
        $this->get('/dashboard')->assertRedirect(route('revision.asistente'));

        $this->actingAs($this->usuarioConRol('director'));
        $this->get('/dashboard')->assertRedirect(route('revision.director'));

        $this->actingAs($this->usuarioConRol('consulta'));
        $this->get('/dashboard')->assertRedirect(route('revision.resoluciones'));

        $this->actingAs(User::factory()->create(['tipo' => 'estudiante']));
        $this->get('/dashboard')->assertRedirect(route('estudiante.catalogo'));

        $this->actingAs($this->usuarioConRol('docente'));
        $this->get('/dashboard')->assertOk()->assertSee('Tu cuenta aún no tiene un módulo asignado');
    }

    public function test_cada_destino_deja_entrar_a_su_rol(): void
    {
        $destinos = (new \ReflectionClassConstant(PanelController::class, 'DESTINOS'))->getValue();

        foreach ($destinos as $destino) {
            $this->assertTrue(Route::has($destino['ruta']), "No existe la ruta {$destino['ruta']}");

            $permiso = collect(Route::getRoutes()->getByName($destino['ruta'])->gatherMiddleware())
                ->first(fn ($middleware) => is_string($middleware) && str_starts_with($middleware, 'rol:'));
            $this->assertNotNull($permiso, "La ruta {$destino['ruta']} no tiene permiso rol:");

            $permitidos = explode(',', substr($permiso, 4));
            foreach ($destino['roles'] as $rol) {
                $this->assertContains($rol, $permitidos, "La ruta {$destino['ruta']} no deja entrar al rol {$rol}");
            }
        }
    }

    public function test_la_pagina_de_inicio_manda_al_login_o_al_panel(): void
    {
        $this->get('/')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['tipo' => 'estudiante']));
        $this->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_no_hay_registro_publico(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Ana Pérez',
            'email' => 'ana.perez@uees.edu.ec',
            'password' => 'ClaveSegura123',
            'password_confirmation' => 'ClaveSegura123',
        ])->assertNotFound();

        $this->assertGuest();
        $this->get(route('login'))->assertOk()->assertSee('Crea tu contraseña')->assertSee(route('password.request'), false);
    }

    public function test_el_login_muestra_errores_en_espanol(): void
    {
        $usuario = User::factory()->create(['email' => 'persona@uees.edu.ec']);

        $this->post('/login', ['email' => $usuario->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.']);
    }
}
