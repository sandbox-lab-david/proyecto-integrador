<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayudante de cátedra | Portal del estudiante</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            color: #202939;
        }

        a { color: #781c35; }

        .encabezado {
            background: white;
            border-top: 4px solid #781c35;
            border-bottom: 1px solid #e0e4eb;
        }

        .encabezado-contenido {
            max-width: 1200px;
            margin: auto;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .marca {
            display: flex;
            align-items: center;
            gap: 22px;
            min-width: 0;
        }

        .logo-uees {
            display: block;
            width: 145px;
            height: auto;
            flex-shrink: 0;
        }

        .nombre-portal {
            padding-left: 22px;
            border-left: 1px solid #dfe3ea;
            color: #781c35;
            font-size: 20px;
            line-height: 1.4;
        }

        .enlace-catalogo {
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            white-space: nowrap;
        }

        main {
            max-width: 1200px;
            margin: auto;
            padding: 30px 24px 24px;
        }

        .volver {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            text-decoration: none;
        }

        .volver:hover,
        .enlace-catalogo:hover {
            text-decoration: underline;
        }

        .introduccion {
            margin: 30px 0 26px;
        }

        .categoria {
            display: block;
            margin-bottom: 12px;
            color: #596579;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(28px, 3vw, 40px);
            line-height: 1.2;
            letter-spacing: -0.8px;
        }

        .introduccion p {
            margin: 0;
            color: #596579;
            font-size: 16px;
            line-height: 1.7;
        }

        .distribucion {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            align-items: start;
            gap: 20px;
        }

        .contenido {
            display: grid;
            gap: 18px;
            min-width: 0;
        }

        .panel {
            background: white;
            border: 1px solid #e0e4eb;
            border-radius: 12px;
            padding: 26px;
            min-width: 0;
        }

        .titulo-seccion {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        h2 {
            margin: 0;
            font-size: 22px;
            line-height: 1.4;
        }

        svg { display: block; }

        .icono-titulo {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            color: #781c35;
        }

        .lista-requisitos {
            list-style: none;
            margin: 0;
            padding: 0;
            counter-reset: requisito;
        }

        .lista-requisitos li {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            padding: 20px 0;
            counter-increment: requisito;
        }

        .lista-requisitos li + li {
            border-top: 1px solid #e7eaf0;
        }

        .lista-requisitos li:last-child {
            padding-bottom: 0;
        }

        .lista-requisitos li::before {
            content: counter(requisito);
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            border: 1px solid #9e405a;
            border-radius: 50%;
            background: #fcf7f9;
            color: #781c35;
            font-size: 15px;
            font-weight: bold;
        }

        .lista-requisitos h3 {
            margin: 2px 0 5px;
            font-size: 16px;
            line-height: 1.4;
        }

        .lista-requisitos p {
            margin: 0;
            color: #596579;
            font-size: 14px;
            line-height: 1.6;
        }

        .informacion-adicional {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .tarjeta-informacion {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 22px;
        }

        .icono-circular {
            display: grid;
            place-items: center;
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #f7edf0;
            color: #781c35;
        }

        .icono-circular svg {
            width: 24px;
            height: 24px;
        }

        .tarjeta-informacion h2 {
            font-size: 16px;
        }

        .tarjeta-informacion p,
        .lista-documentos {
            margin: 8px 0 0;
            color: #596579;
            font-size: 14px;
            line-height: 1.6;
        }

        .lista-documentos {
            padding-left: 18px;
        }

        .lista-documentos li + li {
            margin-top: 8px;
        }

        .aviso {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 20px 22px;
            border: 1px solid #f0dfb6;
            border-radius: 12px;
            background: #fff9eb;
        }

        .aviso svg {
            width: 25px;
            height: 25px;
            flex-shrink: 0;
            color: #b57900;
        }

        .aviso h2 {
            font-size: 16px;
        }

        .aviso p {
            margin: 7px 0 0;
            color: #596579;
            font-size: 14px;
            line-height: 1.6;
        }

        .siguiente-paso h2 {
            margin-bottom: 26px;
        }

        .lista-pasos {
            display: grid;
            gap: 24px;
            list-style: none;
            margin: 0 0 28px;
            padding: 0;
        }

        .lista-pasos li {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .lista-pasos h3 {
            margin: 2px 0 6px;
            font-size: 16px;
            line-height: 1.4;
        }

        .lista-pasos p {
            margin: 0;
            color: #596579;
            font-size: 14px;
            line-height: 1.6;
        }

        .acciones {
            display: grid;
            gap: 12px;
        }

        .boton {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            min-height: 48px;
            padding: 13px 16px;
            border: 1px solid #781c35;
            border-radius: 8px;
            background: #781c35;
            color: white;
            font: inherit;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        .boton svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .boton:disabled {
            border-color: #dfe3ea;
            background: #f5f6fa;
            color: #697386;
            cursor: not-allowed;
        }

        .nota {
            margin: 16px 0 0;
            color: #697386;
            font-size: 12px;
            line-height: 1.6;
        }

        footer {
            margin-top: 28px;
            padding: 20px 0 0;
            border-top: 1px solid #e0e4eb;
            color: #697386;
            font-size: 12px;
            text-align: center;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid #d9b2bd;
            outline-offset: 4px;
        }

        @media (max-width: 1000px) {
            .distribucion {
                grid-template-columns: minmax(0, 1fr) 290px;
            }

            .informacion-adicional {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 800px) {
            .distribucion {
                grid-template-columns: 1fr;
            }

            .informacion-adicional {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .encabezado-contenido {
                padding: 14px 16px;
            }

            .marca { gap: 14px; }
            .logo-uees { width: 105px; }

            .nombre-portal {
                padding-left: 14px;
                font-size: 16px;
            }

            .enlace-catalogo { display: none; }
            main { padding: 24px 16px; }
            .introduccion { margin-top: 26px; }
            .panel { padding: 22px; }

            .informacion-adicional {
                grid-template-columns: 1fr;
            }

            .lista-requisitos li { gap: 12px; }
            h2 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <header class="encabezado">
        <div class="encabezado-contenido">
            <div class="marca">
                <img
                    class="logo-uees"
                    src="{{ asset('images/estudiante/Logo-UEES.gif') }}"
                    alt="Universidad Espíritu Santo"
                >

                <strong class="nombre-portal">
                    Portal del estudiante
                </strong>
            </div>

            <a
                class="enlace-catalogo"
                href="{{ route('estudiante.catalogo') }}"
            >
                Catálogo de trámites
            </a>
        </div>
    </header>

    <main>
        <a class="volver" href="{{ route('estudiante.catalogo') }}">
            <span aria-hidden="true">←</span>
            Volver al catálogo
        </a>

        <div class="introduccion">
            <span class="categoria">Apoyo académico</span>

            <h1>Ayudante de cátedra</h1>

            <p>
                Solicita participar como ayudante de cátedra.
                Revisa las condiciones académicas antes de iniciar
                tu solicitud.
            </p>
        </div>

        <div class="distribucion">
            <div class="contenido">
                <section class="panel" aria-labelledby="titulo-requisitos">
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
                        <li>
                            <div>
                                <h3>Estudiante regular y matriculado</h3>
                                <p>
                                    Ser estudiante regular y estar matriculado
                                    en el periodo académico en el que se
                                    ejercerá la ayudantía.
                                </p>
                            </div>
                        </li>

                        <li>
                            <div>
                                <h3>Materia aprobada con mínimo 90</h3>
                                <p>
                                    Haber aprobado la materia en la que
                                    se ejercerá la ayudantía con un
                                    promedio mínimo de 90.
                                </p>
                            </div>
                        </li>

                        <li>
                            <div>
                                <h3>Dos periodos y promedio global mínimo de 90</h3>
                                <p>
                                    Haber concluido al menos dos periodos
                                    académicos en la misma carrera y tener
                                    un promedio global mínimo de 90.
                                </p>
                            </div>
                        </li>

                        <li>
                            <div>
                                <h3>Sin materias reprobadas</h3>
                                <p>
                                    No haber reprobado ninguna materia
                                    en la misma carrera.
                                </p>
                            </div>
                        </li>

                        <li>
                            <div>
                                <h3>Sin sanciones disciplinarias</h3>
                                <p>
                                    No haber sido sujeto de ninguna
                                    sanción disciplinaria en la UEES.
                                </p>
                            </div>
                        </li>

                        <li>
                            <div>
                                <h3>Recomendaciones y solicitud docente</h3>
                                <p>
                                    Contar con la recomendación de al menos
                                    dos docentes, además de la solicitud
                                    del docente de la materia en la que
                                    se realizará la ayudantía.
                                </p>
                            </div>
                        </li>

                        <li>
                            <div>
                                <h3>Requisitos adicionales de la facultad</h3>
                                <p>
                                    Cumplir los requisitos adicionales
                                    que determine el Consejo Directivo
                                    Académico de la facultad.
                                </p>
                            </div>
                        </li>
                    </ol>
                </section>

                <div class="informacion-adicional">
                    <section
                        class="panel tarjeta-informacion"
                        aria-labelledby="titulo-documentos"
                    >
                        <span class="icono-circular">
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
                        </span>

                        <div>
                            <h2 id="titulo-documentos">
                                Documentos de respaldo
                            </h2>

                            <ul class="lista-documentos">
                                <li>
                                    Recomendaciones de al menos dos docentes.
                                </li>
                                <li>
                                    Solicitud del docente de la materia
                                    en la que se realizará la ayudantía.
                                </li>
                            </ul>
                        </div>
                    </section>

                    <section
                        class="panel tarjeta-informacion"
                        aria-labelledby="titulo-plazo"
                    >
                        <span class="icono-circular">
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
                        </span>

                        <div>
                            <h2 id="titulo-plazo">Plazo indicado</h2>

                            <p>
                                El plazo para solicitar este trámite
                                está pendiente de confirmación
                                por la facultad.
                            </p>
                        </div>
                    </section>
                </div>

                <section class="aviso" aria-labelledby="titulo-aviso">
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
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 11v6M12 7h.01"/>
                    </svg>

                    <div>
                        <h2 id="titulo-aviso">
                            Información pendiente de confirmación
                        </h2>

                        <p>
                            Confirma con la facultad el plazo, el formato
                            de los respaldos y los requisitos adicionales
                            aplicables.
                        </p>

                        <p>
                            Esta ficha es informativa y no representa
                            una aprobación de elegibilidad.
                        </p>
                    </div>
                </section>
            </div>

            <aside
                class="panel siguiente-paso"
                aria-labelledby="titulo-siguiente"
            >
                <h2 id="titulo-siguiente">Tu siguiente paso</h2>

                <ol class="lista-pasos">
                    <li>
                        <span class="icono-circular">
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
                                <rect x="5" y="3" width="14" height="18" rx="2"/>
                                <path d="M9 8h6M9 12h6M9 16h3"/>
                            </svg>
                        </span>

                        <div>
                            <h3>Completa tus datos</h3>
                            <p>
                                Revisa tus datos y completa la información
                                del trámite.
                            </p>
                        </div>
                    </li>

                    <li>
                        <span class="icono-circular">
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
                                <path d="m21 11-8 8a6 6 0 0 1-8.5-8.5l8-8a4 4 0 0 1 5.7 5.7l-8 8a2 2 0 0 1-2.8-2.8l7.5-7.5"/>
                            </svg>
                        </span>

                        <div>
                            <h3>Adjunta tus respaldos</h3>
                            <p>
                                Agrega las recomendaciones y la solicitud
                                del docente de la materia.
                            </p>
                        </div>
                    </li>

                    <li>
                        <span class="icono-circular">
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
                                <path d="m16 3 5 5-12 12H4v-5zM14 5l5 5M13 21h8"/>
                            </svg>
                        </span>

                        <div>
                            <h3>Firma y sube el PDF</h3>
                            <p>
                                Descarga el documento oficial, fírmalo
                                y sube el PDF firmado para enviar
                                la solicitud.
                            </p>
                        </div>
                    </li>
                </ol>

                <div class="acciones">
                    <a class="boton boton-iniciar" href="{{ route('solicitudes.create', ['tramite' => 'ayudante']) }}">Iniciar solicitud
                        <span aria-hidden="true">→</span>
                    </a>

                    <button class="boton" type="button" disabled>
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
                        </svg>

                        Consultar chatbot
                    </button>
                </div>

                <p class="nota">El formulario está disponible con datos de demostración. La conexión con el chatbot estará disponible en una siguiente entrega.</p>
            </aside>
        </div>

        <footer>
            UEES · Solicitudes estudiantiles
        </footer>
    </main>
</body>
</html>