<x-guest-layout titulo="Confirmar contraseña">
    <p class="ui-antetitulo">Acceso</p>
    <h1 class="ui-titulo">Confirma tu contraseña</h1>
    <p class="ui-texto-gris mt-chico">Esta es una sección protegida. Escribe tu contraseña para continuar.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="ui-form mt-amplio">
        @csrf

        <div class="ui-campo @error('password') ui-campo-error @enderror">
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" class="ui-control" required autocomplete="current-password">
            @error('password')<p class="ui-error-campo">{{ $message }}</p>@enderror
        </div>

        <div class="ui-acciones justify-end">
            <button type="submit" class="ui-btn ui-btn-primario">Confirmar</button>
        </div>
    </form>
</x-guest-layout>
