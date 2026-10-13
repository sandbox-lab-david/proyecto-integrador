<?php

namespace App\Services\Documentos;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use RuntimeException;
use Symfony\Component\Process\Process;

class WordService
{
    public function rellenarPlantilla(string $rutaPlantilla, array $valores, array $tablas = []): string
    {
        $carpeta = sys_get_temp_dir().DIRECTORY_SEPARATOR.'uees-word-'.Str::uuid();
        File::ensureDirectoryExists($carpeta);
        try {
            Settings::setTempDir($carpeta);
            Settings::setOutputEscapingEnabled(true);
            $plantilla = new TemplateProcessor($rutaPlantilla);
            foreach ($tablas as $marcador => $filas) {
                $plantilla->cloneRowAndSetValues($marcador, $filas);
            }
            $plantilla->setValues($valores);
            $ruta = $carpeta.'/documento.docx';
            $plantilla->saveAs($ruta);
            return $ruta;
        } catch (\Throwable $error) {
            File::deleteDirectory($carpeta);
            throw $error;
        }
    }

    public function convertirAPdf(string $rutaDocx): string
    {
        $binario = PHP_OS_FAMILY === 'Windows'
            ? 'C:/Program Files/LibreOffice/program/soffice.com' : '/usr/bin/libreoffice';
        if (! is_file($binario)) {
            throw new RuntimeException('Instala LibreOffice en el servidor para generar el PDF. Puedes seguir descargando el Word.');
        }
        $carpeta = sys_get_temp_dir().DIRECTORY_SEPARATOR.'uees-pdf-'.Str::uuid();
        File::ensureDirectoryExists($carpeta);
        try {
            File::copy($rutaDocx, $carpeta.'/documento.docx');
            $rutaPerfil = str_replace('\\', '/', $carpeta).'/perfil';
            $perfil = 'file://'.(str_starts_with($rutaPerfil, '/') ? '' : '/').implode('/', array_map(
                fn ($segmento) => str_replace('%3A', ':', rawurlencode($segmento)),
                explode('/', $rutaPerfil),
            ));
            $proceso = new Process([$binario, '-env:UserInstallation='.$perfil, '--headless', '--convert-to', 'pdf', '--outdir', $carpeta, $carpeta.'/documento.docx']);
            $proceso->setTimeout(60);
            $proceso->mustRun();
            $ruta = $carpeta.'/documento.pdf';
            if (! is_file($ruta) || ! str_starts_with(file_get_contents($ruta), '%PDF-')) {
                throw new RuntimeException('No se pudo convertir el Word a PDF.');
            }
            File::delete($carpeta.'/documento.docx');
            File::deleteDirectory($carpeta.'/perfil');
            return $ruta;
        } catch (\Throwable $error) {
            File::deleteDirectory($carpeta);
            throw new RuntimeException('No se pudo generar el PDF: '.$error->getMessage(), 0, $error);
        }
    }
}
