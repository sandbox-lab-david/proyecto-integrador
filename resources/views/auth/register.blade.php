<x-guest-layout titulo="Crear cuenta">
    <p class="ui-antetitulo">Acceso</p>
    <h1 class="ui-titulo">Crear cuenta de estudiante</h1>
    <p class="ui-texto-gris mt-chico">Solo para estudiantes, con su correo institucional. El personal (asistentes, directores) recibe su cuenta del administrador académico.</p>

    <form method="POST" action="{{ route('register') }}" class="ui-form mt-amplio">
        @csrf

        <div class="ui-campo @error('name') ui-campo-error @enderror">
            <label for="name">Nombres y apellidos</label>
            <input id="name" type="text" name="name" class="ui-control" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')<p class="ui-error-campo">{{ $message }}</p>@enderror
        </div>

        <div class="ui-campo @error('email') ui-campo-error @enderror">
            <label for="email">Correo institucional</label>
            <input id="email" type="email" name="email" class="ui-control" value="{{ old('email') }}" required autocomplete="username" placeholder="nombre@uees.edu.ec">
            <p class="ui-ayuda">Debe terminar en @uees.edu.ec.</p>
            @error('email')<p class="ui-error-campo">{{ $message }}</p>@enderror
        </div>

        <div class="ui-campo @error('password') ui-campo-error @enderror">
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" class="ui-control" required autocomplete="new-password">
            <p class="ui-ayuda">Mínimo 8 caracteres.</p>
            @error('password')<p class="ui-error-campo">{{ $message }}</p>@enderror
        </div>

        <div class="ui-campo">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="ui-control" required autocomplete="new-password">
        </div>

        <div class="ui-acciones justify-between">
            <a class="ui-enlace text-chico" href="{{ route('login') }}">Ya tengo cuenta</a>
            <button type="submit" class="ui-btn ui-btn-primario">Crear cuenta</button>
        </div>
    </form>
</x-guest-layout>
