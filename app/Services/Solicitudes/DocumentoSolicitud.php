<?php

namespace App\Services\Solicitudes;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/** Completa copias de las plantillas oficiales sin modificar sus originales. */
class DocumentoSolicitud
{
    public const PLANTILLAS = [
        'alcance-homologacion' => 'ALCANCE DE HOMOLOGACION.docx',
        'ayudante' => 'AYUDANTE DE CATEDRA.docx',
        'cambio-carrera' => 'CAMBIO DE CARRERA.docx',
        'cambio-malla' => 'CAMBIO DE MALLA_PENSUM.docx',
        'cambio-modalidad' => 'CAMBIO DE MODALIDAD.docx',
        'gracia' => 'EXAMEN DE GRACIA.docx',
        'recuperacion' => 'EXAMEN DE RECUPERACION_.docx',
        'suficiencia' => 'EXAMEN DE SUFICIENCIA.docx',
        'supletorio' => 'EXAMEN DE SUPLETORIO.docx',
        'homologacion' => 'HOMOLOGACION.docx',
        'incompleto' => 'INCOMPLETO.docx',
        'person-to-person' => 'PERSON TO PERSON.docx',
        'recalificacion' => 'RECALIFICACION DE EXAMEN.docx',
        'registro-extemporaneo' => 'REGISTRO EXTEMPORANEO.docx',
        'reincorporacion' => 'REINCORPORACION A CARRERA.docx',
        'retiro-carrera' => 'RETIRO DE CARRERA.docx',
        'retiro-materia' => 'RETIRO DE MATERIA.docx',
        'retiro-universidad' => 'RETIRO DE UNIVERSIDAD.docx',
        'retiro-extemporaneo' => 'RETIRO EXTEMPORANEO.docx',
        'tercer-registro' => 'TERCER REGISTRO.docx',
    ];

    private const NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private array $valores = [];

    private function marcador(string $valor): string
    {
        $nombre = 'valor_'.count($this->valores);
        $this->valores[$nombre] = $valor;
        return '${'.$nombre.'}';
    }

    public function generarWord(array $datos): string
    {
        $this->valores = [];
        $plantilla = self::PLANTILLAS[$datos['tramite']] ?? null;
        if ($plantilla === null) {
            throw new RuntimeException('No existe una plantilla para este trámite.');
        }
        $directorio = sys_get_temp_dir().DIRECTORY_SEPARATOR.'uees-plantilla-'.Str::uuid();
        File::ensureDirectoryExists($directorio);
        try {
            $ruta = $directorio.'/solicitud.docx';
            if (! copy(storage_path('app/private/plantillas/'.$plantilla), $ruta)) {
                throw new RuntimeException('No se encontró la plantilla del trámite.');
            }
            $zip = new ZipArchive;
            if ($zip->open($ruta) !== true) {
                throw new RuntimeException('No se pudo abrir la plantilla Word.');
            }
            try {
                $xml = new DOMDocument;
                if (! $xml->loadXML($zip->getFromName('word/document.xml'), LIBXML_NONET)) {
                    throw new RuntimeException('La plantilla Word contiene XML inválido.');
                }
                $this->llenar($xml, $datos);
                $this->ajustarCopia($xml);
                if (! $zip->addFromString('word/document.xml', $xml->saveXML())) {
                    throw new RuntimeException('No se pudo completar el documento Word.');
                }
            } finally {
                $zip->close();
            }
            // DOM prepara marcadores en una copia conservando los runs originales.
            // PhpWord realiza el llenado definitivo mediante el contrato C50.
            $relleno = app(\App\Services\Documentos\WordService::class)->rellenarPlantilla($ruta, $this->valores);
            try {
                return file_get_contents($relleno);
            } finally {
                File::deleteDirectory(dirname($relleno));
            }
        } finally {
            File::deleteDirectory($directorio);
        }
    }

