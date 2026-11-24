<x-guest-layout titulo="Iniciar sesión">
    <p class="ui-antetitulo">Acceso</p>
    <h1 class="ui-titulo">Iniciar sesión</h1>
    <p class="ui-texto-gris mt-chico">Entra con tu correo institucional de la UEES.</p>

    @if (session('status'))
        <div class="ui-alerta ui-alerta-exito mt-amplio" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="ui-form mt-amplio">
        @csrf

        <div class="ui-campo @error('email') ui-campo-error @enderror">
            <label for="email">Correo institucional</label>
            <input id="email" type="email" name="email" class="ui-control" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nombre@uees.edu.ec">
            @error('email')<p class="ui-error-campo">{{ $message }}</p>@enderror
        </div>

        <div class="ui-campo @error('password') ui-campo-error @enderror">
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" class="ui-control" required autocomplete="current-password">
            @error('password')<p class="ui-error-campo">{{ $message }}</p>@enderror
        </div>

        <label class="ui-casilla">
            <input type="checkbox" name="remember">
            <span>Mantener la sesión iniciada</span>
        </label>

        <div class="ui-acciones justify-between">
            <a class="ui-enlace text-chico" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
            <button type="submit" class="ui-btn ui-btn-primario">Ingresar</button>
        </div>
    </form>

    <p class="ui-texto-gris text-chico mt-amplio">
        ¿Primera vez? Tu cuenta ya existe con tu correo institucional. <a class="ui-enlace" href="{{ route('password.request') }}">Crea tu contraseña</a>
    </p>
</x-guest-layout>
