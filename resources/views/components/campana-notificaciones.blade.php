@auth
    @once
        <link rel="stylesheet" href="{{ asset('css/notificaciones.css') }}">
        <script src="{{ asset('js/notificaciones.js') }}" defer></script>
    @endonce
    <details class="avisos-campana" data-campana-notificaciones
        data-resumen="{{ route('notificaciones.resumen') }}" data-csrf="{{ csrf_token() }}">
        <summary aria-label="Notificaciones">
            <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z"/><path d="M10 21h4"/>
            </svg>
            <span class="avisos-contador" data-contador hidden>0</span>
        </summary>
        <section class="avisos-panel" aria-label="Tus notificaciones">
            <h2>Notificaciones</h2>
            <div class="avisos-herramientas">
                <a href="{{ route('notificaciones.index') }}">Ver todas</a>
                <form action="{{ route('notificaciones.leer-todas') }}" method="POST" data-leer-todas>
                    @csrf
                    <button type="submit">Marcar todas como leídas</button>
                </form>
            </div>
            <p data-estado role="status">Cargando notificaciones…</p>
            <ul class="avisos-lista" data-lista></ul>
        </section>
    </details>
@endauth
