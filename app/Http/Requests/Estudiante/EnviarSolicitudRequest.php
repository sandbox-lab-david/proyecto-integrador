<?php

namespace App\Http\Requests\Estudiante;

use App\Services\Solicitudes\TramitesSimulados;
use Illuminate\Validation\Rule;

class EnviarSolicitudRequest extends GenerarDocumentoRequest
{
    public function rules(TramitesSimulados $tramites): array
    {
        $definicion = $tramites->buscar((string) $this->input('tramite'));
        $reglas = parent::rules($tramites) + [
            'requisitos' => ['present', 'array'],
            'requisitos.*' => ['required', Rule::in(array_column($definicion['documentos'] ?? [], 'id'))],
        ];
        return $reglas;
    }
}
