<?php

namespace App\Modules\Chatbot\Http\Requests;

use App\Services\Chatbot\ChatbotCatalogoService;
use App\Services\Solicitudes\ReglasCampoSolicitud;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ChatbotMensajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->esEvaluacionPersonal()) {
            return true;
        }
        if (config('app.env') === 'local' && $this->routeIs('chatbot.pruebas.mensaje')) {
            return true;
        }

        return $this->user() !== null && $this->user()->activo;
    }

    public function esEvaluacionPersonal(): bool
    {
        return $this->input('intencion') === 'comprobar_referencias';
    }

    protected function failedAuthorization()
    {
        abort($this->user() ? 403 : 401, 'Inicia sesión con una cuenta activa para consultar tus referencias personales.');
    }

    public function esTextoLibre(): bool
    {
        return $this->filled('texto');
    }

    public function rules(): array
    {
        $ids = array_column(app(ChatbotCatalogoService::class)->obtenerCatalogo()['tramites'], 'id');
        $base = ['version' => ['required', 'integer', 'in:3'], 'tramite_id' => [$this->esTextoLibre() ? 'nullable' : 'required', 'string', Rule::in($ids)]];
        if ($this->esTextoLibre()) {
            return $base + ['texto' => ['required', 'string', 'max:1000'], 'intencion' => ['prohibited'], 'datos_usuario' => ['prohibited']];
        }

        return $base + [
            'pregunta_id' => ['required', 'string'],
            'intencion' => ['required', Rule::in(['consultar_descripcion', 'consultar_requisitos', 'consultar_documentos', 'consultar_costo', 'consultar_plazo', 'comprobar_referencias'])],
            'origen_datos' => ['required', Rule::in(['ninguno', 'manual'])],
            'datos_usuario' => ['present', 'array:gpa_general,fecha_ultima_recuperacion,documentos_subidos'],
            'datos_usuario.gpa_general' => ['nullable', 'numeric', 'between:0,100'],
            'datos_usuario.fecha_ultima_recuperacion' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:'.now(ReglasCampoSolicitud::zonaHoraria())->toDateString()],
            'datos_usuario.documentos_subidos' => ['nullable', 'array'],
            'datos_usuario.documentos_subidos.*' => ['string'],
            'mostrar_referencias' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $v): void
    {
        $v->after(function ($v) {
            if ($this->esTextoLibre()) {
                return;
            }
            $p = app(ChatbotCatalogoService::class)->obtenerPregunta((string) $this->input('pregunta_id'));
            if (! $p || $p['tramite_id'] !== $this->input('tramite_id') || $p['intencion'] !== $this->input('intencion')) {
                $v->errors()->add('pregunta_id', 'Selecciona una pregunta válida para este trámite.');
            }
            if (! $this->esEvaluacionPersonal() && $this->input('datos_usuario', []) !== []) {
                $v->errors()->add('datos_usuario', 'La consulta informativa no necesita datos personales.');
            }
        });
    }

    public function messages(): array
    {
        return ['tramite_id.in' => 'El trámite indicado no existe en el catálogo.', 'version.in' => 'Actualiza la página para utilizar la versión 3 del chatbot.', 'datos_usuario.array' => 'Usa únicamente GPA general y fecha del último examen; no envíes notas de materias ni GPA del período.', 'datos_usuario.gpa_general.between' => 'El GPA general debe estar entre 0 y 100.', 'datos_usuario.fecha_ultima_recuperacion.before_or_equal' => 'La fecha del último examen no puede estar en el futuro.', 'datos_usuario.fecha_ultima_recuperacion.date_format' => 'Introduce una fecha válida con formato AAAA-MM-DD.'];
    }
}
