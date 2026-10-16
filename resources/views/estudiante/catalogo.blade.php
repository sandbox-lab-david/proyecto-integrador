<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trámites | Portal del estudiante</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            color: #202939;
        }


        main {
            max-width: 1100px;
            margin: auto;
            padding: 36px 24px;
        }

        h1 {
            margin: 0 0 12px;
        }

        .introduccion {
            color: #596579;
            line-height: 1.6;
        }

        .aviso {
            background: #fff3d6;
            padding: 14px;
            border-radius: 10px;
            margin: 24px 0;
            line-height: 1.5;
        }

        .controles {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin: 24px 0;
        }

        .controles > div {
            min-width: 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input,
        select {
            width: 100%;
            padding: 13px;
            border: 1px solid #b8c1ce;
            border-radius: 8px;
            font: inherit;
            background: white;
            color: #202939;
        }

        input:focus,
        select:focus {
            outline: 3px solid #d9b2bd;
            border-color: #781c35;
        }

        .resultado {
            color: #596579;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .tarjetas {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            align-items: stretch;
        }

        .tarjeta {
            min-width: 0;
            background: white;
            border: 1px solid #e2e5ec;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            box-shadow: 0 4px 14px rgba(32, 41, 57, 0.04);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .tarjeta:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(32, 41, 57, 0.08);
        }

        .tarjeta .categoria {
            margin: 0 0 16px;
            padding: 6px 11px;
            border-radius: 20px;
            background: #f6e8ed;
            color: #781c35;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.4;
        }

        .tarjeta h2 {
            margin: 0 0 12px;
            font-size: 20px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .tarjeta .descripcion {
            margin: 0 0 24px;
            color: #596579;
            font-size: 14px;
            line-height: 1.6;
        }

        .tarjeta .acciones {
            width: 100%;
            margin-top: auto;
        }

        .enlace-ficha,
        .tarjeta button {
            display: block;
            width: 100%;
            padding: 12px 16px;
            border: 0;
            border-radius: 9px;
            font: inherit;
            font-size: 14px;
            font-weight: bold;
            line-height: 1.4;
            text-align: center;
        }

        .enlace-ficha {
            background: #781c35;
            color: white;
            text-decoration: none;
            transition: background 0.2s;
        }

        .enlace-ficha:hover {
            background: #5d1529;
        }

        .enlace-ficha:focus-visible {
            outline: 3px solid #781c35;
            outline-offset: 4px;
        }

        .tarjeta button:disabled {
            background: #f0f1f5;
            color: #687386;
            cursor: default;
        }

        .tarjeta .pendiente {
            margin: 10px 0 0;
            font-size: 12px;
            line-height: 1.5;
            text-align: center;
            color: #687386;
        }

        [hidden] {
            display: none !important;
        }

        #vacio {
            text-align: center;
            padding: 32px;
            background: white;
            border: 1px solid #e2e5ec;
            border-radius: 12px;
            line-height: 1.6;
        }

        @media (max-width: 850px) {
            .tarjetas {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            main {
                padding: 24px 16px;
            }

            .controles,
            .tarjetas {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 28px;
            }

            .tarjeta {
                padding: 22px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .tarjeta,
            .enlace-ficha {
                transition: none;
            }

            .tarjeta:hover {
                transform: none;
            }
        }

            .marca-portal {
                max-width: 1150px;
                margin: auto;
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .logo-uees {
                display: block;
                width: 170px;
                height: auto;
                padding: 6px;
                background: white;
                border-radius: 8px;
            }

            .marca-portal strong {
                font-size: 20px;
                line-height: 1.3;
            }

            @media (max-width: 600px) {

               

                .marca-portal strong {
                    font-size: 16px;
                }
            }

            header.encabezado-portal {
                background: white;
                color: #202939;
                padding: 18px 24px;
                border-top: 5px solid #781c35;
                border-bottom: 1px solid #e0e4eb;
                box-shadow: 0 3px 12px rgba(32, 41, 57, 0.04);
            }

            .marca-portal {
                max-width: 1052px;
                margin: auto;
                display: flex;
                align-items: center;
                gap: 24px;
            }

            .logo-uees {
                display: block;
                width: 150px;
                height: auto;
                flex-shrink: 0;
            }

            .titulo-portal {
                display: flex;
                flex-direction: column;
                gap: 6px;
                border-left: 1px solid #e0e4eb;
                padding-left: 24px;
            }

            .titulo-portal strong {
                color: #781c35;
                font-size: 21px;
                line-height: 1.3;
            }

            .titulo-portal span {
                color: #596579;
                font-size: 13px;
                line-height: 1.5;
            }

            .enlace-solicitudes {
                margin-left: auto;
                color: #781c35;
                font-size: 14px;
                font-weight: bold;
                text-decoration: none;
                white-space: nowrap;
            }

            .enlace-solicitudes:hover {
                text-decoration: underline;
            }

            .enlace-solicitudes:focus-visible {
                outline: 3px solid #d9b2bd;
                outline-offset: 4px;
            }

            @media (max-width: 600px) {
                header.encabezado-portal {
                    padding: 16px;
                }

                .marca-portal {
                    flex-wrap: wrap;
                }

                .marca-portal {
                    gap: 14px;
                }

                .logo-uees {
                    width: 105px;
                }

                .titulo-portal {
                    padding-left: 14px;
                }

                .titulo-portal strong {
                    font-size: 17px;
                }

                .titulo-portal span {
                    font-size: 12px;
                }
            }

                .portada-catalogo {
                    padding: 30px;
                    margin-bottom: 22px;
                    border: 1px solid #eadce1;
                    border-radius: 18px;
                    background: linear-gradient(120deg, #fbf2f5, #ffffff);
                }

                .portada-etiqueta {
                    display: block;
                    margin-bottom: 12px;
                    color: #781c35;
                    font-size: 11px;
                    font-weight: bold;
                    letter-spacing: 1.5px;
                }

                .portada-catalogo h1 {
                    margin: 0 0 12px;
                    max-width: 650px;
                    color: #202939;
                    font-size: 30px;
                    line-height: 1.25;
                }

                .portada-catalogo p {
                    max-width: 650px;
                    margin: 0;
                    color: #596579;
                    font-size: 15px;
                    line-height: 1.6;
                }

                .estado-catalogo {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 7px 12px;
                    border-radius: 20px;
                    background: #f1e5ea;
                    color: #781c35;
                    font-size: 12px;
                    line-height: 1.5;
                }

                .filtros-catalogo {
                    padding: 22px;
                    background: white;
                    border: 1px solid #e0e4eb;
                    border-radius: 14px;
                }

                .filtros-catalogo .controles {
                    margin: 0;
                    gap: 18px;
                }

                .filtros-catalogo label {
                    font-size: 13px;
                    margin-bottom: 9px;
                }

                .filtros-catalogo input,
                .filtros-catalogo select {
                    min-height: 46px;
                    padding: 12px 14px;
                    background: #fafbfc;
                    font-size: 14px;
                }

                .resultado {
                    margin: 20px 0 16px;
                    font-size: 13px;
                }

                @media (max-width: 600px) {
                    .portada-catalogo {
                        padding: 24px 20px;
                    }

                    .portada-catalogo h1 {
                        font-size: 25px;
                    }

                    .filtros-catalogo {
                        padding: 20px;
                    }
                }

                /* Solicitudes sin terminar (borradores de la sesión) */
                .borradores {
                    padding: 22px;
                    margin-bottom: 22px;
                    background: white;
                    border: 1px solid #eadce1;
                    border-left: 4px solid #781c35;
                    border-radius: 14px;
                }

                .borradores h2 {
                    margin: 0;
                    font-size: 18px;
                }

                .borradores ul {
                    list-style: none;
                    padding: 0;
                    margin: 8px 0 0;
                }

                .borradores li {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 16px;
                    padding: 14px 0;
                    border-bottom: 1px solid #e0e4eb;
                }

                .borradores li strong {
                    display: block;
                    margin-bottom: 4px;
                    font-size: 15px;
                }

                .borradores li span,
                .borradores p {
                    color: #596579;
                    font-size: 13px;
                    line-height: 1.5;
                }

                .borradores li a {
                    flex-shrink: 0;
                    padding: 10px 16px;
                    border-radius: 9px;
                    background: #781c35;
                    color: white;
                    font-size: 14px;
                    font-weight: bold;
                    text-decoration: none;
                }

                .borradores li a:hover {
                    background: #5d1529;
                }

                .borradores li a:focus-visible {
                    outline: 3px solid #d9b2bd;
                    outline-offset: 3px;
                }

                .borradores p {
                    margin: 14px 0 0;
                }

                @media (max-width: 600px) {
                    .borradores {
                        padding: 20px;
                    }

                    .borradores li {
                        flex-direction: column;
                        align-items: flex-start;
                    }
                }

                /* Diseño de las tarjetas del catálogo */
                #tarjetas {
                    display: grid;
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                    gap: 18px;
                }

                #tarjetas .tarjeta {
                    display: flex;
                    flex-direction: column;
                    min-height: 210px;
                    padding: 24px;
                    background: #fff;
                    border: 1px solid #e5e7ed;
                    border-radius: 16px;
                    box-shadow: 0 4px 16px rgba(32, 41, 57, 0.035);
                    transition: transform 180ms ease, box-shadow 180ms ease,
                                border-color 180ms ease;
                }

                #tarjetas .tarjeta:hover {
                    transform: translateY(-3px);
                    border-color: #d9b2bd;
                    box-shadow: 0 10px 24px rgba(120, 28, 53, 0.08);
                }

                #tarjetas .cabecera-tarjeta {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    margin-bottom: 18px;
                }

                #tarjetas .icono-tramite {
                    display: grid;
                    place-items: center;
                    flex-shrink: 0;
                    width: 44px;
                    height: 44px;
                    border-radius: 12px;
                    background: #f7edf0;
                    color: #781c35;
                }

                #tarjetas .icono-tramite svg {
                    width: 23px;
                    height: 23px;
                }

                #tarjetas .categoria {
                    margin: 0;
                    padding: 0;
                    border-radius: 0;
                    background: transparent;
                    color: #697386;
                    font-size: 12px;
                    font-weight: 600;
                    line-height: 1.5;
                }

                #tarjetas .tarjeta h2 {
                    margin: 0 0 20px;
                    color: #202939;
                    font-size: 20px;
                    line-height: 1.4;
                }

                #tarjetas .acciones {
                    display: flex;
                    margin-top: auto;
                    padding-top: 16px;
                    border-top: 1px solid #eef0f4;
                }

                #tarjetas .enlace-ficha,
                #tarjetas .acciones button {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 12px;
                    width: 100%;
                    margin: 0;
                    padding: 0;
                    border: 0;
                    border-radius: 0;
                    background: transparent;
                    color: #781c35;
                    font: inherit;
                    font-size: 14px;
                    font-weight: bold;
                    text-align: left;
                    text-decoration: none;
                    box-shadow: none;
                }

                #tarjetas .enlace-ficha:hover {
                    background: transparent;
                    color: #541426;
                }

                #tarjetas .enlace-ficha:focus-visible {
                    outline: 3px solid #d9b2bd;
                    outline-offset: 6px;
                    border-radius: 4px;
                }

                #tarjetas .flecha-enlace {
                    font-size: 22px;
                    transition: transform 180ms ease;
                }

                #tarjetas .enlace-ficha:hover .flecha-enlace {
                    transform: translateX(4px);
                }

                #tarjetas .acciones button:disabled {
                    color: #8a93a2;
                    cursor: default;
                }

                @media (max-width: 950px) {
                    #tarjetas {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }

                @media (max-width: 600px) {
                    #tarjetas {
                        grid-template-columns: 1fr;
                        gap: 14px;
                    }

                    #tarjetas .tarjeta {
                        min-height: 185px;
                        padding: 20px;
                    }
                }

                @media (prefers-reduced-motion: reduce) {
                    #tarjetas .tarjeta,
                    #tarjetas .flecha-enlace {
                        transition: none;
                    }

                    #tarjetas .tarjeta:hover,
                    #tarjetas .enlace-ficha:hover .flecha-enlace {
                        transform: none;
                    }
                }

                #tarjetas .enlace-ficha {
                    width: fit-content;
                    justify-content: center;
                    gap: 14px;
                    padding: 12px 18px;
                    border: 1px solid #781c35;
                    border-radius: 10px;
                    background: #781c35;
                    color: white;
                    font-size: 14px;
                    font-weight: bold;
                    text-decoration: none;
                }

                #tarjetas .enlace-ficha:hover {
                    background: #5d1529;
                    border-color: #5d1529;
                    color: white;
                }

                #tarjetas .enlace-ficha .flecha-enlace {
                    font-size: 18px;
                }


    </style>
