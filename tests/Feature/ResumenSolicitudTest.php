<?php

namespace Tests\Feature;

use App\Models\EstadoSolicitud;
use App\Models\Solicitud;
use App\Models\TipoTramite;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * <x-resumen-solicitud> (contrato C57).
 */
class ResumenSolicitudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogoInformativoSeeder::class);
    }

    private function solicitud(string $tramite, array $detalle, bool $enviada = true): Solicitud
    {
        $factory = Solicitud::factory();

        return ($enviada ? $factory->enviada() : $factory)->create([
            'codigo' => 'SOL-2026-00042',
            'tipo_tramite_id' => TipoTramite::where('codigo', $tramite)->firstOrFail()->id,
            'estado_solicitud_id' => EstadoSolicitud::where('codigo', $enviada ? 'enviada' : 'borrador')->firstOrFail()->id,
            'detalle' => $detalle,
        ]);
    }

    private function recuperacion(array $campos = []): Solicitud
    {
        return $this->solicitud('examen_recuperacion', [
            'tramite' => 'recuperacion',
            'nombre' => 'Estudiante Ficticio',
            'codigo' => '2026000001',
            'cedula' => '0900000000',
            'carrera' => 'Computación',
            'correo' => 'ficticio@uees.edu.ec',
            'celular' => '0990000000',
            'campos' => $campos + [
                'gpa_general' => '82.5',
                'historial_recuperacion' => 'con_fecha',
                'ultima_recuperacion' => '2026-03-14',
                'observacion' => "Primera línea\nSegunda línea",
            ],
            'materias' => [[
                'id' => '7', 'codigo' => 'MAT202', 'nombre' => 'Cálculo II', 'periodo' => 'Ordinario I 2026',
                'paralelo' => 'A', 'docente' => 'Docente de prueba A',
                'datos' => ['estado' => 'reprobada', 'nota' => '55', 'asistencia' => '90'],
            ]],
            'clave_envio' => '00000000-0000-0000-0000-000000000000',
            'envio_usuario_id' => 99,
        ]);
    }

    public function test_muestra_encabezado_datos_del_estudiante_campos_y_materias_con_etiquetas_del_catalogo(): void
    {
        $solicitud = $this->recuperacion();

        $this->blade('<x-resumen-solicitud :solicitud="$solicitud" />', ['solicitud' => $solicitud])
            ->assertSee('SOL-2026-00042')
            ->assertSee($solicitud->tipoTramite->nombre)
            ->assertSee('Enviada el '.$solicitud->enviada_at->timezone('America/Guayaquil')->format('d/m/Y H:i'))
            ->assertSeeInOrder(['Datos del estudiante', 'Nombres y apellidos', 'Estudiante Ficticio', 'Cédula', '0900000000', 'Celular', '0990000000'])
            ->assertSeeInOrder(['Datos del trámite', 'GPA general', '82.5', 'Exámenes de recuperación anteriores',
                'He rendido uno y conozco la fecha', 'Fecha del último examen de recuperación', '14/03/2026', 'Observación'])
            ->assertSeeInOrder(['Materias (1)', 'MAT202 · Cálculo II', 'Ordinario I 2026 · Paralelo A · Docente de prueba A',
                'Estado académico declarado', 'Reprobada', 'Nota de la materia', '55', 'Asistencia (%)', '90'])
            ->assertDontSee('clave_envio')
            ->assertDontSee('00000000-0000-0000-0000-000000000000');
    }

    public function test_omite_un_campo_condicional_cuya_condicion_no_se_cumple(): void
    {
        $solicitud = $this->recuperacion(['historial_recuperacion' => 'nunca']);

        $this->blade('<x-resumen-solicitud :solicitud="$solicitud" />', ['solicitud' => $solicitud])
            ->assertSee('Nunca he rendido un examen de recuperación')
            ->assertDontSee('Fecha del último examen de recuperación')
            ->assertDontSee('14/03/2026');
    }

    public function test_escapa_lo_declarado_y_muestra_campos_que_el_catalogo_ya_no_define(): void
    {
        $solicitud = $this->recuperacion(['observacion' => '<script>alert(1)</script>', 'campo_retirado' => 'Valor antiguo']);

        $this->blade('<x-resumen-solicitud :solicitud="$solicitud" />', ['solicitud' => $solicitud])
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSeeInOrder(['Campo retirado', 'Valor antiguo']);
    }

    public function test_tercer_registro_muestra_los_registros_anteriores_de_cada_materia(): void
    {
        $solicitud = $this->solicitud('tercer_registro', [
            'tramite' => 'tercer-registro',
            'nombre' => 'Estudiante Ficticio',
            'campos' => [],
            'materias' => [[
                'id' => '3', 'codigo' => 'FIS101', 'nombre' => 'Física I',
                'datos' => ['registro_1_anio' => '2024', 'registro_1_periodo' => 'Ordinario I', 'registro_1_nota' => '40',
                    'registro_2_anio' => '2025', 'registro_2_periodo' => 'Ordinario II', 'registro_2_nota' => '45'],
            ]],
        ]);

        $this->blade('<x-resumen-solicitud :solicitud="$solicitud" />', ['solicitud' => $solicitud])
            ->assertSee('Este trámite no tiene datos adicionales.')
            ->assertSeeInOrder(['FIS101 · Física I', 'Año del registro 1', '2024', 'Nota del registro 1', '40',
                'Periodo del registro 2', 'Ordinario II', 'Nota del registro 2', '45']);
    }

    public function test_sin_encabezado_y_para_un_borrador(): void
    {
        $solicitud = $this->solicitud('examen_recuperacion', ['tramite' => 'recuperacion'], enviada: false);

        $this->blade('<x-resumen-solicitud :solicitud="$solicitud" />', ['solicitud' => $solicitud])
            ->assertSee('Borrador sin enviar')
            ->assertSee('La solicitud no tiene datos del estudiante.')
            ->assertDontSee('Materias (');

        $this->blade('<x-resumen-solicitud :solicitud="$solicitud" :encabezado="false" class="resumen-solicitud--sin-marco" />', ['solicitud' => $solicitud])
            ->assertSee('class="resumen-solicitud resumen-solicitud--sin-marco"', false)
            ->assertDontSee('SOL-2026-00042')
            ->assertDontSee('Borrador sin enviar');
    }
}