    private function llenar(DOMDocument $xml, array $datos): void
    {
        $xp = new DOMXPath($xml);
        $xp->registerNamespace('w', self::NS);
        $codigo = $datos['tramite'];
        $campos = $datos['campos'] ?? [];
        $materias = $datos['materias'];
        $lista = implode('; ', array_map(fn ($m) => $m['codigo'].' — '.$m['nombre'], $materias));
        $definicion = app(TramitesSimulados::class)->buscar($codigo);
        $motivo = $campos['motivo'] ?? $campos['circunstancia'] ?? $campos['fundamento'] ?? $campos['detalle'] ?? $campos['reclamo'] ?? '';
        $carreraOcurrencia = 0;
        $anioOcurrencia = 0;
        foreach ($xp->query('//w:body//w:p') as $p) {
            $texto = $this->texto($xp, $p);
            $reemplazos = [
                '[Nombres y Apellidos]' => $datos['nombre'], '[NOMBRES Y APELLIDOS]' => $datos['nombre'],
                '[código estudiantil]' => 'código estudiantil '.$datos['codigo'],
                '[XXXXXXXXXX]' => $datos['cedula'] ?? $campos['cedula'] ?? '',
                'XXXXXXXXXX' => str_contains($texto, 'Celular:') ? $datos['celular'] : $datos['codigo'],
                '[CARRERA]' => $datos['carrera'], '[XXXXXXXXX]' => $campos['institucion'] ?? '',
                '[XXXXXXXX]' => $campos['carrera_destino'] ?? '',
                'Samborondón, __ de ______ de ______' => 'Samborondón, '.now()->locale('es')->translatedFormat('j \\d\\e F \\d\\e Y'),
                'Correo Institucional: @uees.edu.ec' => 'Correo Institucional: '.$datos['correo'],
                '[Explicación detallada del motivo]' => $motivo,
                '[Código y Nombre de la materia]' => $lista,
                '[Periodo]' => $campos['periodo'] ?? '',
                'parcial _______' => 'parcial '.(($campos['parcial'] ?? '') === 'primer_parcial' ? 'primero' : 'segundo'),
                'Código de la asignatura, Nombre de la asignatura' => $lista,
                'Código de la Materia Y Nombre de la Materia:_________________________' => 'Materias solicitadas: '.$lista,
            ];
            // Los marcadores repetidos de carrera/año tienen significados distintos.
            while (str_contains($this->texto($xp, $p), '[Nombre de la carrera]')) {
                $valor = $datos['carrera'];
                if ($carreraOcurrencia++ > 0) {
                    $valor = ($campos['carrera_destino'] ?? '') ?: $datos['carrera'];
                }
                $this->reemplazar($xp, $p, '[Nombre de la carrera]', $valor, true);
            }
            while (str_contains($this->texto($xp, $p), '[Año]')) {
                $valor = $codigo === 'cambio-malla'
                    ? ($anioOcurrencia++ === 0 ? $campos['pensum_actual'] : $campos['malla_destino'])
                    : ($campos['anio'] ?? '');
                $this->reemplazar($xp, $p, '[Año]', $valor, true);
            }
            if ($codigo === 'suficiencia') {
                $reemplazos['examen de gracia'] = 'examen de suficiencia';
            }
            if ($codigo === 'cambio-carrera' && mb_strtolower(trim($campos['nuevo_codigo'] ?? '')) !== 'sí' && mb_strtolower(trim($campos['nuevo_codigo'] ?? '')) !== 'si') {
                $reemplazos[' y la creación de un código nuevo'] = '';
            }
            if ($codigo === 'registro-extemporaneo' && ! in_array(mb_strtolower(trim($campos['oyente'] ?? '')), ['si', 'sí'], true)) {
                $reemplazos['Cabe recalcar que a las mencionadas asignaturas he asistido de oyente hasta la presente, así mismo, el motivo'] = 'El motivo';
            }
            foreach ($reemplazos as $buscar => $valor) {
                $this->reemplazar($xp, $p, $buscar, (string) $valor);
            }
            // Plantillas sin tabla: completa sus líneas conservando los encabezados.
            $etiquetas = ['Código de Materia' => 'codigo', 'Nombre de la Materia' => 'nombre', 'Período/Sesión' => 'periodo', 'Año' => 'anio', 'Código de la materia' => 'codigo', 'Nombre de la materia' => 'nombre', 'Paralelo' => 'paralelo', 'Periodo' => 'periodo'];
            foreach ($etiquetas as $etiqueta => $clave) {
                if (trim($texto) === $etiqueta && $p->parentNode->localName !== 'tc') {
                    $valores = array_map(fn ($m) => $clave === 'anio' ? $this->anio($m) : $m[$clave], $materias);
                    $this->reemplazar($xp, $p, $etiqueta, $etiqueta.': '.implode('; ', $valores));
                }
            }
        }
        foreach ($xp->query('//w:tbl') as $tabla) {
            $filas = $xp->query('./w:tr', $tabla);
            $celdas = $xp->query('./w:tc', $filas->item(0));
            if ($celdas->length === 5) {
                $modelo = $filas->item(1);
                foreach ($materias as $m) {
                    $fila = $modelo->cloneNode(true);
                    foreach ([$m['codigo'], $m['nombre'], $m['periodo'].' / '.$m['paralelo'], $this->anio($m), $m['docente']] as $i => $valor) {
                        $this->celda($xp, $xp->query('./w:tc', $fila)->item($i), $valor);
                    }
                    $tabla->insertBefore($fila, $modelo);
                }
                $tabla->removeChild($modelo);
            } elseif ($codigo === 'tercer-registro') {
                // Repite la tabla de registros para cada materia seleccionada.
                foreach ($materias as $m) {
                    $titulo = $xml->createElementNS(self::NS, 'w:p');
                    $this->agregarTexto($titulo, $m['codigo'].' — '.$m['nombre']);
                    $tabla->parentNode->insertBefore($titulo, $tabla);
                    $copia = $tabla->cloneNode(true);
                    foreach ([1, 2] as $registro) {
                        $fila = $xp->query('./w:tr', $copia)->item($registro);
                        foreach (['anio', 'periodo', 'nota'] as $i => $clave) {
                            $this->celda($xp, $xp->query('./w:tc', $fila)->item($i + 1), $m['datos']['registro_'.$registro.'_'.$clave]);
                        }
                    }
                    $tabla->parentNode->insertBefore($copia, $tabla);
                }
                $tabla->parentNode->removeChild($tabla);
            }
        }
        // Conserva también datos declarados que no tienen un espacio en la plantilla.
        $ancla = null;
        foreach ($xp->query('//w:body/w:p') as $p) {
            if (str_starts_with(trim($this->texto($xp, $p)), 'Atentamente')) {
                $ancla = $p;
                break;
            }
        }
        if ($ancla) {
            $lineas = [];
            foreach (['cedula', 'facultad', 'modalidad', 'periodo'] as $campoPersonal) {
                if (filled($datos[$campoPersonal] ?? null)) {
                    $lineas[] = DatosFormularioSolicitud::PERSONALES[$campoPersonal][0].': '.$datos[$campoPersonal];
                }
            }
            if ($codigo === 'homologacion') {
                $lineas[] = 'Código estudiantil: '.$datos['codigo'];
            }
            foreach ($materias as $m) {
                $lineas[] = $m['codigo'].' — Periodo: '.$m['periodo'].'; paralelo: '.$m['paralelo'].'; docente: '.$m['docente'];
                $contenido = $xml->textContent;
                foreach ($this->valores as $clave => $valor) {
                    $contenido = str_replace('${'.$clave.'}', $valor, $contenido);
                }
                if (! str_contains($contenido, $m['codigo'])) {
                    $lineas[] = 'Materia solicitada: '.$m['codigo'].' — '.$m['nombre'];
                }
            }
            foreach ($definicion['campos'] as $campo) {
                $valor = $campos[$campo['nombre']] ?? '';
                if ($valor !== '') {
                    $lineas[] = $campo['etiqueta'].': '.($campo['opciones'][$valor] ?? $valor);
                }
            }
            foreach ($materias as $m) {
                foreach ($definicion['campos_materia'] as $campo) {
                    $valor = $m['datos'][$campo['nombre']] ?? '';
                    if ($valor !== '' && $codigo !== 'tercer-registro') {
                        $lineas[] = $m['codigo'].' — '.$campo['etiqueta'].': '.($campo['opciones'][$valor] ?? $valor);
                    }
                }
            }
            foreach ($lineas as $linea) {
                $p = $xml->createElementNS(self::NS, 'w:p');
                $this->agregarTexto($p, $linea);
                $ancla->parentNode->insertBefore($p, $ancla);
            }
        }
    }

