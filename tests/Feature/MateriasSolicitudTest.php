<?php

namespace Tests\Feature;

use App\Services\Solicitudes\MateriasSolicitud;
use App\Services\Solicitudes\TramitesSimulados;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MateriasSolicitudTest extends TestCase
{
    use RequisitosPrologPrueba;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Esquema mínimo aislado en SQLite :memory:, sin tocar migraciones de G1.
        foreach (['materias', 'periodos', 'trabajadores'] as $tabla) {
            Schema::create($tabla, function (Blueprint $t) use ($tabla) {
                $t->id();
                if ($tabla === 'trabajadores') {
                    $t->string('nombres'); $t->string('apellidos');
                } else {
                    $t->string('codigo'); $t->string('nombre');
                }
            });
        }
        Schema::create('cursos', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('materia_id');
            $t->unsignedBigInteger('periodo_id'); $t->unsignedBigInteger('profesor_id')->nullable();
            $t->string('paralelo');
        });
    }

    private function curso(): void
    {
        DB::table('materias')->insert(['id' => 12, 'codigo' => 'BD401', 'nombre' => 'Bases de datos']);
        DB::table('periodos')->insert(['id' => 4, 'codigo' => '2026-2', 'nombre' => 'Ordinario II 2026']);
        DB::table('trabajadores')->insert(['id' => 7, 'nombres' => 'Ana', 'apellidos' => 'Pérez']);
        DB::table('cursos')->insert(['id' => 25, 'materia_id' => 12, 'periodo_id' => 4, 'profesor_id' => 7, 'paralelo' => 'B']);
    }

    public function test_catalogo_usa_curso_periodo_y_docente_de_la_base(): void
    {
        $this->curso();
        $this->assertSame([[
            'id' => '25', 'curso_id' => 25, 'codigo' => 'BD401', 'nombre' => 'Bases de datos',
            'periodo' => 'Ordinario II 2026', 'paralelo' => 'B', 'docente' => 'Ana Pérez',
        ]], app(MateriasSolicitud::class)->todas());
        $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))
            ->assertOk()->assertSee('portal-estudiante')->assertSee('Bases de datos')
            ->assertDontSee('calculo-demo')->assertSee('data-simulado="0"', false);
    }

    public function test_catalogo_vacio_no_inventa_materias(): void
    {
        $this->assertSame([], app(MateriasSolicitud::class)->todas());
        $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))
            ->assertOk()->assertSee('No hay cursos disponibles');
        $this->putJson(route('borradores-temporales.update', 'recuperacion'), [
            'paso' => 1, 'origen_materias' => 'catalogo', 'materias' => [['id' => 'calculo-demo']],
        ])->assertUnprocessable()->assertJsonValidationErrors('materias.0.id');
    }

    public function test_todos_los_tramites_que_piden_materias_ofrecen_los_cursos_de_la_base(): void
    {
        $this->curso();
        foreach (array_keys(\App\Services\Solicitudes\DocumentoSolicitud::PLANTILLAS) as $codigo) {
            $definicion = app(TramitesSimulados::class)->buscar($codigo);
            $respuesta = $this->get(route('solicitudes.create', ['tramite' => $codigo]))->assertOk();
            $this->assertSame('25', $respuesta->viewData('materias')[0]['id'], $codigo);
            if ($definicion['max_materias'] > 0) {
                $respuesta->assertSee('id="materia"', false)->assertSee('Bases de datos');
                $this->putJson(route('borradores-temporales.update', $codigo), [
                    'paso' => 2, 'origen_materias' => 'catalogo',
                    'materias' => [['id' => '25', 'datos' => []]],
                ])->assertOk();
                $borrador = app(\App\Services\Solicitudes\BorradoresTemporales::class)->buscar($codigo);
                $this->assertSame('Bases de datos', $borrador['materias'][0]['nombre'], $codigo);
            } else {
                $respuesta->assertDontSee('id="materia"', false);
            }
        }
    }

    public function test_al_retomar_actualiza_el_curso_sin_borrar_las_notas_declaradas(): void
    {
        $this->curso();
        app(\App\Services\Solicitudes\BorradoresTemporales::class)->guardar('gracia', [
            'paso' => 2, 'origen_materias' => 'catalogo',
            'materias' => [['id' => '25', 'nombre' => 'Nombre anterior', 'datos' => ['nota' => '72.50']]],
        ]);
        DB::table('materias')->where('id', 12)->update(['nombre' => 'Bases de datos actualizada']);
        $respuesta = $this->get(route('solicitudes.create', ['tramite' => 'gracia']))->assertOk();
        $materia = $respuesta->viewData('borrador')['materias'][0];
        $this->assertSame('Bases de datos actualizada', $materia['nombre']);
        $this->assertSame('72.50', $materia['datos']['nota']);
    }

    public function test_base_sin_cursos_muestra_aviso_sin_error_de_servidor(): void
    {
        Schema::drop('cursos');
        $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))
            ->assertOk()->assertSee('No hay cursos disponibles');
    }

    public function test_borrador_conserva_datos_manuales_y_valida_curso_real(): void
    {
        $this->curso();
        $this->actingAs((new \App\Models\User)->forceFill(['id' => 99, 'tipo' => 'estudiante',
            'activo' => true, 'name' => 'Nombre de la cuenta', 'email' => 'cuenta@uees.edu.ec']));
        $this->putJson(route('borradores-temporales.update', 'recuperacion'), [
            'paso' => 1, 'nombre' => 'Ana Pérez', 'codigo' => '123',
            'correo' => 'ana@uees.edu.ec', 'carrera' => 'Computación',
            'cedula' => '1234567890', 'facultad' => 'Ingeniería',
            'modalidad' => 'Presencial', 'periodo' => '2026-2',
            'origen_materias' => 'catalogo',
            'materias' => [['id' => '25', 'nombre' => 'Nombre manipulado', 'datos' => []]],
        ])->assertOk();
        $respuesta = $this->get(route('solicitudes.create', ['tramite' => 'recuperacion']))->assertOk();
        $this->assertSame('Ana Pérez', $respuesta->viewData('borrador')['nombre']);
        $this->assertSame('ana@uees.edu.ec', $respuesta->viewData('borrador')['correo']);
        $this->assertSame('catalogo', $respuesta->viewData('borrador')['origen_materias']);
        $this->assertSame([], $respuesta->viewData('datosCuenta'));
        $this->assertSame('25', $respuesta->viewData('borrador')['materias'][0]['id']);
        $this->assertSame('Bases de datos', $respuesta->viewData('borrador')['materias'][0]['nombre']);
    }

    public function test_las_veinte_fichas_comparten_layout_y_chatbot(): void
    {
        $rutas = collect(Route::getRoutes()->getRoutes())->filter(fn ($ruta) =>
            str_starts_with($ruta->getName() ?? '', 'estudiante.tramites.'));
        $this->assertCount(20, $rutas);
        foreach ($rutas as $ruta) {
            $this->get(route($ruta->getName()))->assertOk()
                ->assertSee('portal-estudiante')->assertSee('data-cb-flotante', false);
        }
    }

    public function test_chatbot_conserva_el_tramite_en_las_rutas_directas_del_formulario(): void
    {
        foreach (['recuperacion', 'gracia', 'supletorio'] as $codigo) {
            $id = app(\App\Services\Catalogo\CatalogoTramites::class)->buscar($codigo)['id'];
            $this->get(route('estudiante.solicitudes.'.$codigo.'.crear'))->assertOk()
                ->assertSee('data-tramite="'.$id.'"', false)->assertSee('data-simulado="0"', false);
        }
    }
}
