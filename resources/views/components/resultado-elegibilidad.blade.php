@props(['evaluacion' => null])

@php
    $titulos = [
        'cumple' => 'Cumples los requisitos',
        'no_cumple' => 'Por ahora no cumples los requisitos',
        'revision_manual' => 'Este caso lo revisa la facultad',
    ];
    $iconos = ['cumple' => '✓', 'no_cumple' => '×', 'revision_manual' => '?'];
    $clases = ['cumple' => 'cb-cumple', 'no_cumple' => 'cb-no-cumple', 'revision_manual' => 'cb-revision'];

    $resultado = data_get($evaluacion, 'resultado');
    $resultado = $resultado instanceof \BackedEnum ? $resultado->value : $resultado;
    $resultado = $resultado === 'requiere_revision' ? 'revision_manual' : $resultado;

    $motivos = collect(data_get($evaluacion, 'motivos') ?? [])
        ->map(fn ($m) => is_array($m) ? ($m['texto'] ?? '') : (string) $m)
        ->filter()
        ->values();

    $verificado = (bool) data_get($evaluacion, 'con_datos_verificados', false);
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
    <div data-cb="resultado-orientativo" @if ($verificado) hidden @endif>
        <span class="cb-orientativo">Resultado orientativo</span>
        <p class="cb-nota">Se calculó con los datos que declaraste. La facultad los verifica cuando revisa la solicitud.</p>
    </div>
</div>

@once
    <link rel="stylesheet" href="{{ asset('assets/chatbot/chatbot.css') }}">
@endonce
