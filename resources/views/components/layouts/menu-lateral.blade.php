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

@php
    $paginaActual = collect($secciones)->flatten(1)->first(fn ($enlace) => request()->routeIs($enlace['activo'] ?? $enlace['ruta']))['texto'] ?? 'Ir a…';
@endphp

@if ($secciones)
    <aside class="md:w-64 shrink-0 bg-superficie border-b md:border-b-0 md:border-r border-linea px-normal py-medio md:py-amplio">
        <input type="checkbox" id="menu-movil" class="peer sr-only">
        <label for="menu-movil" class="md:hidden flex cursor-pointer items-center justify-between gap-medio rounded-control border border-linea px-3 py-2 text-cuerpo">
            <span><span class="text-gris">Menú ·</span> <span class="font-semibold text-vino">{{ $paginaActual }}</span></span>
            <span aria-hidden="true">▾</span>
        </label>
        <nav aria-label="Menú de {{ $modulo }}" class="hidden peer-checked:flex md:flex flex-col gap-amplio mt-medio md:mt-0">
            @foreach ($secciones as $seccion => $enlaces)
                <div>
                    <p class="px-3 mb-chico text-mini font-bold uppercase tracking-[1.4px] text-gris">{{ $seccion }}</p>
                    <ul class="flex flex-col gap-1">
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