    private function anio(array $materia): string
    {
        return preg_match('/\b(20\d{2})\b/', $materia['periodo'], $coincidencias) ? $coincidencias[1] : '';
    }

    /** Evita páginas vacías y mantiene juntos firma/contacto en la copia. */
    private function ajustarCopia(DOMDocument $xml): void
    {
        $xp = new DOMXPath($xml);
        $xp->registerNamespace('w', self::NS);
        $parrafos = iterator_to_array($xp->query('//w:body/w:p'));
        foreach (array_reverse($parrafos) as $p) {
            if (trim($this->texto($xp, $p)) !== '' || $xp->query('.//w:drawing|.//w:pict', $p)->length) {
                break;
            }
            $p->parentNode->removeChild($p);
        }
        $firma = false;
        $marcadores = [];
        foreach ($this->valores as $nombre => $valor) $marcadores['${'.$nombre.'}'] = $valor;
        foreach ($xp->query('//w:body/w:p') as $p) {
            $texto = strtr($this->texto($xp, $p), $marcadores);
            if (str_starts_with(trim($texto), 'Dr.') || str_contains($texto, 'Correo Institucional:')) {
                $pr = $xp->query('./w:pPr', $p)->item(0);
                if (! $pr) {
                    $pr = $xml->createElementNS(self::NS, 'w:pPr');
                    $p->insertBefore($pr, $p->firstChild);
                }
                foreach (iterator_to_array($xp->query('./w:jc', $pr)) as $jc) {
                    $pr->removeChild($jc);
                }
                $jc = $xml->createElementNS(self::NS, 'w:jc');
                $jc->setAttributeNS(self::NS, 'w:val', 'left');
                $pr->appendChild($jc);
            }
            $firma = $firma || str_starts_with(trim($texto), 'Atentamente');
            if (! $firma) continue;
            $propiedades = $xp->query('./w:pPr', $p)->item(0);
            if (! $propiedades) {
                $propiedades = $xml->createElementNS(self::NS, 'w:pPr');
                $p->insertBefore($propiedades, $p->firstChild);
            }
            // Evita enviar solo la firma/contacto a otra página por espaciados heredados.
            $espaciado = $xp->query('./w:spacing', $propiedades)->item(0);
            if (! $espaciado) {
                $espaciado = $xml->createElementNS(self::NS, 'w:spacing');
                $propiedades->appendChild($espaciado);
            }
            $espaciado->setAttributeNS(self::NS, 'w:before', '0');
            $espaciado->setAttributeNS(self::NS, 'w:after', '80');
            if (! str_contains($texto, 'Celular:') && ! $xp->query('./w:keepNext', $propiedades)->length) {
                $propiedades->appendChild($xml->createElementNS(self::NS, 'w:keepNext'));
            }
        }
    }

