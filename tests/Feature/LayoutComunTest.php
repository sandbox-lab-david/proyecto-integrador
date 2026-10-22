<?php

namespace Tests\Feature;

use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LayoutComunTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/prueba-marco/general', fn () => 'ok')->name('prueba.general');
        Route::get('/prueba-marco/solo-asistente', fn () => 'ok')->name('prueba.asistente');
        Route::getRoutes()->refreshNameLookups();
    }

    private function menuDePrueba(): array
    {
        return [
            'Sección de prueba' => [
                ['texto' => 'Enlace para todos', 'ruta' => 'prueba.general', 'activo' => 'prueba.general'],
                ['texto' => 'Enlace de asistente', 'ruta' => 'prueba.asistente', 'activo' => 'prueba.asistente', 'roles' => ['asistente']],
                ['texto' => 'Enlace sin ruta', 'ruta' => 'ruta.que.no.existe'],
            ],
        ];
    }

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

    public function test_el_marco_muestra_el_modulo_el_titulo_y_el_contenido(): void
    {
        $html = (string) $this->blade(
            '<x-layouts.app titulo="Página" modulo="Portal del estudiante" :menu="$menu">Contenido propio</x-layouts.app>',
            ['menu' => $this->menuDePrueba()]
        );

        $this->assertStringContainsString('<title>Página · Portal del estudiante · UEES</title>', $html);
        $this->assertStringContainsString('Portal del estudiante', $html);
        $this->assertStringContainsString('Contenido propio', $html);
        $this->assertStringContainsString('Enlace para todos', $html);
    }

    public function test_el_menu_salta_las_rutas_que_no_existen(): void
    {
        $this->blade('<x-layouts.app modulo="M" :menu="$menu">x</x-layouts.app>', ['menu' => $this->menuDePrueba()])
            ->assertDontSee('Enlace sin ruta');
    }

    public function test_el_menu_esconde_los_enlaces_con_rol_a_quien_no_lo_tiene(): void
    {
        $this->blade('<x-layouts.app modulo="M" :menu="$menu">x</x-layouts.app>', ['menu' => $this->menuDePrueba()])
            ->assertDontSee('Enlace de asistente');

        $this->actingAs($this->usuarioConRol('director'));
        $this->blade('<x-layouts.app modulo="M" :menu="$menu">x</x-layouts.app>', ['menu' => $this->menuDePrueba()])
            ->assertSee('Enlace para todos')
            ->assertDontSee('Enlace de asistente');
    }

    public function test_el_menu_muestra_los_enlaces_con_rol_a_quien_si_lo_tiene(): void
    {
        $this->actingAs($this->usuarioConRol('asistente'));

        $this->blade('<x-layouts.app modulo="M" :menu="$menu">x</x-layouts.app>', ['menu' => $this->menuDePrueba()])
            ->assertSee('Enlace de asistente');
    }

    public function test_activo_acepta_una_lista_de_rutas(): void
    {
        $menu = ['S' => [['texto' => 'Enlace con lista', 'ruta' => 'prueba.asistente', 'activo' => ['otra.ruta', 'prueba.lista*']]]];
        Route::get('/prueba-marco/lista', fn () => view()->file(
            resource_path('views/components/layouts/app.blade.php'), ['slot' => 'x', 'modulo' => 'M', 'titulo' => null, 'menu' => $menu]
        ))->name('prueba.lista');
        Route::getRoutes()->refreshNameLookups();

        $html = $this->get('/prueba-marco/lista')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>\s*Enlace con lista\s*</', $html);
    }

    public function test_sin_menu_no_se_muestra_la_columna_lateral(): void
    {
        $this->blade('<x-layouts.app modulo="M">x</x-layouts.app>')
            ->assertDontSee('<aside', false);
    }

    public function test_las_paginas_del_grupo_1_usan_el_marco_y_resaltan_su_enlace(): void
    {
        $this->actingAs($this->usuarioConRol('admin'));

        $paginas = [
            '/administracion' => 'Inicio',
            '/periodos' => 'Periodos académicos',
            '/materias' => 'Materias',
            '/paralelos' => 'Paralelos y docentes',
            '/administracion/cargas' => 'Importaciones SIAC',
        ];

        foreach ($paginas as $url => $enlace) {
            $respuesta = $this->get($url);
            $respuesta->assertOk();
            $respuesta->assertSee('Administración académica');
            $this->assertMatchesRegularExpression(
                '/aria-current="page"[^>]*>\s*'.preg_quote($enlace, '/').'\s*</',
                $respuesta->getContent(),
                "En $url no se resalta \"$enlace\""
            );
        }
    }

    public function test_las_vistas_antiguas_siguen_funcionando_con_el_puente(): void
    {
        $this->get('/tramites')->assertOk()->assertSee('Administración académica');

        $this->actingAs(User::factory()->create(['tipo' => 'trabajador']));
        $this->get('/profile')->assertOk()->assertSee('Administración académica');
    }
}
