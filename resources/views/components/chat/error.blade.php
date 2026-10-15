@props(['titulo' => null, 'mensaje' => null, 'reintentable' => false, 'suave' => false])

<div {{ $attributes->class(['cb-sistema', 'cb-sistema-suave' => $suave]) }} data-cb="error" role="alert">
    <span class="cb-sistema-icono" aria-hidden="true" data-cb="error-icono">!</span>
    <div>
        <strong data-cb="error-titulo">{{ $titulo }}</strong>
        <p data-cb="error-mensaje">{{ $mensaje }}</p>
        <div class="cb-acciones" data-cb="error-acciones">
            <button type="button" class="cb-btn cb-btn-primario" data-cb="reintentar" @if (! $reintentable) hidden @endif>Reintentar</button>
            <a class="cb-btn cb-btn-secundario" data-cb="error-enlace" href="#" hidden></a>
        </div>
    </div>
</div>
