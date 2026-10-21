@props([
    'tramite' => null,
    'modo' => 'pagina',
    'simulado' => false,
    'urlMensaje' => null,
    'facultad' => null,
])

@php
    $flotante = $modo === 'flotante';
    $id = 'cb-chat-' . $modo;
    $nombreFacultad = $facultad ?? 'Facultad de Ingeniería';
@endphp

@if ($flotante)
    <div class="cb cb-flotante" data-cb-flotante>
        <button type="button" class="cb-lanzador" data-cb-abrir aria-expanded="false" aria-controls="{{ $id }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H10l-4.2 3.6A.5.5 0 0 1 5 19.2V16h-.5A.5.5 0 0 1 4 15.5z" fill="currentColor"/></svg>
            <span>¿Dudas con un trámite?</span>
        </button>
@endif

<section {{ $attributes->class(['cb', 'cb-widget', 'cb-modo-' . $modo]) }}
         id="{{ $id }}"
         data-chatbot
         data-tramite="{{ $tramite }}"
         data-facultad="{{ $nombreFacultad }}"
         data-simulado="{{ $simulado ? '1' : '0' }}"
         data-url-catalogo="{{ url('/chatbot/catalogo') }}"
         data-url-mensaje="{{ $urlMensaje ?? url('/chatbot/mensaje') }}"
         data-url-login="{{ url('/login') }}"
         data-csrf="{{ csrf_token() }}"
         aria-label="Asistente de trámites"
         @if ($flotante) role="dialog" hidden @endif>

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
            <div class="cb-chat-cab-txt">
                <strong>Asistente de trámites</strong>
                <span data-cb="cab-tramite">{{ $nombreFacultad }}</span>
            </div>
            <button type="button" class="cb-btn-quieto" data-cb="nueva">Nueva consulta</button>
            @if ($flotante)
                <a class="cb-icono-cab" href="{{ route('chatbot.index') }}" data-cb="ampliar" aria-label="Abrir en pantalla completa" title="Abrir en pantalla completa">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M10 20H4v-6M20 4l-7 7M4 20l7-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <button type="button" class="cb-icono-cab" data-cb="cerrar" aria-label="Cerrar el asistente" title="Cerrar">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            @endif
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

@if ($flotante)
    </div>
@endif

@once
    <link rel="stylesheet" href="{{ asset('assets/chatbot/chatbot.css') }}">

    <script src="{{ asset('assets/chatbot/chatbot.js') }}" defer></script>
@endonce
