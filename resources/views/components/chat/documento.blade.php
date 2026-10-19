@props(['documento' => []])

@php
    $obligaciones = [
        'obligatorio' => ['Obligatorio', 'cb-etq-vino'],
        'opcional' => ['Opcional', null],
        'condicional' => ['Según tu caso', null],
        'por_confirmar' => ['Por confirmar', 'cb-etq-pendiente'],
    ];
    $clave = $documento['obligacion'] ?? null;
    if (! isset($obligaciones[$clave])) {
        $clave = ($documento['obligatorio'] ?? null) === true ? 'obligatorio' : 'por_confirmar';
    }
    [$etiqueta, $claseEtiqueta] = $obligaciones[$clave];
@endphp

<li class="cb-doc" data-cb="documento">
    <div class="cb-doc-fila">
        <strong data-cb="doc-nombre">{{ $documento['nombre'] ?? '' }}</strong>
        <span class="cb-etq" data-cb="doc-formato" @if (empty($documento['formato'])) hidden @endif>{{ $documento['formato'] ?? '' }}</span>
    </div>
    <p data-cb="doc-descripcion" @if (empty($documento['descripcion'])) hidden @endif>{{ $documento['descripcion'] ?? '' }}</p>
    <div class="cb-doc-fila">
        <span @class(['cb-etq', $claseEtiqueta]) data-cb="doc-obligatorio">{{ $etiqueta }}</span>
        <a class="cb-enlace" data-cb="doc-enlace" href="{{ $documento['url'] ?? '#' }}" @if (empty($documento['url'])) hidden @endif>Descargar formato ↓</a>
    </div>
</li>
