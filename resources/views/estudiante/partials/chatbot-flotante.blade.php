@php
    // Adaptación de los códigos del portal a los identificadores del chatbot.
    $codigosChatbot = [
        'alcance-homologacion' => 'alcance_homologacion',
        'ayudante' => 'ayudante_catedra',
        'cambio-carrera' => 'cambio_carrera',
        'cambio-malla' => 'cambio_malla',
        'cambio-modalidad' => 'cambio_modalidad',
        'gracia' => 'examen_gracia',
        'recuperacion' => 'examen_recuperacion',
        'suficiencia' => 'examen_suficiencia',
        'supletorio' => 'examen_supletorio',
        'homologacion' => 'homologacion',
        'incompleto' => 'incompleto',
        'person-to-person' => 'person_to_person',
        'recalificacion' => 'recalificacion',
        'registro-extemporaneo' => 'registro_extemporaneo',
        'reincorporacion' => 'reincorporacion_carrera',
        'retiro-carrera' => 'retiro_carrera',
        'retiro-materia' => 'retiro_materia',
        'retiro-universidad' => 'retiro_universidad',
        'retiro-extemporaneo' => 'retiro_extemporaneo',
        'tercer-registro' => 'tercer_registro',
    ];
    $rutaChatbot = request()->route()?->getName() ?? '';
    $codigoChatbot = str_starts_with($rutaChatbot, 'estudiante.tramites.')
        ? substr($rutaChatbot, strlen('estudiante.tramites.'))
        : (request()->route('tramite') ?? request()->query('tramite'));
    $tramiteChatbot = is_string($codigoChatbot)
        ? ($codigosChatbot[$codigoChatbot] ?? null)
        : null;
@endphp

<x-chat-widget modo="flotante" :tramite="$tramiteChatbot" :simulado="false" />

<style>
    /* Colores del portal aplicados únicamente a su asistente flotante. */
    .cb-flotante {
        --cb-vino: #781c35;
        --cb-vino-oscuro: #5d1529;
        --cb-vino-suave: #f6e8ed;
    }
    @media print { .cb-flotante { display: none !important; } }
</style>
