@props([
    'tramite' => null,
    'modo' => 'pagina',
    'simulado' => true,
])

<section {{ $attributes->class(['cb', 'cb-widget', 'cb-modo-' . $modo]) }}
         data-chatbot
         data-tramite="{{ $tramite }}"
         data-simulado="{{ $simulado ? '1' : '0' }}"
         data-url-catalogo="{{ url('/chatbot/catalogo') }}"
         data-url-mensaje="{{ url('/chatbot/mensaje') }}"
         data-url-historial="{{ url('/chatbot/historial') }}"
         data-url-login="{{ url('/login') }}"
         data-csrf="{{ csrf_token() }}"
         aria-label="Asistente de trámites">

    @if ($modo === 'pagina')
        <aside class="cb-lateral" aria-label="Trámites">
            <h2>Trámites</h2>
            <label class="cb-buscador">
                <span aria-hidden="true">⌕</span>
                <span class="cb-oculto">Buscar trámite</span>
                <input type="search" placeholder="Buscar trámite" autocomplete="off" data-cb="buscar">
            </label>
            <div class="cb-lista-tramites" data-cb="lista-tramites"></div>
        </aside>
    @endif

    <div class="cb-chat">
        <div class="cb-chat-cab">
            <div>
                <strong>Asistente de trámites</strong>
                <span data-cb="cab-tramite">Facultad de Ingeniería</span>
            </div>
            <button type="button" class="cb-btn-quieto" data-cb="nueva">Nueva consulta</button>
        </div>

        <div class="cb-conversacion" data-cb="conversacion" role="log" aria-live="polite" aria-label="Conversación">
            <div class="cb-conversacion-in" data-cb="mensajes"></div>
        </div>

        <div class="cb-composer">
            <div class="cb-composer-in">
                <button type="button" class="cb-tramite-chip" data-cb="cambiar" aria-label="Elegir trámite">
                    <span aria-hidden="true">⌕</span>
                    <b data-cb="chip-texto">Elegir trámite</b>
                </button>
                <form class="cb-escribir" data-cb="composer" autocomplete="off">
                    <label class="cb-oculto" for="cb-texto-{{ $modo }}">Escribe tu pregunta</label>
                    <input id="cb-texto-{{ $modo }}" type="text" maxlength="300" placeholder="Escribe tu pregunta" data-cb="entrada">
                    <button type="submit" class="cb-enviar" data-cb="enviar" aria-label="Enviar">→</button>
                </form>
            </div>
        </div>

        <div class="cb-panel-buscar" data-cb="panel-buscar" hidden>
            <div class="cb-panel-buscar-cab">
                <strong>Elige un trámite</strong>
                <button type="button" class="cb-btn-quieto" data-cb="cerrar-buscar">Cerrar</button>
            </div>
            <label class="cb-buscador">
                <span aria-hidden="true">⌕</span>
                <span class="cb-oculto">Buscar trámite</span>
                <input type="search" placeholder="Buscar trámite" autocomplete="off" data-cb="buscar">
            </label>
            <div class="cb-lista-tramites" data-cb="lista-tramites"></div>
        </div>
    </div>

    <template data-plantilla="bot"><x-chat.mensaje-bot /></template>
    <template data-plantilla="usuario"><x-chat.mensaje-usuario /></template>
    <template data-plantilla="espera"><x-chat.escribiendo /></template>
    <template data-plantilla="respuesta"><x-chat.respuesta /></template>
    <template data-plantilla="documento"><x-chat.documento /></template>
    <template data-plantilla="solicitud"><x-chat.solicitud /></template>
    <template data-plantilla="sugerencias"><x-chat.sugerencias /></template>
    <template data-plantilla="error"><x-chat.error /></template>
    <template data-plantilla="formulario"><x-chat.formulario /></template>
    <template data-plantilla="campo-texto"><x-chat.campo tipo="texto" /></template>
    <template data-plantilla="campo-numero"><x-chat.campo tipo="numero" /></template>
    <template data-plantilla="campo-seleccion"><x-chat.campo tipo="seleccion" /></template>
    <template data-plantilla="campo-booleano"><x-chat.campo tipo="booleano" /></template>
    <template data-plantilla="campo-fecha"><x-chat.campo tipo="fecha" /></template>
</section>

@once
    <link rel="stylesheet" href="{{ asset('assets/chatbot/chatbot.css') }}">
    @if ($simulado)
        <script src="{{ asset('assets/chatbot/chatbot-simulado.js') }}" defer></script>
    @endif
    <script src="{{ asset('assets/chatbot/chatbot.js') }}" defer></script>
@endonce
