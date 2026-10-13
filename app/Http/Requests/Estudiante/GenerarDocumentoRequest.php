<?php

namespace App\Http\Requests\Estudiante;

use App\Services\Solicitudes\TramitesSimulados;
use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\ReglasCampoSolicitud;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerarDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Conserva el acceso actual de la sesión de demostración.
        return true;
    }

    public function rules(TramitesSimulados $tramites): array
    {
        $definicion = $tramites->buscar((string) $this->input('tramite'));
        $reglas = [
            'formato' => ['sometimes', 'in:docx,pdf'],
            'tramite' => [
                'required',
                'string',
                Rule::in(array_keys(DocumentoSolicitud::PLANTILLAS)),
            ],

            'nombre' => ['required', 'string', 'max:150'],
            'codigo' => ['required', 'string', 'max:30'],
            'carrera' => ['required', 'string', 'max:150'],
            'correo' => ['required', 'email', 'max:150'],

            'celular' => [
                'required',
                'string',
                'max:20',
                'regex:/\A[0-9]+\z/',
            ],

            'materias' => ['present', 'array', 'min:'.(($definicion['max_materias'] ?? 0) > 0 ? 1 : 0), 'max:'.($definicion['max_materias'] ?? 0)],
            'materias.*.id' => ['required', 'distinct', Rule::in(array_column($tramites->materias(), 'id'))],
            'materias.*.datos' => ['present', 'array'],
            'campos' => ['present', 'array'],
            'declaracion' => ['required', 'accepted'],
        ];
        foreach (['campos' => 'campos', 'campos_materia' => 'materias.*.datos'] as $grupo => $prefijo) {
            $campos = $definicion[$grupo] ?? [];
            $reglas[$prefijo] = ['present', 'array'.($campos ? ':'.implode(',', array_column($campos, 'nombre')) : '')];
            foreach ($campos as $campo) {
                $requerido = $campo['obligatorio'];
                if (isset($campo['mostrar_si'])) {
                    $condicion = $campo['mostrar_si'];
                    $requerido = $requerido && $this->input('campos.'.$condicion['campo']) === $condicion['valor'];
                }
                $tipo = ReglasCampoSolicitud::tipo($campo);
                $reglas[$prefijo.'.'.$campo['nombre']] = array_merge([$requerido ? 'required' : 'nullable', 'string'], $tipo);
            }
        }
        return $reglas;
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe enviarse como texto.',
            'max.string' => 'El campo :attribute no debe superar :max caracteres.',
            'max.numeric' => 'El campo :attribute no debe ser mayor que :max.',
            'max.array' => 'El campo :attribute admite como máximo :max elementos.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'date_format' => 'El campo :attribute debe tener el formato :format.',

            'correo.email' => 'Ingresa un correo electrónico válido.',

            'celular.required' => 'Ingresa tu número de celular.',
            'celular.max' => 'El celular admite como máximo 20 dígitos.',
            'celular.regex' => 'El celular solo debe contener números.',

            'materia_id.required' => 'Selecciona una materia.',
            'materia_id.in' => 'Selecciona una materia del catálogo de prueba.',

            'tramite.in' => 'Selecciona un trámite válido para generar su documento.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tramite' => 'trámite',
            'nombre' => 'nombre',
            'codigo' => 'código estudiantil',
            'carrera' => 'carrera',
            'correo' => 'correo electrónico',
            'celular' => 'celular',
            'materia_id' => 'materia',
        ];
    }
}
