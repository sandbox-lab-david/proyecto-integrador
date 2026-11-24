<x-layouts.app titulo="Carreras" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Estructura académica</p>
    <h1 class="ui-titulo">Carreras</h1>
    <p class="ui-texto-gris mt-chico">Cada carrera pertenece a una facultad y se ofrece en una o varias modalidades. El portal del estudiante solo muestra las carreras activas.</p>

    @php
        $marcadas = collect(session()->hasOldInput() ? old('modalidades', []) : ($editando?->modalidades->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id);
    @endphp

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">{{ $editando ? 'Editar carrera' : 'Nueva carrera' }}</h2>
            <form method="POST" action="{{ $editando ? route('administracion.carreras.update', $editando) : route('administracion.carreras.store') }}" class="ui-form">
                @csrf
                @if ($editando) @method('PUT') @endif
                <div class="ui-campo @error('facultad_id') ui-campo-error @enderror">
                    <label for="facultad_id">Facultad</label>
                    <select id="facultad_id" name="facultad_id" class="ui-control">
                        <option value="">Selecciona una facultad</option>
                        @foreach ($facultades as $facultad)
                            <option value="{{ $facultad->id }}" @selected((string) old('facultad_id', $editando?->facultad_id ?? request('facultad')) === (string) $facultad->id)>{{ $facultad->nombre }}{{ $facultad->activo ? '' : ' (inactiva)' }}</option>
                        @endforeach
                    </select>
                    @error('facultad_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-[110px_minmax(0,1fr)] gap-medio">
                    <div class="ui-campo @error('codigo') ui-campo-error @enderror">
                        <label for="codigo">Código</label>
                        <input id="codigo" name="codigo" class="ui-control" value="{{ old('codigo', $editando?->codigo) }}" placeholder="COMP" maxlength="20">
                        @error('codigo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                    <div class="ui-campo @error('nivel') ui-campo-error @enderror">
                        <label for="nivel">Nivel</label>
                        <select id="nivel" name="nivel" class="ui-control">
                            @foreach (['grado' => 'Grado', 'posgrado' => 'Posgrado'] as $valor => $texto)
                                <option value="{{ $valor }}" @selected(old('nivel', $editando?->nivel ?? 'grado') === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                        @error('nivel')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="ui-campo @error('nombre') ui-campo-error @enderror">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" class="ui-control" value="{{ old('nombre', $editando?->nombre) }}" placeholder="Ingeniería en Computación">
                    @error('nombre')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <fieldset class="ui-campo @error('modalidades') ui-campo-error @enderror">
                    <legend class="font-semibold">Modalidades en que se ofrece</legend>
                    <div class="grid grid-cols-2 gap-chico mt-chico">
                        @foreach ($modalidades as $modalidad)
                            <label class="ui-casilla">
                                <input type="checkbox" name="modalidades[]" value="{{ $modalidad->id }}" @checked($marcadas->contains($modalidad->id))>
                                <span>{{ $modalidad->nombre }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('modalidades')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </fieldset>
                <label class="ui-casilla">
                    <input type="checkbox" name="admite_tercer_registro" value="1" @checked(session()->hasOldInput() ? old('admite_tercer_registro') : ($editando ? $editando->admite_tercer_registro : true))>
                    <span>Admite tercer registro</span>
                </label>
                <p class="ui-ayuda">Medicina y Enfermería no lo admiten.</p>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario">{{ $editando ? 'Guardar cambios' : 'Guardar carrera' }}</button>
                    @if ($editando)
                        <a href="{{ route('administracion.carreras') }}" class="ui-btn ui-btn-quieto">Cancelar</a>
                    @endif
                </div>
            </form>
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Carreras registradas</h2>
            @include('administracion.partes.buscador', [
                'accion' => route('administracion.carreras'),
                'filtro' => ['nombre' => 'facultad', 'etiqueta' => 'Todas las facultades', 'opciones' => $facultades->pluck('nombre', 'id')],
            ])
            @if ($carreras->isEmpty())
                <div class="ui-vacio">No hay carreras que coincidan.</div>
            @else
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead>
                            <tr>
                                <th>Carrera</th>
                                <th>Facultad</th>
                                <th>Modalidades</th>
                                <th>Estado</th>
                                <th><span class="ui-sr-oculto">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($carreras as $carrera)
                                <tr>
                                    <td>
                                        <span class="font-semibold">{{ $carrera->nombre }}</span>
                                        <span class="block text-chico text-gris">{{ $carrera->codigo }} · {{ ucfirst($carrera->nivel) }}{{ $carrera->admite_tercer_registro ? '' : ' · Sin tercer registro' }}</span>
                                    </td>
                                    <td class="text-chico">{{ $carrera->facultad->nombre ?? '—' }}</td>
                                    <td class="text-chico">{{ $carrera->modalidades->pluck('nombre')->join(', ') ?: '—' }}</td>
                                    <td>
                                        @if ($carrera->activo)
                                            <span class="ui-badge ui-badge-exito">Activa</span>
                                        @else
                                            <span class="ui-badge">Inactiva</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex flex-col items-end gap-chico">
                                            <a href="{{ route('administracion.carreras.editar', $carrera) }}" class="ui-btn ui-btn-quieto ui-btn-chico">Editar</a>
                                            <form method="POST" action="{{ route('administracion.carreras.estado', $carrera) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">{{ $carrera->activo ? 'Desactivar' : 'Activar' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $carreras->links('administracion.partes.paginacion') }}
            @endif
        </section>
    </div>
</x-layouts.app>
