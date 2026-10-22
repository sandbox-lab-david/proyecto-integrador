@php
    $menu = [
        'Módulo' => [
            ['Inicio', 'administracion.inicio', 'administracion.inicio'],
        ],
        'Solicitudes académicas' => [
            ['Catálogo de trámites', 'estudiante.catalogo', ['estudiante.catalogo', 'estudiante.tramites.*', 'estudiante.solicitudes.*', 'solicitudes.create']],
            ['Mis solicitudes', 'solicitudes.index', ['solicitudes.index', 'solicitudes.show', 'solicitudes.edit']],
        ],
        'Estructura académica' => [
            ['Periodos académicos', 'administracion.periodos', 'administracion.periodos*'],
            ['Materias', 'administracion.materias', 'administracion.materias*'],
            ['Paralelos y docentes', 'administracion.paralelos', 'administracion.paralelos*'],
        ],
        'Datos' => [
            ['Importaciones SIAC', 'cargas.index', 'cargas.*'],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Administración académica') · UEES</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ui-pagina">
    <header class="ui-barra">
        <img src="{{ asset('assets/chatbot/img/facultad-ingenieria-uees.png') }}" alt="UEES Facultad de Ingeniería">
        <span class="ui-barra-titulo">Administración académica</span>
        <div class="ml-auto flex items-center gap-medio">
            @auth
                <span class="hidden sm:inline text-chico text-gris">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ui-btn ui-btn-quieto ui-btn-chico">Cerrar sesión</button>
                </form>
            @else
                <a class="ui-enlace text-chico" href="{{ route('login') }}">Iniciar sesión</a>
            @endauth
        </div>
    </header>

    <div class="flex flex-col md:flex-row md:min-h-[calc(100dvh-68px)]">
        <aside class="md:w-64 shrink-0 bg-superficie border-b md:border-b-0 md:border-r border-linea px-normal py-amplio">
            <nav aria-label="Menú principal" class="flex md:flex-col gap-amplio overflow-x-auto">
                @foreach ($menu as $seccion => $enlaces)
                    <div class="shrink-0">
                        <p class="px-3 mb-chico text-mini font-bold uppercase tracking-[1.4px] text-gris">{{ $seccion }}</p>
                        <ul class="flex md:flex-col gap-1">
                            @foreach ($enlaces as [$texto, $ruta, $patron])
                                @php $activo = request()->routeIs(...(array) $patron); @endphp
                                <li>
                                    <a href="{{ route($ruta) }}"
                                       @if ($activo) aria-current="page" @endif
                                       class="block whitespace-nowrap rounded-control px-3 py-2 text-cuerpo no-underline {{ $activo ? 'bg-vino-suave text-vino font-semibold' : 'text-texto hover:bg-fondo' }}">
                                        {{ $texto }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </aside>

        <main class="flex-1 min-w-0">
            <div class="ui-contenedor">
                @if (session('exito') || session('success'))
                    <div class="ui-alerta ui-alerta-exito mb-amplio" role="status">{{ session('exito') ?? session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="ui-alerta ui-alerta-error mb-amplio" role="alert">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="ui-alerta ui-alerta-error mb-amplio" role="alert">
                        <strong>No se pudo guardar</strong>
                        Revisa los campos marcados en el formulario.
                    </div>
                @endif

                @yield('content')
                @yield('contenido')
            </div>
        </main>
    </div>
</body>
</html>
