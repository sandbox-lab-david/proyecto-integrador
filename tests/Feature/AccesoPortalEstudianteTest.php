<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AccesoPortalEstudianteTest extends TestCase
{
    private function rutasPortal(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($ruta) => str_starts_with($ruta->uri(), 'estudiante/')
                && $ruta->getName() !== 'estudiante.acceso')->values()->all();
    }

    private function uri($ruta): string
    {
        return '/'.preg_replace_callback('/\{([^}]+)\}/', fn ($m) => match ($m[1]) {
            'tramite' => 'gracia', 'solicitud' => '1',
            default => 'a29bb213-0738-4465-8faa-61ef53643d50',
        }, $ruta->uri());
    }

    public function test_todas_las_rutas_del_portal_exigen_sesion(): void
    {
        $rutas = $this->rutasPortal();
        $this->assertGreaterThan(30, count($rutas));
        foreach ($rutas as $ruta) {
            $this->json($ruta->methods()[0], $this->uri($ruta))->assertUnauthorized();
        }
        $this->get(route('estudiante.catalogo'))->assertRedirect(route('login'));
    }

    public function test_un_trabajador_no_puede_entrar_ni_subir_archivos_al_portal(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 91, 'tipo' => 'trabajador', 'activo' => true]));
        foreach ($this->rutasPortal() as $ruta) {
            $this->json($ruta->methods()[0], $this->uri($ruta))->assertForbidden();
        }
    }

    public function test_el_acceso_antiguo_dirige_al_login_de_breeze(): void
    {
        $this->get(route('estudiante.acceso'))->assertRedirect('/login');
    }

    public function test_el_menu_de_g2_usa_rutas_publicadas(): void
    {
        $menu = config('menu_estudiante');
        $this->assertNotEmpty($menu);
        foreach ($menu as $enlaces) {
            foreach ($enlaces as $enlace) {
                $this->assertTrue(Route::has($enlace['ruta']));
            }
        }
    }
}
