<?php

namespace Tests\Feature;

use App\Services\Solicitudes\DocumentosTemporales;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RestriccionesFormularioSolicitudTest extends TestCase
{
    use FacultadesG1Prueba;
    use SesionEstudiantePrueba;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function datos(string $tramite = 'gracia'): array
    {
        $campos = match ($tramite) {
            'cambio-carrera' => ['carrera_destino' => 'Arquitectura', 'nuevo_codigo' => 'no'],
            'cambio-modalidad' => ['modalidad_actual' => 'Presencial', 'modalidad_destino' => 'En línea'],
            default => [],
        };

        return [
            'tramite' => $tramite, 'nombre' => 'Ana Pérez', 'correo' => 'ana@uees.edu.ec',
            'codigo' => '00991234', 'cedula' => '0912345678', 'facultad' => 'Ciencias de la Comunicación',
            'carrera' => 'Diseño Gráfico Interactivo', 'modalidad' => 'Presencial', 'periodo' => '2026-II', 'celular' => '0991234567',
            'materias' => $tramite === 'gracia' ? [[
                'id' => 'fila-1', 'codigo' => 'DIS-900', 'nombre' => 'Diseño & comunicación',
                'periodo' => '2026-I', 'paralelo' => 'Z', 'docente' => 'María Docente',
                'datos' => ['nota' => '59', 'asistencia' => '80'],
            ]] : [],
            'campos' => $campos + ['motivo' => 'Solicito el trámite.'], 'declaracion' => true,
        ];
    }

    public static function cambiosSinEfecto(): array
    {
        return [
            'misma carrera' => ['cambio-carrera', 'carrera_destino', 'Diseño gráfico', []],
            'carrera con mayusculas y espacios' => ['cambio-carrera', 'carrera_destino', "\u{00A0}DISEÑO   GRÁFICO\u{00A0}", []],
            'misma modalidad' => ['cambio-modalidad', 'modalidad_destino', 'Presencial', []],
            'modalidad con mayusculas y espacios' => ['cambio-modalidad', 'modalidad_destino', '  PRESENCIAL  ', []],
            'modalidad personal aunque se altere la actual del tramite' => ['cambio-modalidad', 'modalidad_destino', 'Presencial', ['modalidad_actual' => 'En línea']],
            'modalidad declarada del tramite aunque difiera del paso uno' => ['cambio-modalidad', 'modalidad_destino', 'En línea', ['modalidad_actual' => '  EN   LÍNEA ']],
        ];
    }

    #[DataProvider('cambiosSinEfecto')]
    public function test_rechaza_cambios_sin_efecto_al_guardar_descargar_y_enviar(string $tramite, string $campo, string $destino, array $otros): void
    {
        $datos = $this->datos($tramite);
        $datos['campos'] = array_replace($datos['campos'], $otros, [$campo => $destino]);
        $this->putJson(route('borradores-temporales.update', $tramite), $datos + ['paso' => 2])
            ->assertUnprocessable()->assertJsonValidationErrors('campos.'.$campo);
        $this->postJson(route('solicitudes.documento'), $datos)
            ->assertUnprocessable()->assertJsonValidationErrors('campos.'.$campo);
        $this->postJson(route('solicitudes.enviar'), $datos + ['requisitos' => []])
            ->assertUnprocessable()->assertJsonValidationErrors('campos.'.$campo);
    }

    public function test_permite_cambiar_a_una_carrera_o_modalidad_distinta(): void
    {
        foreach (['cambio-carrera', 'cambio-modalidad'] as $tramite) {
            $datos = $this->datos($tramite);
            $this->putJson(route('borradores-temporales.update', $tramite), $datos + ['paso' => 2])->assertOk();
            $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        }
    }

    public function test_el_borrador_admite_un_cambio_aun_incompleto(): void
    {
        foreach (['cambio-carrera' => 'carrera_destino', 'cambio-modalidad' => 'modalidad_destino'] as $tramite => $destino) {
            $this->putJson(route('borradores-temporales.update', $tramite), [
                'paso' => 2, 'campos' => [$destino => 'En línea'],
            ])->assertOk();
            $datos = $this->datos($tramite);
            $datos['campos'][$destino] = '';
            $this->putJson(route('borradores-temporales.update', $tramite), $datos + ['paso' => 2])->assertOk();
        }
    }

    public function test_la_restriccion_de_cambio_no_se_aplica_a_otros_tramites(): void
    {
        $datos = $this->datos();
        $datos['tramite'] = 'homologacion';
        $datos['campos'] = ['institucion' => 'Otra universidad', 'carrera_destino' => $datos['carrera']];
        $datos['materias'][0]['datos'] = [];
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
    }

    public static function espacios(): array
    {
        return [
            'espacios y saltos' => [" \t\n "],
            'espacios no separables' => ["\u{00A0}\u{00A0}"],
            'espacios ideograficos' => ["\u{3000}\u{3000}"],
            'marca unicode' => ["\u{FEFF}"],
        ];
    }

    #[DataProvider('espacios')]
    public function test_los_espacios_solos_no_completan_campos_obligatorios(string $blanco): void
    {
        $datos = $this->datos();
        $datos['facultad'] = $blanco;
        $datos['materias'][0]['docente'] = $blanco;
        $datos['campos']['motivo'] = $blanco;
        $errores = ['facultad', 'materias.0.docente', 'campos.motivo'];
        $this->postJson(route('solicitudes.documento'), $datos)
            ->assertUnprocessable()->assertJsonValidationErrors($errores);
        $this->postJson(route('solicitudes.enviar'), $datos + ['requisitos' => []])
            ->assertUnprocessable()->assertJsonValidationErrors($errores);

        // Un borrador sigue pudiendo estar incompleto, pero no almacena espacios como datos.
        $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2])->assertOk();
        $borrador = $this->get(route('solicitudes.create', ['tramite' => 'gracia']))->viewData('borrador');
        $this->assertNull($borrador['facultad']);
        $this->assertNull($borrador['materias'][0]['docente']);
        $this->assertNull($borrador['campos']['motivo']);
    }

    public function test_limpia_extremos_y_conserva_ceros_acentos_y_parrafos(): void
    {
        $datos = $this->datos();
        $datos['nombre'] = '  Ana María Pérez  ';
        $datos['codigo'] = '  00991234  ';
        $datos['facultad'] = "\u{00A0}Ciencias de la Comunicación\u{00A0}";
        $datos['materias'][0]['docente'] = '  María Docente  ';
        $datos['materias'][0]['datos']['nota'] = ' 59 ';
        $datos['materias'][0]['datos']['asistencia'] = '   ';
        $datos['campos']['motivo'] = "  Primer párrafo.\n\nSegundo párrafo con  dos espacios internos.  ";
        $esperados = [
            'nombre' => auth()->user()->name, 'codigo' => '00991234', 'facultad' => 'Ciencias de la Comunicación',
            'materias.0.docente' => 'María Docente', 'materias.0.datos.nota' => '59',
            'materias.0.datos.asistencia' => null,
            'campos.motivo' => "Primer párrafo.\n\nSegundo párrafo con  dos espacios internos.",
        ];
        $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2])->assertOk();
        $borrador = $this->get(route('solicitudes.create', ['tramite' => 'gracia']))->viewData('borrador');
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        $documento = app(DocumentosTemporales::class)->buscar('gracia')['datos'];
        foreach ($esperados as $ruta => $valor) {
            $this->assertSame($valor, data_get($borrador, $ruta), $ruta);
            $this->assertSame($valor, data_get($documento, $ruta), $ruta);
        }
    }
}
