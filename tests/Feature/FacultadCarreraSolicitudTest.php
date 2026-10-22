<?php

namespace Tests\Feature;

use App\Services\Solicitudes\BorradoresTemporales;
use App\Services\Solicitudes\CatalogoFacultades;
use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\DocumentosTemporales;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacultadCarreraSolicitudTest extends TestCase
{
    use SesionEstudiantePrueba;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function datos(): array
    {
        return [
            'tramite' => 'gracia', 'nombre' => 'Ana Pérez', 'correo' => 'ana@uees.edu.ec',
            'codigo' => '00991234', 'cedula' => '0912345678', 'facultad' => 'Ingeniería',
            'carrera' => 'Ingeniería en Computación', 'modalidad' => 'Presencial', 'periodo' => '2026-II', 'celular' => '0991234567',
            'materias' => [['id' => 'fila-1', 'codigo' => 'COM-101', 'nombre' => 'Programación',
                'periodo' => '2026-I', 'paralelo' => 'A', 'docente' => 'María Docente', 'datos' => []]],
            'campos' => ['motivo' => 'Solicito el trámite.'], 'declaracion' => true,
        ];
    }

    public function test_los_veinte_formularios_tienen_selectores_vacios_de_facultad_y_carrera(): void
    {
        foreach (array_keys(DocumentoSolicitud::PLANTILLAS) as $tramite) {
            $respuesta = $this->get(route('solicitudes.create', ['tramite' => $tramite]))->assertOk();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$respuesta->getContent());
            $facultad = $dom->getElementById('facultad');
            $carrera = $dom->getElementById('carrera');
            $this->assertSame('select', $facultad->tagName);
            $this->assertSame('select', $carrera->tagName);
            $this->assertTrue($facultad->hasAttribute('required'));
            $this->assertTrue($carrera->hasAttribute('required'));
            $this->assertTrue($carrera->hasAttribute('disabled'));
            $this->assertSame(count(CatalogoFacultades::todas()) + 1, $facultad->getElementsByTagName('option')->length);
        }
    }

    public function test_acepta_las_combinaciones_del_catalogo_sin_consultar_tablas_de_estudiantes(): void
    {
        foreach (CatalogoFacultades::todas() as $facultad => $carreras) {
            foreach ($carreras as $carrera) {
                $this->putJson(route('borradores-temporales.update', 'gracia'), [
                    'paso' => 1, 'facultad' => $facultad, 'carrera' => $carrera,
                ])->assertOk();
            }
        }
    }

    public function test_rechaza_carreras_de_otra_facultad_y_opciones_inventadas(): void
    {
        foreach ([['Ingeniería', 'Medicina', 'carrera'], ['Facultad inventada', 'Ingeniería en Computación', 'facultad'],
            ['Ingeniería', 'Carrera inventada', 'carrera']] as [$facultad, $carrera, $campo]) {
            $datos = array_replace($this->datos(), compact('facultad', 'carrera'));
            $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2])
                ->assertUnprocessable()->assertJsonValidationErrors($campo);
            $this->postJson(route('solicitudes.documento'), $datos)->assertUnprocessable()->assertJsonValidationErrors($campo);
            $this->postJson(route('solicitudes.enviar'), $datos + ['requisitos' => []])
                ->assertUnprocessable()->assertJsonValidationErrors($campo);
        }
    }

    public function test_borrador_incompleto_y_cambio_de_facultad(): void
    {
        $this->putJson(route('borradores-temporales.update', 'gracia'), ['paso' => 1])->assertOk();
        $this->putJson(route('borradores-temporales.update', 'gracia'), [
            'paso' => 1, 'facultad' => 'Ingeniería', 'carrera' => '',
        ])->assertOk();
        $datos = array_replace($this->datos(), ['facultad' => 'Arquitectura y Diseño', 'carrera' => 'Arquitectura']);
        $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 1])->assertOk();
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        $guardado = app(DocumentosTemporales::class)->buscar('gracia')['datos'];
        $this->assertSame('Arquitectura y Diseño', $guardado['facultad']);
        $this->assertSame('Arquitectura', $guardado['carrera']);
    }

    public function test_reconoce_nombres_anteriores_y_los_presenta_con_el_nombre_oficial(): void
    {
        app(BorradoresTemporales::class)->guardar('gracia', ['paso' => 1,
            'facultad' => '  Facultad de INGENIERÍA ', 'carrera' => 'Computación']);
        $borrador = $this->get(route('solicitudes.create', ['tramite' => 'gracia']))->viewData('borrador');
        $this->assertSame('Ingeniería', $borrador['facultad']);
        $this->assertSame('Ingeniería en Computación', $borrador['carrera']);
        $datos = array_replace($this->datos(), ['facultad' => 'Facultad de ingeniería', 'carrera' => 'computacion']);
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        $this->assertSame('Ingeniería en Computación', app(DocumentosTemporales::class)->buscar('gracia')['datos']['carrera']);
    }

    public function test_no_pierde_los_nombres_antiguos_que_requieren_revision(): void
    {
        app(BorradoresTemporales::class)->guardar('gracia', ['paso' => 1, 'facultad' => 'Nombre antiguo', 'carrera' => 'Carrera antigua']);
        $borrador = $this->get(route('solicitudes.create', ['tramite' => 'gracia']))->viewData('borrador');
        $this->assertSame('Nombre antiguo', $borrador['facultad']);
        $this->assertSame('Carrera antigua', $borrador['carrera']);
    }
}
