@props(['columnas' => [], 'paginacion' => null, 'vacio' => 'No hay registros.'])

@if (trim((string) $slot) === '')
    <div class="ui-vacio">{{ $vacio }}</div>
@else
    <div {{ $attributes->merge(['class' => 'ui-tabla-caja']) }}>
        <table class="ui-tabla">
            <thead>
                <tr>
                    @foreach ($columnas as $columna)
                        <th>{{ $columna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
@endif

@if ($paginacion)
    {{ $paginacion->links('administracion.partes.paginacion') }}
@endif
