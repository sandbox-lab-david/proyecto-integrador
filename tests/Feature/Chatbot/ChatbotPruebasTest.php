<?php

namespace Tests\Feature\Chatbot;

use App\Services\Chatbot\Prolog\PrologClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotPruebasTest extends TestCase
{
    use RefreshDatabase;

    public function test_rutas_locales_no_existen_en_produccion(): void
    {
        config(['app.env' => 'production']);
        $this->get('/chatbot/pruebas')->assertNotFound();
        $this->postJson('/chatbot/pruebas/mensaje', ['version' => 3, 'texto' => 'hola'])->assertNotFound();
    }

    public function test_solo_endpoint_local_permite_comprobaciones_anonimas(): void
    {
        config(['app.env' => 'local']);
        $this->get('/chatbot/pruebas')->assertOk()->assertSee('Modo de pruebas local')->assertSee('data-simulado="0"', false);
        $m = $this->createMock(PrologClient::class);
        $m->method('evaluar')->willReturn(['version' => 3, 'kb_version' => 'catalogo-informativo-001', 'resultado' => null, 'comprobaciones' => []]);
        $this->app->instance(PrologClient::class, $m);
        $p = ['version' => 3, 'pregunta_id' => 'examen_recuperacion__referencias', 'tramite_id' => 'examen_recuperacion', 'intencion' => 'comprobar_referencias', 'origen_datos' => 'manual', 'datos_usuario' => ['gpa_general' => 72]];
        $this->postJson('/chatbot/pruebas/mensaje', $p)->assertOk()->assertJsonPath('meta.verificacion', 'declarada');
        $this->assertDatabaseCount('mensajes_chat', 2);
        $this->postJson('/chatbot/mensaje', $p)->assertUnauthorized();
        $this->postJson('/api/chatbot/mensaje', $p)->assertUnauthorized();
    }
}
