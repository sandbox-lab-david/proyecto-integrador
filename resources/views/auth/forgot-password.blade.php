<x-guest-layout titulo="Recuperar contraseña">
    <p class="ui-antetitulo">Acceso</p>
    <h1 class="ui-titulo">¿Olvidaste tu contraseña?</h1>
    <p class="ui-texto-gris mt-chico">Escribe tu correo institucional y te enviaremos un enlace para crear una nueva.</p>

    @if (session('status'))
        <div class="ui-alerta ui-alerta-exito mt-amplio" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="ui-form mt-amplio">
        @csrf

        <div class="ui-campo @error('email') ui-campo-error @enderror">
            <label for="email">Correo institucional</label>
            <input id="email" type="email" name="email" class="ui-control" value="{{ old('email') }}" required autofocus placeholder="nombre@uees.edu.ec">
            @error('email')<p class="ui-error-campo">{{ $message }}</p>@enderror
        </div>

        <div class="ui-acciones justify-between">
            <a class="ui-enlace text-chico" href="{{ route('login') }}">Volver a iniciar sesión</a>
            <button type="submit" class="ui-btn ui-btn-primario">Enviar enlace</button>
        </div>
    </form>
</x-guest-layout>
