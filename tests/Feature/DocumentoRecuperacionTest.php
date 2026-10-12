<?php

namespace Tests\Feature;

use App\Services\Solicitudes\DocumentoRecuperacion;
use App\Services\Solicitudes\TramitesSimulados;
use Tests\TestCase;
use ZipArchive;

class DocumentoRecuperacionTest extends TestCase
{
    public function test_llena_la_plantilla_sin_modificar_el_original(): void
    {
        $plantilla = storage_path('app/private/plantillas/EXAMEN DE RECUPERACION_.docx');
        $hash = hash_file('sha256', $plantilla);
        $destino = storage_path('app/private/verificacion-'.\Illuminate\Support\Str::uuid().'.docx');
        try {
            app(DocumentoRecuperacion::class)->llenar([
                'nombre' => 'Ana & Pérez', 'codigo' => '2026000012',
                'carrera' => 'Computación', 'correo' => 'ana@uees.edu.ec',
                'celular' => '0991234567',
                'materia' => app(TramitesSimulados::class)->materias()[0],
            ], $destino);
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($destino));
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            $documento = new \DOMDocument;
            $this->assertTrue($documento->loadXML($xml));
            $texto = $documento->textContent;
            foreach (['Ana & Pérez', '2026000012', 'Computación', 'ana@uees.edu.ec', '0991234567', 'MAT202', 'Cálculo II'] as $valor) {
                $this->assertStringContainsString($valor, $texto);
            }
            $this->assertStringNotContainsString('XXXXXXXXXX', $texto);
            $this->assertSame($hash, hash_file('sha256', $plantilla));
        } finally {
            unlink($destino);
        }
    }
}
