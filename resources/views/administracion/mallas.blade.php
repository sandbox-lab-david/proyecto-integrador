<x-layouts.app titulo="Mallas" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Estructura académica</p>
    <h1 class="ui-titulo">Mallas</h1>
    <p class="ui-texto-gris mt-chico">Una malla es una versión del plan de estudios de una carrera. Cada carrera tiene una sola malla vigente: al marcar una, la anterior deja de serlo.</p>

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">{{ $editando ? 'Editar malla' : 'Nueva malla' }}</h2>
            <form method="POST" action="{{ $editando ? route('administracion.mallas.update', $editando) : route('administracion.mallas.store') }}" class="ui-form">
                @csrf
                @if ($editando) @method('PUT') @endif
                <div class="ui-campo @error('carrera_id') ui-campo-error @enderror">
                    <label for="carrera_id">Carrera</label>
                    <select id="carrera_id" name="carrera_id" class="ui-control">
                        <option value="">Selecciona una carrera</option>
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id }}" @selected((string) old('carrera_id', $editando?->carrera_id ?? request('carrera')) === (string) $carrera->id)>{{ $carrera->nombre }}</option>
                        @endforeach
                    </select>
                    @error('carrera_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('version') ui-campo-error @enderror">
                    <label for="version">Versión</label>
                    <input id="version" name="version" class="ui-control" value="{{ old('version', $editando?->version) }}" placeholder="2024" maxlength="20">
                    @error('version')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <label class="ui-casilla">
                    <input type="checkbox" name="vigente" value="1" @checked(session()->hasOldInput() ? old('vigente') : (bool) $editando?->vigente)>
                    <span>Es la malla vigente de la carrera</span>
                </label>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario">{{ $editando ? 'Guardar cambios' : 'Crear malla' }}</button>
                    @if ($editando)
                        <a href="{{ route('administracion.mallas') }}" class="ui-btn ui-btn-quieto">Cancelar</a>
                    @endif
                </div>
            </form>
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Mallas registradas</h2>
            @include('administracion.partes.buscador', [
                'accion' => route('administracion.mallas'),
                'placeholder' => 'Buscar por carrera',
                'filtro' => ['nombre' => 'carrera', 'etiqueta' => 'Todas las carreras', 'opciones' => $carreras->pluck('nombre', 'id')],
            ])
            @if ($mallas->isEmpty())
                <div class="ui-vacio">No hay mallas que coincidan.</div>
            @else
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead>
                            <tr>
                                <th>Carrera</th>
                                <th>Versión</th>
                                <th>Materias</th>
                                <th>Estado</th>
                                <th><span class="ui-sr-oculto">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mallas as $malla)
                                <tr>
                                    <td>
                                        <span class="font-semibold">{{ $malla->carrera->nombre ?? '—' }}</span>
                                        <span class="block text-chico text-gris">{{ $malla->carrera->codigo ?? '' }}</span>
                                    </td>
                                    <td>{{ $malla->version }}</td>
                                    <td>{{ $malla->materias_count }}</td>
                                    <td>
                                        @if ($malla->vigente)
                                            <span class="ui-badge ui-badge-exito">Vigente</span>
                                        @else
                                            <span class="ui-badge">No vigente</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-right">
                                        <a href="{{ route('administracion.mallas.ver', $malla) }}" class="ui-btn ui-btn-secundario ui-btn-chico">Materias</a>
                                        <a href="{{ route('administracion.mallas.editar', $malla) }}" class="ui-btn ui-btn-quieto ui-btn-chico">Editar</a>
                                        @unless ($malla->vigente)
                                            <form method="POST" action="{{ route('administracion.mallas.vigente', $malla) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">Marcar vigente</button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $mallas->links('administracion.partes.paginacion') }}
            @endif
        </section>
    </div>
</x-layouts.app>
