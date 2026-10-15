<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Person to Person | Portal del estudiante</title>

    @include('estudiante.partials.estilo-ficha')
</head>
<body>
    @include('estudiante.partials.encabezado-ficha')

    <main>
        <a class="volver" href="{{ route('estudiante.catalogo') }}">
            ← Volver al catálogo
        </a>

        <div class="introduccion"><span class="categoria">Apoyo académico</span>

            <h1>Person to Person</h1>

            <p>
                Consulta las condiciones del trámite Person to Person
                y selecciona las materias correspondientes
                antes de presentar tu solicitud.
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

            <p>
                Las condiciones de elegibilidad y el alcance de este
                trámite deben confirmarse con la facultad.
            </p>



            <ol class="lista-requisitos" role="list">
                <li><div><h3>Materias de la solicitud</h3><p>Seleccionar las materias de la solicitud.</p></div></li>
                <li><div><h3>Motivo de la solicitud</h3><p>Indicar el motivo, si corresponde.
                    Este campo no está marcado como obligatorio
                    en el formulario definido.</p></div></li>
            </ol>
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
                Los documentos de respaldo requeridos para este trámite
                están pendientes de confirmación por la facultad.
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
                de confirmación por la facultad.
            </p>
        </div></section></div>

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
                Este trámite requiere revisión manual.
                Confirma con la facultad en qué casos aplica,
                las materias habilitadas y las condiciones
                de participación.
            </p>

            <p>
                Esta ficha es informativa y no representa una
                aprobación de elegibilidad.
            </p>
        </div></section>


    </div>
<aside class="panel siguiente-paso">
            <h2 id="titulo-siguiente">Tu siguiente paso</h2>
@include('estudiante.partials.pasos-ficha')

            <div class="acciones">
                <a class="boton boton-iniciar" href="{{ route('solicitudes.create', ['tramite' => 'person-to-person']) }}">Iniciar solicitud <span aria-hidden="true">→</span>
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