@props(['costo' => null, 'plazo' => null, 'tipo' => null])

@php
    $monto = $costo ? '$' . number_format($costo['centavos'] / 100, 2, ',', '.') : null;
    $fecha = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->locale('es')->translatedFormat('j \d\e F \d\e Y') : null;
    $inicio = $plazo['inicio'] ?? null;
    $fin = $plazo['fin'] ?? null;
    $verCosto = $costo || $tipo === 'costo';
    $verPlazo = $plazo || $tipo === 'plazo';
@endphp

<div {{ $attributes->class('cb-datos') }} data-cb="datos" @if (! $verCosto && ! $verPlazo) hidden @endif>
    <div class="cb-dato" data-cb="dato-costo" @if (! $verCosto) hidden @endif>
        <span>Costo</span>
        <strong data-cb="costo-valor">{{ $monto ?? 'Consulta con tu facultad' }}</strong>
        <small data-cb="costo-moneda" @if (! $costo) hidden @endif>{{ $costo['moneda'] ?? 'USD' }}</small>
        <small data-cb="costo-nota" @if ($costo) hidden @endif>Lo confirma la facultad</small>
    </div>
    <div class="cb-dato" data-cb="dato-inicio" @if (! $inicio) hidden @endif>
        <span>Desde</span>
        <strong data-cb="plazo-inicio">{{ $fecha($inicio) }}</strong>
    </div>
    <div class="cb-dato" data-cb="dato-fin" @if (! $verPlazo) hidden @endif>
        <span>Hasta</span>
        <strong data-cb="plazo-fin">{{ $fecha($fin) ?? 'Consulta con tu facultad' }}</strong>
        <small data-cb="plazo-fin-nota" @if ($fin) hidden @endif>La define la facultad</small>
    </div>
    <div class="cb-dato cb-dato-ancho" data-cb="dato-plazo" @if (! ($plazo['texto'] ?? null)) hidden @endif>
        <span>Plazo</span>
        <p data-cb="plazo-texto">{{ $plazo['texto'] ?? '' }}</p>
    </div>
</div>
