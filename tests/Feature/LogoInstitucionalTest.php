<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_barra_superior_muestra_el_logo_de_la_universidad(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('images/logo-uees.png', false)
            ->assertSee('alt="Universidad Espíritu Santo"', false)
            ->assertDontSee('facultad-ingenieria-uees.png', false);
    }

    public function test_la_barra_del_chatbot_muestra_el_logo_de_la_universidad(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/chatbot')
            ->assertOk()
            ->assertSee('images/logo-uees.png', false)
            ->assertDontSee('facultad-ingenieria-uees.png', false);
    }

    public function test_el_logo_y_el_icono_de_la_pestana_existen(): void
    {
        $this->assertGreaterThan(0, filesize(public_path('images/logo-uees.png')));
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));
    }
}
