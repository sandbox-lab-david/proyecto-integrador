@props(['nota' => null])

<div {{ $attributes->class('cb-usuario') }} data-cb="usuario">
    <span data-cb="texto">{{ $slot }}</span>
    <small data-cb="nota" @if (! $nota) hidden @endif>{{ $nota }}</small>
</div>
