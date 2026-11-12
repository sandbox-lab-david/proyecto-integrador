<?php

namespace Tests\Feature;

use App\Models\Trabajador;
use App\Models\User;
use App\Services\Academico\AutorizacionService;
use Database\Seeders\RolSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CrearAdministradorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolSeeder::class);
        Notification::fake();
    }

    public function test_crea_el_administrador_y_le_envia_el_enlace(): void
    {
        $this->artisan('admin:crear', ['correo' => 'Maria.Perez@uees.edu.ec', 'nombres' => 'María', 'apellidos' => 'Pérez'])
            ->expectsOutputToContain('Administrador creado')
            ->assertSuccessful();

        $usuario = User::where('email', 'maria.perez@uees.edu.ec')->first();
        $this->assertSame('trabajador', $usuario->tipo);
        $this->assertTrue($usuario->activo);
        $this->assertFalse(Hash::check('password123', $usuario->password));

        $trabajador = Trabajador::where('codigo', 'ADM-001')->first();
        $this->assertSame($usuario->id, $trabajador->user_id);
        $this->assertTrue(app(AutorizacionService::class)->tieneRol($usuario, 'admin'));
        Notification::assertSentTo($usuario, ResetPassword::class);

        $this->actingAs($usuario)->get('/dashboard')->assertRedirect(route('administracion.inicio'));
    }

    public function test_acepta_otro_codigo_de_empleado(): void
    {
        $this->artisan('admin:crear', ['correo' => 'jefe@uees.edu.ec', 'nombres' => 'Luis', 'apellidos' => 'Mora', '--codigo' => 'ADM-777'])
            ->assertSuccessful();

        $this->assertTrue(Trabajador::where('codigo', 'ADM-777')->exists());
    }

    public function test_rechaza_correos_que_no_son_institucionales_o_repetidos(): void
    {
        $this->artisan('admin:crear', ['correo' => 'alguien@gmail.com', 'nombres' => 'A', 'apellidos' => 'B'])
            ->expectsOutputToContain('@uees.edu.ec')
            ->assertFailed();

        $this->artisan('admin:crear', ['correo' => 'admin.uno@uees.edu.ec', 'nombres' => 'A', 'apellidos' => 'B'])->assertSuccessful();

        $this->artisan('admin:crear', ['correo' => 'admin.uno@uees.edu.ec', 'nombres' => 'A', 'apellidos' => 'B', '--codigo' => 'ADM-002'])
            ->expectsOutputToContain('Ya existe una cuenta')
            ->assertFailed();

        $this->artisan('admin:crear', ['correo' => 'admin.dos@uees.edu.ec', 'nombres' => 'A', 'apellidos' => 'B'])
            ->expectsOutputToContain('--codigo')
            ->assertFailed();

        $this->assertSame(1, User::count());
    }
}
