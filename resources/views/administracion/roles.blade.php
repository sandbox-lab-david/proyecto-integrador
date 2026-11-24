<x-layouts.app titulo="Roles" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Personas</p>
    <h1 class="ui-titulo">Roles</h1>
    <p class="ui-texto-gris mt-chico">Estos roles deciden a qué módulos entra cada persona. Son fijos porque los demás módulos los usan por su código (por ejemplo <code>rol:asistente</code>). Para dar o quitar un rol, edita a la persona en <a class="ui-enlace" href="{{ route('administracion.personal') }}">Personal</a>.</p>

    <div class="ui-tabla-caja mt-amplio">
        <table class="ui-tabla">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Rol</th>
                    <th>Para qué sirve</th>
                    <th>Personas activas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $rol)
                    <tr>
                        <td class="font-semibold"><code>{{ $rol->codigo }}</code></td>
                        <td>{{ $rol->nombre }}</td>
                        <td>{{ $rol->descripcion }}</td>
                        <td><a class="ui-enlace" href="{{ route('administracion.personal', ['rol' => $rol->codigo]) }}">{{ $rol->personas }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
