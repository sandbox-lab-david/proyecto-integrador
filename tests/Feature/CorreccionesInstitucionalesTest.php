<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\CarreraEstudiante;
use App\Models\EstadoSolicitud;
use App\Models\Periodo;
use App\Models\User;
use App\Services\Archivos\FirmadosTemporales;
use App\Services\Archivos\RespaldosTemporales;
use App\Services\Solicitudes\DocumentosTemporales;
use App\Services\Solicitudes\EnvioSolicitud;
use App\Services\Solicitudes\TramitesSimulados;
use Database\Seeders\CatalogoInformativoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CorreccionesInstitucionalesTest extends TestCase
{
    use ArchivosDePrueba, RefreshDatabase, RequisitosPrologPrueba;

    private function prepararDocumentos(array $datos): array
    {
        app(DocumentosTemporales::class)->guardar('gracia', 'Word de prueba', $datos);
        app(FirmadosTemporales::class)->guardar($this->pdf(), 'gracia');
        $datos['clave_envio'] = app(DocumentosTemporales::class)->buscar('gracia')['version'];
        $datos['requisitos'] = [];
        foreach (app(TramitesSimulados::class)->buscar('gracia')['documentos'] as $documento) {
            if ($documento['obligatorio']) {
                $archivo = app(RespaldosTemporales::class)->agregar($this->pdf(), 'gracia');
                $datos['requisitos'][$archivo['id']] = $documento['id'];
            }
        }
        return $datos;
    }

    public function test_reenvio_real_conserva_expediente_y_registra_estado_anterior_correcto(): void
    {
        $this->discoTemporal();
        $this->seed(CatalogoInformativoSeeder::class);
        $usuario = User::factory()->create(['tipo' => 'estudiante', 'activo' => true]);
        $this->actingAs($usuario);
        $facultad = DB::table('facultades')->insertGetId(['codigo' => 'ING', 'nombre' => 'Ingeniería', 'activo' => true]);
        $carrera = Carrera::create(['facultad_id' => $facultad, 'codigo' => 'COM', 'nombre' => 'Computación', 'nivel' => 'grado', 'activo' => true]);
        $periodo = Periodo::create(['codigo' => 'ACTUAL', 'nombre' => 'Período actual', 'tipo' => 'ordinario', 'fecha_inicio' => today()->subDays(10), 'fecha_fin' => today()->addDays(30), 'semana_primer_parcial' => 4, 'activo' => true]);
        CarreraEstudiante::create(['user_id' => $usuario->id, 'carrera_id' => $carrera->id, 'periodo_id' => $periodo->id, 'estado' => 'activo']);
        $datos = ['tramite' => 'gracia', 'nombre' => $usuario->name, 'codigo' => '20260001',
            'carrera' => $carrera->nombre, 'periodo' => $periodo->codigo, 'correo' => $usuario->email,
            'celular' => '0991234567', 'declaracion' => true, 'campos' => ['motivo' => 'Solicitud inicial'],
            'materias' => [app(TramitesSimulados::class)->materias()[0] + ['datos' => ['nota' => '70', 'asistencia' => '80']]]];
        $envio = app(EnvioSolicitud::class);
        $inicial = $envio->enviar($usuario, $this->prepararDocumentos($datos));
        $rutaAnterior = $inicial->documentosGenerados()->firstOrFail()->ruta_docx;
        $observada = EstadoSolicitud::create(['codigo' => 'observada', 'nombre' => 'Observada']);
        $inicial->forceFill(['estado_solicitud_id' => $observada->id])->save();
        $inicial = $inicial->fresh();
        $base = EnvioSolicitud::revision($inicial);

        $request = Request::create('/', 'POST', ['solicitud_correccion' => $inicial->id]);
        $request->setLaravelSession($this->app['session.store']);
        $this->app->instance('request', $request);
        $datos['campos']['motivo'] = 'Motivo corregido';
        $corregida = $envio->enviar($usuario, $this->prepararDocumentos($datos), $inicial, $base);

        $this->assertSame($inicial->id, $corregida->id);
        $this->assertSame($inicial->codigo, $corregida->codigo);
        $this->assertDatabaseCount('solicitudes', 1);
        $this->assertSame('enviada', $corregida->fresh()->estadoSolicitud->codigo);
        $this->assertSame([1, 2], $corregida->documentosGenerados()->orderBy('version')->pluck('version')->all());
        Storage::disk('local')->assertExists($rutaAnterior);
        $this->assertDatabaseHas('historial_solicitud', ['solicitud_id' => $inicial->id,
            'estado_anterior' => 'observada', 'estado_nuevo' => 'enviada', 'visible_estudiante' => true]);
    }
}
