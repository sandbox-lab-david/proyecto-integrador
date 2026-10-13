<?php

namespace Tests\Feature;

use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\TramitesSimulados;
use Tests\TestCase;
use ZipArchive;

class DocumentoSolicitudTest extends TestCase
{
    public static function tramites(): array
    {
        return array_map(fn ($codigo) => [$codigo], array_keys(DocumentoSolicitud::PLANTILLAS));
    }

    private function datos(string $codigo): array
    {
        $catalogo = app(TramitesSimulados::class);
        $definicion = $catalogo->buscar($codigo);
        $valores = fn ($campos) => collect($campos)->mapWithKeys(fn ($c) => [$c['nombre'] => match ($c['tipo']) {
            'numero' => ($c['entero'] ?? false) ? (str_contains($c['nombre'], 'anio') ? '2025' : '2') : '80',
            'hora' => '08:30', 'fecha' => '2025-01-01', 'lista' => (string) array_key_first($c['opciones']), default => 'Dato declarado & comprobado',
        }])->all();
        return [
            'tramite' => $codigo, 'nombre' => 'Ana & Pérez', 'codigo' => '2026000012',
            'carrera' => 'Computación', 'correo' => 'ana@uees.edu.ec', 'celular' => '0991234567',
            'campos' => $valores($definicion['campos']), 'declaracion' => true,
            'materias' => array_map(fn ($m) => $m + ['datos' => $valores($definicion['campos_materia'])],
                array_slice($catalogo->materias(), 0, min(2, $definicion['max_materias']))),
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('tramites')]
    public function test_genera_cada_plantilla_sin_cambiar_el_original(string $codigo): void
    {
        $plantilla = storage_path('app/private/plantillas/'.DocumentoSolicitud::PLANTILLAS[$codigo]);
        $hash = hash_file('sha256', $plantilla);
        $datos = $this->datos($codigo);
        $ruta = sys_get_temp_dir().DIRECTORY_SEPARATOR.'uees-test-'.\Illuminate\Support\Str::uuid().'.docx';
        file_put_contents($ruta, app(DocumentoSolicitud::class)->generarWord($datos));
        try {
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($ruta));
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            $dom = new \DOMDocument;
            $this->assertTrue($dom->loadXML($xml));
            $texto = $dom->textContent;
            foreach (['Ana & Pérez', 'Computación', 'ana@uees.edu.ec', '0991234567'] as $valor) {
                $this->assertStringContainsString($valor, $texto);
            }
            foreach ($datos['materias'] as $m) {
                $this->assertStringContainsString($m['codigo'], $texto);
                $this->assertStringContainsString($m['nombre'], $texto);
            }
            $this->assertDoesNotMatchRegularExpression('/\[(?!Firma\])[^\]]+\]|X{8,}|__ de ______/', $texto);
            $this->assertSame($hash, hash_file('sha256', $plantilla));
            if ($codigo === 'suficiencia') {
                $this->assertStringNotContainsString('examen de gracia', $texto);
            }
        } finally {
            unlink($ruta);
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('tramites')]
    public function test_descarga_desde_el_formulario_general(string $codigo): void
    {
        $datos = $this->datos($codigo);
        $datos['materias'] = array_map(fn ($m) => ['id' => $m['id'], 'datos' => $m['datos']], $datos['materias']);
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="solicitud-'.$codigo.'.docx"');
    }

    public function test_rechaza_datos_incompletos_o_materias_ajenas_al_catalogo(): void
    {
        $datos = $this->datos('gracia');
        $datos['materias'] = [['id' => 'inventada', 'datos' => []]];
        $datos['campos']['motivo'] = '';
        $datos['declaracion'] = false;
        $this->postJson(route('solicitudes.documento'), $datos)->assertUnprocessable()
            ->assertJsonValidationErrors(['materias.0.id', 'campos.motivo', 'declaracion']);
    }

    public function test_recuperacion_rechaza_dos_materias_y_exige_fecha_condicional(): void
    {
        $datos = $this->datos('recuperacion');
        $datos['materias'] = array_map(fn ($m) => ['id' => $m['id'], 'datos' => []], app(TramitesSimulados::class)->materias());
        $datos['campos']['historial_recuperacion'] = 'con_fecha';
        $datos['campos']['ultima_recuperacion'] = '';
        $this->postJson(route('solicitudes.documento'), $datos)->assertUnprocessable()
            ->assertJsonValidationErrors(['materias', 'campos.ultima_recuperacion']);
    }
}
