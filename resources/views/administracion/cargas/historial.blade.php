<x-layouts.app titulo="Historial de importaciones" modulo="Administración académica" :menu="config('menu_administracion')">
    <div class="flex flex-wrap items-end justify-between gap-normal">
        <div>
            <p class="ui-antetitulo">Importaciones SIAC</p>
            <h1 class="ui-titulo">Historial de importaciones</h1>
            <p class="ui-texto-gris mt-chico">Cada carga queda registrada con su archivo, usuario, fecha y resultado.</p>
        </div>
        <a href="{{ route('cargas.index') }}" class="ui-btn ui-btn-primario">Nueva carga</a>
    </div>

    @if (empty($historial) || count($historial) === 0)
        <div class="ui-vacio mt-amplio">Todavía no hay cargas registradas.</div>
    @else
        <div class="ui-tabla-caja mt-amplio">
            <table class="ui-tabla">
                <thead>
                    <tr>
                        <th>N.º</th>
                        <th>Archivo</th>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Creadas</th>
                        <th>Errores</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($historial as $item)
                        <tr>
                            <td class="font-semibold">#{{ $item->id }}</td>
                            <td>{{ $item->nombre_archivo }}</td>
                            <td>{{ ucfirst($item->tipo_carga) }}</td>
                            <td class="whitespace-nowrap">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $item->user->name ?? 'Sistema' }}</td>
                            <td><span class="ui-badge ui-badge-exito">{{ $item->filas_creadas }}</span></td>
                            <td>
                                @if ($item->filas_errores > 0)
                                    <a href="{{ route('cargas.reporte-errores', $item->id) }}" class="ui-btn ui-btn-secundario ui-btn-chico">Descargar errores ({{ $item->filas_errores }})</a>
                                @else
                                    <span class="text-chico text-gris">Sin errores</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>
