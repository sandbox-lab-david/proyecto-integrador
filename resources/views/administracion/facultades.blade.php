<x-layouts.app titulo="Facultades" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Estructura académica</p>
    <h1 class="ui-titulo">Facultades</h1>
    <p class="ui-texto-gris mt-chico">Las facultades agrupan las carreras. Al correo de notificaciones llegan los avisos de las solicitudes de esa facultad. Una facultad con carreras activas no se puede desactivar.</p>

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">{{ $editando ? 'Editar facultad' : 'Nueva facultad' }}</h2>
            <form method="POST" action="{{ $editando ? route('administracion.facultades.update', $editando) : route('administracion.facultades.store') }}" class="ui-form">
                @csrf
                @if ($editando) @method('PUT') @endif
                <div class="ui-campo @error('codigo') ui-campo-error @enderror">
                    <label for="codigo">Código</label>
                    <input id="codigo" name="codigo" class="ui-control" value="{{ old('codigo', $editando?->codigo) }}" placeholder="ING" maxlength="20">
                    @error('codigo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('nombre') ui-campo-error @enderror">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" class="ui-control" value="{{ old('nombre', $editando?->nombre) }}" placeholder="Ingeniería">
                    @error('nombre')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('correo_notificaciones') ui-campo-error @enderror">
                    <label for="correo_notificaciones">Correo de notificaciones <span class="ui-opcional">(opcional)</span></label>
                    <input id="correo_notificaciones" name="correo_notificaciones" type="email" class="ui-control" value="{{ old('correo_notificaciones', $editando?->correo_notificaciones) }}" placeholder="facultad@uees.edu.ec">
                    <p class="ui-ayuda">Asistente o secretaría de la facultad.</p>
                    @error('correo_notificaciones')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario">{{ $editando ? 'Guardar cambios' : 'Guardar facultad' }}</button>
                    @if ($editando)
                        <a href="{{ route('administracion.facultades') }}" class="ui-btn ui-btn-quieto">Cancelar</a>
                    @endif
                </div>
            </form>
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Facultades registradas</h2>
            @include('administracion.partes.buscador', ['accion' => route('administracion.facultades')])
            @if ($facultades->isEmpty())
                <div class="ui-vacio">No hay facultades que coincidan.</div>
            @else
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Carreras</th>
                                <th>Estado</th>
                                <th><span class="ui-sr-oculto">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($facultades as $facultad)
                                <tr>
                                    <td class="font-semibold">{{ $facultad->codigo }}</td>
                                    <td>
                                        {{ $facultad->nombre }}
                                        @if ($facultad->correo_notificaciones)
                                            <span class="block text-chico text-gris">{{ $facultad->correo_notificaciones }}</span>
                                        @endif
                                    </td>
                                    <td><a class="ui-enlace" href="{{ route('administracion.carreras', ['facultad' => $facultad->id]) }}">{{ $facultad->carreras_activas_count }} de {{ $facultad->carreras_count }} activas</a></td>
                                    <td>
                                        @if ($facultad->activo)
                                            <span class="ui-badge ui-badge-exito">Activa</span>
                                        @else
                                            <span class="ui-badge">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-right">
                                        <a href="{{ route('administracion.facultades.editar', $facultad) }}" class="ui-btn ui-btn-quieto ui-btn-chico">Editar</a>
                                        <form method="POST" action="{{ route('administracion.facultades.estado', $facultad) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">{{ $facultad->activo ? 'Desactivar' : 'Activar' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $facultades->links('administracion.partes.paginacion') }}
            @endif
        </section>
    </div>
</x-layouts.app>
