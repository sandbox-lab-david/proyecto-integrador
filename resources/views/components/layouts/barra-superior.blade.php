@props(['modulo'])

<header class="ui-barra">
    <img src="{{ asset('images/logo-uees.png') }}" alt="Universidad Espíritu Santo">
    <span class="ui-barra-titulo">{{ $modulo }}</span>
    <div class="ml-auto flex items-center gap-medio">
        @auth
            <x-campana-notificaciones />
            <span class="hidden sm:inline text-chico text-gris">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">Cerrar sesión</button>
            </form>
        @else
            @if (Route::has('login'))
                <a class="ui-enlace text-chico" href="{{ route('login') }}">Iniciar sesión</a>
            @endif
        @endauth
    </div>
</header>
