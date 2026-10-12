<?php

namespace App\Services\Solicitudes;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;

class DocumentoRecuperacion
{
    public function generarWord(array $datos): string
    {
        if (! class_exists(TemplateProcessor::class)) {
            throw new RuntimeException('La generación del Word requiere PhpWord. El administrador debe integrar esta dependencia; no se necesitan cambios en config.');
        }
        $directorio = storage_path('app/private/documentos-temporales/'.Str::uuid());
        File::ensureDirectoryExists($directorio);
        try {
            $ruta = $directorio.'/solicitud-recuperacion.docx';
            $this->llenar($datos, $ruta);
            return file_get_contents($ruta);
        } finally {
            File::deleteDirectory($directorio);
        }
    }

    public function generar(array $datos): string
    {
        if (! class_exists(TemplateProcessor::class) || ! class_exists(\Dompdf\Dompdf::class)) {
            throw new RuntimeException('Las dependencias PhpWord y Dompdf deben ser integradas por el administrador del repositorio para generar documentos.');
        }
        $directorio = storage_path('app/private/documentos-temporales/'.Str::uuid());
        File::ensureDirectoryExists($directorio);

        try {
            $docx = $directorio.'/solicitud-recuperacion.docx';
            $this->llenar($datos, $docx);
            $pdf = $directorio.'/solicitud-recuperacion.pdf';
            $word = IOFactory::load($docx, 'Word2007');
            Settings::setOutputEscapingEnabled(false);
            (new \PhpOffice\PhpWord\Writer\PDF\DomPDF($word))->save($pdf);
            if (! is_file($pdf) || ! str_starts_with(file_get_contents($pdf), '%PDF-')) {
                throw new RuntimeException('No se pudo convertir el documento a PDF.');
            }

            return file_get_contents($pdf);
        } finally {
            File::deleteDirectory($directorio);
        }
    }

    public function llenar(array $datos, string $destino): void
    {
        $plantilla = storage_path('app/private/plantillas/EXAMEN DE RECUPERACION_.docx');
        if (! is_file($plantilla) || ! copy($plantilla, $destino)) {
            throw new RuntimeException('No se encontró la plantilla de recuperación.');
        }
        $zip = new ZipArchive;
        if ($zip->open($destino) !== true) {
            throw new RuntimeException('No se pudo abrir la plantilla Word.');
        }
        try {
            $xml = new DOMDocument;
            $xml->loadXML($zip->getFromName('word/document.xml'), LIBXML_NONET);
            $xpath = new DOMXPath($xml);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $reemplazos = [
                '[Nombres y Apellidos]' => '${nombre}',
                '[Nombre de la carrera]' => '${carrera}',
                'XXXXXXXXXX' => '${codigo}',
                'Samborondón, __ de ______ de ______' => 'Samborondón, ${fecha}',
                'Correo Institucional: @uees.edu.ec' => 'Correo Institucional: ${correo}',
                'Celular: XXXXXXXXXX' => '   Celular: ${celular}',
            ];
            foreach ($xpath->query('//w:p') as $parrafo) {
                $nodos = $xpath->query('.//w:t', $parrafo);
                $texto = '';
                foreach ($nodos as $nodo) {
                    $texto .= $nodo->textContent;
                }
                $nuevo = strtr($texto, $reemplazos);
                if ($nuevo !== $texto && $nodos->length) {
                    $primero = $nodos->item(0);
                    while ($primero->firstChild) {
                        $primero->removeChild($primero->firstChild);
                    }
                    $primero->appendChild($xml->createTextNode($nuevo));
                    for ($i = 1; $i < $nodos->length; $i++) {
                        $nodos->item($i)->nodeValue = '';
                    }
                }
            }
            $celdas = $xpath->query('(//w:tbl)[1]/w:tr[2]/w:tc');
            if ($celdas->length !== 5) {
                throw new RuntimeException('La tabla de la plantilla cambió: se esperan cinco columnas.');
            }
            $materia = $datos['materia'];
            $valores = ['${materia_codigo}', '${materia_nombre}', '${materia_periodo}', '${materia_anio}', '${materia_docente}'];
            foreach ($celdas as $i => $celda) {
                $parrafo = $xpath->query('./w:p', $celda)->item(0);
                $run = $xml->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:r');
                $texto = $xml->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t');
                $texto->appendChild($xml->createTextNode($valores[$i]));
                $run->appendChild($texto);
                $parrafo->appendChild($run);
            }
            $zip->addFromString('word/document.xml', $xml->saveXML());
        } finally {
            $zip->close();
        }
        // Adapta los espacios originales en una copia; PhpWord completa los marcadores.
        Settings::setTempDir(dirname($destino));
        Settings::setOutputEscapingEnabled(true);
        $procesador = new TemplateProcessor($destino);
        $procesador->setValues([
            'nombre' => $datos['nombre'], 'codigo' => $datos['codigo'],
            'carrera' => $datos['carrera'], 'correo' => $datos['correo'],
            'celular' => $datos['celular'],
            'fecha' => now()->locale('es')->translatedFormat('j \\d\\e F \\d\\e Y'),
            'materia_codigo' => $materia['codigo'], 'materia_nombre' => $materia['nombre'],
            'materia_periodo' => $materia['periodo'].' / '.$materia['paralelo'],
            'materia_anio' => preg_match('/\\b(20\\d{2})\\b/', $materia['periodo'], $anio) ? $anio[1] : '',
            'materia_docente' => $materia['docente'],
        ]);
        $procesador->saveAs($destino);
    }
}
