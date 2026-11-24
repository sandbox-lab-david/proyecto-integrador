<?php

namespace Tests\Feature\Chatbot;

use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisibilidadChatbotTest extends TestCase
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

    public function test_el_estudiante_ve_a_ueesito(): void
    {
        $this->actingAs(User::factory()->create(['tipo' => 'estudiante', 'activo' => true]));

        $this->assertStringContainsString('data-cb-abrir', (string) $this->blade('<x-chat-widget modo="flotante" />'));
    }

    public function test_los_roles_de_revision_no_ven_a_ueesito(): void
    {
        foreach (['admin', 'asistente', 'director', 'consulta'] as $rol) {
            $this->actingAs($this->usuarioConRol($rol));

            $this->assertStringNotContainsString('data-cb-abrir', (string) $this->blade('<x-chat-widget modo="flotante" />'), $rol);
        }
    }

    public function test_un_rol_agregado_en_la_configuracion_ve_a_ueesito(): void
    {
        config(['chatbot.roles_visibles' => ['estudiante', 'director']]);

        $this->actingAs($this->usuarioConRol('director'));
        $this->assertStringContainsString('data-cb-abrir', (string) $this->blade('<x-chat-widget modo="flotante" />'));

        $this->actingAs($this->usuarioConRol('asistente'));
        $this->assertStringNotContainsString('data-cb-abrir', (string) $this->blade('<x-chat-widget modo="flotante" />'));
    }

    public function test_la_pagina_completa_del_chat_no_depende_del_rol(): void
    {
        $this->actingAs($this->usuarioConRol('asistente'));

        $this->assertStringContainsString('data-chatbot', (string) $this->blade('<x-chat-widget modo="pagina" />'));
    }
}
