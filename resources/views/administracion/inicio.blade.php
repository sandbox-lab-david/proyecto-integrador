<x-layouts.app titulo="Inicio" modulo="Administración académica" :menu="config('menu_administracion')">
    <p class="ui-antetitulo">Grupo 1 · Módulo de administración</p>
    <h1 class="ui-titulo">Administración académica</h1>
    <p class="ui-texto-gris mt-chico">Catálogos que usan los demás módulos: estructura académica, personas e importaciones del SIAC.</p>

    <dl class="ui-datos mt-amplio">
        @foreach ($conteos as $nombre => $total)
            <div class="ui-dato">
                <dt>{{ $nombre }}</dt>
                <dd>{{ $total }}</dd>
            </div>
        @endforeach
    </dl>

    <div class="ui-alerta {{ $periodoActivo ? 'ui-alerta-exito' : 'ui-alerta-aviso' }} mt-amplio">
        @if ($periodoActivo)
            <strong>Periodo activo</strong>
            {{ $periodoActivo->nombre }} ({{ \Illuminate\Support\Carbon::parse($periodoActivo->fecha_inicio)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($periodoActivo->fecha_fin)->format('d/m/Y') }})
        @else
            <strong>Sin periodo activo</strong>
            Crea o activa un periodo para que los demás módulos puedan asignarlo a las solicitudes.
        @endif
    </div>

    @foreach (collect(config('menu_administracion'))->except('Módulo') as $seccion => $enlaces)
        <h2 class="ui-subtitulo mt-grande mb-medio">{{ $seccion }}</h2>
        <div class="ui-tarjetas">
            @foreach ($enlaces as $enlace)
                <a class="ui-tarjeta" href="{{ route($enlace['ruta']) }}">
                    <h3 class="ui-subtitulo">{{ $enlace['texto'] }}</h3>
                    <p class="ui-texto-gris">{{ $enlace['descripcion'] ?? '' }}</p>
                </a>
            @endforeach
        </div>
    @endforeach
</x-layouts.app>
