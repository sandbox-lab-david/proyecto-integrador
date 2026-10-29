<?php

namespace App\Services\Documentos;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
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
        $binario = $this->buscarLibreOffice();
        $carpeta = sys_get_temp_dir().DIRECTORY_SEPARATOR.'uees-pdf-'.Str::uuid();
        File::ensureDirectoryExists($carpeta);
        try {
            File::copy($rutaDocx, $carpeta.'/documento.docx');
            $rutaPerfil = str_replace('\\', '/', $carpeta).'/perfil';
            $perfil = 'file://'.(str_starts_with($rutaPerfil, '/') ? '' : '/').implode('/', array_map(
                fn ($segmento) => str_replace('%3A', ':', rawurlencode($segmento)),
                explode('/', $rutaPerfil),
            ));
            // El servidor web no siempre transmite a los procesos las rutas de Windows.
            // LibreOffice necesita un directorio temporal escribible también desde HTTP.
            $entorno = PHP_OS_FAMILY === 'Windows'
                ? ['TEMP' => $carpeta, 'TMP' => $carpeta]
                : ['TMPDIR' => $carpeta];
            if (PHP_OS_FAMILY === 'Windows') {
                foreach (['SystemRoot', 'WINDIR', 'USERPROFILE', 'APPDATA', 'LOCALAPPDATA', 'PROGRAMDATA', 'PATH', 'PATHEXT', 'COMSPEC'] as $variable) {
                    $valor = getenv($variable);
                    if (is_string($valor) && $valor !== '') $entorno[$variable] = $valor;
                }
            }
            $proceso = new Process([$binario, '-env:UserInstallation='.$perfil, '--headless', '--convert-to', 'pdf', '--outdir', $carpeta, $carpeta.'/documento.docx'], null, $entorno);
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
            throw new RuntimeException('No se pudo generar el PDF. Inténtalo de nuevo o descarga el documento Word.', 0, $error);
        }
    }

    private function buscarLibreOffice(): string
    {
        foreach ($this->candidatosLibreOffice(PHP_OS_FAMILY) as $candidato) {
            if (is_string($candidato) && is_file($candidato)) return $candidato;
        }

        throw new RuntimeException('La descarga PDF no está disponible en este momento. Puedes descargar el Word y guardarlo como PDF.');
    }

    /** Rutas en las que se busca LibreOffice según el sistema del servidor (PHP_OS_FAMILY). */
    private function candidatosLibreOffice(string $sistema): array
    {
        $buscador = new ExecutableFinder;

        return match ($sistema) {
            'Windows' => [
                (getenv('ProgramFiles') ?: 'C:/Program Files').'/LibreOffice/program/soffice.com',
                (getenv('ProgramFiles(x86)') ?: 'C:/Program Files (x86)').'/LibreOffice/program/soffice.com',
                getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA').'/Programs/LibreOffice/program/soffice.com' : null,
                $buscador->find('soffice.com'),
            ],
            // En macOS el instalador deja soffice dentro del paquete .app, fuera del PATH.
            'Darwin' => [
                '/Applications/LibreOffice.app/Contents/MacOS/soffice',
                getenv('HOME') ? getenv('HOME').'/Applications/LibreOffice.app/Contents/MacOS/soffice' : null,
                $buscador->find('soffice'),
                $buscador->find('libreoffice'),
            ],
            default => [$buscador->find('libreoffice'), $buscador->find('soffice'), '/usr/bin/libreoffice'],
        };
    }
}
