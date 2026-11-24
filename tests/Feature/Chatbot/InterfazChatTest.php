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
            $this->assertStringContainsString('data-plantilla="'.$plantilla.'"', $html);
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

    public function test_componente_presenta_referencias_sin_dictamenes(): void
    {
        $this->blade('<x-resultado-elegibilidad :evaluacion="$e" />', ['e' => ['comprobaciones' => [['texto' => 'Referencia GPA general: 75']]]])
            ->assertSee('Referencia GPA general: 75')->assertDontSee('Cumples los requisitos')->assertDontSee('no cumples');
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

    public function test_documento_sin_obligacion_confirmada_no_se_muestra_como_opcional(): void
    {
        foreach ([['obligatorio' => false], ['obligatorio' => null], []] as $obligacion) {
            $this->blade('<x-chat.documento :documento="$d" />', [
                'd' => ['nombre' => 'Respaldo del motivo'] + $obligacion,
            ])
                ->assertSee('Consulta con tu facultad')
                ->assertDontSee('Opcional')
                ->assertDontSee('Obligatorio');
        }
    }

    public function test_documento_con_obligacion_explicita_usa_su_etiqueta(): void
    {
        $this->blade('<x-chat.documento :documento="$d" />', ['d' => ['nombre' => 'Copia', 'obligacion' => 'opcional']])
            ->assertSee('Opcional');
        $this->blade('<x-chat.documento :documento="$d" />', ['d' => ['nombre' => 'Copia', 'obligacion' => 'condicional']])
            ->assertSee('Según tu caso');
        $this->blade('<x-chat.documento :documento="$d" />', ['d' => ['nombre' => 'Copia', 'obligacion' => 'obligatorio']])
            ->assertSee('Obligatorio')
            ->assertSee('cb-etq-vino', false);
    }

    public function test_costo_y_plazo_sin_fechas_orientan_a_la_facultad(): void
    {
        $this->blade('<x-chat.datos-clave tipo="costo" />')->assertSee('Consulta con tu facultad')->assertDontSee('Por confirmar');
        $this->blade('<x-chat.datos-clave tipo="plazo" />')->assertSee('Consulta con tu facultad')->assertDontSee('Por confirmar');
    }
}
