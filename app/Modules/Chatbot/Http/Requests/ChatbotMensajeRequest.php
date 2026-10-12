<?php

namespace App\Modules\Chatbot\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotMensajeRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     * El chatbot es de libre acceso para estudiantes y visitantes.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación que debe cumplir el payload JSON según el contrato v2.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'version' => ['required', 'integer', 'in:2'],
            'pregunta_id' => ['required', 'string'],
            'tramite_id' => ['required', 'string'],
            'intencion' => ['required', 'string'],
            'origen_datos' => ['required', 'string', 'in:ninguno,sesion,manual'],
            // 'present' permite que datos_usuario sea {} en consultas informativas
            'datos_usuario' => ['present', 'array'],
        ];
    }
}
