<?php

namespace Tests\Feature\Chatbot;

use Tests\TestCase;

class InterfazChatTest extends TestCase
{
    public function test_la_pagina_del_chat_carga_con_el_widget(): void
    {
        $this->get('/chatbot')
            ->assertOk()
            ->assertSee('data-chatbot', false)
            ->assertSee('assets/chatbot/chatbot.js', false)
            ->assertSee('Escribe tu pregunta');
    }

    public function test_el_widget_trae_todas_las_plantillas(): void
    {
        $html = $this->get('/chatbot')->getContent();

        foreach (['bot', 'usuario', 'espera', 'respuesta', 'documento', 'solicitud', 'sugerencias', 'error', 'formulario',
            'campo-texto', 'campo-numero', 'campo-seleccion', 'campo-booleano', 'campo-fecha'] as $plantilla) {
            $this->assertStringContainsString('data-plantilla="' . $plantilla . '"', $html);
        }
    }

    public function test_el_widget_recibe_el_tramite_inicial(): void
    {
        $this->get('/chatbot?tramite=examen_recuperacion')
            ->assertOk()
            ->assertSee('data-tramite="examen_recuperacion"', false);
    }

    public function test_el_widget_en_modo_panel_no_muestra_la_lista_lateral(): void
    {
        $this->blade('<x-chat-widget tramite="tercer_registro" modo="panel" />')
            ->assertSee('cb-modo-panel', false)
            ->assertSee('data-tramite="tercer_registro"', false)
            ->assertDontSee('cb-lateral', false);
    }

    public function test_el_widget_flotante_empieza_cerrado_con_su_boton(): void
    {
        $html = (string) $this->blade('<x-chat-widget modo="flotante" />');

        $this->assertStringContainsString('data-cb-abrir', $html);
        $this->assertStringContainsString('¿Dudas con un trámite?', $html);
        $this->assertStringContainsString('aria-controls="cb-chat-flotante"', $html);
        $this->assertMatchesRegularExpression('/id="cb-chat-flotante".*?role="dialog" hidden/s', $html);
        $this->assertStringContainsString('data-cb="cerrar"', $html);
        $this->assertStringNotContainsString('cb-lateral', $html);
    }

    public function test_la_pagina_de_demo_muestra_la_burbuja_y_el_boton_de_la_ficha(): void
    {
        $this->get('/chatbot/demo')
            ->assertOk()
            ->assertSee('cb-modo-flotante', false)
            ->assertSee('data-abrir-chatbot="examen_recuperacion"', false)
            ->assertSee('Consultar chatbot');
    }

    public function test_resultado_cumple_con_datos_declarados_es_orientativo(): void
    {
        $vista = $this->blade('<x-resultado-elegibilidad :evaluacion="$e" />', [
            'e' => [
                'resultado' => 'cumple',
                'motivos' => [['codigo' => 'gpa_ok', 'texto' => 'Tu GPA de 82 supera el mínimo de 75.']],
                'con_datos_verificados' => false,
            ],
        ]);

        $vista->assertSee('Cumples los requisitos')
            ->assertSee('cb-cumple', false)
            ->assertSee('Tu GPA de 82 supera el mínimo de 75.')
            ->assertSee('Resultado orientativo');
    }

    public function test_resultado_no_cumple_muestra_cada_motivo(): void
    {
        $this->blade('<x-resultado-elegibilidad :evaluacion="$e" />', [
            'e' => ['resultado' => 'no_cumple', 'motivos' => ['Tu GPA es 71 y el mínimo es 75.', 'Hubo fraude académico.']],
        ])
            ->assertSee('Por ahora no cumples los requisitos')
            ->assertSee('cb-no-cumple', false)
            ->assertSee('Tu GPA es 71 y el mínimo es 75.')
            ->assertSee('Hubo fraude académico.');
    }

    public function test_resultado_revision_manual_acepta_el_nombre_anterior(): void
    {
        $this->blade('<x-resultado-elegibilidad :evaluacion="$e" />', [
            'e' => ['resultado' => 'requiere_revision', 'motivos' => [], 'con_datos_verificados' => true],
        ])
            ->assertSee('Este caso lo revisa la facultad')
            ->assertSee('cb-revision', false);
    }

    public function test_resultado_con_datos_verificados_no_es_orientativo(): void
    {
        $html = (string) $this->blade('<x-resultado-elegibilidad :evaluacion="$e" />', [
            'e' => ['resultado' => 'cumple', 'motivos' => [], 'con_datos_verificados' => true],
        ]);

        $this->assertMatchesRegularExpression('/data-cb="resultado-orientativo"\s+hidden/', $html);
    }

    public function test_respuesta_muestra_documentos_costo_y_aviso(): void
    {
        $this->blade('<x-chat.respuesta :respuesta="$r" />', [
            'r' => [
                'titulo' => 'Documentos requeridos',
                'mensaje' => 'Estos son los documentos registrados.',
                'items' => [],
                'documentos' => [[
                    'id' => 'solicitud', 'nombre' => 'Solicitud firmada', 'descripcion' => 'Formulario',
                    'obligatorio' => true, 'formato' => 'PDF', 'url' => null,
                ]],
                'costo' => ['centavos' => 2000, 'moneda' => 'USD'],
                'plazo' => ['texto' => 'Al terminar el semestre', 'inicio' => '2027-01-15', 'fin' => null],
                'resultado' => null,
                'aviso' => 'Regla pendiente de confirmar.',
                'acciones' => [['tipo' => 'iniciar_solicitud', 'etiqueta' => 'Iniciar esta solicitud', 'url' => '/solicitudes/create?tramite=examen_recuperacion']],
            ],
        ])
            ->assertSee('Solicitud firmada')
            ->assertSee('Obligatorio')
            ->assertSee('$20,00')
            ->assertSee('15 de enero de 2027')
            ->assertSee('La define la facultad')
            ->assertSee('Regla pendiente de confirmar.')
            ->assertSee('Iniciar esta solicitud');
    }

    public function test_reporte_sin_preguntas_muestra_estado_vacio(): void
    {
        $admin = new \App\Models\User([
            'name' => 'Administrador General',
            'email' => 'admin@uees.edu.ec',
        ]);
        $admin->id = 1;
        $admin->email_verified_at = now();
        $admin->role = 'admin';

        $this->actingAs($admin)
            ->get('/chatbot/reporte')
            ->assertOk()
            ->assertSee('No hay preguntas sin resolver');
    }

    public function test_reporte_lista_las_preguntas_no_resueltas(): void
    {
        $this->view('chatbot.reporte', [
            'preguntas' => [
                ['texto' => 'puedo pedir beca?', 'tramite' => null, 'fecha' => '2026-10-08 10:30:00'],
                ['texto' => 'cuanto cuesta el supletorio', 'tramite' => 'Examen supletorio', 'fecha' => '2026-10-08 11:00:00'],
            ],
        ])
            ->assertSee('2 preguntas')
            ->assertSee('puedo pedir beca?')
            ->assertSee('Ninguno')
            ->assertSee('Examen supletorio')
            ->assertSee('08/10/2026 11:00');
    }
}
