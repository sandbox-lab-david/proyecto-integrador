<?php

namespace Tests\Feature\Chatbot;

use App\Models\User;
use App\Services\Chatbot\ChatbotCatalogoService;
use App\Services\Chatbot\ChatbotOrchestratorService;
use Tests\TestCase;

class ChatbotControllerTest extends TestCase
{
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

    public function test_historial_vacio_retorna_array_de_mensajes(): void
    {
        $response = $this->getJson('/chatbot/historial');

        $response->assertOk()
            ->assertJsonStructure(['mensajes']);
    }

    public function test_limpiar_historial_retorna_204(): void
    {
        $response = $this->deleteJson('/chatbot/historial');

        $response->assertNoContent();
    }

    public function test_reporte_con_admin_muestra_vista_reporte(): void
    {
        $admin = new User([
            'name' => 'Admin de Pruebas',
            'email' => 'admin@uees.edu.ec',
        ]);
        $admin->id = 1;
        $admin->email_verified_at = now();
        $admin->role = 'admin';

        $response = $this->actingAs($admin)->get('/chatbot/reporte');

        $response->assertOk()
            ->assertSee('Preguntas no resueltas');
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
