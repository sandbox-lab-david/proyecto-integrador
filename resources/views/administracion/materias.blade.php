<x-layouts.app titulo="Materias" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Estructura académica</p>
    <h1 class="ui-titulo">Materias</h1>
    <p class="ui-texto-gris mt-chico">Catálogo institucional de asignaturas. Los demás módulos las usan para que el estudiante elija en lugar de escribir. Una materia desactivada no aparece en formularios nuevos, pero sigue en las solicitudes antiguas.</p>

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">{{ $editando ? 'Editar materia' : 'Nueva materia' }}</h2>
            <form method="POST" action="{{ $editando ? route('administracion.materias.update', $editando) : route('administracion.materias.store') }}" class="ui-form">
                @csrf
                @if ($editando) @method('PUT') @endif
                <div class="ui-campo @error('codigo') ui-campo-error @enderror">
                    <label for="codigo">Código</label>
                    <input id="codigo" name="codigo" class="ui-control" value="{{ old('codigo', $editando?->codigo) }}" placeholder="UMAT202" maxlength="20">
                    @error('codigo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('nombre') ui-campo-error @enderror">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" class="ui-control" value="{{ old('nombre', $editando?->nombre) }}" placeholder="Cálculo diferencial">
                    @error('nombre')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('creditos') ui-campo-error @enderror">
                    <label for="creditos">Créditos</label>
                    <input id="creditos" name="creditos" type="number" min="0" max="20" class="ui-control" value="{{ old('creditos', $editando?->creditos ?? 4) }}">
                    @error('creditos')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('categoria_materia_id') ui-campo-error @enderror">
                    <label for="categoria_materia_id">Categoría <span class="ui-opcional">(opcional)</span></label>
                    <select id="categoria_materia_id" name="categoria_materia_id" class="ui-control">
                        <option value="">Sin categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected((string) old('categoria_materia_id', $editando?->categoria_materia_id) === (string) $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_materia_id')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario">{{ $editando ? 'Guardar cambios' : 'Guardar materia' }}</button>
                    @if ($editando)
                        <a href="{{ route('administracion.materias') }}" class="ui-btn ui-btn-quieto">Cancelar</a>
                    @endif
                </div>
            </form>
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Materias registradas</h2>
            @include('administracion.partes.buscador', ['accion' => route('administracion.materias')])
            @if ($materias->isEmpty())
                <div class="ui-vacio">No hay materias que coincidan.</div>
            @else
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Créditos</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th><span class="ui-sr-oculto">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($materias as $materia)
                                <tr>
                                    <td class="font-semibold">{{ $materia->codigo }}</td>
                                    <td>{{ $materia->nombre }}</td>
                                    <td>{{ $materia->creditos }}</td>
                                    <td>{{ $materia->categoria->nombre ?? '—' }}</td>
                                    <td>
                                        @if ($materia->activo)
                                            <span class="ui-badge ui-badge-exito">Activa</span>
                                        @else
                                            <span class="ui-badge">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-right">
                                        <a href="{{ route('administracion.materias.editar', $materia) }}" class="ui-btn ui-btn-quieto ui-btn-chico">Editar</a>
                                        <form method="POST" action="{{ route('administracion.materias.estado', $materia) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">{{ $materia->activo ? 'Desactivar' : 'Activar' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $materias->links('administracion.partes.paginacion') }}
            @endif
        </section>
    </div>
</x-layouts.app>
