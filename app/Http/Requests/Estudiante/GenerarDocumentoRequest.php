<?php

namespace App\Http\Requests\Estudiante;

use App\Services\Solicitudes\TramitesSimulados;
use App\Services\Solicitudes\DocumentoSolicitud;
use App\Services\Solicitudes\ReglasCampoSolicitud;
use App\Services\Solicitudes\DatosFormularioSolicitud;
use App\Services\Solicitudes\PerfilFormularioSolicitud;
use App\Services\Solicitudes\CatalogoFacultades;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'origen_materias' => ['sometimes', 'in:catalogo'],
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
            'cedula' => ['nullable', 'string', 'max:30'],
            'facultad' => ['nullable', 'string', 'max:150'],
            'modalidad' => ['nullable', 'string', 'max:100'],
            'periodo' => ['nullable', 'string', 'max:100'],

            'celular' => [
                'required',
                'string',
                'max:20',
                'regex:/\A[0-9]+\z/',
            ],

            'materias' => ['present', 'array', 'min:'.(($definicion['max_materias'] ?? 0) > 0 ? 1 : 0), 'max:'.($definicion['max_materias'] ?? 0)],
            'materias.*.datos' => ['present', 'array'],
            'campos' => ['present', 'array'],
            'declaracion' => ['required', 'accepted'],
        ];
        $reglas = array_replace($reglas, DatosFormularioSolicitud::reglas(false));
        if ($this->input('origen_materias') === 'catalogo') {
            $reglas['materias.*.id'][] = Rule::in(array_column(app(\App\Services\Solicitudes\MateriasSolicitud::class)->todas(), 'id'));
        }
        foreach (CatalogoFacultades::restricciones($this->input('facultad'), app(PerfilFormularioSolicitud::class)->facultades()) as $campo => $regla) {
            $reglas[$campo][] = $regla;
        }
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

    protected function prepareForValidation(): void
    {
        $this->merge(app(\App\Services\Solicitudes\MateriasSolicitud::class)->entrada($this->all()));
        $this->merge(DatosFormularioSolicitud::limpiarEntrada($this->all()));
        $this->merge(CatalogoFacultades::normalizar($this->all()));
        // La identidad procede de la cuenta.
        $this->merge(DatosFormularioSolicitud::cuenta($this->user()));
        // Periodo vigente e inscripción de G1 (la modalidad sale de la carrera elegida): no los decide el estudiante.
        $this->merge(app(PerfilFormularioSolicitud::class)->fijos($this->all()));
    }

    public function withValidator(Validator $validador): void
    {
        $validador->after(function (Validator $resultado): void {
            DatosFormularioSolicitud::validarMateriasUnicas($resultado);
            DatosFormularioSolicitud::validarCambio($resultado, (string) $this->input('tramite'));
            ReglasCampoSolicitud::validarCronologia($resultado, (string) $this->input('tramite'));
        });
    }

    public function messages(): array
    {
        return array_replace(DatosFormularioSolicitud::mensajes(), [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe enviarse como texto.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'date_format' => 'El campo :attribute debe tener el formato :format.',

            'correo.email' => 'Ingresa un correo electrónico válido.',

            'celular.required' => 'Ingresa tu número de celular.',

            'materias.*.id.distinct' => 'Hay una materia repetida en la solicitud.',

            'tramite.in' => 'Selecciona un trámite válido para generar su documento.',
        ]);
    }

    public function attributes(): array
    {
        $atributos = [
            'tramite' => 'trámite',
            'nombre' => 'nombre',
            'codigo' => 'código estudiantil',
            'carrera' => 'carrera',
            'correo' => 'correo electrónico',
            'celular' => 'celular',
            'materia_id' => 'materia',
        ];
        foreach (DatosFormularioSolicitud::PERSONALES as $campo => [$etiqueta]) $atributos[$campo] = mb_strtolower($etiqueta);
        foreach (DatosFormularioSolicitud::MATERIA as $campo => [$etiqueta]) $atributos['materias.*.'.$campo] = mb_strtolower($etiqueta);
        return $atributos + ReglasCampoSolicitud::atributos(
            app(TramitesSimulados::class)->buscar((string) $this->input('tramite')) ?? []);
    }
}