</head>
<body>
    <header class="encabezado-portal">
    <div class="marca-portal">
        <img
            class="logo-uees"
            src="{{ asset('images/estudiante/Logo-UEES.gif') }}"
            alt="Universidad Espíritu Santo — UEES"
        >

        <div class="titulo-portal">
            <strong>Portal del estudiante</strong>
            <span>Tus solicitudes académicas, en un solo lugar</span>
        </div>

        <a class="enlace-solicitudes" href="{{ route('solicitudes.index') }}">
            Mis solicitudes
        </a>
    </div>
</header>

    <main>
       <section class="portada-catalogo">
            <span class="portada-etiqueta">GESTIONES ACADÉMICAS</span>

            <h1>¿Qué trámite necesitas realizar?</h1>

            <p>
                Explora las opciones y revisa los requisitos
                antes de preparar tu solicitud.
            </p>

            <span class="estado-catalogo">
                Formularios y envío de solicitudes en desarrollo
            </span>
        </section>

        @if ($borradores)
            <section class="borradores" aria-labelledby="titulo-borradores">
                <h2 id="titulo-borradores">Tienes solicitudes sin terminar</h2>

                <ul>
                    @foreach ($borradores as $borrador)
                        <li>
                            <div>
                                <strong>{{ $borrador['nombre'] }}</strong>
                                <span>
                                    Paso {{ $borrador['paso'] }} de 4<time
                                        datetime="{{ $borrador['guardado_at'] }}" data-guardado></time>
                                </span>
                            </div>

                            <a href="{{ route('solicitudes.create', ['tramite' => $borrador['codigo']]) }}"
                                aria-label="Continuar la solicitud de {{ $borrador['nombre'] }}">
                                Continuar →
                            </a>
                        </li>
                    @endforeach
                </ul>

                <p>
                    Los borradores se conservan en este navegador
                    mientras tu sesión siga abierta.
                </p>
            </section>
        @endif

        <section class="filtros-catalogo" aria-label="Buscar y filtrar trámites">
            <div class="controles">
                <div>
                    <label for="busqueda">Buscar un trámite</label>

                    <input
                        id="busqueda"
                        type="search"
                        placeholder="Escribe el nombre del trámite…"
                    >
                </div>

                <div>
                    <label for="categoria">Filtrar por categoría</label>

                    <select id="categoria">
                        <option value="">Todas las categorías</option>
                        <option>Homologación</option>
                        <option>Apoyo académico</option>
                        <option>Cambios académicos</option>
                        <option>Evaluaciones</option>
                        <option>Registro</option>
                        <option>Retiros y continuidad</option>
                    </select>
                </div>
            </div>
        </section>

        <p id="resultado" class="resultado" role="status"></p>

        <section
            id="tarjetas"
            class="tarjetas"
            aria-label="Trámites disponibles"
        ></section>

        <div id="vacio" hidden>
            No encontramos trámites con esos filtros.
            Prueba otro nombre o selecciona todas las categorías.
        </div>
    </main>

    <script>

        // Borradores: la hora de guardado se muestra en la hora local del navegador.
        document.querySelectorAll('time[data-guardado]').forEach(marca => {
            const fecha = new Date(marca.dateTime);
            const hora = fecha.toLocaleTimeString('es-EC', { hour: '2-digit', minute: '2-digit', hour12: false });

            marca.textContent = fecha.toDateString() === new Date().toDateString()
                ? ` · guardado hoy a las ${hora}`
                : ` · guardado el ${fecha.toLocaleDateString('es-EC')} a las ${hora}`;
        });

        const fichas = {
            'Examen de recuperación':
                "{{ route('estudiante.tramites.recuperacion') }}",
            'Examen de gracia':
                "{{ route('estudiante.tramites.gracia') }}",
            'Ayudante de cátedra':
                "{{ route('estudiante.tramites.ayudante') }}",
            'Tercer registro':
                "{{ route('estudiante.tramites.tercer-registro') }}",
            'Recalificación de examen':
                "{{ route('estudiante.tramites.recalificacion') }}",
            'Examen supletorio':
                "{{ route('estudiante.tramites.supletorio') }}",
            'Homologación':
                "{{ route('estudiante.tramites.homologacion') }}",

            'Retiro de materia':
                "{{ route('estudiante.tramites.retiro-materia') }}",

            'Retiro de carrera':
                "{{ route('estudiante.tramites.retiro-carrera') }}",

            'Retiro de universidad':
                "{{ route('estudiante.tramites.retiro-universidad') }}",
            'Retiro extemporáneo':
                "{{ route('estudiante.tramites.retiro-extemporaneo') }}",
            'Cambio de carrera':
                "{{ route('estudiante.tramites.cambio-carrera') }}",
            'Cambio de modalidad':
                "{{ route('estudiante.tramites.cambio-modalidad') }}",
            'Cambio de malla / pénsum':
                "{{ route('estudiante.tramites.cambio-malla') }}",
            'Reincorporación a carrera':
                "{{ route('estudiante.tramites.reincorporacion') }}",
            'Examen de suficiencia':
                "{{ route('estudiante.tramites.suficiencia') }}",
            'Incompleto':
                "{{ route('estudiante.tramites.incompleto') }}",
            'Person to Person':
                "{{ route('estudiante.tramites.person-to-person') }}",
            'Alcance de homologación':
                "{{ route('estudiante.tramites.alcance-homologacion') }}",
            'Registro extemporáneo':
                "{{ route('estudiante.tramites.registro-extemporaneo') }}",
        };

        const tramites = [
            { nombre: 'Alcance de homologación', categoria: 'Homologación' },
            { nombre: 'Ayudante de cátedra', categoria: 'Apoyo académico' },
            { nombre: 'Cambio de carrera', categoria: 'Cambios académicos' },
            { nombre: 'Cambio de malla / pénsum', categoria: 'Cambios académicos' },
            { nombre: 'Cambio de modalidad', categoria: 'Cambios académicos' },
            { nombre: 'Examen de gracia', categoria: 'Evaluaciones' },
            { nombre: 'Examen de recuperación', categoria: 'Evaluaciones' },
            { nombre: 'Examen de suficiencia', categoria: 'Evaluaciones' },
            { nombre: 'Examen supletorio', categoria: 'Evaluaciones' },
            { nombre: 'Homologación', categoria: 'Homologación' },
            { nombre: 'Incompleto', categoria: 'Retiros y continuidad' },
            { nombre: 'Person to Person', categoria: 'Apoyo académico' },
            { nombre: 'Recalificación de examen', categoria: 'Evaluaciones' },
            { nombre: 'Registro extemporáneo', categoria: 'Registro' },
            { nombre: 'Reincorporación a carrera', categoria: 'Retiros y continuidad' },
            { nombre: 'Retiro de carrera', categoria: 'Retiros y continuidad' },
            { nombre: 'Retiro de materia', categoria: 'Retiros y continuidad' },
            { nombre: 'Retiro de universidad', categoria: 'Retiros y continuidad' },
            { nombre: 'Retiro extemporáneo', categoria: 'Retiros y continuidad' },
            { nombre: 'Tercer registro', categoria: 'Registro' },
        ];

        const busqueda = document.getElementById('busqueda');
        const categoria = document.getElementById('categoria');
        const tarjetas = document.getElementById('tarjetas');
        const resultado = document.getElementById('resultado');
        const vacio = document.getElementById('vacio');

        function normalizar(texto) {
            return texto
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        }

               function crearIcono(categoriaTramite) {
            const dibujos = {
                'Homologación': `
                    <rect x="5" y="3" width="14" height="18" rx="2"/>
                    <path d="M9 8h6M9 12h6M9 16h3"/>
                `,
                'Apoyo académico': `
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3 20v-2a6 6 0 0 1 12 0v2"/>
                    <path d="M17 5a3 3 0 0 1 0 6M21 20v-2a6 6 0 0 0-4-5"/>
                `,
                'Cambios académicos': `
                    <path d="M4 7h15M15 3l4 4-4 4"/>
                    <path d="M20 17H5M9 13l-4 4 4 4"/>
                `,
                'Evaluaciones': `
                    <rect x="5" y="4" width="14" height="17" rx="2"/>
                    <path d="M9 4V2h6v2M9 10h6M9 15l2 2 4-4"/>
                `,
                'Retiros y continuidad': `
                    <path d="M10 4H5v16h5M10 12h11M17 8l4 4-4 4"/>
                `,
                'Registro': `
                    <rect x="4" y="5" width="16" height="16" rx="2"/>
                    <path d="M8 3v4M16 3v4M4 10h16M9 15h6M12 12v6"/>
                `,
            };

            const contenedor = document.createElement('span');
            contenedor.className = 'icono-tramite';
            contenedor.setAttribute('aria-hidden', 'true');

            // Los dibujos son fijos y no contienen datos ingresados por usuarios.
            contenedor.innerHTML = `
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    focusable="false"
                >
                    ${dibujos[categoriaTramite] || dibujos['Homologación']}
                </svg>
            `;

            return contenedor;
        }

        function mostrarTramites() {
            const texto = normalizar(busqueda.value);

            const filtrados = tramites.filter(tramite =>
                normalizar(tramite.nombre).includes(texto) &&
                (
                    categoria.value === '' ||
                    tramite.categoria === categoria.value
                )
            );

            tarjetas.replaceChildren();

            filtrados.forEach(tramite => {
                const tarjeta = document.createElement('article');
                tarjeta.className = 'tarjeta';

                const cabecera = document.createElement('div');
                cabecera.className = 'cabecera-tarjeta';

                const etiqueta = document.createElement('span');
                etiqueta.className = 'categoria';
                etiqueta.textContent = tramite.categoria;

                cabecera.append(
                    crearIcono(tramite.categoria),
                    etiqueta
                );

                const titulo = document.createElement('h2');
                titulo.textContent = tramite.nombre;

                const tieneFicha = Object.hasOwn(fichas, tramite.nombre);

                const acciones = document.createElement('div');
                acciones.className = 'acciones';

                const enlace = document.createElement(
                    tieneFicha ? 'a' : 'button'
                );

                const textoEnlace = document.createElement('span');
                textoEnlace.textContent = tieneFicha
                    ? 'Consultar requisitos'
                    : 'Disponible próximamente';

                enlace.append(textoEnlace);

                if (tieneFicha) {
                    enlace.href = fichas[tramite.nombre];
                    enlace.className = 'enlace-ficha';
                    enlace.setAttribute(
                        'aria-label',
                        `Consultar requisitos de ${tramite.nombre}`
                    );

                    const flecha = document.createElement('span');
                    flecha.className = 'flecha-enlace';
                    flecha.textContent = '→';
                    flecha.setAttribute('aria-hidden', 'true');

                    enlace.append(flecha);
                } else {
                    enlace.type = 'button';
                    enlace.disabled = true;
                }

                acciones.append(enlace);
                tarjeta.append(cabecera, titulo, acciones);
                tarjetas.append(tarjeta);
            });

            resultado.textContent = filtrados.length === 1
                ? '1 trámite encontrado'
                : `${filtrados.length} trámites encontrados`;

            vacio.hidden = filtrados.length !== 0;
        }

        busqueda.addEventListener('input', mostrarTramites);
        categoria.addEventListener('change', mostrarTramites);

        mostrarTramites();
    </script>
</body>
</html>