<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis solicitudes | Portal del estudiante</title>

    @include('estudiante.partials.estilo-ficha')
    @include('estudiante.partials.estilo-seguimiento')
</head>
<body>
    @include('estudiante.partials.encabezado-ficha')

    <main>
        <a class="volver" href="{{ route('estudiante.catalogo') }}">
            ← Volver al catálogo
        </a>

        <div class="introduccion"><span class="categoria">Seguimiento</span>

            <h1>Mis solicitudes</h1>

            <p>
                Retoma las solicitudes que dejaste a medias y revisa
                en qué va cada una de las que ya registraste.
            </p>
        </div>

        <div class="contenido">
            <section class="panel" aria-labelledby="titulo-sin-terminar">
                <h2 id="titulo-sin-terminar">Sin terminar</h2>

                @if ($sinTerminar)
                    <ul class="lista-solicitudes">
                        @foreach ($sinTerminar as $pendiente)
                            <li>
                                <div>
                                    <strong>{{ $pendiente['nombre'] }}</strong>

                                    <span class="dato">
                                        Paso {{ $pendiente['paso'] }} de 4<time
                                            datetime="{{ $pendiente['guardado_at'] }}" data-guardado></time>
                                    </span>

                                    @if ($pendiente['respaldos'] > 0)
                                        <span class="dato">
                                            {{ $pendiente['respaldos'] === 1
                                                ? '1 respaldo adjunto'
                                                : $pendiente['respaldos'].' respaldos adjuntos' }}
                                        </span>
                                    @endif

                                    @if ($pendiente['firmado'] !== null)
                                        <span class="dato">PDF firmado: {{ $pendiente['firmado'] }}</span>
                                    @endif
                                </div>

                                <div class="acciones-solicitud">
                                    <a class="boton-fila"
                                        href="{{ route('solicitudes.create', ['tramite' => $pendiente['codigo']]) }}"
                                        aria-label="Continuar la solicitud de {{ $pendiente['nombre'] }}">
                                        Continuar →
                                    </a>

                                    <button class="boton-fila boton-secundario" type="button"
                                        data-descartar="{{ route('borradores-temporales.destroy', $pendiente['codigo']) }}"
                                        data-nombre="{{ $pendiente['nombre'] }}"
                                        aria-label="Descartar la solicitud de {{ $pendiente['nombre'] }}">
                                        Descartar
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <p id="mensaje-descartar" class="nota" role="status">
                        Se conservan en este navegador mientras tu sesión siga abierta.
                    </p>
                @else
                    <p class="vacio">
                        No tienes solicitudes a medias.
                        <a href="{{ route('estudiante.catalogo') }}">Elige un trámite en el catálogo</a>
                        para empezar una.
                    </p>
                @endif
            </section>

            <section class="panel" aria-labelledby="titulo-registradas">
                <h2 id="titulo-registradas">Solicitudes registradas</h2>

                @if ($solicitudes === null)
                    <p class="vacio">
                        El seguimiento todavía no está disponible. Cuando lo esté, aquí verás
                        el estado de cada solicitud, su línea de tiempo, las observaciones
                        de la facultad y la resolución final.
                    </p>
                @else
                    @include('estudiante.partials.solicitudes-registradas')
                @endif
            </section>
        </div>
    </main>

    <script>
        // La hora de guardado se muestra en la hora local del navegador.
        document.querySelectorAll('time[data-guardado]').forEach(marca => {
            const fecha = new Date(marca.dateTime);
            const hora = fecha.toLocaleTimeString('es-EC', { hour: '2-digit', minute: '2-digit', hour12: false });

            marca.textContent = fecha.toDateString() === new Date().toDateString()
                ? ` · guardado hoy a las ${hora}`
                : ` · guardado el ${fecha.toLocaleDateString('es-EC')} a las ${hora}`;
        });

        const mensajeDescartar = document.getElementById('mensaje-descartar');

        document.querySelectorAll('button[data-descartar]').forEach(boton => {
            boton.addEventListener('click', async () => {
                const pregunta = `¿Descartar la solicitud de ${boton.dataset.nombre}? `
                    + 'Se borrará lo que escribiste y los archivos que adjuntaste.';

                if (!confirm(pregunta)) {
                    return;
                }

                boton.disabled = true;

                const respuesta = await fetch(boton.dataset.descartar, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) }
                }).catch(() => null);

                if (respuesta?.ok) {
                    location.reload();

                    return;
                }

                boton.disabled = false;
                mensajeDescartar.textContent = 'No se pudo descartar la solicitud. Inténtalo de nuevo.';
            });
        });
    </script>
</body>
</html>
