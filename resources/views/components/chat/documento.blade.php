@props(['documento' => []])

@php
    $obligatorio = $documento['obligatorio'] ?? true;
@endphp

<li class="cb-doc" data-cb="documento">
    <div class="cb-doc-fila">
        <strong data-cb="doc-nombre">{{ $documento['nombre'] ?? '' }}</strong>
        <span class="cb-etq" data-cb="doc-formato" @if (empty($documento['formato'])) hidden @endif>{{ $documento['formato'] ?? '' }}</span>
    </div>
    <p data-cb="doc-descripcion" @if (empty($documento['descripcion'])) hidden @endif>{{ $documento['descripcion'] ?? '' }}</p>
    <div class="cb-doc-fila">
        <span @class(['cb-etq', 'cb-etq-vino' => $obligatorio]) data-cb="doc-obligatorio">{{ $obligatorio ? 'Obligatorio' : 'Opcional' }}</span>
        <a class="cb-enlace" data-cb="doc-enlace" href="{{ $documento['url'] ?? '#' }}" @if (empty($documento['url'])) hidden @endif>Descargar formato ↓</a>
    </div>
</li>
