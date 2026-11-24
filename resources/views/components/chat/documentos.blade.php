@props(['documentos' => []])

<ul {{ $attributes->class('cb-docs') }} data-cb="documentos" @if (empty($documentos)) hidden @endif>
    @foreach ($documentos as $documento)
        <x-chat.documento :documento="$documento" />
    @endforeach
</ul>
