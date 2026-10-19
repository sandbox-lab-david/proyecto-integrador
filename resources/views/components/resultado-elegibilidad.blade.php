@props(['evaluacion' => null])

@php
    $titulos = [
        'cumple' => 'Cumples los requisitos',
        'no_cumple' => 'Por ahora no cumples los requisitos',
        'revision_manual' => 'Este caso lo revisa la facultad',
        'parcial' => 'Solo se revisó una parte de los requisitos',
        'sin_evaluacion' => 'Este trámite no tiene revisión automática',
    ];
    $iconos = ['cumple' => '✓', 'no_cumple' => '×', 'revision_manual' => '?', 'parcial' => '…', 'sin_evaluacion' => 'i'];
    $clases = [
        'cumple' => 'cb-cumple',
        'no_cumple' => 'cb-no-cumple',
        'revision_manual' => 'cb-revision',
        'parcial' => 'cb-revision',
        'sin_evaluacion' => 'cb-sin-evaluacion',
    ];

    $resultado = data_get($evaluacion, 'resultado');
    $resultado = $resultado instanceof \BackedEnum ? $resultado->value : $resultado;

    $listaMotivos = collect(data_get($evaluacion, 'motivos') ?? []);
    $codigos = $listaMotivos->map(fn ($m) => is_array($m) ? ($m['codigo'] ?? '') : '');

    if ($resultado && $codigos->contains('evaluacion_no_disponible')) {
        $resultado = 'sin_evaluacion';
    } elseif ($resultado === 'requiere_revision' && $codigos->contains('condiciones_no_evaluadas')) {
        $resultado = 'parcial';
    } elseif ($resultado === 'requiere_revision') {
        $resultado = 'revision_manual';
    }

    $motivos = $listaMotivos
        ->map(fn ($m) => is_array($m) ? ($m['texto'] ?? '') : (string) $m)
        ->filter()
        ->values();

    $verificado = (bool) data_get($evaluacion, 'con_datos_verificados', false);
    $ocultarOrientativo = $verificado || $resultado === 'sin_evaluacion';
@endphp

<div {{ $attributes->class(['cb', 'cb-resultado-caja']) }}
     data-cb="resultado"
     @foreach ($titulos as $clave => $texto) data-titulo-{{ $clave }}="{{ $texto }}" data-icono-{{ $clave }}="{{ $iconos[$clave] }}" data-clase-{{ $clave }}="{{ $clases[$clave] }}" @endforeach
     @if (! $resultado) hidden @endif>
    <div @class(['cb-resultado', $clases[$resultado] ?? null]) data-cb="resultado-caja">
        <div class="cb-resultado-cab">
            <span class="cb-res-icono" aria-hidden="true" data-cb="resultado-icono">{{ $iconos[$resultado] ?? '' }}</span>
            <strong data-cb="resultado-titulo">{{ $titulos[$resultado] ?? '' }}</strong>
        </div>
        <ul @class(['cb-check', 'cb-motivos' => $resultado !== 'cumple']) data-cb="resultado-motivos">
            @foreach ($motivos as $motivo)
                <li>{{ $motivo }}</li>
            @endforeach
        </ul>
    </div>
    <div data-cb="resultado-orientativo" @if ($ocultarOrientativo) hidden @endif>
        <span class="cb-orientativo">Resultado orientativo</span>
        <p class="cb-nota">Se calculó con los datos que declaraste. La facultad los verifica cuando revisa la solicitud.</p>
    </div>
</div>

@once
    <link rel="stylesheet" href="{{ asset('assets/chatbot/chatbot.css') }}">
@endonce
