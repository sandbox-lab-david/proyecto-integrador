<?php

namespace Tests\Feature;

use App\Jobs\ProcesarImportacion;
use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Importacion;
use App\Models\Malla;
use App\Models\Trabajador;
use App\Models\User;
use App\Services\Academico\CatalogoAcademicoService;
use App\Services\Academico\EstudianteService;
use App\Services\Academico\Importadores\Plantilla;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportacionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('local');
        $this->admin = User::where('email', 'admin@uees.edu.ec')->first();
    }

    private function subir(string $entidad, UploadedFile $archivo): Importacion
    {
        $this->actingAs($this->admin)->post(route('cargas.preview'), ['entidad' => $entidad, 'archivo' => $archivo])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        return Importacion::latest('id')->first();
    }

    private function csv(string $nombre, string $contenido): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nombre, $contenido);
    }

    private function ejemplo(string $entidad): UploadedFile
    {
        return $this->csv("{$entidad}.csv", file_get_contents(database_path("data/ejemplos/{$entidad}.csv")));
    }

    private function importar(string $entidad, UploadedFile $archivo): Importacion
    {
        $importacion = $this->subir($entidad, $archivo);
        $this->actingAs($this->admin)->get(route('cargas.vista-previa', $importacion))->assertOk();
        $this->actingAs($this->admin)->post(route('cargas.procesar', $importacion))->assertRedirect(route('cargas.detalle', $importacion));

        return $importacion->fresh();
    }

    public function test_la_tabla_usa_los_nombres_del_excel(): void
    {
        $this->assertTrue(Schema::hasColumn('importaciones', 'user_id'));
        $this->assertFalse(Schema::hasColumn('importaciones', 'useri_id'));
    }

    public function test_solo_el_administrador_entra(): void
    {
        $this->actingAs(User::where('email', 'asistente@uees.edu.ec')->first())->get(route('cargas.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('cargas.index'))->assertOk()->assertSee('Paralelos')->assertDontSee('Notas');
        $this->actingAs($this->admin)->get('/importaciones')->assertNotFound();
    }

    public function test_se_descargan_las_plantillas_y_los_ejemplos(): void
    {
        foreach (['carreras', 'materias', 'mallas', 'docentes', 'paralelos', 'estudiantes'] as $tipo) {
            $this->actingAs($this->admin)->get(route('cargas.plantilla', $tipo))->assertOk()->assertDownload("plantilla_{$tipo}.xlsx");
            $this->actingAs($this->admin)->get(route('cargas.ejemplo', $tipo))->assertOk()->assertDownload("ejemplo_{$tipo}.csv");
        }

        $this->actingAs($this->admin)->get(route('cargas.plantilla', 'notas'))->assertNotFound();
    }

    public function test_importar_los_seis_ejemplos_en_orden(): void
    {
        $esperado = ['carreras' => 3, 'materias' => 4, 'mallas' => 5, 'docentes' => 3, 'paralelos' => 4, 'estudiantes' => 3];

        foreach ($esperado as $entidad => $filas) {
            $importacion = $this->importar($entidad, $this->ejemplo($entidad));
            $this->assertSame('completada', $importacion->estado, $entidad);
            $this->assertSame([$filas, $filas, 0, 0], [$importacion->total_filas, $importacion->creadas, $importacion->actualizadas, $importacion->rechazadas], $entidad);
            $this->assertSame($this->admin->id, $importacion->user_id);
        }

        $datos = Carrera::where('codigo', 'DATA')->first();
        $this->assertSame(['En línea', 'Presencial'], $datos->modalidades->pluck('nombre')->sort()->values()->all());
        $this->assertFalse((bool) Carrera::where('codigo', 'FISI')->value('admite_tercer_registro'));

        $malla = Malla::where('carrera_id', Carrera::where('codigo', 'MECA')->value('id'))->first();
        $this->assertTrue((bool) $malla->vigente);
        $this->assertCount(3, app(CatalogoAcademicoService::class)->materiasDeMalla($malla->id));

        $this->assertTrue(Trabajador::conRol('docente')->where('codigo', 'DOC-101')->exists());
        $this->assertSame('DOC-101', Curso::whereHas('materia', fn ($q) => $q->where('codigo', 'MEC101'))->first()->profesor->codigo);

        $estudiante = Estudiante::where('codigo', '2026100002')->first();
        $this->assertSame('estudiante', $estudiante->user->tipo);
        $this->assertSame('vortiz.demo@uees.edu.ec', Estudiante::where('codigo', '2026100001')->first()->user->email);
        $inscripcion = app(EstudianteService::class)->carrerasActivas($estudiante->id)->sole();
        $this->assertSame('En línea', $inscripcion->carreraModalidad->modalidad->nombre);
        $this->assertSame('2026', $inscripcion->malla->version);
    }

    public function test_volver_a_importar_actualiza_en_vez_de_duplicar(): void
    {
        $this->importar('materias', $this->ejemplo('materias'));
        $segunda = $this->importar('materias', $this->csv('materias.csv', "codigo,nombre,creditos\nMEC101,Mecatrónica Básica,5\nNUE101,Materia Nueva,2\n"));

        $this->assertSame([1, 1], [$segunda->creadas, $segunda->actualizadas]);
        $this->assertSame('Mecatrónica Básica', \App\Models\Materia::where('codigo', 'MEC101')->value('nombre'));
        $this->assertSame(1, \App\Models\Materia::where('codigo', 'MEC101')->count());
    }

    public function test_la_vista_previa_muestra_los_errores_y_no_guarda_nada(): void
    {
        $contenido = "codigo,nombres,apellidos,correo,carrera_codigo,modalidad\n"
            ."2026200001,Ana,Ruiz,aruiz@uees.edu.ec,COMP,Presencial\n"
            ."12345,Luis,Mora,lmora@uees.edu.ec,COMP,Presencial\n"
            ."2026200003,Eva,Paz,epaz@gmail.com,COMP,Presencial\n"
            ."2026200004,Iván,Sol,isol@uees.edu.ec,NOEXISTE,Presencial\n"
            ."2026200005,Rita,Gil,rgil@uees.edu.ec,COMP,Dual\n"
            ."2026200001,Ana,Ruiz,aruiz@uees.edu.ec,COMP,Presencial\n";

        $importacion = $this->subir('estudiantes', $this->csv('estudiantes.csv', $contenido));

        $this->actingAs($this->admin)->get(route('cargas.vista-previa', $importacion))
            ->assertOk()
            ->assertSee('codigo debe tener 10 caracteres')
            ->assertSee('debe terminar en @uees.edu.ec')
            ->assertSee('No existe la carrera con código NOEXISTE')
            ->assertSee('no se ofrece en la modalidad Dual')
            ->assertSee('es igual a la fila 2')
            ->assertSee('Confirmar e importar 1 filas');

        $this->assertFalse(Estudiante::where('codigo', '2026200001')->exists());

        $this->actingAs($this->admin)->post(route('cargas.procesar', $importacion));
        $importacion->refresh();

        $this->assertSame([6, 1, 5], [$importacion->total_filas, $importacion->creadas, $importacion->rechazadas]);
        $this->assertSame([3, 4, 5, 6, 7], collect($importacion->errores)->pluck('fila')->all());

        $csv = $this->actingAs($this->admin)->get(route('cargas.reporte-errores', $importacion))->streamedContent();
        $this->assertStringContainsString('fila,codigo,motivo', $csv);
        $this->assertStringContainsString('NOEXISTE', $csv);

        $this->actingAs($this->admin)->post(route('cargas.procesar', $importacion))->assertStatus(409);
    }

    public function test_si_faltan_columnas_no_se_puede_confirmar(): void
    {
        $importacion = $this->subir('materias', $this->csv('materias.csv', "codigo,nombre\nX1,Algo\n"));

        $this->actingAs($this->admin)->get(route('cargas.vista-previa', $importacion))
            ->assertSee('Faltan columnas obligatorias')->assertSee('creditos')->assertDontSee('Confirmar e importar');

        $this->actingAs($this->admin)->post(route('cargas.procesar', $importacion));
        $this->assertSame('fallida', $importacion->fresh()->estado);
    }

    public function test_lee_archivos_xlsx_y_csv_de_excel_en_espanol(): void
    {
        Excel::store(new Plantilla(['codigo', 'nombre', 'creditos', 'categoria'], [['XLS101', 'Desde Excel', 3, 'General']]), 'tmp/materias.xlsx', 'local');
        $xlsx = new UploadedFile(Storage::disk('local')->path('tmp/materias.xlsx'), 'materias.xlsx', null, null, true);
        $this->assertSame(1, $this->importar('materias', $xlsx)->creadas);

        $latino = mb_convert_encoding("Código;Nombre;Créditos\nLAT101;Programación Básica;4\n", 'ISO-8859-1', 'UTF-8');
        $this->assertSame(1, $this->importar('materias', $this->csv('materias.csv', $latino))->creadas);

        $this->assertSame('Desde Excel', \App\Models\Materia::where('codigo', 'XLS101')->value('nombre'));
        $this->assertSame('Programación Básica', \App\Models\Materia::where('codigo', 'LAT101')->value('nombre'));
    }

    public function test_los_archivos_grandes_van_a_la_cola(): void
    {
        Queue::fake();
        $filas = collect(range(1, 501))->map(fn ($i) => "GR{$i},Materia {$i},3")->join("\n");
        $importacion = $this->subir('materias', $this->csv('materias.csv', "codigo,nombre,creditos\n{$filas}\n"));

        $this->actingAs($this->admin)->get(route('cargas.vista-previa', $importacion));
        $this->actingAs($this->admin)->post(route('cargas.procesar', $importacion))->assertSessionHas('exito', fn ($m) => str_contains($m, 'segundo plano'));

        $this->assertSame('procesando', $importacion->fresh()->estado);
        Queue::assertPushed(ProcesarImportacion::class, fn ($job) => $job->importacion->is($importacion));

        (new ProcesarImportacion($importacion->fresh()))->handle(app(\App\Services\Academico\ImportacionService::class));
        $this->assertSame([501, 'completada'], [$importacion->fresh()->creadas, $importacion->fresh()->estado]);
    }

    public function test_cancelar_borra_el_archivo_y_el_registro(): void
    {
        $importacion = $this->subir('materias', $this->ejemplo('materias'));
        Storage::disk('local')->assertExists($importacion->archivo_ruta);

        $this->actingAs($this->admin)->delete(route('cargas.cancelar', $importacion))->assertRedirect(route('cargas.index'));

        Storage::disk('local')->assertMissing($importacion->archivo_ruta);
        $this->assertNull($importacion->fresh());
    }

    public function test_el_historial_muestra_las_importaciones(): void
    {
        $importacion = $this->importar('docentes', $this->ejemplo('docentes'));

        $this->actingAs($this->admin)->get(route('cargas.historial'))->assertOk()->assertSee('#'.$importacion->id)->assertSee('docentes.csv');
        $this->actingAs($this->admin)->get(route('cargas.detalle', $importacion))->assertOk()->assertSee('Todas las filas se importaron sin errores');
    }
}
