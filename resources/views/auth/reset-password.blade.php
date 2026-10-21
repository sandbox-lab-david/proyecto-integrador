<x-guest-layout titulo="Nueva contraseña">
    <p class="ui-antetitulo">Acceso</p>
    <h1 class="ui-titulo">Crear una nueva contraseña</h1>

    <form method="POST" action="{{ route('password.store') }}" class="ui-form mt-amplio">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="ui-campo @error('email') ui-campo-error @enderror">
            <label for="email">Correo institucional</label>
            <input id="email" type="email" name="email" class="ui-control" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @error('email')<p class="ui-error-campo">{{ $message }}</p>@enderror
        </div>

        <div class="ui-campo @error('password') ui-campo-error @enderror">
            <label for="password">Nueva contraseña</label>
            <input id="password" type="password" name="password" class="ui-control" required autocomplete="new-password">
            <p class="ui-ayuda">Mínimo 8 caracteres.</p>
            @error('password')<p class="ui-error-campo">{{ $message }}</p>@enderror
        </div>

        <div class="ui-campo">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="ui-control" required autocomplete="new-password">
        </div>

        <div class="ui-acciones justify-end">
            <button type="submit" class="ui-btn ui-btn-primario">Guardar contraseña</button>
        </div>
    </form>
</x-guest-layout>
