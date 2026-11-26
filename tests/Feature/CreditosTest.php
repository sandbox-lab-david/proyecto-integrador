<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditosTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_de_creditos_muestra_los_cuatro_grupos_sin_iniciar_sesion(): void
    {
        $this->get('/creditos')
            ->assertOk()
            ->assertSee('Grupo 1 · Administración académica')
            ->assertSee('Grupo 2 · Portal del estudiante')
            ->assertSee('Grupo 3 · Revisión y Consejo')
            ->assertSee('Grupo 4 · Catálogo de trámites y chatbot')
            ->assertDontSee('## Documentación');
    }

    public function test_el_pie_con_los_creditos_aparece_en_el_login(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Proyecto Integrador 2026')
            ->assertSee(route('creditos'), false);
    }

    public function test_el_pie_con_los_creditos_aparece_en_el_marco_comun(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@uees.edu.ec')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('administracion.inicio'))
            ->assertOk()
            ->assertSee('Proyecto Integrador 2026')
            ->assertSee(route('creditos'), false);
    }
}
