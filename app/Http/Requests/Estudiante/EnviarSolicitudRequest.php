<?php

namespace App\Http\Requests\Estudiante;

use App\Services\Solicitudes\TramitesSimulados;

class EnviarSolicitudRequest extends GenerarDocumentoRequest
{
    public function rules(TramitesSimulados $tramites): array
    {
        $reglas = parent::rules($tramites) + [
            'clave_envio' => ['required', 'uuid'],
            'requisitos' => ['present', 'array'],
            'requisitos.*' => ['required', 'string', 'max:150'], // La lista vigente la valida EnvioSolicitud consultando Prolog.
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
