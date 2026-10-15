@props(['costo' => null, 'plazo' => null])

@php
    $monto = $costo ? '$' . number_format($costo['centavos'] / 100, 2, ',', '.') : null;
    $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->locale('es')->translatedFormat('j \d\e F \d\e Y') : null;
    $inicio = $plazo['inicio'] ?? null;
    $fin = $plazo['fin'] ?? null;
@endphp

<div {{ $attributes->class('cb-datos') }} data-cb="datos" @if (! $costo && ! $plazo) hidden @endif>
    <div class="cb-dato" data-cb="dato-costo" @if (! $costo) hidden @endif>
        <span>Costo</span>
        <strong data-cb="costo-valor">{{ $monto }}</strong>
        <small data-cb="costo-moneda">{{ $costo['moneda'] ?? 'USD' }}</small>
    </div>
    <div class="cb-dato" data-cb="dato-inicio" @if (! $inicio) hidden @endif>
        <span>Desde</span>
        <strong data-cb="plazo-inicio">{{ $fecha($inicio) }}</strong>
    </div>
    <div class="cb-dato" data-cb="dato-fin" @if (! $plazo) hidden @endif>
        <span>Hasta</span>
        <strong data-cb="plazo-fin">{{ $fecha($fin) ?? 'Sin fecha' }}</strong>
        <small data-cb="plazo-fin-nota" @if ($fin) hidden @endif>La define la facultad</small>
    </div>
    <div class="cb-dato cb-dato-ancho" data-cb="dato-plazo" @if (! ($plazo['texto'] ?? null)) hidden @endif>
        <span>Plazo</span>
        <p data-cb="plazo-texto">{{ $plazo['texto'] ?? '' }}</p>
    </div>
</div>
