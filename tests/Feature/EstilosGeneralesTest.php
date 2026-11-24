<?php

namespace Tests\Feature;

use Tests\TestCase;

class EstilosGeneralesTest extends TestCase
{
    public function test_la_pagina_de_estilos_carga_con_el_css_general(): void
    {
        $this->get('/estilos')
            ->assertOk()
            ->assertSee('Estilos generales del sistema')
            ->assertSee('build/assets/app-', false);
    }

    public function test_la_pagina_de_estilos_muestra_las_piezas_comunes(): void
    {
        $html = $this->get('/estilos')->getContent();

        foreach (['ui-btn-primario', 'ui-control', 'ui-alerta-error', 'ui-badge-exito', 'ui-tarjeta', 'ui-tabla',
            'ui-paginacion', 'ui-modal', 'ui-pasos', 'ui-vacio', 'ui-cargando', 'ui-archivo', 'cb-resultado'] as $clase) {
            $this->assertStringContainsString($clase, $html);
        }
    }

    public function test_el_css_general_define_las_variables_que_usa_el_chat(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $chat = file_get_contents(public_path('assets/chatbot/chatbot.css'));

        $this->assertStringContainsString('@theme static', $css);

        preg_match_all('/var\((--(?:color|font|radius)-[a-z-]+),/', $chat, $usadas);

        $this->assertNotEmpty($usadas[1]);

        foreach (array_unique($usadas[1]) as $variable) {
            $this->assertStringContainsString($variable . ':', $css, "Falta {$variable} en app.css");
        }
    }

    public function test_la_pagina_del_chat_carga_el_css_general_antes_que_el_suyo(): void
    {
        $html = $this->get('/chatbot')->getContent();

        $this->assertLessThan(
            strpos($html, 'assets/chatbot/chatbot.css'),
            strpos($html, 'build/assets/app-')
        );
    }
}
