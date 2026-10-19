<?php

namespace App\Modules\Chatbot\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatbotMensajeRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     *
     * API-01: Las consultas informativas generales son públicas para cualquier visitante.
     * Las evaluaciones personales de elegibilidad requieren autenticación con cuenta institucional.
     */
    public function authorize(): bool
    {
        if ($this->esEvaluacionPersonal()) {
            $user = $this->user();
            $pruebaLocal = config('app.env') === 'local'
                && $this->routeIs('chatbot.pruebas.mensaje');
            if ($user === null && !$pruebaLocal) {
                return false;
            }

            // API-01: Comprobar que el usuario institucional tenga permisos (no inactivo ni suspendido)
            $rol = $user?->rol ?? $user?->role ?? null;
            if ($rol !== null && in_array($rol, ['inactivo', 'suspendido', 'bloqueado'], true)) {
                abort(403, 'Tu cuenta institucional se encuentra inhabilitada para realizar evaluaciones.');
            }

            return true;
        }

        return true;
    }

    /**
     * Determina si la petición intenta realizar una evaluación personal.
     */
    public function esEvaluacionPersonal(): bool
    {
        if ($this->input('intencion') === 'evaluar_elegibilidad') {
            return true;
        }

        $preguntaId = (string) $this->input('pregunta_id');
        if (str_ends_with($preguntaId, '__elegibilidad')) {
            return true;
        }

        return false;
    }

    /**
     * Si la autorización falla en una evaluación personal, se rechaza con 401 (sesión requerida).
     */
    protected function failedAuthorization()
    {
        abort(401, 'Debes iniciar sesión con tu cuenta institucional para realizar una evaluación personal de elegibilidad.');
    }

    /**
     * Indica si la petición llega en modo texto libre (el usuario escribió
     * directamente en lugar de usar un botón del catálogo).
     */
    public function esTextoLibre(): bool
    {
        return $this->filled('texto');
    }

    /**
     * Reglas de validación según el modo de entrada:
     *
     * - Modo texto libre : version + texto + tramite_id (el frontend manda
     *   {version, texto, tramite_id} cuando el usuario escribe libremente).
     * - Modo botón       : contrato v2 completo con pregunta_id, intencion,
     *   origen_datos y datos_usuario.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $reglaTramiteCatalogo = ['string', Rule::in($this->tramitesValidos())];

        if ($this->esTextoLibre()) {
            return [
                'version'    => ['required', 'integer', 'in:2'],
                'texto'      => ['required', 'string', 'max:1000'],
                'tramite_id' => array_merge(['nullable'], $reglaTramiteCatalogo),
            ];
        }

        return [
            'version'       => ['required', 'integer', 'in:2'],
            'pregunta_id'   => ['required', 'string'],
            'tramite_id'    => array_merge(['required'], $reglaTramiteCatalogo),
            'intencion'     => ['required', 'string'],
            'origen_datos'  => ['required', 'string', 'in:ninguno,sesion,manual'],
            // 'present' permite que datos_usuario sea {} en consultas informativas
            'datos_usuario'                          => ['present', 'array'],
            'datos_usuario.promedio'                 => ['nullable', 'numeric', 'between:0,100'],
            'datos_usuario.gpa'                      => ['nullable', 'numeric', 'between:0,100'],
            'datos_usuario.period_gpa'               => ['nullable', 'numeric', 'between:0,100'],
            'datos_usuario.nota'                     => ['nullable', 'numeric', 'between:0,100'],
            'datos_usuario.grade'                    => ['nullable', 'numeric', 'between:0,100'],
            'datos_usuario.asistencia'               => ['nullable', 'numeric', 'between:0,100'],
            'datos_usuario.attendance'               => ['nullable', 'numeric', 'between:0,100'],
            'datos_usuario.materia_id'               => ['nullable', 'string', 'max:100'],
            'datos_usuario.subjects'                 => ['nullable', 'string', 'max:100'],
            'datos_usuario.tipo_solicitante'         => ['nullable', 'string', 'max:50'],
            'datos_usuario.fecha_ultima_recuperacion'=> ['nullable', 'date_format:Y-m-d'],
            'datos_usuario.listener'                 => ['nullable', 'boolean'],
            'datos_usuario.new_code'                 => ['nullable', 'boolean'],
            'datos_usuario.fraude'                   => ['nullable', 'boolean'],
        ];
    }

    /**
     * Mensajes de validación en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'version.required'      => 'La versión del protocolo es obligatoria.',
            'version.in'            => 'La versión del protocolo debe ser 2.',
            'texto.required'        => 'El mensaje no puede estar vacío.',
            'texto.max'             => 'El mensaje no puede superar los 1000 caracteres.',
            'tramite_id.required'   => 'El identificador del trámite es obligatorio.',
            'tramite_id.in'         => 'El trámite indicado no existe en el catálogo.',
            'pregunta_id.required'  => 'El identificador de pregunta es obligatorio.',
            'intencion.required'    => 'La intención de la consulta es obligatoria.',
            'origen_datos.required' => 'El origen de los datos es obligatorio.',
            'origen_datos.in'       => 'El origen de datos debe ser ninguno, sesion o manual.',
            'datos_usuario.present' => 'El campo datos_usuario debe estar presente (puede ser {}).',
            'datos_usuario.array'   => 'El campo datos_usuario debe ser un objeto válido.',

            // Validación de campos individuales de datos_usuario
            'datos_usuario.promedio.numeric'                      => 'El promedio debe ser un número.',
            'datos_usuario.promedio.between'                      => 'El promedio debe estar entre 0 y 100.',
            'datos_usuario.gpa.numeric'                           => 'El GPA debe ser un número.',
            'datos_usuario.gpa.between'                           => 'El GPA debe estar entre 0 y 100.',
            'datos_usuario.period_gpa.numeric'                    => 'El GPA del periodo debe ser un número.',
            'datos_usuario.period_gpa.between'                    => 'El GPA del periodo debe estar entre 0 y 100.',
            'datos_usuario.nota.numeric'                          => 'La nota debe ser un número.',
            'datos_usuario.nota.between'                          => 'La nota debe estar entre 0 y 100.',
            'datos_usuario.grade.numeric'                         => 'La nota debe ser un número.',
            'datos_usuario.grade.between'                         => 'La nota debe estar entre 0 y 100.',
            'datos_usuario.asistencia.numeric'                    => 'El porcentaje de asistencia debe ser un número.',
            'datos_usuario.asistencia.between'                    => 'La asistencia debe estar entre 0 y 100.',
            'datos_usuario.attendance.numeric'                    => 'El porcentaje de asistencia debe ser un número.',
            'datos_usuario.attendance.between'                    => 'La asistencia debe estar entre 0 y 100.',
            'datos_usuario.materia_id.string'                     => 'El identificador de materia debe ser texto.',
            'datos_usuario.materia_id.max'                        => 'El identificador de materia no puede superar los 100 caracteres.',
            'datos_usuario.subjects.string'                       => 'La materia seleccionada debe ser texto.',
            'datos_usuario.subjects.max'                          => 'La materia seleccionada no puede superar los 100 caracteres.',
            'datos_usuario.tipo_solicitante.string'               => 'El tipo de solicitante debe ser texto.',
            'datos_usuario.tipo_solicitante.max'                  => 'El tipo de solicitante no puede superar los 50 caracteres.',
            'datos_usuario.fecha_ultima_recuperacion.date_format' => 'La fecha de la última recuperación debe tener formato YYYY-MM-DD.',
            'datos_usuario.listener.boolean'                      => 'El campo oyente debe ser verdadero o falso.',
            'datos_usuario.new_code.boolean'                      => 'El campo nuevo código debe ser verdadero o falso.',
            'datos_usuario.fraude.boolean'                        => 'El campo fraude debe ser verdadero o falso.',
        ];
    }

    /**
     * Comprobaciones avanzadas de relaciones y campos de perfil (API-04).
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v) {
            if ($this->esTextoLibre()) {
                return;
            }

            $preguntaId = (string) $this->input('pregunta_id');
            $tramiteId = (string) $this->input('tramite_id');
            $intencion = (string) $this->input('intencion');

            $catalogoService = app(\App\Services\Chatbot\ChatbotCatalogoService::class);
            $pregunta = $catalogoService->obtenerPregunta($preguntaId);

            // API-04: Validar coherencia de la terna pregunta_id, tramite_id e intencion
            if ($pregunta === null) {
                $v->errors()->add('pregunta_id', 'El identificador de pregunta no existe en el catálogo.');
                return;
            }

            if ($pregunta['tramite_id'] !== $tramiteId) {
                $v->errors()->add('tramite_id', 'El trámite indicado no corresponde a la pregunta seleccionada.');
            }

            if ($pregunta['intencion'] !== $intencion) {
                $v->errors()->add('intencion', 'La intención indicada no corresponde a la pregunta seleccionada.');
            }

            // API-04: Restringir campos de datos_usuario según el trámite y la intención
            $datosUsuario = $this->input('datos_usuario');
            if (is_array($datosUsuario)) {
                if ($intencion !== 'evaluar_elegibilidad' && !empty($datosUsuario)) {
                    $v->errors()->add('datos_usuario', 'Las consultas informativas no deben enviar campos en datos_usuario.');
                } elseif ($intencion === 'evaluar_elegibilidad') {
                    $camposPermitidos = $catalogoService->obtenerNombresCamposValidosPorTramite($tramiteId);
                    foreach (array_keys($datosUsuario) as $campo) {
                        if (!in_array($campo, $camposPermitidos, true)) {
                            $v->errors()->add("datos_usuario.{$campo}", "El campo '{$campo}' no está permitido para el trámite '{$tramiteId}'.");
                        }
                    }
                }
            }
        });
    }

    /**
     * Lee los identificadores de trámite válidos directamente de hechos.pl.
     * Usa el mismo patrón regex que ChatbotCatalogoService para mantener
     * consistencia sin crear una dependencia circular.
     *
     * @return array<int, string>
     */
    protected function tramitesValidos(): array
    {
        $kbPath = base_path('prolog/kb/hechos.pl');
        if (!file_exists($kbPath)) {
            return [];
        }
        preg_match_all('/tramite\(([a-z0-9_]+),\s*"[^"]+"\)\./u', file_get_contents($kbPath), $m);
        return $m[1] ?? [];
    }
}
