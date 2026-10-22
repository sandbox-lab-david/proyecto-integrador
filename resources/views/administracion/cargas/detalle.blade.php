<x-layouts.app titulo="Importación #{{ $importacion->id }}" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo"><a class="ui-enlace" href="{{ route('cargas.historial') }}">Historial de importaciones</a></p>
    <h1 class="ui-titulo">Importación #{{ $importacion->id }}: {{ $importador->titulo() }}</h1>

    <div class="ui-tarjeta mt-amplio">
        <dl class="grid gap-medio sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="ui-texto-gris text-chico">Archivo</dt><dd class="font-semibold break-all">{{ $importacion->nombreArchivo() }}</dd></div>
            <div><dt class="ui-texto-gris text-chico">Usuario</dt><dd class="font-semibold">{{ $importacion->user->name ?? '—' }}</dd></div>
            <div><dt class="ui-texto-gris text-chico">Fecha</dt><dd class="font-semibold">{{ $importacion->created_at->timezone('America/Guayaquil')->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="ui-texto-gris text-chico">Estado</dt><dd>@include('administracion.cargas.estado', ['estado' => $importacion->estado])</dd></div>
        </dl>
    </div>

    @if ($importacion->estado === 'procesando')
        <div class="ui-alerta ui-alerta-aviso mt-amplio">
            <strong>Se está procesando en segundo plano</strong>
            Actualiza esta página en unos minutos. Si no avanza, revisa que esté corriendo <code>php artisan queue:work</code>.
        </div>
    @endif

    <dl class="ui-datos mt-amplio">
        <div class="ui-dato"><dt>Filas</dt><dd>{{ $importacion->total_filas }}</dd></div>
        <div class="ui-dato"><dt>Creadas</dt><dd>{{ $importacion->creadas }}</dd></div>
        <div class="ui-dato"><dt>Actualizadas</dt><dd>{{ $importacion->actualizadas }}</dd></div>
        <div class="ui-dato"><dt>Rechazadas</dt><dd>{{ $importacion->rechazadas }}</dd></div>
    </dl>

    @if ($importacion->errores)
        <div class="flex flex-wrap items-center justify-between gap-medio mt-amplio mb-medio">
            <h2 class="ui-subtitulo">Filas rechazadas</h2>
            <a href="{{ route('cargas.reporte-errores', $importacion) }}" class="ui-btn ui-btn-secundario ui-btn-chico">Descargar errores (CSV)</a>
        </div>
        <div class="ui-tabla-caja">
            <table class="ui-tabla">
                <thead>
                    <tr>
                        <th>Fila</th>
                        <th>Código</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($importacion->errores as $error)
                        <tr>
                            <td class="font-semibold">{{ $error['fila'] ?? '—' }}</td>
                            <td>{{ $error['codigo'] ?? '—' }}</td>
                            <td>{{ $error['motivo'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif ($importacion->estado === 'completada')
        <div class="ui-alerta ui-alerta-exito mt-amplio">Todas las filas se importaron sin errores.</div>
    @endif
</x-layouts.app>
