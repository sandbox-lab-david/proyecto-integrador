<form method="GET" action="{{ $accion }}" class="flex flex-wrap gap-medio items-center mb-medio">
    <label for="buscar" class="ui-sr-oculto">Buscar</label>
    <input id="buscar" name="buscar" type="search" class="ui-control flex-1 min-w-[180px]" value="{{ request('buscar') }}" placeholder="{{ $placeholder ?? 'Buscar por código o nombre' }}">
    @isset($filtro)
        <label for="{{ $filtro['nombre'] }}" class="ui-sr-oculto">{{ $filtro['etiqueta'] }}</label>
        <select id="{{ $filtro['nombre'] }}" name="{{ $filtro['nombre'] }}" class="ui-control w-auto max-w-[260px]">
            <option value="">{{ $filtro['etiqueta'] }}</option>
            @foreach ($filtro['opciones'] as $valor => $texto)
                <option value="{{ $valor }}" @selected((string) request($filtro['nombre']) === (string) $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    @endisset
    <button type="submit" class="ui-btn ui-btn-secundario">Buscar</button>
    @if (request()->filled('buscar') || (isset($filtro) && request()->filled($filtro['nombre'])))
        <a href="{{ $accion }}" class="ui-btn ui-btn-quieto">Limpiar</a>
    @endif
</form>
