@props(['respuesta' => []])

@php
    $r = $respuesta;
    $acciones = $r['acciones'] ?? [];
    $evaluacion = isset($r['resultado']) ? [
        'resultado' => $r['resultado']['estado'] ?? null,
        'motivos' => $r['resultado']['motivos'] ?? [],
        'con_datos_verificados' => ($r['meta']['verificacion'] ?? null) === 'institucional',
    ] : null;
@endphp

<div class="cb-respuesta" data-cb="respuesta">
    <p class="cb-resp-titulo" data-cb="titulo" @if (empty($r['titulo'])) hidden @endif>{{ $r['titulo'] ?? '' }}</p>
    <p class="cb-resp-texto" data-cb="mensaje" @if (empty($r['mensaje'])) hidden @endif>{{ $r['mensaje'] ?? '' }}</p>
    <ul class="cb-check" data-cb="items" @if (empty($r['items'])) hidden @endif>
        @foreach ($r['items'] ?? [] as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    <x-chat.documentos :documentos="$r['documentos'] ?? []" />
    <x-chat.datos-clave :costo="$r['costo'] ?? null" :plazo="$r['plazo'] ?? null" />
    <x-resultado-elegibilidad :evaluacion="$evaluacion" />
    <ul class="cb-solicitudes" data-cb="solicitudes" @if (empty($r['solicitudes'])) hidden @endif>
        @foreach ($r['solicitudes'] ?? [] as $solicitud)
            <x-chat.solicitud :solicitud="$solicitud" />
        @endforeach
    </ul>
    <x-chat.aviso :texto="$r['aviso'] ?? null" />
    <div class="cb-acciones" data-cb="acciones" @if (empty($acciones)) hidden @endif>
        @foreach ($acciones as $accion)
            <a @class([
                'cb-btn',
                'cb-btn-primario' => $accion['tipo'] === 'iniciar_solicitud',
                'cb-btn-secundario' => $accion['tipo'] !== 'iniciar_solicitud',
            ]) href="{{ $accion['url'] }}">{{ $accion['etiqueta'] }}</a>
        @endforeach
    </div>
</div>
