<x-layouts.app titulo="Directores de carrera" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Personas</p>
    <h1 class="ui-titulo">Directores de carrera</h1>
    <p class="ui-texto-gris mt-chico">Cada carrera tiene un solo director vigente. Al asignar uno nuevo, la etapa del anterior se cierra el día antes. El historial se conserva.</p>

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">Asignar director</h2>
            @if ($directores->isEmpty())
                <div class="ui-alerta ui-alerta-aviso">
                    Nadie tiene el rol de director. Márcalo primero en <a class="ui-enlace" href="{{ route('administracion.personal') }}">Personal</a>.
                </div>
            @endif
            <form method="POST" action="{{ route('administracion.directores.store') }}" class="ui-form">
                @csrf
                <div class="ui-campo @error('carrera_id') ui-campo-error @enderror">
                    <label for="carrera_id">Carrera</label>
                    <select id="carrera_id" name="carrera_id" class="ui-control">
                        <option value="">Selecciona una carrera</option>
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id }}" @selected((string) old('carrera_id') === (string) $carrera->id)>{{ $carrera->nombre }}</option>
                        @endforeach
                    </select>
                    @error('carrera_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('trabajador_id') ui-campo-error @enderror">
                    <label for="trabajador_id">Director</label>
                    <select id="trabajador_id" name="trabajador_id" class="ui-control">
                        <option value="">Selecciona una persona</option>
                        @foreach ($directores as $director)
                            <option value="{{ $director->id }}" @selected((string) old('trabajador_id') === (string) $director->id)>{{ $director->apellidos }}, {{ $director->nombres }}</option>
                        @endforeach
                    </select>
                    @error('trabajador_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('fecha_inicio') ui-campo-error @enderror">
                    <label for="fecha_inicio">Desde</label>
                    <input id="fecha_inicio" name="fecha_inicio" type="date" class="ui-control" value="{{ old('fecha_inicio', $hoy) }}">
                    @error('fecha_inicio')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario" @disabled($directores->isEmpty())>Asignar</button>
                </div>
            </form>
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Asignaciones</h2>
            @include('administracion.partes.buscador', [
                'accion' => route('administracion.directores'),
                'placeholder' => 'Buscar por carrera',
                'filtro' => ['nombre' => 'estado', 'etiqueta' => 'Todas', 'opciones' => ['vigentes' => 'Solo vigentes']],
            ])
            @error('fecha_fin')
                <div class="ui-alerta ui-alerta-error mb-medio">{{ $message }}</div>
            @enderror
            @if ($asignaciones->isEmpty())
                <div class="ui-vacio">No hay asignaciones que coincidan.</div>
            @else
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead>
                            <tr>
                                <th>Carrera</th>
                                <th>Director</th>
                                <th>Periodo</th>
                                <th>Estado</th>
                                <th><span class="ui-sr-oculto">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asignaciones as $asignacion)
                                @php($vigente = ! $asignacion->fecha_fin || $asignacion->fecha_fin->toDateString() >= $hoy)
                                <tr>
                                    <td class="font-semibold">{{ $asignacion->carrera->nombre ?? '—' }}</td>
                                    <td>{{ $asignacion->trabajador ? $asignacion->trabajador->nombres.' '.$asignacion->trabajador->apellidos : '—' }}</td>
                                    <td class="whitespace-nowrap">{{ $asignacion->fecha_inicio->format('d/m/Y') }} – {{ $asignacion->fecha_fin?->format('d/m/Y') ?? 'hoy' }}</td>
                                    <td>
                                        @if ($vigente)
                                            <span class="ui-badge ui-badge-exito">Vigente</span>
                                        @else
                                            <span class="ui-badge">Terminado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($vigente && ! $asignacion->fecha_fin)
                                            <form method="POST" action="{{ route('administracion.directores.terminar', $asignacion) }}" class="text-right" onsubmit="return confirm('¿Terminar hoy la etapa de este director?')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="fecha_fin" value="{{ $hoy }}">
                                                <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">Terminar hoy</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $asignaciones->links('administracion.partes.paginacion') }}
            @endif
        </section>
    </div>
</x-layouts.app>
