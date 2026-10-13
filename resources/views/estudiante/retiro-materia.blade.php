<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retiro de materia | Portal del estudiante</title>

    @include('estudiante.partials.estilo-ficha')
</head>
<body>
    @include('estudiante.partials.encabezado-ficha')

    <main>
        <a class="volver" href="{{ route('estudiante.catalogo') }}">
            ← Volver al catálogo
        </a>

        <div class="introduccion"><span class="categoria">Retiros y continuidad</span>

            <h1>Retiro de materia</h1>

            <p>
                Solicita el retiro de una o varias materias.
                Revisa el plazo académico y las condiciones económicas
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
                <li><div><h3>Plazo de la solicitud</h3><p>Presentar la solicitud antes de la semana de
                    exámenes del primer parcial.</p></div></li>
                <li><div><h3>Materias a retirar</h3><p>Seleccionar las materias que deseas retirar.</p></div></li>
                <li><div><h3>Semana del periodo</h3><p>Indicar la semana actual del periodo académico.</p></div></li>
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
                Los documentos de respaldo específicos para el retiro
                ordinario deben confirmarse con la facultad.
            </p>

            <p>
                Si existe una circunstancia excepcional, consulta los
                requisitos del trámite de Retiro extemporáneo y los
                respaldos que correspondan a tu caso.
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
                La solicitud debe presentarse antes de la semana de
                exámenes del primer parcial, según el calendario
                del periodo académico.
            </p>
        </div></section></div>

        <section class="panel">
            <h2>Condiciones económicas</h2>

            <table class="costos">
                <caption class="nota">
                    Porcentajes indicados según la semana del periodo
                </caption>
                <thead>
                    <tr>
                        <th scope="col">Semana</th>
                        <th scope="col">Porcentaje de costo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Semana 1</td>
                        <td>25 %</td>
                    </tr>
                    <tr>
                        <td>Semana 2</td>
                        <td>50 %</td>
                    </tr>
                    <tr>
                        <td>Semana 3 en adelante</td>
                        <td>100 %</td>
                    </tr>
                </tbody>
            </table>

            <p>
                El retiro académico no elimina automáticamente los
                valores pendientes de pago. Confirma con la universidad
                la base de cálculo de los porcentajes y el valor
                correspondiente a tu caso.
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
                Confirma con la facultad la semana de exámenes del
                primer parcial, los respaldos requeridos y el efecto
                del retiro en tu registro académico.
            </p>

            <p>
                Si el plazo ordinario ya terminó, consulta el trámite
                de Retiro extemporáneo. La aceptación de un caso
                excepcional requiere revisión.
            </p>

            <p>
                Esta ficha es informativa y no representa una aprobación
                de elegibilidad.
            </p>
        </div></section>


    </div>
<aside class="panel siguiente-paso">
            <h2 id="titulo-siguiente">Tu siguiente paso</h2>
@include('estudiante.partials.pasos-ficha')

            <div class="acciones">
                <a class="boton boton-iniciar" href="{{ route('solicitudes.create', ['tramite' => 'retiro-materia']) }}">Iniciar solicitud <span aria-hidden="true">→</span>
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