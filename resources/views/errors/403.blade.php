<x-layouts.app titulo="Sin permiso">
    <div class="max-w-xl">
        <p class="ui-antetitulo">Error 403</p>
        <h1 class="ui-titulo">No tienes permiso para ver esta página</h1>
        <p class="ui-texto-gris mt-chico">
            Esta sección es para otro perfil de usuario. Si crees que deberías poder entrar, pide al administrador académico que revise tu rol.
        </p>
        <div class="ui-acciones mt-amplio">
            @auth
                <a href="{{ route('dashboard') }}" class="ui-btn ui-btn-primario">Ir a mi inicio</a>
            @else
                <a href="{{ route('login') }}" class="ui-btn ui-btn-primario">Iniciar sesión</a>
            @endauth
        </div>
    </div>
</x-layouts.app>
