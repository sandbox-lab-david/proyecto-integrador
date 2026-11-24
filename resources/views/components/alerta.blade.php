@props(['tipo' => 'info', 'titulo' => null])

@php
    $clases = [
        'exito' => 'ui-alerta ui-alerta-exito',
        'error' => 'ui-alerta ui-alerta-error',
        'aviso' => 'ui-alerta ui-alerta-aviso',
        'info' => 'ui-alerta',
    ];
@endphp

<div role="{{ $tipo === 'error' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => $clases[$tipo] ?? $clases['info']]) }}>
    @if ($titulo)
        <strong>{{ $titulo }}</strong>
    @endif
    {{ $slot }}
</div>
