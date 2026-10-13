<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva solicitud de {{ $tramite['nombre'] }} | Portal del estudiante</title>

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

        .barra-borrador {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px 16px;
            margin-bottom: 24px;
            padding: 10px 18px;
            background: white;
            border: 1px solid #e0e4eb;
            border-radius: 10px;
        }

        .barra-borrador p { margin: 0; color: #596579; font-size: 14px; }
        .barra-borrador div { display: flex; align-items: center; gap: 16px; }
        .barra-borrador .secundario { padding: 10px 16px; }

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

        .carga { margin-top: 24px; }

        .acciones-archivo { display: flex; gap: 8px; }
        .acciones-archivo button { padding: 10px 14px; }

        dialog {
            width: min(900px, calc(100% - 32px));
            padding: 24px;
            border: 0;
            border-radius: 14px;
            color: inherit;
        }

        dialog::backdrop { background: rgba(32, 41, 57, 0.55); }

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

        .bloque-firmado {
            margin-top: 24px;
            padding: 20px;
            border: 1px solid #eadce1;
            border-left: 4px solid #781c35;
            border-radius: 10px;
        }

        .pasos-firma {
            margin: 12px 0 0;
            padding-left: 20px;
            color: #465166;
            font-size: 14px;
            line-height: 1.7;
        }

        .firmado-subido {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px 16px;
            margin-top: 16px;
            padding: 14px 16px;
            background: #f5f6fa;
            border: 1px solid #e0e4eb;
            border-radius: 9px;
        }

        .agregar-materia { display: flex; gap: 12px; }
        .agregar-materia button { flex-shrink: 0; }
        .detalle-materia .encabezado-resumen { margin-bottom: 4px; }
        .detalle-materia .encabezado-resumen h3 { margin: 0; }
        .detalle-materia .campos { margin-top: 16px; }

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
            .agregar-materia { flex-direction: column; }

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
        <a href="{{ route($tramite['ficha']) }}">
            ← Volver a los requisitos
        </a>

        <h1>Solicitud de {{ $tramite['nombre'] }}</h1>

        <p class="introduccion">
            Confirma tus datos y completa la información de tu solicitud.
        </p>

        <div class="aviso">
            Vista de demostración con un perfil y materias ficticias.
            Usa datos de prueba. La solicitud todavía no se envía; lo que
            escribas queda como borrador en este navegador mientras tu
            sesión siga abierta.
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

        <div class="barra-borrador">
            <p id="estado-borrador" role="status">
                Lo que escribas se guardará como borrador.
            </p>

            <div>
                <button id="descartar-borrador" class="enlace" type="button" hidden>
                    Descartar borrador
                </button>

                <button id="guardar-borrador" class="secundario" type="button">
                    Guardar borrador
                </button>
            </div>
        </div>

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
                                    inputmode="numeric"
                                    pattern="[0-9]+"
                                    title="Ingresa únicamente números."
                                    placeholder="Ingresa tu número de contacto"
                                    required maxlength="20"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
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

                    <p class="introduccion">{{ $tramite['instrucciones'] }}</p>

                    @isset($tramite['aviso'])
                        <div class="aviso">{{ $tramite['aviso'] }}</div>
                    @endisset

                    @if ($tramite['max_materias'] > 0)
                        <div class="campos">
                            <div class="campo completo">
                                <label for="materia">
                                    {{ $tramite['max_materias'] === 1 ? 'Materia de la solicitud' : 'Materias de la solicitud' }}
                                </label>

                                <div class="agregar-materia">
                                    <select id="materia" aria-describedby="ayuda-materia">
                                        <option value="">Selecciona una materia</option>
                                        @foreach ($materias as $materia)
                                            <option value="{{ $materia['id'] }}">
                                                {{ $materia['codigo'] }} — {{ $materia['nombre'] }}
                                                ({{ $materia['periodo'] }}, paralelo {{ $materia['paralelo'] }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <button id="agregar-materia" class="secundario"
                                        type="button">
                                        Agregar
                                    </button>
                                </div>

                                <p id="ayuda-materia" class="ayuda">
                                    {{ $tramite['max_materias'] === 1
                                        ? 'Este trámite admite una sola materia.'
                                        : 'Puedes agregar hasta '.$tramite['max_materias'].' materias.' }}
                                    Lista ficticia para probar el selector.
                                </p>
                            </div>
                        </div>

                        <p id="error-materias" class="error" role="alert"></p>

                        <div id="materias-elegidas"></div>
                    @endif

                    <div id="campos-tramite" class="campos">
                        @foreach ($tramite['campos'] as $campo)
                            @php($id = 'campo-'.$campo['nombre'])

                            <div class="campo completo"
                                @isset($campo['mostrar_si'])
                                    data-mostrar-si="campo-{{ $campo['mostrar_si']['campo'] }}"
                                    data-valor="{{ $campo['mostrar_si']['valor'] }}"
                                    hidden
                                @endisset>

                                <label for="{{ $id }}">
                                    {{ $campo['etiqueta'] }}{{ $campo['obligatorio'] ? '' : ' (opcional)' }}
                                </label>

                                @switch($campo['tipo'])
                                    @case('texto_largo')
                                        <textarea id="{{ $id }}" name="{{ $campo['nombre'] }}"
                                            rows="4" maxlength="2000"
                                            @required($campo['obligatorio'])></textarea>
                                        @break

                                    @case('lista')
                                        <select id="{{ $id }}" name="{{ $campo['nombre'] }}"
                                            @required($campo['obligatorio'])>
                                            <option value="">Selecciona una opción</option>
                                            @foreach ($campo['opciones'] as $valor => $texto)
                                                <option value="{{ $valor }}">{{ $texto }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('numero')
                                        <input id="{{ $id }}" name="{{ $campo['nombre'] }}"
                                            type="number" step="0.01"
                                            min="{{ $campo['min'] }}" max="{{ $campo['max'] }}"
                                            @required($campo['obligatorio'])>
                                        @break

                                    @case('fecha')
                                        <input id="{{ $id }}" name="{{ $campo['nombre'] }}"
                                            type="date" data-hasta-hoy
                                            @required($campo['obligatorio'])>
                                        @break

                                    @default
                                        <input id="{{ $id }}" name="{{ $campo['nombre'] }}"
                                            type="text" maxlength="255"
                                            @required($campo['obligatorio'])>
                                @endswitch

                                @isset($campo['ayuda'])
                                    <p class="ayuda">{{ $campo['ayuda'] }}</p>
                                @endisset
                            </div>
                        @endforeach
                    </div>

                    <p class="ayuda">
                        Los datos académicos son declarados por ti.
                        La asistente los verificará durante la revisión.
                    </p>

                    <div class="acciones">
                        <button id="volver-datos" class="secundario" type="button">
                            ← Volver a tus datos
                        </button>

                        <button id="continuar-respaldos" class="principal"
                            type="button">
                            Continuar a respaldos →
                        </button>
                    </div>
                </section>

                <section id="paso-3" class="panel"
                    aria-labelledby="titulo-respaldos" hidden>

                    <h2 id="titulo-respaldos" tabindex="-1">Tus respaldos</h2>

                    <p class="introduccion">
                        Adjunta los documentos que respaldan tu solicitud
                        e indica a qué requisito corresponde cada uno.
                    </p>

                    <h3>Documentos de este trámite</h3>
                    <ul id="requisitos-respaldo" class="requisitos-respaldo"></ul>

                    <div class="carga">
                        <x-subir-archivo id="archivos" name="archivo"
                            accept="pdf,jpg,png" max="10240" multiple
                            :vista-previa="false" />
                    </div>

                    <p id="estado-archivos" class="seleccion" role="status"></p>
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
                        Tus archivos se guardan de forma temporal mientras
                        completas el formulario. Quedarán unidos a tu
                        solicitud cuando el guardado esté disponible.
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
                        La pre-validación de elegibilidad todavía no está
                        conectada con el motor de reglas. La asistente
                        revisará tu caso.
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
                            {{ $codigoTramite === 'recuperacion' ? 'Descargar documento Word' : 'Confirmar solicitud' }}
                        </button>
                    </div>

                    <p id="mensaje-confirmacion" class="seleccion"
                        role="status"></p>

                    <section id="bloque-firmado" class="bloque-firmado"
                        aria-labelledby="titulo-firmado" hidden>

                        <h3 id="titulo-firmado">Sube tu documento firmado</h3>

                        <ol class="pasos-firma">
                            <li>Imprime el documento oficial y fírmalo.</li>
                            <li>Escanéalo completo en un solo archivo PDF.</li>
                            <li>Súbelo aquí. Si subes otro, reemplaza al anterior.</li>
                        </ol>

                        <div class="carga">
                            <x-subir-archivo id="firmado" name="firmado"
                                accept="pdf" max="10240" etiqueta="PDF firmado"
                                :vista-previa="false" />
                        </div>

                        <p id="estado-firmado" class="seleccion" role="status"></p>
                        <p id="error-firmado" class="error" role="alert"></p>

                        <div id="firmado-subido" class="firmado-subido" hidden>
                            <div>
                                <span class="archivo-nombre"></span>
                                <span class="archivo-tamano"></span>
                            </div>

                            <div class="acciones-archivo">
                                <button id="ver-firmado" class="secundario" type="button">
                                    Ver
                                </button>

                                <button id="quitar-firmado" class="secundario" type="button">
                                    Quitar
                                </button>
                            </div>
                        </div>

                        <p id="recibido-firmado" class="ayuda" hidden>
                            Recibimos tu PDF firmado. En esta demostración
                            la solicitud todavía no se envía a revisión.
                        </p>
                    </section>
                </section>
            </div>

            <aside class="panel">
                <span class="etiqueta">TENLO PRESENTE</span>

                <h2>Primero el documento. Después, tu firma.</h2>

                <p>
                    Al completar el formulario podrás descargar
                    el documento oficial.
                </p>

                <p>
                    Tu solicitud se enviará a revisión cuando subas
                    el PDF firmado y los respaldos requeridos en este portal.
                </p>

                <a href="{{ route($tramite['ficha']) }}">
                    Ver requisitos de este trámite →
                </a>
            </aside>
        </div>

        <dialog id="visor-respaldo" aria-label="Vista previa del archivo">
            <div id="contenido-visor"></div>

            <div class="acciones">
                <button id="cerrar-visor" class="secundario" type="button">
                    Cerrar
                </button>
            </div>
        </dialog>
    </main>

    <script>
        // Definición del trámite y materias de prueba (TramitesSimulados).
        const tramite = @json($tramite);
        const materias = @json($materias);

        // Borrador (RF-2.12): lo escrito se guarda en la sesión, uno por trámite.
        const borrador = @json($borrador);
        const urlBorrador = @json(route('borradores-temporales.update', $codigoTramite));
        const estadoBorrador = document.getElementById('estado-borrador');
        const botonDescartar = document.getElementById('descartar-borrador');
        let pasoActual = 1;
        let temporizadorBorrador = null;

        // Las peticiones que escriben en la sesión van de una en una:
        // dos a la vez se pisarían lo guardado.
        let colaSesion = Promise.resolve();

        function enCola(peticion) {
            const resultado = colaSesion.then(peticion);
            colaSesion = resultado.catch(() => null);

            return resultado;
        }

        const formularioDatos = document.getElementById('formulario-datos');
        const pasos = document.querySelectorAll('.pasos li');

        // Fecha local: evita permitir fechas en el futuro.
        const hoy = new Date();
        document.querySelectorAll('[data-hasta-hoy]').forEach(campo => {
            campo.max = [
                hoy.getFullYear(),
                String(hoy.getMonth() + 1).padStart(2, '0'),
                String(hoy.getDate()).padStart(2, '0')
            ].join('-');
        });

        const titulosPaso = [
            'titulo-datos', 'titulo-tramite', 'titulo-respaldos', 'titulo-revisar'
        ];

        function mostrarPaso(numero) {
            const cambio = numero !== pasoActual;
            pasoActual = numero;

            [1, 2, 3, 4].forEach(paso => {
                document.getElementById(`paso-${paso}`).hidden = numero !== paso;
            });

            pasos.forEach((paso, indice) => {
                const activo = indice === numero - 1;
                paso.classList.toggle('activo', activo);

                if (activo) {
                    paso.setAttribute('aria-current', 'step');
                } else {
                    paso.removeAttribute('aria-current');
                }
            });

            document.getElementById(titulosPaso[numero - 1]).focus();

            if (cambio) programarGuardado();
        }

        formularioDatos.addEventListener('submit', evento => {
            evento.preventDefault();

            const celular = document.getElementById('celular');
            celular.value = celular.value.trim();

            if (formularioDatos.reportValidity()) {
                mostrarPaso(2);
            }
        });

        document.getElementById('volver-datos')
            .addEventListener('click', () => mostrarPaso(1));

        // Paso 2: materias elegidas, cada una con sus datos declarados.
        const elegidas = [];
        const contenedorMaterias = document.getElementById('materias-elegidas');
        const errorMaterias = document.getElementById('error-materias');

        function crearCampo(definicion, id, nombre) {
            const campo = document.createElement('div');
            campo.className = 'campo';

            const etiqueta = document.createElement('label');
            etiqueta.htmlFor = id;
            etiqueta.textContent = definicion.etiqueta +
                (definicion.obligatorio ? '' : ' (opcional)');

            let control;

            if (definicion.tipo === 'lista') {
                control = document.createElement('select');
                control.append(new Option('Selecciona una opción', ''));
                Object.entries(definicion.opciones).forEach(([valor, texto]) => {
                    control.append(new Option(texto, valor));
                });
            } else if (definicion.tipo === 'texto_largo') {
                control = document.createElement('textarea');
                control.rows = 3;
                control.maxLength = 2000;
            } else {
                control = document.createElement('input');
                control.type = definicion.tipo === 'numero' ? 'number' : 'text';

                if (definicion.tipo === 'numero') {
                    control.min = definicion.min;
                    control.max = definicion.max;
                    control.step = '0.01';
                }
            }

            control.id = id;
            control.name = nombre;
            control.required = definicion.obligatorio;
            campo.append(etiqueta, control);

            return campo;
        }

        function pintarMaterias() {
            contenedorMaterias.innerHTML = '';

            elegidas.forEach((materia, indice) => {
                const tarjeta = document.createElement('section');
                tarjeta.className = 'detalle-materia';

                const encabezado = document.createElement('div');
                encabezado.className = 'encabezado-resumen';

                const titulo = document.createElement('h3');
                titulo.textContent = `${materia.codigo} — ${materia.nombre}`;

                const quitar = document.createElement('button');
                quitar.type = 'button';
                quitar.className = 'enlace';
                quitar.textContent = 'Quitar';
                quitar.setAttribute('aria-label', `Quitar ${materia.nombre}`);
                quitar.addEventListener('click', () => {
                    elegidas.splice(indice, 1);
                    pintarMaterias();
                    programarGuardado();
                    document.getElementById('materia').focus();
                });

                encabezado.append(titulo, quitar);

                const datos = document.createElement('p');
                datos.className = 'ayuda';
                datos.textContent =
                    `${materia.periodo} · Paralelo ${materia.paralelo} · ${materia.docente}`;

                tarjeta.append(encabezado, datos);

                if (tramite.campos_materia.length) {
                    const campos = document.createElement('div');
                    campos.className = 'campos';

                    tramite.campos_materia.forEach(definicion => {
                        const campo = crearCampo(
                            definicion,
                            `materia-${indice}-${definicion.nombre}`,
                            `materias[${indice}][${definicion.nombre}]`
                        );
                        const control = campo.querySelector('input, select, textarea');

                        control.value = materia.datos[definicion.nombre] ?? '';
                        control.addEventListener('input', () => {
                            materia.datos[definicion.nombre] = control.value;
                        });

                        campos.append(campo);
                    });

                    tarjeta.append(campos);
                }

                contenedorMaterias.append(tarjeta);
            });

            const lleno = elegidas.length >= tramite.max_materias;
            document.getElementById('materia').disabled = lleno;
            document.getElementById('agregar-materia').disabled = lleno;
        }

        if (tramite.max_materias > 0) {
            document.getElementById('agregar-materia')
                .addEventListener('click', () => {
                    const selector = document.getElementById('materia');
                    const materia = materias.find(item => item.id === selector.value);

                    if (!materia) {
                        errorMaterias.textContent = 'Selecciona una materia para agregarla.';
                        return;
                    }

                    if (elegidas.some(item => item.id === materia.id)) {
                        errorMaterias.textContent = 'Esa materia ya está en tu solicitud.';
                        return;
                    }

                    elegidas.push({ ...materia, datos: {} });
                    selector.value = '';
                    errorMaterias.textContent = '';
                    pintarMaterias();
                    programarGuardado();
                });
        }

        // Campos que solo se muestran según la respuesta de otro campo.
        function actualizarCondicionales() {
            document.querySelectorAll('[data-mostrar-si]').forEach(campo => {
                const origen = document.getElementById(campo.dataset.mostrarSi);
                const visible = origen.value === campo.dataset.valor;

                campo.hidden = !visible;

                campo.querySelectorAll('input, select, textarea').forEach(control => {
                    control.disabled = !visible;

                    if (!visible) {
                        control.value = '';
                    }
                });
            });
        }

        document.getElementById('campos-tramite')
            .addEventListener('change', actualizarCondicionales);

        actualizarCondicionales();

        // Paso 2 → 3: valida materias y campos visibles del trámite.
        document.getElementById('continuar-respaldos')
            .addEventListener('click', () => {
                if (tramite.max_materias > 0 && elegidas.length === 0) {
                    errorMaterias.textContent = 'Agrega al menos una materia.';
                    document.getElementById('materia').focus();
                    return;
                }

                const campos = document.querySelectorAll(
                    '#paso-2 input, #paso-2 select, #paso-2 textarea'
                );

                for (const campo of campos) {
                    if (campo.id !== 'materia' && !campo.disabled && !campo.checkValidity()) {
                        campo.reportValidity();
                        return;
                    }
                }

                mostrarPaso(3);
            });

        document.getElementById('volver-tramite')
            .addEventListener('click', () => mostrarPaso(2));

        // Paso 3: respaldos que pide este trámite.
        const requisitosRespaldo = tramite.documentos;

        const urlRespaldos = @json(route('respaldos-temporales.store'));
        const cabecerasRespaldos = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token())
        };
        const TIPOS_RESPALDO = {
            'application/pdf': 'PDF', 'image/jpeg': 'JPG', 'image/png': 'PNG'
        };

        // Lo que ya está en el servidor, en la carpeta temporal de esta sesión.
        const archivos = @json($respaldosSubidos)
            .map(respaldo => ({
                ...respaldo,
                requisito: borrador?.requisitos[respaldo.id] ?? ''
            }));

        const entradaArchivos = document.getElementById('archivos');
        const listaArchivos = document.getElementById('lista-archivos');
        const errorArchivos = document.getElementById('error-archivos');
        const estadoArchivos = document.getElementById('estado-archivos');
        const visorRespaldo = document.getElementById('visor-respaldo');
        const contenidoVisor = document.getElementById('contenido-visor');

        function formatearTamano(bytes) {
            return bytes < 1024 * 1024
                ? `${Math.max(1, Math.round(bytes / 1024))} KB`
                : `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
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
                    ? 'Pendiente'
                    : `${cantidad} ${cantidad === 1 ? 'archivo' : 'archivos'}`;

                fila.append(nombre, estado);
                lista.append(fila);
            });
        }

        async function abrirVisor(url) {
            const respuesta = await fetch(url, {
                headers: { 'Accept': 'text/html' }
            }).catch(() => null);

            if (!respuesta?.ok) return false;

            contenidoVisor.innerHTML = await respuesta.text();
            visorRespaldo.showModal();

            return true;
        }

        async function verRespaldo(item) {
            if (!await abrirVisor(`${urlRespaldos}/${item.id}`)) {
                errorArchivos.textContent =
                    `No se pudo abrir ${item.nombre_original}. Inténtalo de nuevo.`;
            }
        }

        async function quitarRespaldo(item) {
            const respuesta = await enCola(() => fetch(`${urlRespaldos}/${item.id}`, {
                method: 'DELETE',
                headers: cabecerasRespaldos
            }).catch(() => null));

            if (!respuesta?.ok) {
                errorArchivos.textContent =
                    `No se pudo quitar ${item.nombre_original}. Inténtalo de nuevo.`;
                return false;
            }

            archivos.splice(archivos.indexOf(item), 1);
            errorArchivos.textContent = '';
            programarGuardado();
            return true;
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
                nombre.textContent = item.nombre_original;
                tamano.className = 'archivo-tamano';
                tamano.textContent =
                    `${TIPOS_RESPALDO[item.mime] ?? 'Archivo'} · ${formatearTamano(item.tamano_bytes)}`;
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
                    errorArchivos.textContent = '';
                    pintarRequisitos();
                });
                campo.append(etiqueta, selector);

                const ver = document.createElement('button');
                ver.type = 'button';
                ver.className = 'secundario';
                ver.textContent = 'Ver';
                ver.setAttribute('aria-label', `Ver ${item.nombre_original}`);
                ver.addEventListener('click', () => verRespaldo(item));

                const quitar = document.createElement('button');
                quitar.type = 'button';
                quitar.className = 'secundario';
                quitar.textContent = 'Quitar';
                quitar.setAttribute('aria-label', `Quitar ${item.nombre_original}`);
                quitar.addEventListener('click', async () => {
                    quitar.disabled = true;

                    if (await quitarRespaldo(item)) {
                        pintarArchivos();
                        entradaArchivos.focus();
                    } else {
                        quitar.disabled = false;
                    }
                });

                const acciones = document.createElement('div');
                acciones.className = 'acciones-archivo';
                acciones.append(ver, quitar);

                fila.append(datos, campo, acciones);
                listaArchivos.append(fila);
            });

            pintarRequisitos();
        }

        async function subirRespaldo(archivo) {
            const datos = new FormData();
            datos.append('tramite', @json($codigoTramite));
            datos.append('archivo', archivo);

            const respuesta = await enCola(() => fetch(urlRespaldos, {
                method: 'POST',
                headers: cabecerasRespaldos,
                body: datos
            }).catch(() => null));

            if (respuesta?.ok) return respuesta.json();

            throw new Error(await motivoRechazo(respuesta));
        }

        async function motivoRechazo(respuesta) {
            const motivos = {
                413: 'Supera el tamaño que acepta el servidor.',
                419: 'Tu sesión expiró. Recarga la página.',
                429: 'Subiste demasiados archivos seguidos. Espera un minuto.'
            };
            const error = await respuesta?.json().catch(() => null);

            return motivos[respuesta?.status] ??
                error?.errors?.archivo?.[0] ??
                'No se pudo subir. Inténtalo de nuevo.';
        }

        // El componente subir-archivo ya descartó lo que no es PDF, JPG o PNG de
        // hasta 10 MB; el servidor lo vuelve a comprobar al guardar.
        document.getElementById('paso-3')
            .addEventListener('subir-archivo:cambio', async evento => {
                const nuevos = evento.detail.archivos.filter(archivo =>
                    !archivos.some(item =>
                        item.nombre_original === archivo.name &&
                        item.tamano_bytes === archivo.size
                    )
                );
                const fallidos = [];

                errorArchivos.textContent = '';
                entradaArchivos.disabled = true;

                for (const [indice, archivo] of nuevos.entries()) {
                    estadoArchivos.textContent =
                        `Subiendo ${indice + 1} de ${nuevos.length}: ${archivo.name}…`;

                    try {
                        const subido = await subirRespaldo(archivo);

                        // Si solo hay un requisito pendiente obligatorio, se sugiere ese.
                        const pendiente = requisitosRespaldo.find(requisito =>
                            requisito.obligatorio &&
                            !archivos.some(item => item.requisito === requisito.id)
                        );

                        archivos.push({
                            ...subido,
                            requisito: pendiente ? pendiente.id : ''
                        });
                        pintarArchivos();
                    } catch (error) {
                        fallidos.push(`${archivo.name}: ${error.message}`);
                    }
                }

                entradaArchivos.value = '';
                entradaArchivos.disabled = false;
                estadoArchivos.textContent = '';
                errorArchivos.textContent = fallidos.join(' ');
                programarGuardado();
            });

        document.getElementById('cerrar-visor')
            .addEventListener('click', () => visorRespaldo.close());

        // Al cerrar se vacía, para que el PDF no siga cargado detrás.
        visorRespaldo.addEventListener('close', () => {
            contenidoVisor.innerHTML = '';
        });

        document.getElementById('continuar-revisar')
            .addEventListener('click', () => {
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
                ['Carrera', textoDe('carrera')],
                ['Correo institucional', textoDe('correo')],
                ['Celular', textoDe('celular')]
            ]);

            const filasTramite = [];

            elegidas.forEach((materia, indice) => {
                filasTramite.push([
                    elegidas.length === 1 ? 'Materia' : `Materia ${indice + 1}`,
                    `${materia.codigo} — ${materia.nombre}`
                ]);

                tramite.campos_materia.forEach(definicion => {
                    filasTramite.push([
                        `${definicion.etiqueta} (${materia.codigo})`,
                        textoDe(`materia-${indice}-${definicion.nombre}`)
                    ]);
                });
            });

            tramite.campos.forEach(definicion => {
                const id = `campo-${definicion.nombre}`;

                if (!document.getElementById(id).disabled) {
                    filasTramite.push([definicion.etiqueta, textoDe(id)]);
                }
            });

            pintarLista('lista-tramite', filasTramite);

            const listaRespaldos = document.getElementById('lista-respaldos');
            listaRespaldos.innerHTML = '';

            archivos.forEach(item => {
                const requisito = requisitosRespaldo.find(
                    r => r.id === item.requisito
                );
                const fila = document.createElement('li');
                fila.textContent = `${item.nombre_original} — ${requisito.nombre}`;
                listaRespaldos.append(fila);
            });

            document.getElementById('mensaje-confirmacion').textContent = '';
        }

        document.querySelectorAll('[data-ir-a]').forEach(boton => {
            boton.addEventListener('click', () => {
                mostrarPaso(Number(boton.dataset.irA));
            });
        });

        document.getElementById('volver-respaldos')
            .addEventListener('click', () => mostrarPaso(3));

        document.getElementById('confirmar-solicitud')
            .addEventListener('click', async () => {
                const declaracion = document.getElementById('declaracion');

                if (!declaracion.reportValidity()) return;

                bloqueFirmado.hidden = false;

                if (@json($codigoTramite) === 'recuperacion') {
                    const boton = document.getElementById('confirmar-solicitud');
                    const mensaje = document.getElementById('mensaje-confirmacion');
                    boton.disabled = true;
                    mensaje.textContent = 'Preparando el documento…';
                    try {
                        const respuesta = await fetch(@json(route('solicitudes.documento')), {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token())},
                            body: JSON.stringify({tramite: 'recuperacion', nombre: textoDe('nombre'), codigo: textoDe('codigo'), carrera: textoDe('carrera'), correo: textoDe('correo'), celular: textoDe('celular'), materia_id: elegidas[0]?.id})
                        });
                        if (!respuesta.ok) {
                            const error = await respuesta.json();
                            throw new Error(error.message || 'No se pudo generar el documento.');
                        }
                        const url = URL.createObjectURL(await respuesta.blob());
                        const enlace = document.createElement('a');
                        enlace.href = url;
                        enlace.download = 'solicitud-recuperacion.docx';
                        enlace.click();
                        setTimeout(() => URL.revokeObjectURL(url), 60000);
                        mensaje.textContent = 'Word descargado con datos de demostración. Ábrelo en Microsoft Word y usa Archivo → Exportar → Crear PDF. Imprímelo, fírmalo y sube abajo el PDF firmado. La solicitud todavía no se guarda ni se envía.';
                    } catch (error) {
                        mensaje.textContent = error.message;
                    } finally {
                        boton.disabled = false;
                    }
                    return;
                }

                document.getElementById('mensaje-confirmacion').textContent =
                    'Vista de demostración: la solicitud no se guardó. ' +
                    'Cuando el sistema esté conectado, aquí se generará ' +
                    'tu código de solicitud y el documento oficial para firmar. ' +
                    'Mientras tanto puedes probar abajo la subida del PDF firmado.';
            });

        // PDF firmado (RF-2.11): uno por trámite, en la carpeta temporal de la sesión.
        const urlFirmado = @json(route('firmados-temporales.store', $codigoTramite));
        const bloqueFirmado = document.getElementById('bloque-firmado');
        const entradaFirmado = document.getElementById('firmado');
        const estadoFirmado = document.getElementById('estado-firmado');
        const errorFirmado = document.getElementById('error-firmado');
        let firmado = @json($firmado);

        function pintarFirmado() {
            const fila = document.getElementById('firmado-subido');

            fila.hidden = firmado === null;
            document.getElementById('recibido-firmado').hidden = firmado === null;

            if (firmado) {
                fila.querySelector('.archivo-nombre').textContent = firmado.nombre_original;
                fila.querySelector('.archivo-tamano').textContent =
                    `PDF · ${formatearTamano(firmado.tamano_bytes)}`;
            }
        }

        // El componente subir-archivo ya descartó lo que no es un PDF de hasta
        // 10 MB; el servidor lo vuelve a comprobar al guardar.
        bloqueFirmado.addEventListener('subir-archivo:cambio', async evento => {
            const archivo = evento.detail.archivos[0];

            if (!archivo) return;

            const datos = new FormData();
            datos.append('archivo', archivo);

            errorFirmado.textContent = '';
            estadoFirmado.textContent = `Subiendo ${archivo.name}…`;
            entradaFirmado.disabled = true;

            const respuesta = await enCola(() => fetch(urlFirmado, {
                method: 'POST',
                headers: cabecerasRespaldos,
                body: datos
            }).catch(() => null));

            entradaFirmado.value = '';
            entradaFirmado.disabled = false;
            estadoFirmado.textContent = '';

            if (!respuesta?.ok) {
                errorFirmado.textContent = `${archivo.name}: ${await motivoRechazo(respuesta)}`;
                return;
            }

            firmado = await respuesta.json();
            pintarFirmado();
        });

        document.getElementById('ver-firmado')
            .addEventListener('click', async () => {
                errorFirmado.textContent = await abrirVisor(urlFirmado)
                    ? ''
                    : 'No se pudo abrir el PDF firmado. Inténtalo de nuevo.';
            });

        document.getElementById('quitar-firmado')
            .addEventListener('click', async () => {
                const respuesta = await enCola(() => fetch(urlFirmado, {
                    method: 'DELETE',
                    headers: cabecerasRespaldos
                }).catch(() => null));

                if (!respuesta?.ok) {
                    errorFirmado.textContent = 'No se pudo quitar el PDF firmado. Inténtalo de nuevo.';
                    return;
                }

                firmado = null;
                errorFirmado.textContent = '';
                pintarFirmado();
                entradaFirmado.focus();
            });

        // Si ya había uno subido, el bloque se muestra sin volver a confirmar.
        bloqueFirmado.hidden = firmado === null;
        pintarFirmado();

        // Borrador: guardar, descartar y retomar.
        function cuandoSeGuardo(iso) {
            const fecha = new Date(iso);
            const hora = fecha.toLocaleTimeString('es-EC', { hour: '2-digit', minute: '2-digit', hour12: false });

            return fecha.toDateString() === new Date().toDateString()
                ? `hoy a las ${hora}`
                : `el ${fecha.toLocaleDateString('es-EC')} a las ${hora}`;
        }

        // Las casillas de confirmación no se guardan: se marcan en cada visita.
        function datosBorrador() {
            const campos = {};
            const requisitos = {};

            tramite.campos.forEach(definicion => {
                campos[definicion.nombre] =
                    document.getElementById(`campo-${definicion.nombre}`).value;
            });

            archivos.forEach(item => {
                requisitos[item.id] = item.requisito;
            });

            return {
                paso: pasoActual,
                celular: document.getElementById('celular').value,
                materias: elegidas.map(materia => ({ id: materia.id, datos: materia.datos })),
                campos,
                requisitos
            };
        }

        function enviarBorrador(opciones = {}) {
            return fetch(urlBorrador, {
                method: 'PUT',
                headers: { ...cabecerasRespaldos, 'Content-Type': 'application/json' },
                body: JSON.stringify(datosBorrador()),
                ...opciones
            }).catch(() => null);
        }

        function cancelarGuardado() {
            clearTimeout(temporizadorBorrador);
            temporizadorBorrador = null;
        }

        function programarGuardado() {
            cancelarGuardado();
            temporizadorBorrador = setTimeout(guardarBorrador, 1500);
        }

        async function guardarBorrador() {
            cancelarGuardado();

            const respuesta = await enCola(() => enviarBorrador());

            if (respuesta?.status === 422) {
                const detalle = await respuesta.json().catch(() => null);
                const errores = Object.values(detalle?.errors ?? {}).flat();

                const mensaje = errores[0]
                    || detalle?.message
                    || 'Revisa los datos ingresados.';

                estadoBorrador.textContent =
                    `No se guardaron los últimos cambios: ${mensaje}`;

                return;
            }

            if (!respuesta?.ok) {
                estadoBorrador.textContent = respuesta?.status === 419
                    ? 'Tu sesión expiró y el borrador no se guardó. Recarga la página.'
                    : 'No se pudo guardar el borrador. Se intentará de nuevo con tu próximo cambio.';
                return;
            }

            const guardado = await respuesta.json();
            estadoBorrador.textContent = `Borrador guardado ${cuandoSeGuardo(guardado.guardado_at)}.`;
            botonDescartar.hidden = false;
        }

        const contenido = document.querySelector('.distribucion');
        contenido.addEventListener('input', programarGuardado);
        contenido.addEventListener('change', programarGuardado);

        document.getElementById('guardar-borrador')
            .addEventListener('click', guardarBorrador);

        // Si quedó un cambio sin guardar al salir de la página, se envía en ese momento.
        window.addEventListener('pagehide', () => {
            if (temporizadorBorrador !== null) {
                enviarBorrador({ keepalive: true });
            }
        });

        botonDescartar.addEventListener('click', async () => {
            if (!confirm('¿Descartar este borrador? Se borrará lo que escribiste y los archivos que adjuntaste.')) {
                return;
            }

            cancelarGuardado();

            const respuesta = await enCola(() => fetch(urlBorrador, {
                method: 'DELETE',
                headers: cabecerasRespaldos
            }).catch(() => null));

            if (!respuesta?.ok) {
                estadoBorrador.textContent = 'No se pudo descartar el borrador. Inténtalo de nuevo.';
                return;
            }

            cancelarGuardado();
            formularioDatos.reset();
            document.getElementById('declaracion').checked = false;
            document.querySelectorAll(
                '#campos-tramite input, #campos-tramite select, #campos-tramite textarea'
            ).forEach(control => {
                control.value = '';
            });
            actualizarCondicionales();

            elegidas.length = 0;
            archivos.length = 0;

            if (tramite.max_materias > 0) {
                errorMaterias.textContent = '';
                pintarMaterias();
            }

            errorArchivos.textContent = '';
            pintarArchivos();

            firmado = null;
            errorFirmado.textContent = '';
            bloqueFirmado.hidden = true;
            pintarFirmado();

            pasoActual = 1;
            mostrarPaso(1);
            botonDescartar.hidden = true;
            estadoBorrador.textContent = 'Borrador descartado.';
        });

        if (borrador) {
            document.getElementById('celular').value = borrador.celular ?? '';
            document.getElementById('confirmar-datos').checked = borrador.paso > 1;

            tramite.campos.forEach(definicion => {
                document.getElementById(`campo-${definicion.nombre}`).value =
                    borrador.campos[definicion.nombre] ?? '';
            });
            actualizarCondicionales();

            borrador.materias.forEach(guardada => {
                const materia = materias.find(item => item.id === guardada.id);

                if (materia) {
                    elegidas.push({ ...materia, datos: { ...guardada.datos } });
                }
            });

            if (tramite.max_materias > 0) {
                pintarMaterias();
            }

            // El paso 4 se vuelve a armar al pasar por la validación del paso 3.
            pasoActual = Math.min(borrador.paso, 3);

            if (pasoActual > 1) {
                mostrarPaso(pasoActual);
            }

            estadoBorrador.textContent =
                `Recuperamos tu borrador, guardado ${cuandoSeGuardo(borrador.guardado_at)}.`;
            botonDescartar.hidden = false;
        }

        pintarArchivos();
    </script>
</body>
</html>
