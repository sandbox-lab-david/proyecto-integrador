{{-- Respuesta de la pre-validación (RF-2.8); el formulario la inserta en #resultado-prevalidacion. --}}
@if ($prevalidacion['disponible'])
    @if ($prevalidacion['comprobaciones'])
        <x-resultado-elegibilidad :evaluacion="$prevalidacion" />
    @endif

    @foreach ($prevalidacion['items'] as $item)
        <p>{{ $item }}</p>
    @endforeach

    @if ($prevalidacion['aviso'])
        <div class="aviso">{{ $prevalidacion['aviso'] }}</div>
    @endif
@else
    <p class="error" role="alert">{{ $prevalidacion['error'] }}</p>
    <button class="secundario" type="button" data-reintentar-prevalidacion>
        Volver a consultar
    </button>
@endif
