<?php

namespace Tests\Feature\Chatbot;

use App\Models\Conversacion;
use App\Models\User;
use App\Modules\Chatbot\Http\Controllers\ChatbotController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use ReflectionMethod;
use Tests\TestCase;

class ChatbotControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogo_retorna_version_2_y_listas(): void
    {
        $response = $this->getJson('/chatbot/catalogo');

        $response->assertOk()
            ->assertJsonPath('version', 2)
            ->assertJsonStructure([
                'version',
                'categorias',
                'tramites',
                'preguntas',
            ]);
    }

    public function test_conversaciones_y_reporte_no_se_exponen_por_http(): void
    {
        $this->getJson('/chatbot/historial')->assertNotFound();
        $this->deleteJson('/chatbot/historial')->assertNotFound();
        $this->getJson('/api/chatbot/historial')->assertNotFound();
        $this->deleteJson('/api/chatbot/historial')->assertNotFound();
        $this->get('/chatbot/reporte')->assertNotFound();
    }

    public function test_una_interaccion_anonima_se_guarda_para_metricas(): void
    {
        $this->enviarPregunta('primera pregunta anónima');

        $anonima = Conversacion::sole();
        $this->assertNull($anonima->user_id);
        $this->assertSame(2, $anonima->mensajes()->count());
    }

    public function test_api_sin_sesion_tambien_guarda_la_interaccion_anonima(): void
    {
        $this->postJson('/api/chatbot/mensaje', [
            'version' => 2,
            'texto' => 'consulta anónima por API',
        ])->assertOk();

        $conversacion = Conversacion::sole();
        $this->assertNull($conversacion->user_id);
        $this->assertNull($conversacion->session_id);
        $this->assertSame(2, $conversacion->mensajes()->count());
    }

    public function test_la_misma_sesion_no_mezcla_anonimos_ni_cuentas_distintas(): void
    {
        $anonima = $this->obtenerConversacionDeMetricas(null);
        $primeraCuenta = User::factory()->create();
        $primera = $this->obtenerConversacionDeMetricas($primeraCuenta);
        $segundaCuenta = User::factory()->create();
        $segunda = $this->obtenerConversacionDeMetricas($segundaCuenta);

        $this->assertSame(3, Conversacion::count());
        $this->assertNull($anonima->fresh()->user_id);
        $this->assertSame(str_repeat('a', 40), $anonima->session_id);
        $this->assertSame($anonima->session_id, $primera->session_id);
        $this->assertSame($anonima->session_id, $segunda->session_id);
        $this->assertSame($primeraCuenta->id, $primera->user_id);
        $this->assertSame($segundaCuenta->id, $segunda->user_id);
        $this->assertSame($primera->id, $this->obtenerConversacionDeMetricas($primeraCuenta)->id);
        $this->assertSame($anonima->id, $this->obtenerConversacionDeMetricas(null)->id);
    }

    private function enviarPregunta(string $texto): void
    {
        $this->postJson('/chatbot/mensaje', ['version' => 2, 'texto' => $texto])->assertOk();
    }

    private function obtenerConversacionDeMetricas(?User $user): Conversacion
    {
        $request = Request::create('/chatbot/mensaje', 'POST');
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(60), str_repeat('a', 40)));
        $request->setUserResolver(fn () => $user);

        return (new ReflectionMethod(ChatbotController::class, 'obtenerConversacionActiva'))
            ->invoke(app(ChatbotController::class), $request);
    }

    public function test_mensaje_consulta_estado_solicitud_retorna_tipo_estado_solicitud(): void
    {
        $response = $this->postJson('/chatbot/mensaje', [
            'version' => 2,
            'texto'   => '¿Cómo va el estado de mi solicitud?',
        ]);

        $response->assertOk()
            ->assertJsonPath('version', 2)
            ->assertJsonPath('tipo', 'estado_solicitud')
            ->assertJsonStructure([
                'version',
                'estado',
                'tipo',
                'solicitudes',
                'acciones',
            ]);
    }
}
