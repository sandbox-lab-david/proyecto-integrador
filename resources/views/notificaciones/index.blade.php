<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones | Portal del estudiante</title>
    @include('estudiante.partials.estilo-ficha')
</head>
<body>
    @include('estudiante.partials.encabezado-ficha')
    <main class="avisos-pagina">
        <h1>Notificaciones</h1>
        <p>{{ $sinLeer }} {{ $sinLeer === 1 ? 'aviso sin leer' : 'avisos sin leer' }}.</p>
        <div class="avisos-herramientas">
            <a href="{{ route('solicitudes.index') }}">Mis solicitudes</a>
            @if($sinLeer > 0)
                <form method="POST" action="{{ route('notificaciones.leer-todas') }}">
                    @csrf
                    <button type="submit">Marcar todas como leídas</button>
                </form>
            @endif
        </div>
        <ul class="avisos-lista">
            @forelse($notificaciones as $aviso)
                <li @class(['aviso-sin-leer' => $aviso->read_at === null])>
                    <form method="POST" action="{{ route('notificaciones.leer', $aviso->id) }}">
                        @csrf
                        <button type="submit" class="aviso-abrir">
                            <b>{{ $aviso->data['titulo'] ?? 'Notificación' }}</b>
                            <span>{{ $aviso->data['mensaje'] ?? '' }}</span>
                            <small>{{ $aviso->read_at ? 'Leída' : 'Sin leer' }} · {{ $aviso->created_at->locale('es')->diffForHumans() }}</small>
                        </button>
                    </form>
                </li>
            @empty
                <li><p>No tienes notificaciones.</p></li>
            @endforelse
        </ul>
        @if($notificaciones->hasPages())
            <nav class="avisos-paginacion" aria-label="Páginas de notificaciones">
                @if($notificaciones->previousPageUrl())
                    <a href="{{ $notificaciones->previousPageUrl() }}" rel="prev">Anterior</a>
                @endif
                <span>Página {{ $notificaciones->currentPage() }} de {{ $notificaciones->lastPage() }}</span>
                @if($notificaciones->nextPageUrl())
                    <a href="{{ $notificaciones->nextPageUrl() }}" rel="next">Siguiente</a>
                @endif
            </nav>
        @endif
    </main>
</body>
</html>
