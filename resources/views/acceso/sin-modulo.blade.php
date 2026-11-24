<x-layouts.app titulo="Sin módulo asignado">
    <div class="max-w-xl">
        <p class="ui-antetitulo">Tu cuenta</p>
        <h1 class="ui-titulo">Tu cuenta aún no tiene un módulo asignado</h1>
        <p class="ui-texto-gris mt-chico">
            Iniciaste sesión como <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}),
            pero todavía no tienes un rol con una sección disponible en el sistema.
        </p>
        <div class="ui-alerta ui-alerta-aviso mt-amplio">
            Pide al administrador académico que te asigne el rol que corresponde a tu trabajo.
        </div>
    </div>
</x-layouts.app>
