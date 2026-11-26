<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SesionVencidaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['env'] = 'local';
    }

    public function test_cerrar_sesion_con_la_pagina_vencida_lleva_al_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Tu sesión ya estaba cerrada.');
    }

    public function test_cerrar_sesion_cuando_la_sesion_ya_se_borro_lleva_al_login(): void
    {
        $this->post('/logout')
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Tu sesión ya estaba cerrada.');
    }

    public function test_un_formulario_vencido_vuelve_a_la_pagina_con_lo_escrito(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@uees.edu.ec')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('administracion.facultades'))
            ->post(route('administracion.facultades.store'), ['codigo' => 'NUE', 'nombre' => 'Facultad nueva', 'password' => 'secreto'])
            ->assertRedirect(route('administracion.facultades'))
            ->assertSessionHas('error', 'La página estuvo abierta mucho tiempo. Vuelve a intentarlo.')
            ->assertSessionHasInput('codigo', 'NUE')
            ->assertSessionMissing('_old_input.password');

        $this->assertDatabaseMissing('facultades', ['codigo' => 'NUE']);
    }

    public function test_las_consultas_del_chatbot_siguen_respondiendo_en_json(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/chatbot/mensaje', ['version' => 3, 'texto' => 'requisitos'])
            ->assertStatus(419)
            ->assertJsonPath('error.codigo', 'sesion_vencida');
    }
}
