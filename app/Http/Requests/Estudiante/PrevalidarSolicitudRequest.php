<?php

namespace App\Http\Requests\Estudiante;

use App\Services\Solicitudes\DatosFormularioSolicitud;
use App\Services\Solicitudes\ReglasCampoSolicitud;
use App\Services\Solicitudes\TramitesSimulados;
use Illuminate\Foundation\Http\FormRequest;

/** Datos del paso 4 que la pre-validación (RF-2.8) puede enviar a Prolog. */
class PrevalidarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(TramitesSimulados $tramites): array
    {
        $definicion = $tramites->buscar((string) $this->input('tramite'));
        abort_if($definicion === null, 404);

        $reglas = [
            'tramite' => ['required', 'string', 'max:60'],
            'campos' => ['present', 'array'],
            // Respaldo => documento que cubre; el servicio descarta los archivos que no están subidos.
            'requisitos' => ['sometimes', 'array', 'max:50'],
            'requisitos.*' => ['nullable', 'string', 'max:150'],
        ];
        // Solo se validan los campos de la definición; el servicio decide cuáles viajan al motor.
        foreach ($definicion['campos'] ?? [] as $campo) {
            $reglas['campos.'.$campo['nombre']] = array_merge(['nullable', 'string'], ReglasCampoSolicitud::tipo($campo));
        }

        return $reglas;
    }

    public function messages(): array
    {
        return DatosFormularioSolicitud::mensajes();
    }

    public function attributes(): array
    {
        return ['tramite' => 'trámite'] + ReglasCampoSolicitud::atributos(
            app(TramitesSimulados::class)->buscar((string) $this->input('tramite')) ?? []);
    }
}
