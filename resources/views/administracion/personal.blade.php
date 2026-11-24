<x-layouts.app titulo="Personal" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Personas</p>
    <h1 class="ui-titulo">Personal</h1>
    <p class="ui-texto-gris mt-chico">Asistentes, directores, docentes, administradores y personal de consulta. Una persona puede tener varios roles. Los docentes pueden registrarse sin acceso al sistema.</p>

    @php
        $rolesMarcados = collect(session()->hasOldInput() ? old('roles', []) : ($editando ? $rolesDe->get($editando->id, collect())->all() : []));
        $facultadesMarcadas = collect(session()->hasOldInput() ? old('facultades', []) : ($editando?->facultades->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id);
        $conAcceso = session()->hasOldInput() ? (bool) old('acceso') : ($editando ? (bool) $editando->user?->activo : true);
        $nombresRol = $roles->pluck('nombre', 'codigo');
    @endphp

    <div class="grid gap-amplio mt-amplio lg:grid-cols-[340px_minmax(0,1fr)] items-start">
        <section class="ui-tarjeta">
            <h2 class="ui-subtitulo">{{ $editando ? 'Editar persona' : 'Nueva persona' }}</h2>
            <form method="POST" action="{{ $editando ? route('administracion.personal.update', $editando) : route('administracion.personal.store') }}" class="ui-form">
                @csrf
                @if ($editando) @method('PUT') @endif
                <div class="grid grid-cols-2 gap-medio">
                    <div class="ui-campo @error('nombres') ui-campo-error @enderror">
                        <label for="nombres">Nombres</label>
                        <input id="nombres" name="nombres" class="ui-control" value="{{ old('nombres', $editando?->nombres) }}">
                        @error('nombres')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                    <div class="ui-campo @error('apellidos') ui-campo-error @enderror">
                        <label for="apellidos">Apellidos</label>
                        <input id="apellidos" name="apellidos" class="ui-control" value="{{ old('apellidos', $editando?->apellidos) }}">
                        @error('apellidos')<p class="ui-error-campo">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="ui-campo @error('correo') ui-campo-error @enderror">
                    <label for="correo">Correo institucional</label>
                    <input id="correo" name="correo" type="email" class="ui-control" value="{{ old('correo', $editando?->correo) }}" placeholder="nombre@uees.edu.ec">
                    @error('correo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <div class="ui-campo @error('codigo') ui-campo-error @enderror">
                    <label for="codigo">Código de empleado</label>
                    <input id="codigo" name="codigo" class="ui-control" value="{{ old('codigo', $editando?->codigo) }}" placeholder="DOC-004" maxlength="20">
                    @error('codigo')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </div>
                <fieldset class="ui-campo @error('roles') ui-campo-error @enderror">
                    <legend class="font-semibold">Roles</legend>
                    <div class="grid grid-cols-2 gap-chico mt-chico">
                        @foreach ($roles as $rol)
                            <label class="ui-casilla">
                                <input type="checkbox" name="roles[]" value="{{ $rol->codigo }}" @checked($rolesMarcados->contains($rol->codigo))>
                                <span>{{ $rol->nombre }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </fieldset>
                <fieldset class="ui-campo @error('facultades') ui-campo-error @enderror">
                    <legend class="font-semibold">Facultades <span class="ui-opcional">(obligatorio para asistentes)</span></legend>
                    <div class="grid gap-chico mt-chico max-h-48 overflow-y-auto">
                        @foreach ($facultades as $facultad)
                            <label class="ui-casilla">
                                <input type="checkbox" name="facultades[]" value="{{ $facultad->id }}" @checked($facultadesMarcadas->contains($facultad->id))>
                                <span>{{ $facultad->nombre }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('facultades')<p class="ui-error-campo">{{ $message }}</p>@enderror
                </fieldset>
                <label class="ui-casilla">
                    <input type="checkbox" name="acceso" value="1" @checked($conAcceso)>
                    <span>Puede entrar al sistema</span>
                </label>
                <p class="ui-ayuda">Al darle acceso se le envía un correo para que cree su contraseña.</p>
                @error('acceso')<p class="ui-error-campo">{{ $message }}</p>@enderror
                <div class="ui-acciones">
                    <button type="submit" class="ui-btn ui-btn-primario">{{ $editando ? 'Guardar cambios' : 'Guardar persona' }}</button>
                    @if ($editando)
                        <a href="{{ route('administracion.personal') }}" class="ui-btn ui-btn-quieto">Cancelar</a>
                    @endif
                </div>
            </form>
        </section>

        <section>
            <h2 class="ui-subtitulo mb-medio">Personal registrado</h2>
            @include('administracion.partes.buscador', [
                'accion' => route('administracion.personal'),
                'placeholder' => 'Buscar por nombre, correo o código',
                'filtro' => ['nombre' => 'rol', 'etiqueta' => 'Todos los roles', 'opciones' => $nombresRol],
            ])
            @if ($personal->isEmpty())
                <div class="ui-vacio">No hay personas que coincidan.</div>
            @else
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead>
                            <tr>
                                <th>Persona</th>
                                <th>Roles</th>
                                <th>Estado</th>
                                <th><span class="ui-sr-oculto">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($personal as $persona)
                                <tr>
                                    <td>
                                        <span class="font-semibold">{{ $persona->apellidos }}, {{ $persona->nombres }}</span>
                                        <span class="block text-chico text-gris">{{ $persona->correo }}{{ $persona->codigo ? ' · '.$persona->codigo : '' }}</span>
                                    </td>
                                    <td>
                                        @foreach ($rolesDe->get($persona->id, collect()) as $codigo)
                                            <span class="ui-badge ui-badge-vino">{{ $nombresRol[$codigo] ?? $codigo }}</span>
                                        @endforeach
                                        @if ($persona->facultades->isNotEmpty())
                                            <span class="block text-chico text-gris mt-chico">Facultad: {{ $persona->facultades->pluck('codigo')->join(', ') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (! $persona->activo)
                                            <span class="ui-badge">Inactivo</span>
                                        @elseif ($persona->user?->activo)
                                            <span class="ui-badge ui-badge-exito">Con acceso</span>
                                        @else
                                            <span class="ui-badge ui-badge-info">Sin acceso</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex flex-col items-end gap-chico">
                                            <a href="{{ route('administracion.personal.editar', $persona) }}" class="ui-btn ui-btn-quieto ui-btn-chico">Editar</a>
                                            @unless ($persona->user_id && $persona->user_id === auth()->id())
                                                <form method="POST" action="{{ route('administracion.personal.estado', $persona) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">{{ $persona->activo ? 'Desactivar' : 'Activar' }}</button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $personal->links('administracion.partes.paginacion') }}
            @endif
        </section>
    </div>
</x-layouts.app>
