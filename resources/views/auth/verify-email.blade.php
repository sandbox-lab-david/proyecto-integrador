<x-guest-layout titulo="Verificar correo">
    <p class="ui-antetitulo">Acceso</p>
    <h1 class="ui-titulo">Verifica tu correo</h1>
    <p class="ui-texto-gris mt-chico">Te enviamos un enlace de verificación a tu correo institucional. Ábrelo para activar tu cuenta.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="ui-alerta ui-alerta-exito mt-amplio" role="status">Te enviamos un nuevo enlace de verificación.</div>
    @endif

    <div class="ui-acciones justify-between mt-amplio">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="ui-btn ui-btn-primario">Reenviar enlace</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="ui-btn ui-btn-quieto">Cerrar sesión</button>
        </form>
    </div>
</x-guest-layout>
