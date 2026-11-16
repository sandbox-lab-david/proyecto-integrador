<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Facultad;
use App\Models\Malla;
use App\Models\Periodo;
use App\Models\Rol;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatosDeProduccionTest extends TestCase
{
    use RefreshDatabase;

    public function test_en_produccion_el_seeder_no_crea_datos_de_prueba(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

        $this->assertSame(0, User::count());
        $this->assertSame(0, Trabajador::count());
        $this->assertSame(0, Estudiante::count());
        $this->assertSame(0, Malla::count());
        $this->assertSame(0, Periodo::count());
        $this->assertSame(0, Curso::count());

        $this->assertSame(9, Facultad::count());
        $this->assertSame(38, Carrera::count());
        $this->assertTrue(Rol::where('codigo', 'admin')->exists());
        $this->assertSame(20, DB::table('tipos_tramite')->count());
        $this->assertTrue(DB::table('estados_solicitud')->exists());
    }

    public function test_en_produccion_se_crea_el_primer_administrador_con_el_comando(): void
    {
        $this->app['env'] = 'production';
        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

        $this->artisan('admin:crear', ['correo' => 'admin.real@uees.edu.ec', 'nombres' => 'Ana', 'apellidos' => 'Real'])->assertSuccessful();

        $this->assertSame(['admin.real@uees.edu.ec'], User::pluck('email')->all());
    }
}
