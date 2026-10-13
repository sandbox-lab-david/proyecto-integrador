@props([
    'id',
    'accion',
    'metodo' => 'POST',
    'boton'  => 'Confirmar',
    'titulo' => '¿Estás seguro?',
])

<button type="button"
        onclick="document.getElementById('{{ $id }}').showModal()"
        class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700">
    {{ $boton }}
</button>

<dialog id="{{ $id }}" class="rounded-lg p-6 w-full max-w-md backdrop:bg-black/50">
    <h2 class="text-lg font-semibold mb-2">{{ $titulo }}</h2>

    <div class="text-gray-700 mb-6">
        {{ $slot }}
    </div>

    <form method="POST" action="{{ $accion }}" class="flex justify-end gap-2">
        @csrf
        @method($metodo)

        <button type="button"
                onclick="this.closest('dialog').close()"
                class="px-3 py-1.5 rounded-lg border border-gray-300 text-sm">
            Cancelar
        </button>
        <button type="submit"
                class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700">
            {{ $boton }}
        </button>
    </form>
</dialog>