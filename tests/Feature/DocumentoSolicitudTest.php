<?php

namespace Tests\Feature;

use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\TramitesSimulados;
use App\Services\Documentos\WordService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class DocumentoSolicitudTest extends TestCase
{
    use FacultadesG1Prueba;
    use MateriasCatalogoPrueba;

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
        $campos = $valores($definicion['campos']);
        if ($codigo === 'cambio-modalidad') {
            $campos['modalidad_actual'] = 'Presencial';
            $campos['modalidad_destino'] = 'En línea';
        }
        return [
            'tramite' => $codigo, 'nombre' => 'Ana & Pérez', 'codigo' => '2026000012',
            'carrera' => 'Ingeniería en Computación', 'correo' => 'ana@uees.edu.ec', 'celular' => '0991234567',
            'cedula' => '0912345678', 'facultad' => 'Ingeniería', 'modalidad' => 'Presencial', 'periodo' => '2026-I',
            'campos' => $campos, 'declaracion' => true,
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
            if ($codigo === 'gracia') {
                $xp = new \DOMXPath($dom);
                $xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                $alineacion = $xp->query('//w:body/w:p[contains(., "Correo Institucional:")]/w:pPr/w:jc')->item(0);
                $this->assertNotNull($alineacion);
                $this->assertSame('left', $alineacion->getAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'val'));
            }
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
        $respuesta = $this->postJson(route('solicitudes.documento'), $datos)->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="solicitud-'.$codigo.'.docx"');
        $version = $respuesta->headers->get('X-Documento-Version');
        $this->assertTrue(\Illuminate\Support\Str::isUuid($version));
        $this->assertSame($version, $this->get(route('solicitudes.create', ['tramite' => $codigo]))->viewData('versionDocumento'));
    }

    public function test_rechaza_datos_incompletos_de_materias_declaradas(): void
    {
        $datos = $this->datos('gracia');
        $datos['materias'] = [['id' => 'inventada', 'datos' => []]];
        $datos['campos']['motivo'] = '';
        $datos['declaracion'] = false;
        $this->postJson(route('solicitudes.documento'), $datos)->assertUnprocessable()
            ->assertJsonValidationErrors(['materias.0.codigo', 'materias.0.nombre', 'materias.0.periodo', 'materias.0.paralelo', 'materias.0.docente', 'campos.motivo', 'declaracion']);
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

    public function test_descarga_pdf_y_limpia_los_archivos_intermedios(): void
    {
        Storage::fake('local');
        $carpeta = sys_get_temp_dir().DIRECTORY_SEPARATOR.'uees-test-pdf-'.\Illuminate\Support\Str::uuid();
        File::ensureDirectoryExists($carpeta);
        $pdf = "%PDF-1.4\nDocumento convertido de prueba.\n%%EOF\n";
        $rutaWord = null;
        $word = \Mockery::mock(WordService::class)->makePartial();
        $word->shouldReceive('convertirAPdf')->once()->andReturnUsing(function (string $ruta) use ($carpeta, $pdf, &$rutaWord) {
            $rutaWord = $ruta;
            $this->assertFileExists($ruta);
            $this->assertStringStartsWith('PK', file_get_contents($ruta));
            file_put_contents($carpeta.'/documento.pdf', $pdf);
            return $carpeta.'/documento.pdf';
        });
        $this->app->instance(WordService::class, $word);
        try {
            $respuesta = $this->postJson(route('solicitudes.documento'), $this->datos('gracia') + ['formato' => 'pdf'])
                ->assertOk()->assertHeader('Content-Type', 'application/pdf')
                ->assertHeader('Content-Disposition', 'attachment; filename="solicitud-gracia.pdf"');
            $this->assertSame($pdf, $respuesta->getContent());
            // El PDF descargado se conserva junto al Word para el expediente.
            $guardado = app(\App\Services\Solicitudes\DocumentosTemporales::class)->buscar('gracia');
            $this->assertSame($pdf, Storage::disk('local')->get($guardado['ruta_pdf']));
            Storage::disk('local')->assertExists($guardado['ruta']);
            $this->assertDirectoryDoesNotExist($carpeta);
            $this->assertDirectoryDoesNotExist(dirname($rutaWord));
        } finally {
            File::deleteDirectory($carpeta);
        }
    }

    public function test_si_falla_la_conversion_no_devuelve_un_pdf_falso_y_se_puede_descargar_word(): void
    {
        Storage::fake('local');
        $rutaWord = null;
        $word = \Mockery::mock(WordService::class)->makePartial();
        $word->shouldReceive('convertirAPdf')->once()->andReturnUsing(function (string $ruta) use (&$rutaWord) {
            $rutaWord = $ruta;
            throw new \RuntimeException('La descarga PDF no está disponible en este momento.');
        });
        $this->app->instance(WordService::class, $word);
        $datos = $this->datos('gracia');
        $this->postJson(route('solicitudes.documento'), $datos + ['formato' => 'pdf'])
            ->assertStatus(503)->assertJsonPath('message', 'La descarga PDF no está disponible en este momento.');
        $this->assertDirectoryDoesNotExist(dirname($rutaWord));
        $this->postJson(route('solicitudes.documento'), $datos)->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="solicitud-gracia.docx"');
        $this->assertNull(app(\App\Services\Solicitudes\DocumentosTemporales::class)->buscar('gracia')['ruta_pdf']);
    }

    public function test_busca_libreoffice_en_las_rutas_de_instalacion_de_cada_sistema(): void
    {
        $candidatos = fn (string $sistema) => (new \ReflectionMethod(WordService::class, 'candidatosLibreOffice'))
            ->invoke(new WordService, $sistema);

        $this->assertContains('/Applications/LibreOffice.app/Contents/MacOS/soffice', $candidatos('Darwin'));
        $this->assertContains('/usr/bin/libreoffice', $candidatos('Linux'));
        $this->assertNotEmpty(array_filter($candidatos('Windows'),
            fn ($ruta) => is_string($ruta) && str_ends_with($ruta, '/LibreOffice/program/soffice.com')));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('tramites')]
    public function test_cedula_solo_es_obligatoria_en_homologacion(string $codigo): void
    {
        Storage::fake('local');
        $datos = $this->datos($codigo);
        unset($datos['cedula']);
        $this->putJson(route('borradores-temporales.update', $codigo), $datos + ['paso' => 1])->assertOk();
        foreach ([null, ''] as $valor) {
            $entrada = $datos;
            if ($valor !== null) $entrada['cedula'] = $valor;
            $respuesta = $this->postJson(route('solicitudes.documento'), $entrada);
            if ($codigo === 'homologacion') {
                $respuesta->assertUnprocessable()->assertJsonValidationErrors('cedula');
            } else {
                $respuesta->assertOk()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            }
        }
    }
}
