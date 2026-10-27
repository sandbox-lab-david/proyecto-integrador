<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Solicitudes\DatosFormularioSolicitud;
use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\DocumentosTemporales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DatosManualesSolicitudTest extends TestCase
{
    use FacultadesG1Prueba;
    use RefreshDatabase, SesionEstudiantePrueba;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function datos(): array
    {
        return [
            'tramite' => 'gracia', 'nombre' => 'Ana Declarada', 'correo' => 'ana@uees.edu.ec',
            'codigo' => '00991234', 'cedula' => '0912345678', 'facultad' => 'Ciencias de la Comunicación',
            'carrera' => 'Diseño Gráfico Interactivo', 'modalidad' => 'En línea', 'periodo' => '2026-II', 'celular' => '0991234567',
            'materias' => [['id' => 'fila-nueva-1', 'codigo' => 'DIS-900', 'nombre' => 'Diseño & comunicación',
                'periodo' => '2026-I', 'paralelo' => 'Z', 'docente' => 'María Docente',
                'datos' => ['nota' => '59', 'asistencia' => '80']]],
            'campos' => ['motivo' => 'Solicito el examen de esta materia.'], 'declaracion' => true,
        ];
    }

    public function test_todos_los_tramites_muestran_campos_vacios_y_editables_sin_datos_ficticios(): void
    {
        foreach (array_keys(DocumentoSolicitud::PLANTILLAS) as $tramite) {
            $respuesta = $this->get(route('solicitudes.create', ['tramite' => $tramite]))->assertOk()
                ->assertDontSee('Estudiante de demostración')->assertDontSee('estudiante.demo@uees.edu.ec')
                ->assertDontSee('calculo-demo')->assertDontSee('Lista ficticia');
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$respuesta->getContent());
            foreach (array_keys(DatosFormularioSolicitud::PERSONALES) as $campo) {
                $input = $dom->getElementById($campo);
                $this->assertNotNull($input, $tramite.': '.$campo);
                $this->assertFalse($input->hasAttribute('readonly'), $tramite.': '.$campo);
                $this->assertSame('', $input->getAttribute('value'));
                if (in_array($campo, DatosFormularioSolicitud::SOLO_DIGITOS, true)) {
                    $this->assertSame('numeric', $input->getAttribute('inputmode'), $tramite.': '.$campo);
                    $this->assertSame('[0-9]+', $input->getAttribute('pattern'), $tramite.': '.$campo);
                    $this->assertStringContainsString('replace(/[^0-9]/g', $input->getAttribute('oninput'));
                }
            }
        }
    }

    public function test_nombre_y_correo_tambien_se_escriben_a_mano_con_sesion_iniciada(): void
    {
        $usuario = User::factory()->create(['name' => 'Nombre de la cuenta', 'email' => 'cuenta@uees.edu.ec']);
        $respuesta = $this->actingAs($usuario)->get(route('solicitudes.create', ['tramite' => 'gracia']))->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$respuesta->getContent());
        foreach (['nombre', 'correo'] as $campo) {
            $this->assertSame('', $dom->getElementById($campo)->getAttribute('value'));
            $this->assertFalse($dom->getElementById($campo)->hasAttribute('readonly'));
        }
        foreach (['codigo', 'cedula', 'facultad', 'carrera', 'modalidad', 'periodo', 'celular'] as $campo) {
            $this->assertFalse($dom->getElementById($campo)->hasAttribute('readonly'));
            $this->assertSame('', $dom->getElementById($campo)->getAttribute('value'));
        }
    }

    public function test_guarda_y_recupera_datos_y_materias_manuales_sin_importar_siac(): void
    {
        $datos = $this->datos();
        $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2])->assertOk();
        $borrador = $this->get(route('solicitudes.create', ['tramite' => 'gracia']))->assertOk()->viewData('borrador');
        foreach (array_keys(DatosFormularioSolicitud::PERSONALES) as $campo) {
            $this->assertSame($datos[$campo], $borrador[$campo]);
        }
        $this->assertEquals($datos['materias'], $borrador['materias']);
    }

    public function test_borrador_y_documento_conservan_los_datos_declarados_sin_modificar_la_cuenta(): void
    {
        $usuario = User::factory()->create(['name' => 'Identidad real', 'email' => 'real@uees.edu.ec']);
        $datos = $this->datos();
        $this->actingAs($usuario)->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2])->assertOk();
        $borrador = $this->get(route('solicitudes.create', ['tramite' => 'gracia']))->viewData('borrador');
        $this->assertSame($datos['nombre'], $borrador['nombre']);
        $this->assertSame($datos['correo'], $borrador['correo']);
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        $guardado = app(DocumentosTemporales::class)->buscar('gracia')['datos'];
        $this->assertSame($datos['nombre'], $guardado['nombre']);
        $this->assertSame($datos['correo'], $guardado['correo']);
        $this->assertSame('Identidad real', $usuario->fresh()->name);
        $this->assertSame('real@uees.edu.ec', $usuario->fresh()->email);
        $this->assertSame($datos['cedula'], $guardado['cedula']);
        $this->assertSame($datos['materias'], $guardado['materias']);
    }

    public function test_el_word_contiene_los_datos_declarados_y_la_materia_escrita(): void
    {
        $datos = $this->datos();
        $respuesta = $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        $ruta = tempnam(sys_get_temp_dir(), 'sol-manual-');
        try {
            file_put_contents($ruta, $respuesta->getContent());
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($ruta));
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            $dom = new \DOMDocument;
            $this->assertTrue($dom->loadXML($xml));
            foreach (['nombre', 'codigo', 'cedula', 'facultad', 'carrera', 'modalidad', 'periodo', 'correo', 'celular'] as $campo) {
                $this->assertStringContainsString($datos[$campo], $dom->textContent);
            }
            foreach (['codigo', 'nombre', 'periodo', 'paralelo', 'docente'] as $campo) {
                $this->assertStringContainsString($datos['materias'][0][$campo], $dom->textContent);
            }
        } finally {
            unlink($ruta);
        }
    }

    public function test_un_borrador_puede_tener_campos_y_materias_incompletos(): void
    {
        $this->putJson(route('borradores-temporales.update', 'gracia'), [
            'paso' => 1, 'cedula' => '', 'carrera' => '', 'materias' => [['id' => 'fila-1', 'nombre' => 'A medias']],
        ])->assertOk();
        $datos = $this->datos();
        $datos['cedula'] = '';
        $datos['materias'][0]['docente'] = '';
        $datos['celular'] = 'hola123';
        $this->postJson(route('solicitudes.documento'), $datos)->assertUnprocessable()
            ->assertJsonValidationErrors(['cedula', 'materias.0.docente', 'celular']);
    }

    public function test_mantiene_limites_de_materias_y_los_ids_no_son_ids_de_base_de_datos(): void
    {
        $datos = $this->datos();
        $datos['materias'] = array_map(fn ($i) => array_replace($datos['materias'][0], ['id' => 'fila-'.$i]), range(1, 11));
        $this->postJson(route('solicitudes.documento'), $datos)->assertJsonValidationErrors('materias');
        $datos['materias'] = array_slice($datos['materias'], 0, 2);
        $datos['tramite'] = 'recuperacion';
        $this->postJson(route('solicitudes.documento'), $datos)->assertJsonValidationErrors('materias');
        $datos['tramite'] = 'gracia';
        $datos['materias'][0]['curso_id'] = 99;
        $this->postJson(route('solicitudes.documento'), $datos)->assertJsonValidationErrors('materias.0');
    }

    public function test_cambiar_datos_personales_o_la_materia_invalida_la_huella_del_documento_firmado(): void
    {
        $datos = $this->datos();
        $servicio = app(DocumentosTemporales::class);
        $original = $servicio->huella($datos);
        foreach (['cedula', 'facultad', 'modalidad', 'periodo'] as $campo) {
            $modificados = $datos;
            $modificados[$campo] = 'Nuevo valor';
            $this->assertNotSame($original, $servicio->huella($modificados));
        }
        $datos['materias'][0]['docente'] = 'Otro docente';
        $this->assertNotSame($original, $servicio->huella($datos));
    }

    public static function identificadoresInvalidos(): array
    {
        return [
            'codigo con letras' => ['codigo', 'ABC123'],
            'cedula con letras' => ['cedula', '091A345678'],
            'celular con letras' => ['celular', '099ABC1234'],
            'notacion cientifica' => ['codigo', '1e8'],
            'decimal' => ['cedula', '09123.45678'],
            'negativo' => ['codigo', '-12345'],
            'signo internacional' => ['celular', '+593991234567'],
            'espacio interno' => ['codigo', '12 345'],
            'digitos de otro alfabeto' => ['cedula', '０９１２３４５６７８'],
            'numero JSON en vez de texto' => ['codigo', 12345],
            'arreglo en vez de texto' => ['cedula', ['0912345678']],
        ];
    }

    #[DataProvider('identificadoresInvalidos')]
    public function test_rechaza_identificadores_invalidos_al_guardar_descargar_y_enviar(string $campo, mixed $valor): void
    {
        $datos = $this->datos();
        $datos[$campo] = $valor;
        $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2])
            ->assertUnprocessable()->assertJsonValidationErrors($campo);
        $this->postJson(route('solicitudes.documento'), $datos)
            ->assertUnprocessable()->assertJsonValidationErrors($campo);
        $this->postJson(route('solicitudes.enviar'), $datos + ['requisitos' => []])
            ->assertUnprocessable()->assertJsonValidationErrors($campo);
    }

    public function test_respeta_las_longitudes_maximas_de_los_identificadores(): void
    {
        foreach (DatosFormularioSolicitud::SOLO_DIGITOS as $campo) {
            $datos = $this->datos();
            $datos[$campo] = str_repeat('1', DatosFormularioSolicitud::PERSONALES[$campo][1] + 1);
            $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2])
                ->assertUnprocessable()->assertJsonValidationErrors($campo);
            $this->postJson(route('solicitudes.documento'), $datos)
                ->assertUnprocessable()->assertJsonValidationErrors($campo);
        }
    }

    public function test_rechaza_la_misma_materia_aunque_tenga_otro_id_mayusculas_o_espacios(): void
    {
        $datos = $this->datos();
        $datos['materias'][0]['codigo'] = 'DIS 900';
        $datos['materias'][0]['periodo'] = '2026   I';
        $datos['materias'][] = array_replace($datos['materias'][0], [
            'id' => 'fila-distinta', 'codigo' => "\u{00A0}dis 900\u{00A0}", 'periodo' => '2026 I', 'paralelo' => ' z ',
        ]);
        $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2])
            ->assertUnprocessable()->assertJsonValidationErrors('materias.1.codigo');
        $this->postJson(route('solicitudes.documento'), $datos)
            ->assertUnprocessable()->assertJsonValidationErrors('materias.1.codigo');
        $this->postJson(route('solicitudes.enviar'), $datos + ['requisitos' => []])
            ->assertUnprocessable()->assertJsonValidationErrors('materias.1.codigo');
    }

    public function test_admite_la_misma_materia_en_otro_periodo_o_paralelo(): void
    {
        foreach (['periodo' => '2025-II', 'paralelo' => 'B'] as $campo => $valor) {
            $datos = $this->datos();
            $datos['materias'][] = array_replace($datos['materias'][0], ['id' => 'fila-distinta', $campo => $valor]);
            $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2])->assertOk();
            $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        }
    }

    public function test_las_filas_incompletas_no_se_confunden_con_materias_repetidas(): void
    {
        $this->putJson(route('borradores-temporales.update', 'gracia'), [
            'paso' => 2, 'materias' => [
                ['id' => 'fila-1', 'codigo' => 'DIS-900'],
                ['id' => 'fila-2', 'codigo' => 'DIS-900'],
            ],
        ])->assertOk();
        $this->putJson(route('borradores-temporales.update', 'gracia'), [
            'paso' => 2, 'materias' => ['fila incorrecta'],
        ])->assertUnprocessable()->assertJsonValidationErrors('materias.0');
    }

    public function test_homologacion_utiliza_la_cedula_personal_sin_pedirla_de_nuevo(): void
    {
        $this->get(route('solicitudes.create', ['tramite' => 'homologacion']))->assertOk()
            ->assertSee('id="cedula"', false)->assertDontSee('id="campo-cedula"', false);
        $datos = $this->datos();
        $datos['tramite'] = 'homologacion';
        $datos['campos'] = ['institucion' => 'Otra universidad', 'carrera_destino' => 'Diseño'];
        $datos['materias'][0]['datos'] = [];
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        $guardado = app(DocumentosTemporales::class)->buscar('homologacion')['datos'];
        $this->assertSame($datos['cedula'], $guardado['cedula']);
        $this->assertArrayNotHasKey('cedula', $guardado['campos']);
        $datos['campos']['cedula'] = 'Otra cédula';
        $this->postJson(route('solicitudes.documento'), $datos)
            ->assertUnprocessable()->assertJsonValidationErrors('campos');
    }
}
