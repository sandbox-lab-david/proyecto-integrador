@props([
    'id',
    'accion',
    'metodo' => 'POST',
    'boton' => 'Confirmar',
    'titulo' => '¿Estás seguro?',
])

<button type="button" onclick="document.getElementById('{{ $id }}').showModal()" {{ $attributes->merge(['class' => 'ui-btn ui-btn-peligro ui-btn-chico']) }}>
    {{ $boton }}
</button>

<dialog id="{{ $id }}" class="ui-modal" aria-labelledby="{{ $id }}-titulo">
    <h2 id="{{ $id }}-titulo">{{ $titulo }}</h2>
    <p>{{ $slot }}</p>

    <form method="POST" action="{{ $accion }}" class="ui-acciones">
        @csrf
        @method($metodo)
        <button type="button" onclick="this.closest('dialog').close()" class="ui-btn ui-btn-quieto">Cancelar</button>
        <button type="submit" class="ui-btn ui-btn-peligro">{{ $boton }}</button>
    </form>
</dialog>
