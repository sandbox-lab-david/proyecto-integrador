@props(['opciones' => []])

<div {{ $attributes->class('cb-sugerencias') }} data-cb="sugerencias">
    @foreach ($opciones as $opcion)
        <button type="button" class="cb-sug" data-valor="{{ $opcion['id'] }}">{{ $opcion['texto'] }}</button>
    @endforeach
</div>
