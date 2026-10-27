<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\DirectorCarrera;
use App\Models\FacultadTrabajador;
use App\Models\Importacion;
use App\Models\Malla;
use App\Models\MallaMateria;
use App\Models\Periodo;
use App\Models\Rol;
use App\Models\Solicitud;
use App\Models\TipoTramite;
use App\Models\Trabajador;
use App\Models\User;
use App\Services\Academico\AutorizacionService;
use App\Services\Academico\CatalogoAcademicoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PendientesG1Test extends TestCase
{
    use RefreshDatabase;

    public function test_las_factories_del_g1_crean_datos_validos(): void
    {
        $this->seed();

        $periodo = Periodo::factory()->create();
        $this->assertFalse((bool) $periodo->activo);
        $this->assertGreaterThan($periodo->fecha_inicio, $periodo->fecha_fin);

        $docente = Trabajador::factory()->conRol('docente')->create();
        $this->assertTrue(Trabajador::conRol('docente')->whereKey($docente->id)->exists());
        $this->assertStringEndsWith('@uees.edu.ec', $docente->correo);

        $conAcceso = Trabajador::factory()->conRol('asistente')->conAcceso()->create();
        $this->assertSame($conAcceso->correo, $conAcceso->user->email);
        $this->assertTrue(app(AutorizacionService::class)->tieneRol($conAcceso->user, 'asistente'));

        $malla = Malla::factory()->vigente()->create();
        MallaMateria::factory()->count(3)->create(['malla_id' => $malla->id]);
        $this->assertCount(3, app(CatalogoAcademicoService::class)->materiasDeMalla($malla->id));

        $asignacion = FacultadTrabajador::factory()->create();
        $this->assertTrue($asignacion->trabajador->facultades->contains($asignacion->facultad_id));

        $direccion = DirectorCarrera::factory()->create();
        $this->assertSame($direccion->trabajador_id, app(AutorizacionService::class)->directorDeCarrera($direccion->carrera_id)->id);
        $this->assertNull(app(AutorizacionService::class)->directorDeCarrera(DirectorCarrera::factory()->terminado()->create()->carrera_id));

        $this->assertNotNull(Rol::factory()->create()->codigo);
        $this->assertSame('completada', Importacion::factory()->create()->estado);
        $this->assertSame('pendiente', Importacion::factory()->pendiente()->create()->estado);

        $curso = Curso::factory()->create();
        $this->assertTrue(Trabajador::conRol('docente')->whereKey($curso->profesor_id)->exists());
    }

    public function test_la_factory_de_solicitudes_del_g2_ya_encuentra_las_del_g1(): void
    {
        $this->seed();
        $tipo = TipoTramite::firstOrCreate(['codigo' => 'retiro_materia'], ['nombre' => 'Retiro de materia', 'slug' => 'retiro-materia', 'contenido' => []]);

        $solicitud = Solicitud::factory()->create([
            'tipo_tramite_id' => $tipo->id,
            'estado_solicitud_id' => DB::table('estados_solicitud')->value('id'),
        ]);

        $this->assertNotNull($solicitud->periodo_id);
        $this->assertNotNull($solicitud->carreraEstudiante->carreraModalidad->carrera);
    }

    public function test_el_personal_necesita_correo_institucional(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'admin@uees.edu.ec')->first())
            ->post(route('administracion.personal.store'), [
                'codigo' => 'DOC-900', 'nombres' => 'Externo', 'apellidos' => 'Prueba', 'correo' => 'externo@gmail.com', 'roles' => ['docente'],
            ])->assertSessionHasErrors(['correo' => 'Usa el correo institucional @uees.edu.ec.']);
    }

    public function test_inicio_muestra_todas_las_secciones_del_menu(): void
    {
        $this->seed();
        $respuesta = $this->actingAs(User::where('email', 'admin@uees.edu.ec')->first())->get(route('administracion.inicio'))->assertOk();

        foreach (collect(config('menu_administracion'))->except('Módulo')->flatten(1) as $enlace) {
            $respuesta->assertSee($enlace['texto'])->assertSee(route($enlace['ruta']), false);
        }

        $respuesta->assertViewHas('conteos', fn ($c) => $c['Estudiantes'] === 1 && $c['Mallas'] === 2 && $c['Docentes'] === 3);
    }

    public function test_los_componentes_c39_usan_el_estilo_comun(): void
    {
        $this->blade('<x-alerta tipo="error" titulo="Ojo">Algo falló</x-alerta>')
            ->assertSee('ui-alerta ui-alerta-error', false)->assertSee('role="alert"', false)->assertSee('Ojo');

        $this->blade('<x-modal-confirmar id="borrar" accion="/x" metodo="DELETE" boton="Borrar">¿Seguro?</x-modal-confirmar>')
            ->assertSee('class="ui-modal"', false)->assertSee('ui-btn ui-btn-peligro', false)->assertSee('name="_method" value="DELETE"', false);

        $this->blade('<x-tabla :columnas="[\'A\']"><tr><td>1</td></tr></x-tabla>')->assertSee('ui-tabla-caja', false)->assertSee('ui-tabla', false);
        $this->blade('<x-tabla :columnas="[\'A\']" vacio="Nada aquí"></x-tabla>')->assertSee('ui-vacio', false)->assertSee('Nada aquí');
        $this->blade('<x-tabla :columnas="[\'A\']" vacio="Sin filas">
            @foreach ([] as $fila)
                <tr><td>{{ $fila }}</td></tr>
            @endforeach
        </x-tabla>')->assertSee('Sin filas')->assertDontSee('<table', false);
    }

    public function test_se_quitaron_las_rutas_viejas(): void
    {
        $this->actingAs(User::factory()->create(['tipo' => 'trabajador']));

        foreach (['/tramites', '/tramites/configurar', '/tramites/plantillas', '/app', '/register'] as $url) {
            $this->get($url)->assertNotFound();
        }

        $this->assertFalse(view()->exists('tramites.index'));
        $this->assertFalse(view()->exists('auth.register'));
        $this->assertTrue(Schema::hasTable('periodos'));
    }
}
