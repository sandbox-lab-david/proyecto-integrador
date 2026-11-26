<?php

namespace Tests\Feature\Chatbot;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccesoChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_sesion_la_pagina_del_chat_lleva_al_login(): void
    {
        $this->get('/chatbot')->assertRedirect(route('login'));
    }

    public function test_sin_sesion_las_consultas_responden_que_hay_que_iniciar_sesion(): void
    {
        $this->postJson('/chatbot/mensaje', ['version' => 3, 'texto' => 'requisitos de homologacion'])
            ->assertUnauthorized()
            ->assertJsonPath('error.codigo', 'sesion_requerida')
            ->assertJsonPath('error.mensaje', 'Inicia sesión para usar el asistente.');
    }

    public function test_la_demo_y_las_pruebas_del_chat_no_existen_en_produccion(): void
    {
        config(['app.env' => 'production']);

        $this->get('/chatbot/demo')->assertNotFound();
        $this->get('/chatbot/pruebas')->assertNotFound();
    }

    public function test_con_sesion_la_pagina_del_chat_abre(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/chatbot')
            ->assertOk()
            ->assertSee('data-chatbot', false);
    }
}
