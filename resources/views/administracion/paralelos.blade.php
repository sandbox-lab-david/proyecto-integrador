<x-layouts.app titulo="Paralelos y docentes" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Estructura académica</p>
    <h1 class="ui-titulo">Paralelos y docentes</h1>
    <p class="ui-texto-gris mt-chico">Cada paralelo es una materia ofertada en un periodo, con su docente. El portal del estudiante los usa para completar paralelo y docente automáticamente.</p>

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">{{ $editando ? 'Editar paralelo' : 'Nuevo paralelo' }}</h2>
            @if ($materias->isEmpty() || $periodos->isEmpty())
                <div class="ui-alerta ui-alerta-aviso">
                    <strong>Faltan datos</strong>
                    Primero registra al menos una <a class="ui-enlace" href="{{ route('administracion.materias') }}">materia</a> y un <a class="ui-enlace" href="{{ route('administracion.periodos') }}">periodo</a>.
                </div>
            @endif
            <form method="POST" action="{{ $editando ? route('administracion.paralelos.update', $editando) : route('administracion.paralelos.store') }}" class="ui-form">
                @csrf
                @if ($editando) @method('PUT') @endif
                <div class="ui-campo @error('materia_id') ui-campo-error @enderror">
                    <label for="materia_id">Materia</label>
                    <select id="materia_id" name="materia_id" class="ui-control">
                        <option value="">Selecciona una materia</option>
                        @foreach ($materias as $materia)
                            <option value="{{ $materia->id }}" @selected((string) old('materia_id', $editando?->materia_id) === (string) $materia->id)>{{ $materia->codigo }} · {{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                    @error('materia_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('periodo_id') ui-campo-error @enderror">
                    <label for="periodo_id">Periodo</label>
                    <select id="periodo_id" name="periodo_id" class="ui-control">
                        <option value="">Selecciona un periodo</option>
                        @foreach ($periodos as $periodo)
                            <option value="{{ $periodo->id }}" @selected((string) old('periodo_id', $editando?->periodo_id ?? $periodos->firstWhere('activo', true)?->id) === (string) $periodo->id)>{{ $periodo->nombre }}{{ $periodo->activo ? ' (activo)' : '' }}</option>
                        @endforeach
                    </select>
                    @error('periodo_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('profesor_id') ui-campo-error @enderror">
                    <label for="profesor_id">Docente <span class="ui-opcional">(opcional)</span></label>
                    <select id="profesor_id" name="profesor_id" class="ui-control">
                        <option value="">Sin asignar</option>
                        @foreach ($docentes as $docente)
                            <option value="{{ $docente->id }}" @selected((string) old('profesor_id', $editando?->profesor_id) === (string) $docente->id)>{{ $docente->apellidos }}, {{ $docente->nombres }}</option>
                        @endforeach
                    </select>
                    @error('profesor_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-medio">
                    <div class="ui-campo @error('paralelo') ui-campo-error @enderror">
                        <label for="paralelo">Paralelo</label>
                        <input id="paralelo" name="paralelo" class="ui-control" value="{{ old('paralelo', $editando?->paralelo) }}" placeholder="A1" maxlength="20">
                        @error('paralelo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                    <div class="ui-campo @error('cupo') ui-campo-error @enderror">
                        <label for="cupo">Cupo <span class="ui-opcional">(opcional)</span></label>
                        <input id="cupo" name="cupo" type="number" min="1" max="200" class="ui-control" value="{{ old('cupo', $editando?->cupo) }}">
                        @error('cupo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario" @disabled($materias->isEmpty() || $periodos->isEmpty())>{{ $editando ? 'Guardar cambios' : 'Guardar paralelo' }}</button>
                    @if ($editando)
                        <a href="{{ route('administracion.paralelos') }}" class="ui-btn ui-btn-quieto">Cancelar</a>
                    @endif
                </div>
            </form>
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Paralelos registrados</h2>
            @include('administracion.partes.buscador', [
                'accion' => route('administracion.paralelos'),
                'placeholder' => 'Buscar por materia',
                'filtro' => ['nombre' => 'periodo', 'etiqueta' => 'Todos los periodos', 'opciones' => $periodos->pluck('nombre', 'id')],
            ])
            @if ($cursos->isEmpty())
                <div class="ui-vacio">No hay paralelos que coincidan.</div>
            @else
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Paralelo</th>
                                <th>Periodo</th>
                                <th>Docente</th>
                                <th>Cupo</th>
                                <th><span class="ui-sr-oculto">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursos as $curso)
                                <tr>
                                    <td>
                                        <span class="font-semibold">{{ $curso->materia->codigo ?? '—' }}</span>
                                        <span class="block text-chico text-gris">{{ $curso->materia->nombre ?? '' }}</span>
                                    </td>
                                    <td><span class="ui-badge ui-badge-vino">{{ $curso->paralelo }}</span></td>
                                    <td>{{ $curso->periodo->nombre ?? '—' }}</td>
                                    <td>{{ $curso->profesor ? $curso->profesor->nombres.' '.$curso->profesor->apellidos : 'Sin asignar' }}</td>
                                    <td>{{ $curso->cupo ?? '—' }}</td>
                                    <td class="whitespace-nowrap text-right">
                                        <a href="{{ route('administracion.paralelos.editar', $curso) }}" class="ui-btn ui-btn-quieto ui-btn-chico">Editar</a>
                                        <form method="POST" action="{{ route('administracion.paralelos.destroy', $curso) }}" class="inline" onsubmit="return confirm('¿Borrar este paralelo?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">Borrar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $cursos->links('administracion.partes.paginacion') }}
            @endif
        </section>
    </div>
</x-layouts.app>
