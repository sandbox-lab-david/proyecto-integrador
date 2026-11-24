<?php

namespace Tests\Feature\Chatbot;

use App\Models\User;
use App\Services\Chatbot\Prolog\PrologClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ChatbotMensajeValidationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(string $sufijo = 'referencias', array $perfil = []): array
    {
        return ['version' => 3, 'tramite_id' => 'examen_recuperacion', 'pregunta_id' => 'examen_recuperacion__'.$sufijo, 'intencion' => $sufijo === 'referencias' ? 'comprobar_referencias' : 'consultar_'.$sufijo, 'origen_datos' => $sufijo === 'referencias' ? 'manual' : 'ninguno', 'datos_usuario' => $perfil];
    }

    private function motor(): void
    {
        $mock = $this->createMock(PrologClient::class);
        $respuesta = fn ($p) => ['version' => 3, 'kb_version' => 'catalogo-informativo-001', 'tramite_id' => $p['tramite_id'], 'intencion' => $p['intencion'], 'items' => ['Información del trámite'], 'documentos' => [], 'costo' => null, 'plazo' => null, 'resultado' => null, 'comprobaciones' => [], 'aviso' => null];
        $mock->method('consultar')->willReturnCallback($respuesta);
        $mock->method('evaluar')->willReturnCallback($respuesta);
        $this->app->instance(PrologClient::class, $mock);
    }

    public function test_version_anterior_se_rechaza(): void
    {
        $this->postJson('/chatbot/mensaje', ['version' => 2, 'texto' => 'hola'])->assertUnprocessable()->assertJsonPath('version', 3);
    }

    public function test_referencias_requieren_sesion_y_cuenta_activa(): void
    {
        $this->postJson('/chatbot/mensaje', $this->payload())->assertUnauthorized();
        $this->actingAs(User::factory()->create(['activo' => false]))->postJson('/chatbot/mensaje', $this->payload())->assertForbidden();
    }

    public function test_todos_los_datos_personalizados_son_opcionales(): void
    {
        $this->motor();
        $this->actingAs(User::factory()->create());
        $this->postJson('/chatbot/mensaje', $this->payload())->assertOk()->assertJsonPath('estado', 'requiere_datos')->assertJsonCount(2, 'campos_pendientes');
        $this->postJson('/chatbot/mensaje', $this->payload() + ['mostrar_referencias' => true])->assertOk()->assertJsonPath('estado', 'respondido')->assertJsonPath('resultado', null);
    }

    public function test_gpa_invalido_y_fechas_futuras_o_imposibles_se_rechazan(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([['gpa_general' => -1], ['gpa_general' => 101], ['gpa_general' => 'abc'], ['fecha_ultima_recuperacion' => '2026-02-30'], ['fecha_ultima_recuperacion' => today()->addDay()->toDateString()]] as $perfil) {
            $this->postJson('/chatbot/mensaje', $this->payload(perfil: $perfil))->assertUnprocessable();
        }
    }

    public function test_alias_notas_y_valores_contradictorios_no_se_aceptan(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['promedio', 'period_gpa', 'subject_grade', 'nota', 'gpa'] as $alias) {
            $this->postJson('/chatbot/mensaje', $this->payload(perfil: ['gpa_general' => 72, $alias => 90]))->assertUnprocessable();
        }
    }

    public function test_terna_pregunta_tramite_intencion_debe_coincidir(): void
    {
        $this->postJson('/chatbot/mensaje', array_replace($this->payload('requisitos'), ['intencion' => 'consultar_costo']))->assertUnprocessable();
        $this->postJson('/chatbot/mensaje', array_replace($this->payload('requisitos'), ['tramite_id' => 'homologacion']))->assertUnprocessable();
        $this->postJson('/chatbot/mensaje', array_replace($this->payload('requisitos'), ['pregunta_id' => 'inexistente']))->assertUnprocessable();
    }

    public function test_consultas_publicas_no_reciben_datos_personales(): void
    {
        $this->motor();
        $this->postJson('/chatbot/mensaje', $this->payload('requisitos'))->assertOk();
        $this->postJson('/chatbot/mensaje', $this->payload('requisitos', ['gpa_general' => 72]))->assertUnprocessable();
    }

    public function test_sesion_no_confiere_verificacion_institucional(): void
    {
        $this->motor();
        $this->actingAs(User::factory()->create());
        $this->postJson('/chatbot/mensaje', array_replace($this->payload(perfil: ['gpa_general' => 72]), ['origen_datos' => 'sesion']))->assertUnprocessable();
        $this->postJson('/chatbot/mensaje', $this->payload(perfil: ['gpa_general' => 72]))->assertOk()->assertJsonPath('meta.verificacion', 'declarada')->assertJsonPath('resultado', null);
    }

    public function test_lenguaje_cotidiano_y_consultas_ambiguas(): void
    {
        $this->motor();
        foreach (['quiero homologar' => 'homologacion', 'retirar una materia' => 'retiro_materia'] as $texto => $id) {
            $this->postJson('/chatbot/mensaje', ['version' => 3, 'texto' => $texto])->assertOk()->assertJsonPath('tramite_id', $id);
        }
        $this->postJson('/chatbot/mensaje', ['version' => 3, 'texto' => 'homologacion o recuperacion', 'tramite_id' => 'retiro_materia'])->assertOk()->assertJsonPath('tipo', 'no_entendido');
    }

    public function test_consultar_gpa_en_texto_libre_exige_sesion_pero_consultar_requisitos_no(): void
    {
        $this->motor();
        $this->postJson('/chatbot/mensaje', ['version' => 3, 'texto' => 'calcular GPA para recuperación'])->assertOk()->assertJsonPath('tipo', 'sesion_requerida');
        $this->postJson('/chatbot/mensaje', ['version' => 3, 'texto' => 'puedo pedir recuperación'])->assertOk()->assertJsonPath('tipo', 'requisitos');
    }

    public function test_seguimiento_redirige_sin_inventar_que_no_existen_solicitudes(): void
    {
        $this->postJson('/chatbot/mensaje', ['version' => 3, 'texto' => 'mis solicitudes'])->assertOk()->assertJsonPath('acciones.0.url', '/estudiante/solicitudes')->assertJsonMissingPath('solicitudes');
    }

    public function test_reporte_sigue_fuera_del_alcance(): void
    {
        $this->get('/chatbot/reporte')->assertNotFound();
    }

    public function test_api_tiene_limite_de_peticiones(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '10.10.1.90']);
        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/chatbot/mensaje', ['version' => 3, 'texto' => 'consulta desconocida'])->assertOk();
        }
        $this->postJson('/api/chatbot/mensaje', ['version' => 3, 'texto' => 'consulta desconocida'])->assertStatus(429);
    }

    public function test_la_fecha_enviada_al_motor_es_la_de_guayaquil(): void
    {
        $this->travelTo(Carbon::parse('2026-10-21 00:30:00', 'UTC'));
        $mock = $this->createMock(PrologClient::class);
        $mock->expects($this->once())->method('evaluar')->with($this->callback(fn ($p) => $p['fecha'] === '2026-10-20'))->willReturn(['version' => 3, 'kb_version' => 'catalogo-informativo-001', 'resultado' => null, 'comprobaciones' => []]);
        $this->app->instance(PrologClient::class, $mock);
        $this->actingAs(User::factory()->create())->postJson('/chatbot/mensaje',$this->payload(perfil: ['gpa_general' => 72]))->assertOk();
    }
}
