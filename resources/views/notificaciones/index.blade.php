{{-- Lista completa de avisos (RF-2.15). La campana de G1 también trae aquí al
     personal, así que el menú del portal solo se muestra a los estudiantes. --}}
@php($esEstudiante = auth()->user()?->tipo === 'estudiante')
<x-layouts.app titulo="Notificaciones"
    :modulo="$esEstudiante ? 'Portal del estudiante' : 'Sistema de Solicitudes Estudiantiles'"
    :menu="$esEstudiante ? config('menu_estudiante', []) : []">
    <style>
        .avisos-encabezado { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
        .avisos-acciones { display: flex; flex-wrap: wrap; gap: 8px; }
        .avisos-caja { margin-top: 24px; padding: 0; overflow: hidden; }
        .avisos-caja > * + * { margin-top: 0; }
        .avisos-items { margin: 0; padding: 0; list-style: none; }
        .avisos-items li + li { border-top: 1px solid var(--color-linea-suave); }
        .avisos-item { display: flex; gap: 14px; width: 100%; padding: 16px 20px; background: none; border: 0;
            border-left: 3px solid transparent; color: inherit; font: inherit; text-align: left; cursor: pointer; }
        .avisos-item:hover { background: var(--color-superficie-hover); }
        .avisos-item:focus-visible { outline: 2px solid var(--color-foco); outline-offset: -2px; }
        .avisos-nueva .avisos-item { border-left-color: var(--color-vino); background: var(--color-vino-suave); }
        .avisos-nueva .avisos-item:hover { background: #f2e2e5; }
        .avisos-icono { flex-shrink: 0; display: grid; place-items: center; width: 36px; height: 36px;
            border-radius: 50%; background: var(--color-gris-suave); color: var(--color-gris); }
        .avisos-nueva .avisos-icono { background: var(--color-superficie); color: var(--color-vino); }
        .avisos-cuerpo { flex: 1; min-width: 0; }
        .avisos-titulo { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-weight: 600; }
        .avisos-mensaje { display: block; margin-top: 4px; color: var(--color-gris); font-size: var(--text-cuerpo);
            white-space: pre-line; overflow-wrap: anywhere; }
        .avisos-fecha { display: block; margin-top: 6px; color: var(--color-gris); font-size: var(--text-chico); }
        .avisos-vacio { padding: 48px 24px; text-align: center; }
        .avisos-vacio .avisos-icono { width: 52px; height: 52px; margin: 0 auto 12px; }
        .avisos-pie { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 12px; margin-top: 16px; }
    </style>

    <div class="avisos-encabezado">
        <div>
            <p class="ui-antetitulo">Avisos</p>
            <h1 class="ui-titulo">Notificaciones</h1>
            <p class="ui-texto-gris mt-chico">
                @if ($sinLeer > 0)
                    Tienes {{ $sinLeer }} {{ $sinLeer === 1 ? 'aviso sin leer' : 'avisos sin leer' }}.
                @else
                    Estás al día: no tienes avisos sin leer.
                @endif
                @if ($esEstudiante)
                    Aquí llegan los cambios de estado de tus solicitudes; también te los enviamos por correo.
                @endif
            </p>
        </div>
        <div class="avisos-acciones">
            @if ($esEstudiante)
                <a href="{{ route('solicitudes.index') }}" class="ui-btn ui-btn-quieto">Mis solicitudes</a>
            @endif
            @if ($sinLeer > 0)
                <form method="POST" action="{{ route('notificaciones.leer-todas') }}">
                    @csrf
                    <button type="submit" class="ui-btn ui-btn-secundario">Marcar todas como leídas</button>
                </form>
            @endif
        </div>
    </div>

    <section class="ui-tarjeta avisos-caja" aria-label="Tus notificaciones">
        @if ($notificaciones->isEmpty())
            <div class="avisos-vacio">
                <span class="avisos-icono" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z"/><path d="M10 21h4"/></svg>
                </span>
                <h2 class="ui-subtitulo">No tienes notificaciones</h2>
                <p class="ui-texto-gris mt-chico">{{ $esEstudiante ? 'Cuando tu solicitud cambie de estado, te avisaremos aquí.' : 'Los avisos del sistema aparecerán aquí.' }}</p>
            </div>
        @else
            <ul class="avisos-items">
                @foreach ($notificaciones as $aviso)
                    @php($nueva = $aviso->read_at === null)
                    <li @class(['avisos-nueva' => $nueva])>
                        <form method="POST" action="{{ route('notificaciones.leer', $aviso->id) }}">
                            @csrf
                            <button type="submit" class="avisos-item">
                                <span class="avisos-icono" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z"/><path d="M10 21h4"/></svg>
                                </span>
                                <span class="avisos-cuerpo">
                                    <span class="avisos-titulo">
                                        {{ $aviso->data['titulo'] ?? 'Notificación' }}
                                        @if ($nueva)
                                            <span class="ui-badge ui-badge-vino">Sin leer</span>
                                        @endif
                                    </span>
                                    @if (filled($aviso->data['mensaje'] ?? null))
                                        <span class="avisos-mensaje">{{ $aviso->data['mensaje'] }}</span>
                                    @endif
                                    <time class="avisos-fecha" datetime="{{ $aviso->created_at->toIso8601String() }}"
                                        title="{{ $aviso->created_at->timezone('America/Guayaquil')->format('d/m/Y H:i') }}">
                                        {{ $aviso->created_at->locale('es')->diffForHumans() }}{{ $nueva ? '' : ' · Leída' }}
                                    </time>
                                </span>
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @if ($notificaciones->hasPages())
        <div class="avisos-pie">
            <p class="ui-texto-gris">Página {{ $notificaciones->currentPage() }} de {{ $notificaciones->lastPage() }}</p>
            <nav class="ui-paginacion" aria-label="Páginas de notificaciones">
                @if ($notificaciones->onFirstPage())
                    <span aria-disabled="true">Anterior</span>
                @else
                    <a href="{{ $notificaciones->previousPageUrl() }}" rel="prev">Anterior</a>
                @endif
                @if ($notificaciones->hasMorePages())
                    <a href="{{ $notificaciones->nextPageUrl() }}" rel="next">Siguiente</a>
                @else
                    <span aria-disabled="true">Siguiente</span>
                @endif
            </nav>
        </div>
    @endif
</x-layouts.app>
