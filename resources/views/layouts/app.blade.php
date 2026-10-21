<x-layouts.app :titulo="trim($__env->yieldContent('titulo')) ?: null" modulo="Administración académica" :menu="config('menu_administracion')">
    @isset($header)
        <div class="mb-amplio">{{ $header }}</div>
    @endisset

    @yield('content')
    @yield('contenido')

    @isset($slot)
        {{ $slot }}
    @endisset
</x-layouts.app>
