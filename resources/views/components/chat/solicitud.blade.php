@props(['solicitud' => []])

<li class="cb-solicitud" data-cb="solicitud">
    <div class="cb-solicitud-datos">
        <strong data-cb="sol-tramite">{{ $solicitud['tramite'] ?? '' }}</strong>
        <span @class(['cb-estado', 'cb-estado-' . ($solicitud['color'] ?? 'gris')]) data-cb="sol-estado">{{ $solicitud['estado'] ?? '' }}</span>
    </div>
    <a class="cb-enlace" data-cb="sol-enlace" href="{{ $solicitud['url'] ?? '#' }}" @if (empty($solicitud['url'])) hidden @endif>Ver solicitud →</a>
</li>
