<?php

namespace App\Services\Academico\Importadores;

use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class Lector implements WithCalculatedFormulas, WithCustomCsvSettings
{
    public function __construct(
        private string $delimitador = ',',
        private string $codificacion = 'UTF-8',
    ) {}

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => $this->delimitador,
            'input_encoding' => $this->codificacion,
        ];
    }
}
