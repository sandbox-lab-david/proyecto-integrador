<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retiro de universidad | Portal del estudiante</title>

    @include('estudiante.partials.estilo-ficha')
</head>
<body>
    @include('estudiante.partials.encabezado-ficha')

    <main>
        <a class="volver" href="{{ route('estudiante.catalogo') }}">
            ← Volver al catálogo
        </a>

        <div class="introduccion"><span class="categoria">Retiros y continuidad</span>

            <h1>Retiro de universidad</h1>

            <p>
                Solicita formalmente tu retiro de la universidad.
                Revisa las condiciones académicas y económicas
                antes de iniciar tu solicitud.
            </p>
        </div>

        <div class="distribucion"><div class="contenido">
<section class="panel">
            <div class="titulo-seccion">
                        <svg
                            class="icono-titulo"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                            focusable="false"
                        >
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6M8 12l1 1 2-2M14 12h2M8 17l1 1 2-2M14 17h2"/>
                        </svg>

                        <h2 id="titulo-requisitos">Requisitos</h2>
                    </div>

            <ol class="lista-requisitos" role="list">
                <li><div><h3>Motivo del retiro</h3><p>Indicar el motivo del retiro de universidad.
                    Este campo es obligatorio.</p></div></li>
                <li><div><h3>Solicitud institucional</h3><p>Presentar la solicitud mediante el formulario
                    institucional y los canales oficiales establecidos.</p></div></li>
            </ol>

            <p>
                Los requisitos adicionales deben confirmarse
                con la facultad o la Secretaría General.
            </p>
        </section>

        <div class="informacion-adicional"><section class="panel tarjeta-informacion"><span class="icono-circular">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                                focusable="false"
                            >
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6M8 13h8M8 17h5"/>
                            </svg>
                        </span><div>
            <h2>Documentos de respaldo</h2>

            <p>
                Los documentos de respaldo específicos para este
                trámite están pendientes de confirmación por
                la facultad o la Secretaría General.
            </p>
        </div></section>

        <section class="panel tarjeta-informacion"><span class="icono-circular">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                                focusable="false"
                            >
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path d="M16 3v4M8 3v4M3 11h18"/>
                            </svg>
                        </span><div>
            <h2>Plazo indicado</h2>

            <p>
                El plazo para presentar la solicitud está pendiente
                de confirmación por la facultad o la Secretaría General.
            </p>
        </div></section></div>

        <section class="panel">
            <h2>Condiciones económicas</h2>

            <p>
                El retiro académico no elimina automáticamente
                los valores pendientes con la universidad.
                Consulta los pagos aplicables y las opciones de
                regularización según el calendario financiero.
            </p>
        </section>

        <section class="aviso"><svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                        focusable="false"
                    >
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 11v6M12 7h.01"/>
                    </svg><div>
            <h2>Información pendiente de confirmación</h2>

            <p>
                Confirma si corresponde un retiro temporal o definitivo,
                sus efectos académicos y las condiciones aplicables
                para una posible reincorporación.
            </p>

            <p>
                Esta ficha corresponde a una solicitud de retiro
                voluntario. Si existe una resolución institucional
                de retiro, consulta el procedimiento correspondiente
                con la Secretaría General.
            </p>

            <p>
                Este trámite requiere revisión.
                Esta ficha es informativa y no representa una
                aprobación de elegibilidad.
            </p>
        </div></section>


    </div>
<aside class="panel siguiente-paso">
            <h2 id="titulo-siguiente">Tu siguiente paso</h2>
@include('estudiante.partials.pasos-ficha')

            <div class="acciones">
                <a class="boton boton-iniciar" href="{{ route('solicitudes.create', ['tramite' => 'retiro-universidad']) }}">Iniciar solicitud <span aria-hidden="true">→</span>
                </a>

                <button class="boton boton-chatbot" type="button" disabled>
<svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                            focusable="false"
                        >
                            <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-4-.9L3 21l1.9-5.5a9 9 0 0 1-.9-4A8.5 8.5 0 0 1 12.5 3 8.5 8.5 0 0 1 21 11.5z"/>
                        </svg> Consultar chatbot
                </button>
            </div>

            <p class="nota">El formulario está disponible con datos de demostración. La conexión con el chatbot estará disponible en una siguiente entrega.</p>
        </aside>
</div>
    </main>
</body>
</html>