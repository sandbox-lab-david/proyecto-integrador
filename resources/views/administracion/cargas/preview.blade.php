<x-layouts.app titulo="Vista previa de importación" modulo="Administración académica" :menu="config('menu_administracion')">
    @php
        $badges = ['valido' => 'ui-badge-exito', 'duplicado' => 'ui-badge-alerta', 'error' => 'ui-badge-peligro'];
        $nombres = ['valido' => 'Válida', 'duplicado' => 'Duplicada', 'error' => 'Con error'];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-normal">
        <div>
            <p class="ui-antetitulo">Importaciones SIAC · Paso 3</p>
            <h1 class="ui-titulo">Vista previa: {{ ucfirst($tipoCarga) }}</h1>
            <p class="ui-texto-gris mt-chico">Solo se guardarán las filas válidas. Corrige las demás en el archivo y vuelve a cargarlo.</p>
        </div>
        <a href="{{ route('cargas.index') }}" class="ui-btn ui-btn-quieto">← Cancelar y volver</a>
    </div>

    <dl class="ui-datos mt-amplio">
        <div class="ui-dato"><dt>Filas válidas</dt><dd class="text-exito">{{ $resumen['correctas'] }}</dd></div>
        <div class="ui-dato"><dt>Duplicadas</dt><dd class="text-alerta">{{ $resumen['duplicados'] }}</dd></div>
        <div class="ui-dato"><dt>Con error</dt><dd class="text-peligro">{{ $resumen['errores'] }}</dd></div>
    </dl>

    <div class="ui-tabla-caja mt-amplio">
        <table class="ui-tabla">
            <thead>
                <tr>
                    <th>Fila</th>
                    <th>Código</th>
                    <th>Nombre o detalle</th>
                    <th>Estado</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    <tr>
                        <td class="font-semibold">{{ $fila['num_fila'] }}</td>
                        <td>{{ $fila['codigo'] }}</td>
                        <td>{{ $fila['nombre'] }}</td>
                        <td><span class="ui-badge {{ $badges[$fila['estado']] ?? '' }}">{{ $nombres[$fila['estado']] ?? $fila['estado'] }}</span></td>
                        <td class="text-chico text-gris">{{ $fila['observacion'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <form action="{{ route('cargas.procesar') }}" method="POST" class="ui-acciones justify-end mt-amplio">
        @csrf
        <input type="hidden" name="batch_id" value="{{ $batchId }}">
        <a href="{{ route('cargas.index') }}" class="ui-btn ui-btn-secundario">Cancelar</a>
        <button type="submit" class="ui-btn ui-btn-primario" @disabled($resumen['correctas'] == 0)>Guardar {{ $resumen['correctas'] }} filas válidas</button>
    </form>
</x-layouts.app>
