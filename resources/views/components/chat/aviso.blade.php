@props(['texto' => null, 'titulo' => 'Ten en cuenta'])

<div {{ $attributes->class('cb-aviso') }} data-cb="aviso" @if (! $texto) hidden @endif>
    <b>{{ $titulo }}</b>
    <span data-cb="aviso-texto">{{ $texto }}</span>
</div>
