<?php

namespace App\Http\Requests\Estudiante;

use App\Services\Solicitudes\TramitesSimulados;
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
        return [
            'tramite' => [
                'required',
                'string',
                Rule::in(['recuperacion']),
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

            'materia_id' => [
                'required',
                'string',
                Rule::in(array_column($tramites->materias(), 'id')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe enviarse como texto.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',

            'correo.email' => 'Ingresa un correo electrónico válido.',

            'celular.required' => 'Ingresa tu número de celular.',
            'celular.max' => 'El celular admite como máximo 20 dígitos.',
            'celular.regex' => 'El celular solo debe contener números.',

            'materia_id.required' => 'Selecciona una materia.',
            'materia_id.in' => 'Selecciona una materia del catálogo de prueba.',

            'tramite.in' => 'La generación de este documento está disponible para examen de recuperación.',
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