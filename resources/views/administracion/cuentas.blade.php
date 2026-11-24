<x-layouts.app titulo="Cuentas de acceso" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Personas</p>
    <h1 class="ui-titulo">Cuentas de acceso</h1>
    <p class="ui-texto-gris mt-chico">Todas las cuentas que pueden iniciar sesión. Las del personal se crean en <a class="ui-enlace" href="{{ route('administracion.personal') }}">Personal</a>; las de estudiantes llegan con la importación del SIAC. Una cuenta desactivada no puede iniciar sesión.</p>

    <section class="mt-amplio">
        @include('administracion.partes.buscador', [
            'accion' => route('administracion.cuentas'),
            'placeholder' => 'Buscar por nombre o correo',
            'filtro' => ['nombre' => 'tipo', 'etiqueta' => 'Todos los tipos', 'opciones' => ['trabajador' => 'Personal', 'estudiante' => 'Estudiantes']],
        ])
        @if ($cuentas->isEmpty())
            <div class="ui-vacio">No hay cuentas que coincidan.</div>
        @else
            <div class="ui-tabla-caja">
                <table class="ui-tabla">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th><span class="ui-sr-oculto">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cuentas as $cuenta)
                            <tr>
                                <td class="font-semibold">{{ $cuenta->name }}</td>
                                <td>{{ $cuenta->email }}</td>
                                <td>
                                    {{ $cuenta->tipo === 'estudiante' ? 'Estudiante' : 'Personal' }}
                                    @if ($cuenta->estudiante)
                                        <span class="block text-chico text-gris">{{ $cuenta->estudiante->codigo }}</span>
                                    @elseif ($cuenta->trabajador?->codigo)
                                        <span class="block text-chico text-gris">{{ $cuenta->trabajador->codigo }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($cuenta->activo)
                                        <span class="ui-badge ui-badge-exito">Activa</span>
                                    @else
                                        <span class="ui-badge">Desactivada</span>
                                    @endif
                                </td>
                                <td>
                                    @unless ($cuenta->is(auth()->user()))
                                        <div class="flex flex-wrap gap-chico justify-end">
                                            @if ($cuenta->activo)
                                                <form method="POST" action="{{ route('administracion.cuentas.enlace', $cuenta) }}">
                                                    @csrf
                                                    <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">Enviar enlace de contraseña</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('administracion.cuentas.estado', $cuenta) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">{{ $cuenta->activo ? 'Desactivar' : 'Activar' }}</button>
                                            </form>
                                        </div>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $cuentas->links('administracion.partes.paginacion') }}
        @endif
    </section>
</x-layouts.app>
