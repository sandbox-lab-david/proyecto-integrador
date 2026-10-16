<style>
        .lista-solicitudes,
        .lista-observaciones {
            list-style: none;
            margin: 10px 0 0;
            padding: 0;
        }

        .lista-solicitudes li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 0;
        }

        .lista-solicitudes li + li,
        .lista-observaciones li + li {
            border-top: 1px solid #e7eaf0;
        }

        .lista-solicitudes li:last-child,
        .lista-observaciones li:last-child {
            padding-bottom: 0;
        }

        .lista-solicitudes strong {
            display: block;
            margin-bottom: 4px;
            font-size: 16px;
            line-height: 1.4;
        }

        .dato {
            display: block;
            color: #596579;
            font-size: 13px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .acciones-solicitud {
            display: flex;
            flex-shrink: 0;
            gap: 10px;
        }

        .boton-fila {
            padding: 10px 16px;
            border: 1px solid #781c35;
            border-radius: 8px;
            background: #781c35;
            color: white;
            font: inherit;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
        }

        .boton-fila:hover {
            border-color: #5d1529;
            background: #5d1529;
        }

        .boton-secundario {
            background: white;
            color: #781c35;
        }

        .boton-secundario:hover {
            background: #fcf7f9;
        }

        .boton-fila:disabled {
            opacity: 0.6;
            cursor: default;
        }

        .vacio {
            margin: 10px 0 0;
            color: #596579;
            font-size: 14px;
            line-height: 1.6;
        }

        .estado-solicitud {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 14px;
            margin-top: 14px;
        }

        .datos-resolucion {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin: 16px 0 0;
        }

        .datos-resolucion dt {
            color: #596579;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .datos-resolucion dd {
            margin: 6px 0 0;
            font-size: 16px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .texto-observacion {
            margin: 8px 0 0;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .lista-observaciones li {
            padding: 16px 0;
        }

        .panel .visor-documento {
            margin-top: 18px;
        }

        @media (max-width: 600px) {
            .lista-solicitudes li {
                flex-direction: column;
                align-items: flex-start;
            }

            .datos-resolucion {
                grid-template-columns: 1fr;
            }
        }
    </style>
