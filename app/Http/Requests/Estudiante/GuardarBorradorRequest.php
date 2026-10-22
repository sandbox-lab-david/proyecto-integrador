<?php

namespace App\Http\Requests\Estudiante;

use App\Services\Solicitudes\TramitesSimulados;
use App\Services\Solicitudes\ReglasCampoSolicitud;
use App\Services\Solicitudes\DatosFormularioSolicitud;
use App\Services\Solicitudes\CatalogoFacultades;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'origen_materias' => ['sometimes', 'in:catalogo'],
            'paso' => ['required', 'integer', 'between:1,4'],
            'nombre' => ['nullable', 'string', 'max:150'],
            'codigo' => ['nullable', 'string', 'max:30'],
            'cedula' => ['nullable', 'string', 'max:30'],
            'facultad' => ['nullable', 'string', 'max:150'],
            'carrera' => ['nullable', 'string', 'max:150'],
            'modalidad' => ['nullable', 'string', 'max:100'],
            'periodo' => ['nullable', 'string', 'max:100'],
            'correo' => ['nullable', 'string', 'max:150'],

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


            'materias.*.datos' => ['array'],
            'materias.*.datos.*' => ['nullable', 'string', ReglasCampoSolicitud::limiteTexto(2000)],

            'campos' => ['array'],
            'campos.*' => ['nullable', 'string', ReglasCampoSolicitud::limiteTexto(2000)],

            'requisitos' => ['array'],
            'requisitos.*' => [
                'nullable',
                'string', 'max:150', // Se comprueba contra Prolog al subir y al enviar; el borrador puede guardarse sin motor.
            ],
        ];
        $reglas = array_replace($reglas, DatosFormularioSolicitud::reglas(true));
        foreach (CatalogoFacultades::restricciones($this->input('facultad')) as $campo => $regla) {
            $reglas[$campo][] = $regla;
        }

        if ($this->input('requisitos')) {
            $consulta = app(\App\Services\Solicitudes\RequisitosSolicitud::class)->paraVista($codigoTramite);
            if ($consulta['disponible']) {
                $reglas['requisitos.*'][] = Rule::in(array_column($consulta['documentos'], 'id'));
            }
        }

        // Valida los campos según la definición de cada trámite.
        // No exige completarlos mientras sea un borrador.
        foreach ([
            'campos' => 'campos',
            'campos_materia' => 'materias.*.datos',
        ] as $grupo => $prefijo) {
            foreach ($definicion[$grupo] as $campo) {
                $rutaCampo = $prefijo.'.'.$campo['nombre'];

                $reglas[$rutaCampo] = array_merge(['nullable', 'string'], ReglasCampoSolicitud::tipo($campo));
            }
        }

        if ($this->input('origen_materias') === 'catalogo') {
            $reglas['materias.*.id'][] = Rule::in(array_column(app(\App\Services\Solicitudes\MateriasSolicitud::class)->todas(), 'id'));
        }
        return $reglas;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(app(\App\Services\Solicitudes\MateriasSolicitud::class)->entrada($this->all()));
        $this->merge(DatosFormularioSolicitud::limpiarEntrada($this->all()));
        $this->merge(CatalogoFacultades::normalizar($this->all()));
    }

    public function withValidator(Validator $validador): void
    {
        $validador->after(function (Validator $resultado): void {
            DatosFormularioSolicitud::validarMateriasUnicas($resultado);
            DatosFormularioSolicitud::validarCambio($resultado, (string) $this->route('tramite'));
            ReglasCampoSolicitud::validarCronologia($resultado, (string) $this->route('tramite'));
        });
    }

    public function messages(): array
    {
        return DatosFormularioSolicitud::mensajes();
    }

    public function attributes(): array
    {
        $atributos = [];
        foreach (DatosFormularioSolicitud::PERSONALES as $campo => [$etiqueta]) {
            $atributos[$campo] = mb_strtolower($etiqueta);
        }
        foreach (DatosFormularioSolicitud::MATERIA as $campo => [$etiqueta]) {
            $atributos['materias.*.'.$campo] = mb_strtolower($etiqueta);
        }
        return $atributos + ReglasCampoSolicitud::atributos(
            app(TramitesSimulados::class)->buscar((string) $this->route('tramite')) ?? []);
    }
}
