<?php

namespace App\Http\Requests\Estudiante;

use App\Services\Solicitudes\TramitesSimulados;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarBorradorRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Conserva el acceso actual de la sesión de demostración.
        return true;
    }

    public function rules(TramitesSimulados $tramites): array
    {
        $codigoTramite = (string) $this->route('tramite');
        $definicion = $tramites->buscar($codigoTramite);

        abort_if($definicion === null, 404);

        $reglas = [
            'paso' => ['required', 'integer', 'between:1,4'],

            // Puede estar vacío porque todavía es un borrador.
            'celular' => [
                'nullable',
                'string',
                'max:20',
                'regex:/\A[0-9]+\z/',
            ],

            'materias' => [
                'array',
                'max:'.$definicion['max_materias'],
            ],

            'materias.*.id' => [
                'required',
                'distinct',
                Rule::in(array_column($tramites->materias(), 'id')),
            ],

            'materias.*.datos' => ['array'],
            'materias.*.datos.*' => ['nullable', 'string', 'max:2000'],

            'campos' => ['array'],
            'campos.*' => ['nullable', 'string', 'max:2000'],

            'requisitos' => ['array'],
            'requisitos.*' => [
                'nullable',
                Rule::in(array_column($definicion['documentos'], 'id')),
            ],
        ];

        // Valida los campos según la definición de cada trámite.
        // No exige completarlos mientras sea un borrador.
        foreach ([
            'campos' => 'campos',
            'campos_materia' => 'materias.*.datos',
        ] as $grupo => $prefijo) {
            foreach ($definicion[$grupo] as $campo) {
                $rutaCampo = $prefijo.'.'.$campo['nombre'];

                $reglas[$rutaCampo] = match ($campo['tipo']) {
                    'numero' => [
                        'nullable',
                        'string',
                        'numeric',
                        'min:'.$campo['min'],
                        'max:'.$campo['max'],
                    ],
                    'lista' => [
                        'nullable',
                        'string',
                        Rule::in(array_keys($campo['opciones'])),
                    ],
                    'fecha' => [
                        'nullable',
                        'string',
                        'date_format:Y-m-d',
                        'before_or_equal:today',
                    ],
                    default => ['nullable', 'string', 'max:2000'],
                };
            }
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'celular.string' => 'El celular debe enviarse como texto.',
            'celular.max' => 'El celular admite como máximo 20 dígitos.',
            'celular.regex' => 'El celular solo debe contener números.',
        ];
    }
}