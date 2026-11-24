{{-- G2 consume el marco de G1, sin copiarlo. El puente mantiene esta rama
     operativa hasta que se integre el componente de develop (#12). --}}
@if (view()->exists('components.layouts.app'))
    <x-dynamic-component component="layouts.app"
        :titulo="trim($__env->yieldContent('titulo')) ?: 'Trámites'"
        modulo="Portal del estudiante" :menu="config('menu_estudiante', [])">
        @yield('content')
    </x-dynamic-component>
@else
    @include('layouts.app')
@endif
