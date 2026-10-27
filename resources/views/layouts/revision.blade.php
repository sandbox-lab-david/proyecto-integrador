@php
    $tituloRevision = trim($__env->yieldContent('page-title')) ?: 'Revisión y Consejo';
    $encabezadoRevision = trim($__env->yieldContent('page-heading')) ?: $tituloRevision;
    $descripcionRevision = trim($__env->yieldContent('page-description'));
@endphp

<x-layouts.app :titulo="$tituloRevision" modulo="Revisión y Consejo" :menu="config('menu_revision')">
    <header class="mb-6">
        <h1 class="ui-titulo">{{ $encabezadoRevision }}</h1>
        @if($descripcionRevision)
            <p class="mt-1 text-gris">{{ $descripcionRevision }}</p>
        @endif
    </header>

    @yield('content')
</x-layouts.app>

<x-chat-widget modo="flotante" :simulado="false" />
