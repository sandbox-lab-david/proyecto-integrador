@props(['menu' => [], 'modulo' => ''])

@php
    $usuario = auth()->user();
    $autorizacion = app(\App\Services\Academico\AutorizacionService::class);
    $secciones = [];

    foreach ($menu as $seccion => $enlaces) {
        $visibles = [];

        foreach ($enlaces as $enlace) {
            $ruta = $enlace['ruta'] ?? null;

            if (! $ruta || ! Route::has($ruta)) {
                continue;
            }

            if (! empty($enlace['roles']) && (! $usuario || ! $autorizacion->tieneRol($usuario, $enlace['roles']))) {
                continue;
            }

            $visibles[] = $enlace;
        }

        if ($visibles) {
            $secciones[$seccion] = $visibles;
        }
    }
@endphp

@if ($secciones)
    <aside class="md:w-64 shrink-0 bg-superficie border-b md:border-b-0 md:border-r border-linea px-normal py-amplio">
        <nav aria-label="Menú de {{ $modulo }}" class="flex md:flex-col gap-amplio overflow-x-auto">
            @foreach ($secciones as $seccion => $enlaces)
                <div class="shrink-0">
                    <p class="px-3 mb-chico text-mini font-bold uppercase tracking-[1.4px] text-gris">{{ $seccion }}</p>
                    <ul class="flex md:flex-col gap-1">
                        @foreach ($enlaces as $enlace)
                            @php $activo = request()->routeIs($enlace['activo'] ?? $enlace['ruta']); @endphp
                            <li>
                                <a href="{{ route($enlace['ruta']) }}"
                                   @if ($activo) aria-current="page" @endif
                                   class="block whitespace-nowrap rounded-control px-3 py-2 text-cuerpo no-underline {{ $activo ? 'bg-vino-suave text-vino font-semibold' : 'text-texto hover:bg-fondo' }}">
                                    {{ $enlace['texto'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </aside>
@endif
