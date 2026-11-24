<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Portal del estudiante</title>

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

        header {
            background: #781c35;
            color: white;
            padding: 22px 24px;
            font-size: 20px;
            font-weight: bold;
        }

        main {
            max-width: 1000px;
            margin: auto;
            padding: 48px 24px;
        }

        .contenedor {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            border: 1px solid #e0e4eb;
            border-radius: 18px;
            overflow: hidden;
        }

        .bienvenida {
            background: #781c35;
            color: white;
            padding: 40px;
        }

        .etiqueta {
            font-size: 12px;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .bienvenida h1 {
            font-size: 32px;
            line-height: 1.25;
            margin: 24px 0 16px;
        }

        p,
        li {
            line-height: 1.7;
        }

        .bienvenida ul {
            padding-left: 20px;
        }

        .bienvenida li + li {
            margin-top: 12px;
        }

        .panel {
            padding: 40px;
            min-width: 0;
        }

        h2 {
            margin: 0 0 12px;
            font-size: 26px;
        }

        .descripcion {
            color: #596579;
            margin-bottom: 28px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .campo {
            margin-bottom: 22px;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #b8c1ce;
            border-radius: 9px;
            font: inherit;
            font-size: 14px;
        }

        input:focus-visible,
        button:focus-visible {
            outline: 3px solid #d9b2bd;
            outline-offset: 3px;
        }

        .ayuda {
            font-size: 12px;
            color: #596579;
            margin: 8px 0 0;
        }

        button {
            width: 100%;
            padding: 14px;
            border: 0;
            border-radius: 9px;
            background: #781c35;
            color: white;
            font: inherit;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #5d1529;
        }

        .aviso {
            background: #fff3d6;
            padding: 14px;
            border-radius: 9px;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 24px;
        }

        .mensaje {
            color: #781c35;
            font-size: 14px;
            line-height: 1.6;
        }

        @media (max-width: 750px) {
            .contenedor {
                grid-template-columns: 1fr;
            }

            main {
                padding: 24px 16px;
            }

            .bienvenida,
            .panel {
                padding: 28px;
            }

            .bienvenida h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <header>Portal del estudiante</header>

    <main>
        <div class="contenedor">
            <section class="bienvenida">
                <span class="etiqueta">SOLICITUDES ESTUDIANTILES</span>

                <h1>Tus trámites, en un solo lugar.</h1>

                <p>
                    Accede al portal con tu correo institucional
                    para gestionar tus solicitudes académicas.
                </p>

                <ul>
                    <li>Consulta los requisitos de cada trámite.</li>
                    <li>Completa y presenta tus solicitudes.</li>
                    <li>Consulta estados, observaciones y resoluciones.</li>
                </ul>
            </section>

            <section class="panel" aria-labelledby="titulo-login">
                <h2 id="titulo-login">Iniciar sesión</h2>

                <p class="descripcion">
                    Ingresa tu correo institucional y contraseña.
                </p>

                <form id="formulario-login">
                    <div class="campo">
                        <label for="correo">Correo institucional</label>

                        <input
                            id="correo"
                            type="email"
                            autocomplete="username"
                            placeholder="nombre@uees.edu.ec"
                            required
                            aria-describedby="ayuda-correo"
                        >

                        <p id="ayuda-correo" class="ayuda">
                            Utiliza tu correo con dominio @uees.edu.ec.
                        </p>
                    </div>

                    <div class="campo">
                        <label for="contrasena">Contraseña</label>

                        <input
                            id="contrasena"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Ingresa tu contraseña"
                            required
                        >
                    </div>

                    <button type="submit">Iniciar sesión</button>

                    <p id="mensaje" class="mensaje" role="status"></p>
                </form>

                <div class="aviso">
                    Pantalla de demostración. La autenticación todavía
                    no está conectada. Usa datos ficticios para probarla.
                </div>
            </section>
        </div>
    </main>

    <script>
        const formulario = document.getElementById('formulario-login');
        const correo = document.getElementById('correo');
        const contrasena = document.getElementById('contrasena');
        const mensaje = document.getElementById('mensaje');

        correo.addEventListener('input', () => {
            correo.setCustomValidity('');
            mensaje.textContent = '';
        });

        formulario.addEventListener('submit', evento => {
            evento.preventDefault();

            const dominio = correo.value.trim().split('@')[1];

            if (dominio?.toLowerCase() !== 'uees.edu.ec') {
                correo.setCustomValidity(
                    'Ingresa un correo institucional con dominio @uees.edu.ec.'
                );

                correo.reportValidity();
                return;
            }

            correo.setCustomValidity('');
            contrasena.value = '';

            mensaje.textContent =
                'El formato del correo es válido. El inicio de sesión real ' +
                'está pendiente de integración con el módulo 1.';
        });
    </script>
</body>
</html>