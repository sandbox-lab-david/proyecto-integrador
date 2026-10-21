<?php

namespace Tests\Feature;

use App\Services\Solicitudes\DocumentosTemporales;
use App\Services\Solicitudes\ReglasCampoSolicitud;
use App\Services\Solicitudes\TramitesSimulados;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NotasYFechasSolicitudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-20 15:30:00', ReglasCampoSolicitud::zonaHoraria()));
        Storage::fake('local');
    }

    private function datos(string $tramite): array
    {
        $definicion = app(TramitesSimulados::class)->buscar($tramite);
        $valores = fn ($campos) => collect($campos)->mapWithKeys(fn ($campo) => [$campo['nombre'] => match ($campo['tipo']) {
            'numero' => str_contains($campo['nombre'], 'anio') ? '2025' : '80',
            'fecha' => '2026-10-19', 'hora' => '08:30',
            'lista' => (string) array_key_first($campo['opciones']),
            default => 'Información declarada',
        }])->all();

        return [
            'tramite' => $tramite, 'nombre' => 'Ana Pérez', 'correo' => 'ana@uees.edu.ec',
            'codigo' => '00991234', 'cedula' => '0912345678', 'facultad' => 'Ingeniería',
            'carrera' => 'Ingeniería en Computación', 'modalidad' => 'Presencial', 'periodo' => '2026-II', 'celular' => '0991234567',
            'materias' => [[
                'id' => 'fila-1', 'codigo' => 'COM-101', 'nombre' => 'Programación',
                'periodo' => '2026-I', 'paralelo' => 'A', 'docente' => 'María Docente',
                'datos' => $valores($definicion['campos_materia']),
            ]],
            'campos' => $valores($definicion['campos']), 'declaracion' => true,
        ];
    }

    private function rechazarEnTodasLasRutas(array $datos, array|string $campos): void
    {
        $this->putJson(route('borradores-temporales.update', $datos['tramite']), $datos + ['paso' => 2])
            ->assertUnprocessable()->assertJsonValidationErrors($campos);
        $this->postJson(route('solicitudes.documento'), $datos)
            ->assertUnprocessable()->assertJsonValidationErrors($campos);
        $this->postJson(route('solicitudes.enviar'), $datos + ['requisitos' => []])
            ->assertUnprocessable()->assertJsonValidationErrors($campos);
    }

    public static function camposDeCeroACien(): array
    {
        return [
            'nota recuperacion' => ['recuperacion', 'materias.0.datos.nota'],
            'asistencia recuperacion' => ['recuperacion', 'materias.0.datos.asistencia'],
            'gpa recuperacion' => ['recuperacion', 'campos.gpa_general'],
            'nota gracia' => ['gracia', 'materias.0.datos.nota'],
            'asistencia gracia' => ['gracia', 'materias.0.datos.asistencia'],
            'calificacion ayudante' => ['ayudante', 'materias.0.datos.calificacion'],
            'gpa ayudante' => ['ayudante', 'campos.gpa_general'],
            'nota primer registro' => ['tercer-registro', 'materias.0.datos.registro_1_nota'],
            'nota segundo registro' => ['tercer-registro', 'materias.0.datos.registro_2_nota'],
        ];
    }

    #[DataProvider('camposDeCeroACien')]
    public function test_rechaza_valores_negativos_o_mayores_a_cien(string $tramite, string $campo): void
    {
        foreach (['-0.01', '100.01'] as $valor) {
            $datos = $this->datos($tramite);
            Arr::set($datos, $campo, $valor);
            $this->rechazarEnTodasLasRutas($datos, $campo);
        }
    }

    #[DataProvider('camposDeCeroACien')]
    public function test_acepta_cero_cien_y_notas_decimales(string $tramite, string $campo): void
    {
        foreach (['0', '100', '85.5'] as $valor) {
            $datos = $this->datos($tramite);
            Arr::set($datos, $campo, $valor);
            $this->putJson(route('borradores-temporales.update', $tramite), $datos + ['paso' => 2])->assertOk();
            $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
            $guardado = app(DocumentosTemporales::class)->buscar($tramite)['datos'];
            $this->assertSame($valor, data_get($guardado, $campo));
        }
    }

    public static function fechasInvalidas(): array
    {
        $casos = [];
        foreach (['recuperacion' => 'ultima_recuperacion', 'recalificacion' => 'publicacion_fecha'] as $tramite => $campo) {
            foreach (['2026-02-30', '2026-13-01', '20/10/2026', 'mañana', '2026-10-21'] as $fecha) {
                $casos[$tramite.' '.$fecha] = [$tramite, $campo, $fecha];
            }
        }

        return $casos;
    }

    #[DataProvider('fechasInvalidas')]
    public function test_rechaza_fechas_imposibles_formatos_incorrectos_y_fechas_futuras(string $tramite, string $campo, string $fecha): void
    {
        $datos = $this->datos($tramite);
        $datos['campos'][$campo] = $fecha;
        if ($tramite === 'recuperacion') {
            $datos['campos']['historial_recuperacion'] = 'con_fecha';
        }
        $this->rechazarEnTodasLasRutas($datos, 'campos.'.$campo);
    }

    public function test_no_admite_publicacion_en_una_hora_futura_de_hoy(): void
    {
        $datos = $this->datos('recalificacion');
        $datos['campos']['publicacion_fecha'] = '2026-10-20';
        $datos['campos']['publicacion_hora'] = '15:31';
        $this->rechazarEnTodasLasRutas($datos, 'campos.publicacion_hora');

        foreach (['25:00', '12:75', 'texto'] as $hora) {
            $datos['campos']['publicacion_hora'] = $hora;
            $this->rechazarEnTodasLasRutas($datos, 'campos.publicacion_hora');
        }
    }

    public function test_admite_publicaciones_pasadas_y_del_minuto_actual(): void
    {
        foreach ([['2026-10-19', '23:59'], ['2026-10-20', '15:29'], ['2026-10-20', '15:30']] as [$fecha, $hora]) {
            $datos = $this->datos('recalificacion');
            $datos['campos']['publicacion_fecha'] = $fecha;
            $datos['campos']['publicacion_hora'] = $hora;
            $this->putJson(route('borradores-temporales.update', 'recalificacion'), $datos + ['paso' => 2])->assertOk();
            $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        }
    }

    public function test_admite_fechas_historicas_validas_incluido_un_dia_bisiesto(): void
    {
        foreach (['2024-02-29', '2026-10-20'] as $fecha) {
            $datos = $this->datos('recuperacion');
            $datos['campos']['historial_recuperacion'] = 'con_fecha';
            $datos['campos']['ultima_recuperacion'] = $fecha;
            $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        }
    }

    public function test_el_segundo_registro_no_puede_ser_anterior_al_primero_en_ninguna_materia(): void
    {
        $datos = $this->datos('tercer-registro');
        $datos['materias'][] = array_replace($datos['materias'][0], ['id' => 'fila-2', 'codigo' => 'COM-102']);
        $datos['materias'][1]['datos']['registro_1_anio'] = '2025';
        $datos['materias'][1]['datos']['registro_2_anio'] = '2024';
        $this->rechazarEnTodasLasRutas($datos, 'materias.1.datos.registro_2_anio');
        $respuesta = $this->putJson(route('borradores-temporales.update', 'tercer-registro'), $datos + ['paso' => 2]);
        $this->assertSame('El año del segundo registro no puede ser anterior al del primero.',
            $respuesta->json('errors')['materias.1.datos.registro_2_anio'][0]);

        $datos['materias'][1]['datos']['registro_2_anio'] = '2027';
        $this->rechazarEnTodasLasRutas($datos, 'materias.1.datos.registro_2_anio');
    }

    public function test_admite_registros_en_el_mismo_anio_o_en_anios_sucesivos(): void
    {
        foreach ([['2024', '2025'], ['2025', '2025']] as [$primero, $segundo]) {
            $datos = $this->datos('tercer-registro');
            $datos['materias'][0]['datos']['registro_1_anio'] = $primero;
            $datos['materias'][0]['datos']['registro_2_anio'] = $segundo;
            $this->putJson(route('borradores-temporales.update', 'tercer-registro'), $datos + ['paso' => 2])->assertOk();
            $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
        }
    }

    public function test_las_comparaciones_no_exigen_completar_el_borrador(): void
    {
        $this->putJson(route('borradores-temporales.update', 'tercer-registro'), [
            'paso' => 2, 'materias' => [['id' => 'fila-1', 'datos' => ['registro_1_anio' => '2025']]],
        ])->assertOk();
        $this->putJson(route('borradores-temporales.update', 'recalificacion'), [
            'paso' => 2, 'campos' => ['publicacion_fecha' => '2026-10-20'],
        ])->assertOk();
        $datos = $this->datos('recuperacion');
        $datos['campos']['gpa_general'] = '';
        $datos['materias'][0]['datos']['nota'] = '';
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
    }

    public function test_los_mensajes_indican_el_campo_y_limite_en_espanol(): void
    {
        $datos = $this->datos('gracia');
        $datos['materias'][0]['datos']['nota'] = '-1';
        $respuesta = $this->postJson(route('solicitudes.documento'), $datos)->assertUnprocessable();
        $this->assertSame('El campo calificación actual debe ser mayor o igual que 0.',
            $respuesta->json('errors')['materias.0.datos.nota'][0]);

        $datos['materias'][0]['datos']['nota'] = '101';
        $respuesta = $this->putJson(route('borradores-temporales.update', 'gracia'), $datos + ['paso' => 2]);
        $this->assertSame('El campo calificación actual debe ser menor o igual que 100.',
            $respuesta->json('errors')['materias.0.datos.nota'][0]);
    }

    public function test_datos_no_numericos_o_estructuras_incorrectas_no_provocan_errores_del_servidor(): void
    {
        foreach (['NaN', 'ochenta', ['80']] as $valor) {
            $datos = $this->datos('tercer-registro');
            $datos['materias'][0]['datos']['registro_1_anio'] = $valor;
            $datos['materias'][0]['datos']['registro_2_nota'] = $valor;
            $this->rechazarEnTodasLasRutas($datos, [
                'materias.0.datos.registro_1_anio', 'materias.0.datos.registro_2_nota',
            ]);
        }

        $datos = $this->datos('recalificacion');
        $datos['campos']['publicacion_fecha'] = ['2026-10-20'];
        $this->rechazarEnTodasLasRutas($datos, 'campos.publicacion_fecha');
    }

    public function test_un_anio_academico_no_se_confunde_con_una_nota_ni_una_longitud_de_texto(): void
    {
        $datos = $this->datos('ayudante');
        $datos['campos']['anio'] = '2026';
        $this->putJson(route('borradores-temporales.update', 'ayudante'), $datos + ['paso' => 2])->assertOk();
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk();
    }

    public function test_usa_el_dia_y_hora_de_ecuador_aunque_en_utc_ya_sea_manana(): void
    {
        $this->travelTo(Carbon::parse('2026-10-21T02:00:00Z'));
        $datos = $this->datos('recuperacion');
        $datos['campos']['historial_recuperacion'] = 'con_fecha';
        $datos['campos']['ultima_recuperacion'] = '2026-10-21';
        $this->rechazarEnTodasLasRutas($datos, 'campos.ultima_recuperacion');

        $datos = $this->datos('recalificacion');
        $datos['campos']['publicacion_fecha'] = '2026-10-20';
        $datos['campos']['publicacion_hora'] = '21:01';
        $this->rechazarEnTodasLasRutas($datos, 'campos.publicacion_hora');
        $datos['campos']['publicacion_hora'] = '21:00';
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk();

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$this->get(route('solicitudes.create', ['tramite' => 'recalificacion']))->getContent());
        $this->assertSame('2026-10-20', $dom->getElementById('campo-publicacion_fecha')->getAttribute('max'));
    }
}
