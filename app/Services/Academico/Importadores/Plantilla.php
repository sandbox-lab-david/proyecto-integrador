<?php

namespace App\Services\Academico\Importadores;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Plantilla implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings
{
    public function __construct(private array $encabezados, private array $filas = []) {}

    public function headings(): array
    {
        return $this->encabezados;
    }

    public function array(): array
    {
        return $this->filas;
    }

    public function columnFormats(): array
    {
        $formatos = [];
        foreach (array_values($this->encabezados) as $i => $columna) {
            $letra = chr(65 + $i);
            $formatos["{$letra}2:{$letra}1000"] = NumberFormat::FORMAT_TEXT;
        }

        return $formatos;
    }
}