    private function texto(DOMXPath $xp, DOMElement $p): string
    {
        $texto = '';
        foreach ($xp->query('.//w:t', $p) as $nodo) {
            $texto .= $nodo->textContent;
        }
        return $texto;
    }

    /** Reemplaza incluso marcadores divididos entre runs, sin aplanar su formato. */
    private function reemplazar(DOMXPath $xp, DOMElement $p, string $buscar, string $valor, bool $soloUno = false): void
    {
        $texto = $this->texto($xp, $p);
        $posiciones = [];
        $desde = 0;
        while (($posicion = strpos($texto, $buscar, $desde)) !== false) {
            $posiciones[] = $posicion;
            $desde = $posicion + strlen($buscar);
            if ($soloUno) break;
        }
        foreach (array_reverse($posiciones) as $inicio) {
            $marcador = $this->marcador($valor);
            $fin = $inicio + strlen($buscar);
            $offset = 0;
            foreach ($xp->query('.//w:t', $p) as $nodo) {
                $actual = $nodo->textContent;
                $limite = $offset + strlen($actual);
                if ($offset < $fin && $limite > $inicio) {
                    $izquierda = max(0, $inicio - $offset);
                    $derecha = min(strlen($actual), $fin - $offset);
                    $nodo->textContent = substr($actual, 0, $izquierda).($offset <= $inicio ? $marcador : '').substr($actual, $derecha);
                    $nodo->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                }
                $offset = $limite;
            }
        }
    }

    private function celda(DOMXPath $xp, DOMElement $celda, string $valor): void
    {
        $p = $xp->query('./w:p', $celda)->item(0);
        foreach (iterator_to_array($xp->query('.//w:t', $celda)) as $nodo) {
            $nodo->textContent = '';
        }
        $this->agregarTexto($p, $valor);
    }

    private function agregarTexto(DOMElement $p, string $valor): void
    {
        $xml = $p->ownerDocument;
        if (! $p->getElementsByTagNameNS(self::NS, 'pPr')->length) {
            $pr = $xml->createElementNS(self::NS, 'w:pPr');
            $espacio = $xml->createElementNS(self::NS, 'w:spacing');
            $espacio->setAttributeNS(self::NS, 'w:after', '80');
            $espacio->setAttributeNS(self::NS, 'w:line', '240');
            $espacio->setAttributeNS(self::NS, 'w:lineRule', 'auto');
            $pr->appendChild($espacio);
            $p->insertBefore($pr, $p->firstChild);
        }
        $run = $xml->createElementNS(self::NS, 'w:r');
        $propiedades = $xml->createElementNS(self::NS, 'w:rPr');
        $fuente = $xml->createElementNS(self::NS, 'w:rFonts');
        foreach (['ascii', 'hAnsi', 'cs'] as $atributo) {
            $fuente->setAttributeNS(self::NS, 'w:'.$atributo, 'Arial');
        }
        $propiedades->appendChild($fuente);
        $tamano = $xml->createElementNS(self::NS, 'w:sz');
        $tamano->setAttributeNS(self::NS, 'w:val', '20');
        $propiedades->appendChild($tamano);
        $run->appendChild($propiedades);
        $t = $xml->createElementNS(self::NS, 'w:t');
        $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        $t->appendChild($xml->createTextNode($this->marcador($valor)));
        $run->appendChild($t);
        $p->appendChild($run);
    }
}
