<?php

namespace Tests\Feature\Chatbot;

use App\Models\Conversacion;
use App\Services\Chatbot\Prolog\PrologClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotPruebasTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_local_carga_el_widget_real_con_endpoint_de_pruebas(): void
    {
        config()->set('app.env', 'local');

        $this->get('/chatbot/pruebas?tramite=examen_recuperacion')
            ->assertOk()
            ->assertSee('Modo de pruebas local')
            ->assertSee('data-simulado="0"', false)
            ->assertSee('data-url-mensaje="http://localhost/chatbot/pruebas/mensaje"', false)
            ->assertSee('data-tramite="examen_recuperacion"', false);
    }

    public function test_las_rutas_de_pruebas_devuelven_404_fuera_de_local(): void
    {
        config()->set('app.env', 'production');

        $this->get('/chatbot/pruebas')->assertNotFound();
        $this->postJson('/chatbot/pruebas/mensaje', [
            'version' => 2,
            'texto' => '¿Qué es el examen de recuperación?',
            'tramite_id' => 'examen_recuperacion',
        ])->assertNotFound();
    }

    public function test_visitante_puede_evaluar_con_datos_declarados_y_se_guarda_como_anonimo(): void
    {
        config()->set('app.env', 'local');

        $mockClient = $this->createMock(PrologClient::class);
        $mockClient->expects($this->once())
            ->method('evaluar')
            ->with($this->callback(fn (array $payload) =>
                $payload['origen_datos'] === 'manual'
                && $payload['perfil']['promedio'] === 85.0
            ))
            ->willReturn([
                'version' => 2,
                'tramite_id' => 'examen_recuperacion',
                'intencion' => 'evaluar_elegibilidad',
                'resultado' => [
                    'estado' => 'cumple',
                    'motivos' => [['codigo' => 'promedio_ok', 'texto' => 'Promedio suficiente']],
                ],
            ]);
        $this->app->instance(PrologClient::class, $mockClient);

        $this->postJson('/chatbot/pruebas/mensaje', [
            'version' => 2,
            'pregunta_id' => 'examen_recuperacion__elegibilidad',
            'tramite_id' => 'examen_recuperacion',
            'intencion' => 'evaluar_elegibilidad',
            'origen_datos' => 'manual',
            'datos_usuario' => ['promedio' => 85],
        ])->assertOk()
            ->assertJsonPath('tipo', 'elegibilidad')
            ->assertJsonPath('meta.origen_datos', 'manual')
            ->assertJsonPath('meta.verificacion', 'declarada');

        $conversacion = Conversacion::sole();
        $this->assertNull($conversacion->user_id);
        $this->assertSame(2, $conversacion->mensajes()->count());
    }

    public function test_texto_libre_de_elegibilidad_muestra_formulario_sin_pedir_sesion(): void
    {
        config()->set('app.env', 'local');

        $this->postJson('/chatbot/pruebas/mensaje', [
            'version' => 2,
            'texto' => '¿Soy elegible para el examen de recuperación?',
            'tramite_id' => 'examen_recuperacion',
        ])->assertOk()
            ->assertJsonPath('tipo', 'elegibilidad')
            ->assertJsonPath('estado', 'requiere_datos');
    }

    public function test_la_ruta_habitual_y_la_api_siguen_exigiendo_sesion_en_local(): void
    {
        config()->set('app.env', 'local');
        $payload = [
            'version' => 2,
            'pregunta_id' => 'examen_recuperacion__elegibilidad',
            'tramite_id' => 'examen_recuperacion',
            'intencion' => 'evaluar_elegibilidad',
            'origen_datos' => 'manual',
            'datos_usuario' => ['promedio' => 85],
        ];

        $this->postJson('/chatbot/mensaje', $payload)->assertUnauthorized();
        $this->postJson('/api/chatbot/mensaje', $payload)->assertUnauthorized();
        $this->postJson('/chatbot/mensaje', [
            'version' => 2,
            'texto' => '¿Soy elegible para el examen de recuperación?',
            'tramite_id' => 'examen_recuperacion',
        ])->assertOk()->assertJsonPath('titulo', 'Inicio de sesión requerido');
    }
}
