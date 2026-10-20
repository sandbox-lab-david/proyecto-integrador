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
            'clave_envio' => ['required', 'uuid'],
            'requisitos' => ['present', 'array'],
            'requisitos.*' => ['required', Rule::in(array_column($definicion['documentos'] ?? [], 'id'))],
        ];
        return $reglas;
    }

    public function messages(): array
    {
        return parent::messages() + [
            'clave_envio.required' => 'Descarga el documento antes de enviar la solicitud.',
            'clave_envio.uuid' => 'La versión del documento no es válida. Vuelve a descargarlo.',
        ];
    }
}
