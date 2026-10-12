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

        a {
            color: #781c35;
        }

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

        .icono-titulo {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            color: #781c35;
        }

        svg {
            display: block;
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

        .tarjeta-informacion p {
            margin: 8px 0 0;
            color: #596579;
            font-size: 14px;
            line-height: 1.6;
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
            text-decoration: none;
        }

        .boton-iniciar:hover {
            background: #5d1529;
            border-color: #5d1529;
        }

        .boton svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .boton-chatbot:disabled {
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

            .marca {
                gap: 14px;
            }

            .logo-uees {
                width: 105px;
            }

            .nombre-portal {
                padding-left: 14px;
                font-size: 16px;
            }

            .enlace-catalogo {
                display: none;
            }

            main {
                padding: 24px 16px;
            }

            .introduccion {
                margin-top: 26px;
            }

            .panel {
                padding: 22px;
            }

            .informacion-adicional {
                grid-template-columns: 1fr;
            }

            .lista-requisitos li {
                gap: 12px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>