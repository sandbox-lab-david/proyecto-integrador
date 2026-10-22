<x-layouts.app titulo="Historial de importaciones" modulo="Administración académica" :menu="config('menu_administracion')">
    <div class="flex flex-wrap items-end justify-between gap-normal">
        <div>
            <p class="ui-antetitulo">Importaciones SIAC</p>
            <h1 class="ui-titulo">Historial de importaciones</h1>
            <p class="ui-texto-gris mt-chico">Cada carga queda registrada con su archivo, usuario, fecha y resultado.</p>
        </div>
        <a href="{{ route('cargas.index') }}" class="ui-btn ui-btn-primario">Nueva carga</a>
    </div>

    <form method="GET" action="{{ route('cargas.historial') }}" class="flex flex-wrap gap-medio items-center mt-amplio mb-medio">
        <label for="entidad" class="ui-sr-oculto">Tipo</label>
        <select id="entidad" name="entidad" class="ui-control w-auto">
            <option value="">Todos los tipos</option>
            @foreach ($titulos as $entidad => $titulo)
                <option value="{{ $entidad }}" @selected(request('entidad') === $entidad)>{{ $titulo }}</option>
            @endforeach
        </select>
        <button type="submit" class="ui-btn ui-btn-secundario">Filtrar</button>
    </form>

    @if ($importaciones->isEmpty())
        <div class="ui-vacio">Todavía no hay cargas registradas.</div>
    @else
        <div class="ui-tabla-caja">
            <table class="ui-tabla">
                <thead>
                    <tr>
                        <th>N.º</th>
                        <th>Archivo</th>
                        <th>Fecha y usuario</th>
                        <th>Estado</th>
                        <th>Creadas</th>
                        <th>Actualizadas</th>
                        <th>Rechazadas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($importaciones as $item)
                        <tr>
                            <td class="font-semibold"><a class="ui-enlace" href="{{ route('cargas.detalle', $item) }}">#{{ $item->id }}</a></td>
                            <td>
                                {{ $item->nombreArchivo() }}
                                <span class="block text-chico text-gris">{{ $titulos[$item->entidad] ?? $item->entidad }}</span>
                            </td>
                            <td class="text-chico">
                                {{ $item->created_at->timezone('America/Guayaquil')->format('d/m/Y H:i') }}
                                <span class="block text-gris">{{ $item->user->name ?? '—' }}</span>
                            </td>
                            <td>@include('administracion.cargas.estado', ['estado' => $item->estado])</td>
                            <td>{{ $item->creadas }}</td>
                            <td>{{ $item->actualizadas }}</td>
                            <td>
                                @if ($item->rechazadas)
                                    <a class="ui-enlace" href="{{ route('cargas.detalle', $item) }}">{{ $item->rechazadas }}</a>
                                @else
                                    0
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $importaciones->links('administracion.partes.paginacion') }}
    @endif
</x-layouts.app>
