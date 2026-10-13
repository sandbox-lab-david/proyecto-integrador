<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;

/**
 * Archivos con contenido real para las pruebas de subida. Los de
 * UploadedFile::fake() informan el tipo según su nombre, así que no sirven
 * para probar la validación por contenido.
 */
trait ArchivosDePrueba
{
    /** @var list<string> */
    private array $temporales = [];

    protected function tearDown(): void
    {
        foreach ($this->temporales as $ruta) {
            @unlink($ruta);
        }

        parent::tearDown();
    }

    private function archivo(string $nombre, string $contenido, int $error = UPLOAD_ERR_OK): UploadedFile
    {
        $ruta = tempnam(sys_get_temp_dir(), 'g2-');
        file_put_contents($ruta, $contenido);
        $this->temporales[] = $ruta;

        return new UploadedFile($ruta, $nombre, null, $error, true);
    }

    private function pdf(string $nombre = 'certificado.pdf', int $relleno = 0): UploadedFile
    {
        return $this->archivo(
            $nombre,
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n".str_repeat('0', $relleno)."\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n"
        );
    }

    private function png(string $nombre = 'captura.png'): UploadedFile
    {
        return $this->archivo($nombre, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
        ));
    }

    private function jpg(string $nombre = 'foto.jpg'): UploadedFile
    {
        return $this->archivo(
            $nombre,
            "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9"
        );
    }

    /** Una página web con nombre de PDF: lo que la validación por contenido debe frenar. */
    private function falsoPdf(string $nombre = 'certificado.pdf'): UploadedFile
    {
        return $this->archivo($nombre, '<html><body><script>alert(1)</script></body></html>');
    }
}
