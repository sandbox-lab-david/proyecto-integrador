<?php

namespace Tests\Feature;

use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CambioDeModuloTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioConRoles(array $roles): User
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

        foreach ($roles as $rol) {
            DB::table('rol_trabajador')->insert(['trabajador_id' => $trabajador->id, 'rol' => $rol]);
        }

        return $usuario;
    }

    public function test_admin_que_tambien_es_asistente_ve_el_otro_modulo_en_ambos_menus(): void
    {
        $usuario = $this->usuarioConRoles(['admin', 'asistente']);

        $this->actingAs($usuario)->get('/dashboard')->assertRedirect(route('administracion.inicio'));

        $this->actingAs($usuario)->get(route('administracion.inicio'))
            ->assertOk()
            ->assertSee('Otros módulos')
            ->assertSee('href="'.route('revision.asistente').'"', false);

        $this->actingAs($usuario)->get(route('revision.asistente'))
            ->assertOk()
            ->assertSee('Otros módulos')
            ->assertSee('href="'.route('administracion.inicio').'"', false);
    }

    public function test_admin_que_tambien_es_director_entra_a_revision_por_su_bandeja(): void
    {
        $this->actingAs($this->usuarioConRoles(['admin', 'director']))
            ->get(route('administracion.inicio'))
            ->assertOk()
            ->assertSee('href="'.route('revision.director').'"', false)
            ->assertDontSee('href="'.route('revision.asistente').'"', false);
    }

    public function test_quien_tiene_un_solo_modulo_no_ve_otros_modulos(): void
    {
        $this->actingAs($this->usuarioConRoles(['admin']))
            ->get(route('administracion.inicio'))
            ->assertOk()
            ->assertDontSee('Otros módulos');

        $this->actingAs($this->usuarioConRoles(['asistente']))
            ->get(route('revision.asistente'))
            ->assertOk()
            ->assertDontSee('Otros módulos');

        $this->actingAs($this->usuarioConRoles(['asistente', 'director']))
            ->get(route('revision.director'))
            ->assertOk()
            ->assertDontSee('Otros módulos');
    }
}
