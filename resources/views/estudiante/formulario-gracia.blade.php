<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva solicitud de gracia | Portal del estudiante</title>

    <style>
        * { box-sizing: border-box; }
        [hidden] { display: none !important; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            color: #202939;
        }

        header {
            background: white;
            border-top: 4px solid #781c35;
            border-bottom: 1px solid #e0e4eb;
        }

        .marca {
            max-width: 1150px;
            margin: auto;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .logo {
            width: 145px;
            height: auto;
        }

        .marca strong {
            padding-left: 22px;
            border-left: 1px solid #e0e4eb;
            color: #781c35;
            font-size: 20px;
        }

        main {
            max-width: 1150px;
            margin: auto;
            padding: 32px 24px;
        }

        a { color: #781c35; }
        h1 { font-size: 30px; line-height: 1.3; margin-bottom: 10px; }
        h2 { margin: 0 0 12px; font-size: 24px; }
        p { line-height: 1.6; }
        .introduccion, .ayuda { color: #596579; }

        .aviso {
            background: #fff3d6;
            padding: 14px 18px;
            border-radius: 10px;
            line-height: 1.6;
            margin: 20px 0;
        }

        .pasos {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            list-style: none;
            padding: 0;
            margin: 28px 0;
        }

        .pasos li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 8px;
            border-bottom: 3px solid #e0e4eb;
            color: #596579;
            font-size: 14px;
        }

        .pasos .activo {
            border-color: #781c35;
            color: #781c35;
            font-weight: bold;
        }

        .numero {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #e9ecf1;
        }

        .activo .numero { background: #781c35; color: white; }

        .distribucion {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 290px;
            align-items: start;
            gap: 24px;
        }

        .panel {
            background: white;
            border: 1px solid #e0e4eb;
            border-radius: 14px;
            padding: 28px;
            min-width: 0;
        }

        .campos {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-top: 28px;
        }

        .campo { min-width: 0; }
        .completo { grid-column: 1 / -1; }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #b8c1ce;
            border-radius: 9px;
            font: inherit;
            font-size: 14px;
            color: #202939;
            background: white;
        }

        textarea { resize: vertical; }

        input[readonly] {
            background: #f5f6fa;
            color: #465166;
        }

        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible,
        a:focus-visible,
        button:focus-visible {
            outline: 3px solid #d9b2bd;
            outline-offset: 3px;
        }

        h2:focus { outline: none; }
        .ayuda { font-size: 12px; margin: 8px 0 0; }

        .confirmacion {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 22px;
            font-weight: normal;
            line-height: 1.6;
        }

        .confirmacion input {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 3px;
            accent-color: #781c35;
        }

        .acciones {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-top: 1px solid #e0e4eb;
            padding-top: 22px;
            margin-top: 24px;
        }

        button {
            border: 0;
            border-radius: 9px;
            padding: 13px 20px;
            font: inherit;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .principal { background: #781c35; color: white; }
        .principal:hover { background: #5d1529; }
        .secundario { background: #f6e8ed; color: #781c35; }

        button:disabled {
            background: #eceef2;
            color: #687386;
            cursor: default;
        }

        .etiqueta {
            color: #781c35;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        aside h2 { font-size: 23px; margin-top: 20px; line-height: 1.4; }
        aside p { color: #596579; }

        aside a {
            display: inline-block;
            border-top: 1px solid #e0e4eb;
            padding-top: 18px;
            margin-top: 8px;
            font-size: 14px;
            font-weight: bold;
            line-height: 1.5;
        }

        .detalle-materia {
            margin-top: 24px;
            padding: 20px;
            background: #f5f6fa;
            border: 1px solid #e0e4eb;
            border-radius: 10px;
        }

        .detalle-materia h3 { margin: 0 0 16px; font-size: 18px; }

        dl {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin: 0;
        }

        dt { font-size: 12px; color: #596579; margin-bottom: 5px; }
        dd { margin: 0; font-size: 14px; overflow-wrap: anywhere; }
        .seleccion { color: #781c35; font-size: 14px; }

        h3 { font-size: 18px; margin: 0; }
        .error { color: #b42318; font-size: 14px; margin: 12px 0 0; }
        .error:empty { display: none; }

        .requisitos-respaldo,
        .lista-archivos,
        .lista-resumen {
            list-style: none;
            padding: 0;
            margin: 16px 0 0;
        }

        .requisitos-respaldo li {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 0;
            border-bottom: 1px solid #e0e4eb;
            font-size: 14px;
            line-height: 1.5;
        }

        .estado-requisito { flex-shrink: 0; color: #596579; }
        .estado-requisito.listo { color: #1f7a4d; font-weight: bold; }

        .carga {
            margin-top: 24px;
            padding: 20px;
            border: 1px dashed #b8c1ce;
            border-radius: 10px;
            background: #f5f6fa;
        }

        .carga input { background: white; }

        .lista-archivos li {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #e0e4eb;
        }

        .lista-archivos li:last-child { border-bottom: 0; }

        .archivo-nombre {
            display: block;
            font-size: 14px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .archivo-tamano { font-size: 12px; color: #596579; }
        .lista-archivos label { margin-bottom: 6px; font-size: 12px; }

        .enlace {
            background: none;
            color: #781c35;
            padding: 6px 0;
            text-decoration: underline;
        }

        .bloque-resumen {
            margin-top: 24px;
            padding: 20px;
            background: #f5f6fa;
            border: 1px solid #e0e4eb;
            border-radius: 10px;
        }

        .encabezado-resumen {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        .lista-resumen { margin: 0; font-size: 14px; line-height: 1.6; }
        .encabezado-resumen { flex-wrap: wrap; }
        .encabezado-resumen h3 { overflow-wrap: anywhere; }
        .detalle-materia h3 { margin: 0; }
        .agregar-materia { margin-top: 12px; }
        #resumen-materias { margin-top: 20px; }
        .resumen-materia + .resumen-materia { margin-top: 20px; }
        .resumen-materia h4 { margin: 0 0 12px; }
        dd { white-space: pre-wrap; }
        input, select { min-width: 0; }
        .requisitos-respaldo li { flex-wrap: wrap; }

        @media (max-width: 850px) {
            .distribucion { grid-template-columns: 1fr; }
        }

        @media (max-width: 600px) {
            main { padding: 24px 16px; }
            .marca { padding: 14px 16px; gap: 14px; }
            .logo { width: 105px; }
            .marca strong { padding-left: 14px; font-size: 16px; }
            h1 { font-size: 26px; }
            .panel { padding: 22px; }
            .campos, dl { grid-template-columns: 1fr; }
            .lista-archivos li { grid-template-columns: 1fr; }

            .pasos li {
                flex-direction: column;
                text-align: center;
                font-size: 12px;
                padding: 12px 4px;
            }

            .acciones { align-items: stretch; flex-direction: column; }
        }
    </style>
</head>
<body>
    <header>
        <div class="marca">
            <img
                class="logo"
                src="{{ asset('images/estudiante/Logo-UEES.gif') }}"
                alt="Universidad Espíritu Santo"
            >
            <strong>Portal del estudiante</strong>
        </div>
    </header>

    <main>
        <a href="{{ route('estudiante.tramites.gracia') }}">
            ← Volver a los requisitos
        </a>

        <h1>Solicitud de examen de gracia</h1>

        <p class="introduccion">
            Confirma tus datos y completa la información de tu solicitud.
        </p>

        <div class="aviso">
            Vista de demostración con un perfil y materias ficticias.
            Usa datos de prueba. La solicitud todavía no se guarda ni se envía;
            al recargar la página se perderá lo escrito.
        </div>

        <ol class="pasos" aria-label="Pasos de la solicitud">
            <li class="activo" aria-current="step">
                <span class="numero">1</span><span>Tus datos</span>
            </li>
            <li>
                <span class="numero">2</span><span>Tu trámite</span>
            </li>
            <li>
                <span class="numero">3</span><span>Respaldos</span>
            </li>
            <li>
                <span class="numero">4</span><span>Revisar</span>
            </li>
        </ol>

        <div class="distribucion">
            <div>
                <section id="paso-1" class="panel" aria-labelledby="titulo-datos">
                    <h2 id="titulo-datos" tabindex="-1">Primero, tus datos</h2>

                    <p class="introduccion">
                        Revisa tu información personal y de carrera.
                        Si encuentras un error, solicita su actualización
                        a la unidad académica correspondiente.
                        Puedes completar tu celular de contacto.
                    </p>

                    <form id="formulario-datos">
                        <div class="campos">
                            <div class="campo completo">
                                <label for="nombre">Nombres y apellidos</label>
                                <input id="nombre" type="text"
                                    value="Estudiante de demostración" readonly>
                            </div>

                            <div class="campo">
                                <label for="codigo">Código estudiantil</label>
                                <input id="codigo" type="text"
                                    value="2026000001" readonly>
                            </div>

                            <div class="campo">
                                <label for="cedula">Cédula o pasaporte</label>
                                <input id="cedula" type="text"
                                    placeholder="No disponible en el perfil de prueba"
                                    readonly>
                            </div>

                            <div class="campo">
                                <label for="facultad">Facultad</label>
                                <input id="facultad" type="text"
                                    value="Ingeniería" readonly>
                            </div>

                            <div class="campo">
                                <label for="escuela">Escuela</label>
                                <input id="escuela" type="text"
                                    placeholder="No disponible en el perfil de prueba" readonly>
                            </div>

                            <div class="campo">
                                <label for="carrera">Carrera actual</label>
                                <input id="carrera" type="text"
                                    value="Ingeniería en Computación" readonly>
                            </div>

                            <div class="campo">
                                <label for="modalidad">Modalidad</label>
                                <input id="modalidad" type="text"
                                    value="Presencial" readonly>
                            </div>

                            <div class="campo">
                                <label for="periodo">Periodo actual</label>
                                <input id="periodo" type="text"
                                    value="Periodo de demostración" readonly>
                            </div>

                            <div class="campo">
                                <label for="correo">Correo institucional</label>
                                <input id="correo" type="email"
                                    value="estudiante.demo@uees.edu.ec" readonly>
                            </div>

                            <div class="campo">
                                <label for="celular">Celular</label>
                                <input id="celular" name="celular" type="tel"
                                    autocomplete="tel"
                                    placeholder="Ingresa tu número de contacto"
                                    required maxlength="20">
                            </div>
                        </div>

                        <label class="confirmacion" for="confirmar-datos">
                            <input id="confirmar-datos" type="checkbox" required>
                            <span>Confirmo que he revisado los datos mostrados.</span>
                        </label>

                        <div class="acciones">
                            <a href="{{ route('estudiante.catalogo') }}">
                                Volver al catálogo
                            </a>

                            <button class="principal" type="submit">
                                Continuar a tu trámite →
                            </button>
                        </div>
                    </form>
                </section>

                <section id="paso-2" class="panel"
                    aria-labelledby="titulo-tramite" hidden>

                    <h2 id="titulo-tramite" tabindex="-1">Ahora, tu trámite</h2>

                    <p class="introduccion">
                        Agrega las materias para las que solicitas el examen de gracia
                        y explica el motivo. Los campos marcados con * son obligatorios.
                    </p>

                    <div class="aviso">
                        Las materias deben pertenecer al semestre actual.
                        La referencia de asistencia es al menos 70 % (condición
                        preliminar por confirmar con la facultad) y la nota previa
                        es al menos 80/100. Los datos declarados requieren revisión;
                        completar el formulario no representa una aprobación.
                    </div>

                    <div class="campos">
                        <div class="campo completo">
                            <label for="materia">Materias de la solicitud *</label>
                            <select id="materia" aria-describedby="ayuda-materia error-materias">
                                <option value="">Selecciona una materia para agregar</option>
                            </select>
                            <button id="agregar-materia" class="secundario agregar-materia"
                                type="button">Agregar materia</button>
                            <p id="ayuda-materia" class="ayuda">
                                Agrega de 1 a 10 materias. Esta lista es ficticia;
                                en la versión final se consultarán tus materias del semestre actual.
                            </p>
                        </div>
                    </div>

                    <p id="error-materias" class="error" role="alert"></p>
                    <p id="seleccion" class="seleccion" role="status">
                        No has agregado materias.
                    </p>
                    <div id="materias-seleccionadas"></div>

                    <template id="plantilla-materia">
                        <section class="detalle-materia">
                            <div class="encabezado-resumen">
                                <h3 data-titulo-materia></h3>
                                <button class="enlace" type="button" data-quitar-materia>Quitar</button>
                            </div>
                            <dl data-datos-materia></dl>
                            <div class="campos">
                                <div class="campo">
                                    <label data-etiqueta="asistencia">Asistencia (%) — opcional</label>
                                    <input data-campo="asistencia" type="number"
                                        min="0" max="100" step="0.01" placeholder="Ej. 75">
                                </div>
                                <div class="campo">
                                    <label data-etiqueta="nota">Calificación actual — opcional</label>
                                    <input data-campo="nota" type="number"
                                        min="0" max="100" step="0.01" placeholder="Ej. 85">
                                </div>
                            </div>
                            <p class="ayuda" data-ayuda-materia>
                                Completa estos datos si los conoces. Si no puedes confirmarlos,
                                déjalos vacíos para revisión por la facultad.
                            </p>
                        </section>
                    </template>

                    <div class="campos">
                        <div class="campo completo">
                            <label for="motivo">Motivo de la solicitud *</label>
                            <textarea id="motivo" name="motivo" rows="3"
                                required maxlength="2000"
                                placeholder="Explica por qué solicitas un examen de gracia"></textarea>
                        </div>
                        <div class="campo completo">
                            <label for="justificacion">Justificación *</label>
                            <textarea id="justificacion" name="justificacion" rows="4"
                                required maxlength="2000"
                                placeholder="Detalla las circunstancias que sustentan tu solicitud"></textarea>
                        </div>
                    </div>

                    <div class="acciones">
                        <button id="volver-datos" class="secundario" type="button">
                            ← Volver a tus datos
                        </button>

                        <button id="continuar-respaldos" class="principal"
                            type="button">
                            Continuar a respaldos →
                        </button>
                    </div>

                    <p class="ayuda">
                        El guardado de borradores estará disponible
                        en una siguiente entrega.
                    </p>
                </section>

                <section id="paso-3" class="panel"
                    aria-labelledby="titulo-respaldos" hidden>

                    <h2 id="titulo-respaldos" tabindex="-1">Tus respaldos</h2>

                    <p class="introduccion">
                        Adjunta los documentos que respaldan tu solicitud
                        e indica a qué requisito corresponde cada uno.
                    </p>

                    <div class="aviso">
                        Los respaldos obligatorios están pendientes de confirmación
                        por la facultad. Por ahora puedes agregar documentos de apoyo
                        o continuar sin archivos en esta demostración.
                    </div>

                    <h3>Documentos de apoyo</h3>
                    <ul id="requisitos-respaldo" class="requisitos-respaldo"></ul>

                    <div class="carga">
                        <label for="archivos">Agregar archivos</label>
                        <input id="archivos" type="file" multiple
                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                            aria-describedby="ayuda-archivos">
                        <p id="ayuda-archivos" class="ayuda">
                            PDF, JPG o PNG. Máximo 10 MB por archivo.
                        </p>
                    </div>

                    <p id="estado-archivos" class="ayuda" role="status"></p>
                    <p id="error-archivos" class="error" role="alert"></p>

                    <ul id="lista-archivos" class="lista-archivos"
                        aria-label="Archivos adjuntos"></ul>

                    <p id="sin-archivos" class="ayuda">
                        Todavía no has agregado archivos.
                    </p>

                    <div class="acciones">
                        <button id="volver-tramite" class="secundario" type="button">
                            ← Volver a tu trámite
                        </button>

                        <button id="continuar-revisar" class="principal"
                            type="button">
                            Continuar a revisar →
                        </button>
                    </div>

                    <p class="ayuda">
                        Los archivos se revisan solo en tu navegador;
                        todavía no se suben al servidor.
                    </p>
                </section>

                <section id="paso-4" class="panel"
                    aria-labelledby="titulo-revisar" hidden>

                    <h2 id="titulo-revisar" tabindex="-1">Revisa tu solicitud</h2>

                    <p class="introduccion">
                        Comprueba que todo esté correcto antes de confirmar.
                        Puedes volver a cualquier paso para corregirlo.
                    </p>

                    <section class="bloque-resumen" aria-labelledby="resumen-datos">
                        <div class="encabezado-resumen">
                            <h3 id="resumen-datos">Tus datos</h3>
                            <button class="enlace" type="button" data-ir-a="1"
                                aria-label="Editar tus datos">
                                Editar
                            </button>
                        </div>
                        <dl id="lista-datos"></dl>
                    </section>

                    <section class="bloque-resumen" aria-labelledby="resumen-tramite">
                        <div class="encabezado-resumen">
                            <h3 id="resumen-tramite">Tu trámite</h3>
                            <button class="enlace" type="button" data-ir-a="2"
                                aria-label="Editar tu trámite">
                                Editar
                            </button>
                        </div>
                        <dl id="lista-tramite"></dl>
                        <div id="resumen-materias"></div>
                    </section>

                    <section class="bloque-resumen"
                        aria-labelledby="resumen-respaldos">
                        <div class="encabezado-resumen">
                            <h3 id="resumen-respaldos">Respaldos</h3>
                            <button class="enlace" type="button" data-ir-a="3"
                                aria-label="Editar tus respaldos">
                                Editar
                            </button>
                        </div>
                        <ul id="lista-respaldos" class="lista-resumen"></ul>
                    </section>

                    <div class="aviso">
                        La pre-validación de elegibilidad todavía no está conectada.
                        La facultad revisará las materias, la asistencia, las notas
                        y la justificación. Esta demostración no aprueba tu solicitud.
                    </div>

                    <label class="confirmacion" for="declaracion">
                        <input id="declaracion" type="checkbox" required>
                        <span>
                            Declaro que la información ingresada es verdadera
                            y que los documentos adjuntos son auténticos.
                        </span>
                    </label>

                    <div class="acciones">
                        <button id="volver-respaldos" class="secundario"
                            type="button">
                            ← Volver a respaldos
                        </button>

                        <button id="confirmar-solicitud" class="principal"
                            type="button">
                            Confirmar solicitud
                        </button>
                    </div>

                    <p id="mensaje-confirmacion" class="seleccion"
                        role="status"></p>
                </section>
            </div>

            <aside class="panel">
                <span class="etiqueta">TENLO PRESENTE</span>

                <h2>Primero el documento. Después, tu firma.</h2>

                <p>
                    Cuando el servicio esté disponible, al completar el formulario
                    podrás descargar el documento oficial.
                </p>

                <p>
                    Después deberás firmarlo y subir el PDF firmado junto con
                    los respaldos que confirme la facultad para enviar tu solicitud.
                </p>

                <a href="{{ route('estudiante.tramites.gracia') }}">
                    Ver requisitos de este trámite →
                </a>
            </aside>
        </div>
    </main>

    <script>
        // Cursos de demostración del semestre actual, sin notas ni asistencia precargadas.
        const materias = [
            { id: 'calculo-demo', codigo: 'MAT202', nombre: 'Cálculo II',
                periodo: 'Periodo actual de demostración', paralelo: 'A', docente: 'Docente de prueba A' },
            { id: 'fisica-demo', codigo: 'FIS101', nombre: 'Física I',
                periodo: 'Periodo actual de demostración', paralelo: 'B', docente: 'Docente de prueba B' },
            { id: 'programacion-demo', codigo: 'COM203', nombre: 'Programación II',
                periodo: 'Periodo actual de demostración', paralelo: 'A', docente: 'Docente de prueba C' }
        ];
        const MAXIMO_MATERIAS = 10;
        const seleccionadas = [];
        const formularioDatos = document.getElementById('formulario-datos');
        const pasos = document.querySelectorAll('.pasos li');
        const selectorMateria = document.getElementById('materia');
        const listaMaterias = document.getElementById('materias-seleccionadas');
        const errorMaterias = document.getElementById('error-materias');
        const botonAgregar = document.getElementById('agregar-materia');
        const titulosPaso = ['titulo-datos', 'titulo-tramite', 'titulo-respaldos', 'titulo-revisar'];

        function mostrarPaso(numero) {
            [1, 2, 3, 4].forEach(paso => {
                document.getElementById('paso-' + paso).hidden = numero !== paso;
            });
            pasos.forEach((paso, indice) => {
                const activo = indice === numero - 1;
                paso.classList.toggle('activo', activo);
                if (activo) paso.setAttribute('aria-current', 'step');
                else paso.removeAttribute('aria-current');
            });
            document.getElementById(titulosPaso[numero - 1]).focus();
        }

        function invalidarConfirmacion() {
            document.getElementById('declaracion').checked = false;
            document.getElementById('mensaje-confirmacion').textContent = '';
        }

        document.querySelector('main').addEventListener('input', evento => {
            if (evento.target.id !== 'declaracion') invalidarConfirmacion();
        });
        formularioDatos.addEventListener('submit', evento => {
            evento.preventDefault();
            const celular = document.getElementById('celular');
            celular.value = celular.value.trim();
            if (formularioDatos.reportValidity()) mostrarPaso(2);
        });
        document.getElementById('volver-datos').addEventListener('click', () => mostrarPaso(1));
        document.getElementById('volver-tramite').addEventListener('click', () => mostrarPaso(2));

        function actualizarSelector() {
            selectorMateria.replaceChildren(new Option('Selecciona una materia para agregar', ''));
            materias.filter(materia => !seleccionadas.some(item => item.materia.id === materia.id))
                .forEach(materia => selectorMateria.append(new Option(
                    materia.codigo + ' — ' + materia.nombre + ' (paralelo ' + materia.paralelo + ')',
                    materia.id
                )));
            const sinCupo = seleccionadas.length >= MAXIMO_MATERIAS || selectorMateria.options.length === 1;
            selectorMateria.disabled = sinCupo;
            botonAgregar.disabled = sinCupo;
            document.getElementById('seleccion').textContent = seleccionadas.length
                ? seleccionadas.length + ' de ' + MAXIMO_MATERIAS + ' materias agregadas.'
                : 'No has agregado materias.';
        }

        function filasMateria(materia) {
            return [
                ['Código', materia.codigo], ['Nombre', materia.nombre],
                ['Periodo', materia.periodo], ['Paralelo', materia.paralelo],
                ['Docente', materia.docente], ['Estado', 'Semestre actual (demostración)']
            ];
        }

        botonAgregar.addEventListener('click', () => {
            const materia = materias.find(item => item.id === selectorMateria.value);
            if (!materia) {
                errorMaterias.textContent = 'Selecciona una materia para agregarla.';
                selectorMateria.focus();
                return;
            }
            if (seleccionadas.length >= MAXIMO_MATERIAS ||
                seleccionadas.some(item => item.materia.id === materia.id)) return;

            const item = { materia, asistencia: '', nota: '' };
            seleccionadas.push(item);
            const tarjeta = document.getElementById('plantilla-materia').content.firstElementChild.cloneNode(true);
            const titulo = tarjeta.querySelector('[data-titulo-materia]');
            titulo.id = 'titulo-' + materia.id;
            titulo.textContent = materia.codigo + ' — ' + materia.nombre;
            tarjeta.setAttribute('aria-labelledby', titulo.id);
            const datos = tarjeta.querySelector('[data-datos-materia]');
            datos.id = 'datos-' + materia.id;
            const ayuda = tarjeta.querySelector('[data-ayuda-materia]');
            ayuda.id = 'ayuda-' + materia.id;
            ['asistencia', 'nota'].forEach(nombre => {
                const campo = tarjeta.querySelector('[data-campo="' + nombre + '"]');
                campo.id = nombre + '-' + materia.id;
                campo.name = 'materias[' + materia.id + '][' + nombre + ']';
                campo.setAttribute('aria-describedby', ayuda.id);
                tarjeta.querySelector('[data-etiqueta="' + nombre + '"]').htmlFor = campo.id;
                campo.addEventListener('input', () => { item[nombre] = campo.value; });
            });
            const quitar = tarjeta.querySelector('[data-quitar-materia]');
            quitar.setAttribute('aria-label', 'Quitar ' + materia.nombre);
            quitar.addEventListener('click', () => {
                seleccionadas.splice(seleccionadas.indexOf(item), 1);
                tarjeta.remove();
                errorMaterias.textContent = '';
                invalidarConfirmacion();
                actualizarSelector();
                selectorMateria.focus();
            });
            listaMaterias.append(tarjeta);
            pintarLista(datos.id, filasMateria(materia));
            errorMaterias.textContent = '';
            invalidarConfirmacion();
            actualizarSelector();
            tarjeta.querySelector('input').focus();
        });
        selectorMateria.addEventListener('change', () => { errorMaterias.textContent = ''; });

        function validarTramite() {
            if (!seleccionadas.length) {
                errorMaterias.textContent = 'Agrega al menos una materia para continuar.';
                selectorMateria.focus();
                return false;
            }
            for (const campo of document.querySelectorAll('#paso-2 input, #paso-2 textarea')) {
                if (campo.tagName === 'TEXTAREA') campo.value = campo.value.trim();
                if (!campo.reportValidity()) return false;
            }
            return true;
        }
        document.getElementById('continuar-respaldos').addEventListener('click', () => {
            if (validarTramite()) mostrarPaso(3);
        });

        // La facultad aún no ha definido respaldos obligatorios para examen de gracia.
        const requisitosRespaldo = [
            { id: 'apoyo-justificacion', nombre: 'Documento de apoyo a la justificación', obligatorio: false },
            { id: 'otro', nombre: 'Otro documento de apoyo', obligatorio: false }
        ];

        const TAMANO_MAXIMO = 10 * 1024 * 1024;
        const archivos = [];
        const entradaArchivos = document.getElementById('archivos');
        const listaArchivos = document.getElementById('lista-archivos');
        const errorArchivos = document.getElementById('error-archivos');

        function formatearTamano(bytes) {
            return bytes < 1024 * 1024
                ? `${Math.max(1, Math.round(bytes / 1024))} KB`
                : `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        }

        // Revisa los primeros bytes: no basta con la extensión del nombre.
        async function tipoPorContenido(archivo) {
            const bytes = new Uint8Array(await archivo.slice(0, 8).arrayBuffer());
            const empiezaCon = firma => firma.every((valor, i) => bytes[i] === valor);

            if (empiezaCon([0x25, 0x50, 0x44, 0x46, 0x2D])) return 'PDF';
            if (empiezaCon([0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A])) return 'PNG';
            if (empiezaCon([0xFF, 0xD8, 0xFF])) return 'JPG';
            return null;
        }

        function pintarRequisitos() {
            const lista = document.getElementById('requisitos-respaldo');
            lista.innerHTML = '';

            requisitosRespaldo.forEach(requisito => {
                const cantidad = archivos.filter(
                    item => item.requisito === requisito.id
                ).length;

                const fila = document.createElement('li');
                const nombre = document.createElement('span');
                const estado = document.createElement('span');

                nombre.textContent = requisito.nombre +
                    (requisito.obligatorio ? ' (obligatorio)' : ' (opcional)');

                estado.className = 'estado-requisito' + (cantidad ? ' listo' : '');
                estado.textContent = cantidad === 0
                    ? (requisito.obligatorio ? 'Pendiente' : 'Sin adjuntos')
                    : `${cantidad} ${cantidad === 1 ? 'archivo' : 'archivos'}`;

                fila.append(nombre, estado);
                lista.append(fila);
            });
        }

        function pintarArchivos() {
            listaArchivos.innerHTML = '';
            document.getElementById('sin-archivos').hidden = archivos.length > 0;

            archivos.forEach((item, indice) => {
                const fila = document.createElement('li');

                const datos = document.createElement('div');
                const nombre = document.createElement('span');
                const tamano = document.createElement('span');
                nombre.className = 'archivo-nombre';
                nombre.textContent = item.archivo.name;
                tamano.className = 'archivo-tamano';
                tamano.textContent = `${item.tipo} · ${formatearTamano(item.archivo.size)}`;
                datos.append(nombre, tamano);

                const campo = document.createElement('div');
                const etiqueta = document.createElement('label');
                const selector = document.createElement('select');
                selector.id = `requisito-archivo-${indice}`;
                selector.required = true;
                etiqueta.htmlFor = selector.id;
                etiqueta.textContent = 'Corresponde a';

                selector.append(new Option('Selecciona el requisito', ''));
                requisitosRespaldo.forEach(requisito => {
                    selector.append(new Option(requisito.nombre, requisito.id));
                });
                selector.value = item.requisito;

                selector.addEventListener('change', () => {
                    item.requisito = selector.value;
                    invalidarConfirmacion();
                    errorArchivos.textContent = '';
                    pintarRequisitos();
                });
                campo.append(etiqueta, selector);

                const quitar = document.createElement('button');
                quitar.type = 'button';
                quitar.className = 'secundario';
                quitar.textContent = 'Quitar';
                quitar.setAttribute('aria-label', `Quitar ${item.archivo.name}`);
                quitar.addEventListener('click', () => {
                    archivos.splice(indice, 1);
                    invalidarConfirmacion();
                    pintarArchivos();
                    entradaArchivos.focus();
                });

                fila.append(datos, campo, quitar);
                listaArchivos.append(fila);
            });

            pintarRequisitos();
        }

        let revisandoArchivos = false;
        entradaArchivos.addEventListener('change', async () => {
            if (revisandoArchivos) return;
            const nuevos = Array.from(entradaArchivos.files);
            const rechazados = [];
            revisandoArchivos = true;
            entradaArchivos.disabled = true;
            document.getElementById('continuar-revisar').disabled = true;
            document.getElementById('estado-archivos').textContent = 'Revisando archivos…';
            invalidarConfirmacion();
            try {
                for (const archivo of nuevos) {
                    if (archivos.some(item => item.archivo.name === archivo.name &&
                        item.archivo.size === archivo.size &&
                        item.archivo.lastModified === archivo.lastModified)) continue;
                    if (archivo.size > TAMANO_MAXIMO) {
                        rechazados.push(archivo.name + ' supera los 10 MB');
                        continue;
                    }
                    let tipo;
                    try { tipo = await tipoPorContenido(archivo); }
                    catch {
                        rechazados.push(archivo.name + ' no se pudo leer; vuelve a seleccionarlo');
                        continue;
                    }
                    if (!tipo) {
                        rechazados.push(archivo.name + ' no tiene una firma de PDF, JPG o PNG reconocida');
                        continue;
                    }
                    archivos.push({ archivo, tipo, requisito: '' });
                }
            } finally {
                revisandoArchivos = false;
                entradaArchivos.disabled = false;
                entradaArchivos.value = '';
                document.getElementById('continuar-revisar').disabled = false;
                document.getElementById('estado-archivos').textContent = '';
                errorArchivos.textContent = rechazados.length
                    ? 'No se agregaron: ' + rechazados.join('; ') + '.' : '';
                pintarArchivos();
            }
        });

        document.getElementById('continuar-revisar')
            .addEventListener('click', () => {
                if (revisandoArchivos) return;
                const sinRequisito = listaArchivos.querySelector('select:invalid');

                if (sinRequisito) {
                    sinRequisito.reportValidity();
                    return;
                }

                const faltantes = requisitosRespaldo.filter(requisito =>
                    requisito.obligatorio &&
                    !archivos.some(item => item.requisito === requisito.id)
                );

                if (faltantes.length) {
                    errorArchivos.textContent =
                        'Falta adjuntar: ' +
                        faltantes.map(item => item.nombre).join('; ') + '.';
                    entradaArchivos.focus();
                    return;
                }

                errorArchivos.textContent = '';
                pintarResumen();
                mostrarPaso(4);
            });

        // Paso 4: resumen editable.
        function textoDe(id) {
            const campo = document.getElementById(id);

            if (campo.tagName === 'SELECT') {
                return campo.value ? campo.selectedOptions[0].textContent.trim() : '';
            }

            return campo.value.trim();
        }

        function pintarLista(idLista, filas) {
            const lista = document.getElementById(idLista);
            lista.innerHTML = '';

            filas.forEach(([titulo, valor]) => {
                const grupo = document.createElement('div');
                const dt = document.createElement('dt');
                const dd = document.createElement('dd');
                dt.textContent = titulo;
                dd.textContent = valor || 'No indicado';
                grupo.append(dt, dd);
                lista.append(grupo);
            });
        }

        function pintarResumen() {
            pintarLista('lista-datos', [
                ['Nombres y apellidos', textoDe('nombre')],
                ['Código estudiantil', textoDe('codigo')],
                ['Cédula o pasaporte', textoDe('cedula')],
                ['Facultad', textoDe('facultad')],
                ['Escuela', textoDe('escuela')],
                ['Carrera', textoDe('carrera')],
                ['Modalidad', textoDe('modalidad')],
                ['Periodo actual', textoDe('periodo')],
                ['Correo institucional', textoDe('correo')],
                ['Celular', textoDe('celular')]
            ]);

            pintarLista('lista-tramite', [
                ['Motivo', textoDe('motivo')], ['Justificación', textoDe('justificacion')]
            ]);
            const resumenMaterias = document.getElementById('resumen-materias');
            resumenMaterias.replaceChildren();
            seleccionadas.forEach(item => {
                const seccion = document.createElement('section');
                seccion.className = 'resumen-materia';
                const titulo = document.createElement('h4');
                titulo.textContent = item.materia.nombre;
                const datos = document.createElement('dl');
                datos.id = 'resumen-' + item.materia.id;
                seccion.append(titulo, datos);
                resumenMaterias.append(seccion);
                pintarLista(datos.id, [
                    ...filasMateria(item.materia),
                    ['Asistencia (%)', item.asistencia || 'No indicada; requiere revisión'],
                    ['Calificación actual', item.nota || 'No indicada; requiere revisión']
                ]);
            });

            const listaRespaldos = document.getElementById('lista-respaldos');
            listaRespaldos.innerHTML = '';
            if (!archivos.length) {
                const fila = document.createElement('li');
                fila.textContent = 'Sin archivos adjuntos. Respaldos obligatorios pendientes de confirmación por la facultad.';
                listaRespaldos.append(fila);
            }

            archivos.forEach(item => {
                const requisito = requisitosRespaldo.find(
                    r => r.id === item.requisito
                );
                const fila = document.createElement('li');
                fila.textContent = `${item.archivo.name} — ${requisito.nombre}`;
                listaRespaldos.append(fila);
            });

            document.getElementById('mensaje-confirmacion').textContent = '';
        }

        document.querySelectorAll('[data-ir-a]').forEach(boton => {
            boton.addEventListener('click', () => {
                invalidarConfirmacion();
                mostrarPaso(Number(boton.dataset.irA));
            });
        });

        document.getElementById('volver-respaldos')
            .addEventListener('click', () => { invalidarConfirmacion(); mostrarPaso(3); });

        document.getElementById('confirmar-solicitud')
            .addEventListener('click', () => {
                const declaracion = document.getElementById('declaracion');

                if (!declaracion.reportValidity()) return;

                document.getElementById('mensaje-confirmacion').textContent =
                    'Vista de demostración: la solicitud no se guardó. ' +
                    'Cuando el sistema esté conectado, aquí se generará ' +
                    'tu código de solicitud y el documento oficial para firmar.';
            });

        actualizarSelector();
        pintarArchivos();
    </script>
</body>
</html>
