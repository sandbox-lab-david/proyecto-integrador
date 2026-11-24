@props(['evaluacion'=>null])
<div {{ $attributes->class(['cb','cb-resultado-caja']) }} data-cb="resultado" hidden>
    <div data-cb="resultado-caja"><span data-cb="resultado-icono"></span><strong data-cb="resultado-titulo">Referencias informativas</strong><ul data-cb="resultado-motivos"></ul></div>
    <div data-cb="resultado-orientativo"><p>La facultad revisa tu solicitud.</p></div>
</div>
@foreach(data_get($evaluacion,'comprobaciones',[]) as $comprobacion)<p>{{ $comprobacion['texto'] }}</p>@endforeach
@once<link rel="stylesheet" href="{{ asset('assets/chatbot/chatbot.css') }}">@endonce
