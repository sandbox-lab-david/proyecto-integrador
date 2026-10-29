@extends('estudiante.layouts.marco')
@section('titulo', 'Formulario de solicitud')

@section('content')
<div class="portal-estudiante">
    @include('estudiante.partials.estilo-layout')

    <style>
        @scope (.portal-estudiante) {
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
            flex-wrap: wrap;
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
        .requisitos-respaldo li { flex-wrap: wrap; align-items: center; }
        .requisitos-respaldo li > span:first-child { flex: 1 1 220px; }
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

        .bloque-resumen > .ayuda { margin: 0; font-size: 14px; }
        .resultado-prevalidacion p { margin: 12px 0 0; font-size: 14px; }
        .resultado-prevalidacion .aviso { margin: 12px 0 0; }
        .resultado-prevalidacion button { margin-top: 12px; }

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

        .titulo-materias {
            margin: 0 0 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e3e7ee;
            font-size: 16px;
            font-weight: bold;
            color: #202939;
        }
        .filtro-materias { margin-bottom: 20px; }
        .agregar-materia { display: flex; flex-wrap: wrap; gap: 12px; }
        .agregar-materia select { flex: 1 1 240px; }
        .agregar-materia + .ayuda { margin-top: 12px; }
        .agregar-materia button { flex-shrink: 0; }
        .detalle-materia .encabezado-resumen { flex-wrap: wrap; row-gap: 4px; margin-bottom: 4px; }
        .detalle-materia .encabezado-resumen h3 { margin: 0; }
        .detalle-materia .campos { margin-top: 16px; }
        .acciones-materia { display: flex; gap: 16px; flex-shrink: 0; }
        .resumen-materia { margin: 4px 0 0; color: #596579; font-size: 14px; }

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
        }
    </style>
    <main>
        <a href="{{ route($tramite['ficha']) }}">
            ← Volver a los requisitos
        </a>

        <h1>{{ ($solicitudCorreccion ?? null) ? 'Corregir solicitud '.$solicitudCorreccion->codigo : 'Solicitud de '.$tramite['nombre'] }}</h1>
        @if ($solicitudCorreccion ?? null)
            <p class="introduccion">Revisa las observaciones, corrige los datos y vuelve a adjuntar los respaldos requeridos. Descarga el documento actualizado y sube su nuevo PDF firmado. Las versiones anteriores se conservan.</p>
            @if (request()->attributes->get('correccion_desactualizada'))
                <p class="aviso" role="alert">La solicitud cambió desde que empezaste esta corrección. Conservamos tu borrador, pero deberás descartarlo y volver a abrir la solicitud antes de reenviar.</p>
            @endif
            <section class="panel" aria-label="Observaciones de revisión">
                <h2>Observaciones de la facultad</h2>
                @if ($observacionesCorreccion === null)
                    <p>La consulta de observaciones públicas todavía no está integrada con el módulo de revisión.</p>
                @else
                    @forelse ($observacionesCorreccion as $observacionCorreccion)
                        <p>{{ $observacionCorreccion['texto'] }}</p>
                    @empty
                        <p>No hay observaciones públicas registradas.</p>
                    @endforelse
                @endif
            </section>
        @endif

        <p class="introduccion">
            Confirma tus datos y completa la información de tu solicitud.
        </p>

        <div class="introduccion">
            Revisa tus datos, completa los que falten y selecciona las materias del catálogo académico.
            La solicitud todavía no se envía.
            @if ($solicitudCorreccion)
                Los cambios de la corrección y sus archivos se conservan en este
                navegador mientras tu sesión siga abierta.
            @else
                Lo que escribas, los archivos que adjuntes y el documento firmado
                se guardan como borrador en tu cuenta, y puedes retomarlo desde
                «Mis solicitudes».
            @endif
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
                        Los datos disponibles en tu perfil se completan para que los revises y puedas corregirlos.
                        @if ($datosCuenta)
                            El nombre y el correo disponibles en tu cuenta se completan automáticamente.
                        @endif
                        @if ($modalidadesMatricula)
                            La facultad, la carrera y la modalidad corresponden a tus inscripciones activas.
                        @endif
                        Revisa que la información sea correcta antes de continuar.
                    </p>

                    <form id="formulario-datos">
                        @php($camposBloqueados = array_keys($datosCuenta + $datosFijos + ($modalidadesMatricula ? ['modalidad' => true] : [])))
                        <div class="campos">
                            @foreach ($camposPersonales as $campoPersonal => [$etiquetaPersonal, $limitePersonal])
                                <div @class(['campo', 'completo' => $campoPersonal === 'nombre'])>
                                    <label for="{{ $campoPersonal }}">{{ $etiquetaPersonal }}</label>
                                    @if ($campoPersonal === 'facultad')
                                        <select id="facultad" name="facultad" required>
                                            <option value="">Selecciona tu facultad</option>
                                            @foreach ($catalogoFacultades as $nombreFacultad => $carrerasFacultad)
                                                <option value="{{ $nombreFacultad }}" @selected(($datosIniciales['facultad'] ?? '') === $nombreFacultad)>{{ $nombreFacultad }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($campoPersonal === 'carrera')
                                        <select id="carrera" name="carrera" required disabled aria-describedby="ayuda-carrera">
                                            <option value="">Primero selecciona tu facultad</option>
                                        </select>
                                        <p id="ayuda-carrera" class="ayuda">
                                            {{ $modalidadesMatricula ? 'Solo se muestran las carreras en las que tienes una inscripción activa.' : 'Solo se muestran las carreras de la facultad elegida.' }}
                                        </p>
                                    @else
                                    <input id="{{ $campoPersonal }}" name="{{ $campoPersonal }}"
                                        type="{{ $campoPersonal === 'correo' ? 'email' : ($campoPersonal === 'celular' ? 'tel' : 'text') }}"
                                        value="{{ $datosCuenta[$campoPersonal] ?? $datosIniciales[$campoPersonal] ?? '' }}"
                                        required maxlength="{{ $limitePersonal }}"
                                        @readonly(in_array($campoPersonal, $camposBloqueados, true))
                                        @if ($campoPersonal === 'modalidad' && $modalidadesMatricula) aria-describedby="ayuda-modalidad" @endif
                                        @if ($campoPersonal === 'periodo' && isset($datosFijos['periodo'])) aria-describedby="ayuda-periodo" @endif
                                        @if (in_array($campoPersonal, \App\Services\Solicitudes\DatosFormularioSolicitud::SOLO_DIGITOS, true))
                                            @if ($campoPersonal === 'celular') autocomplete="tel" @endif
                                            inputmode="numeric" pattern="[0-9]+"
                                            title="Ingresa únicamente dígitos del 0 al 9."
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        @elseif ($campoPersonal === 'correo')
                                            autocomplete="email"
                                        @elseif ($campoPersonal === 'nombre')
                                            autocomplete="name"
                                        @endif
                                    >
                                    @if ($campoPersonal === 'modalidad' && $modalidadesMatricula)
                                        <p id="ayuda-modalidad" class="ayuda">Corresponde a tu inscripción en la carrera elegida.</p>
                                    @elseif ($campoPersonal === 'periodo' && isset($datosFijos['periodo']))
                                        <p id="ayuda-periodo" class="ayuda">Periodo académico vigente.</p>
                                    @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <label class="confirmacion" for="confirmar-datos">
                            <input id="confirmar-datos" type="checkbox" required>
                            <span>Confirmo que mis datos son correctos.</span>
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

                    @if (!empty($tramite['aviso']))
                        <div class="aviso">{{ $tramite['aviso'] }}</div>
                    @endif

                    @if ($tramite['max_materias'] > 0)
                        <div class="campos">
                            <div class="campo completo">
                                <p class="titulo-materias">
                                    {{ $tramite['max_materias'] === 1 ? 'Materia de la solicitud' : 'Materias de la solicitud' }}
                                </p>

                                <div class="filtro-materias">
                                    <label for="periodo-materias">Filtrar por período</label>
                                    <select id="periodo-materias" aria-describedby="ayuda-materia"><option value="">Todos los períodos disponibles</option></select>
                                </div>

                                <label for="materia">Materia</label>
                                <div class="agregar-materia">
                                    <select id="materia">
                                        <option value="">Selecciona una materia</option>
                                    </select>
                                    <button id="agregar-materia" class="secundario"
                                        type="button" aria-describedby="ayuda-materia">
                                        Agregar materia
                                    </button>
                                </div>

                                <p id="ayuda-materia" class="ayuda">
                                    {{ $tramite['max_materias'] === 1
                                        ? 'Este trámite admite una sola materia.'
                                        : 'Puedes agregar hasta '.$tramite['max_materias'].' materias.' }}
                                    Los cursos se consultan en la base de datos académica.
                                    @if (empty($materias))
                                        No hay cursos disponibles. {{ app(\App\Services\Solicitudes\MateriasSolicitud::class)->avisoSinCursos() }}
                                    @endif
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
                                            type="number" step="{{ $campo['step'] ?? '0.01' }}"
                                            @isset($campo['min']) min="{{ $campo['min'] }}" @endisset
                                            @isset($campo['max']) max="{{ $campo['max'] }}" @endisset
                                            @required($campo['obligatorio'])>
                                        @break

                                    @case('fecha')
                                        <input id="{{ $id }}" name="{{ $campo['nombre'] }}"
                                            type="date" data-hasta-hoy max="{{ now(\App\Services\Solicitudes\ReglasCampoSolicitud::zonaHoraria())->toDateString() }}"
                                            @required($campo['obligatorio'])>
                                        @break

                                    @case('hora')
                                        <input id="{{ $id }}" name="{{ $campo['nombre'] }}"
                                            type="time" step="60" @required($campo['obligatorio'])>
                                        @break

                                    @default
                                        <input id="{{ $id }}" name="{{ $campo['nombre'] }}"
                                            type="text" maxlength="{{ $campo['max_length'] ?? 255 }}"
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
                    @if (!$requisitosMotor['disponible'])
                        <div class="aviso" role="alert">{{ $requisitosMotor['error'] }}</div>
                    @elseif ($requisitosMotor['aviso'])
                        <div class="aviso">{{ $requisitosMotor['aviso'] }}</div>
                    @endif
                    @if ($requisitosMotor['disponible'] && empty($tramite['documentos']))
                        <p class="ayuda">Prolog no indica documentos de respaldo para este trámite. El PDF firmado se adjunta en el paso de revisión.</p>
                    @endif
                    <ul id="requisitos-respaldo" class="requisitos-respaldo"></ul>
                    @if ($tramite['documentos_orientativos'] ?? false)
                        <p class="ayuda">Las opciones de respaldo son orientativas. Adjunta las que correspondan a tu caso; la facultad confirmará los documentos exigidos.</p>
                    @endif

                    <div class="carga" hidden>
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
                        Tus archivos se guardan con el borrador mientras
                        completas el formulario y quedan unidos a tu solicitud
                        al enviarla.
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

                    <section class="bloque-resumen"
                        aria-labelledby="resumen-prevalidacion">
                        <div class="encabezado-resumen">
                            <h3 id="resumen-prevalidacion">Comprobación previa</h3>
                        </div>
                        <p class="ayuda">
                            Prolog compara los datos que declaraste con las
                            referencias del trámite. Es orientativa: no aprueba
                            ni rechaza tu solicitud y no impide enviarla.
                            La facultad revisa cada caso.
                        </p>
                        <div id="resultado-prevalidacion"
                            class="resultado-prevalidacion" aria-live="polite"></div>
                    </section>

                    @if ($requisitosMotor['aviso'])
                        <div class="aviso">{{ $requisitosMotor['aviso'] }}</div>
                    @endif

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
                            Descargar documento Word
                        </button>
                    </div>

                    <div class="campo">
                        <label for="formato-documento">Formato de descarga</label>
                        <select id="formato-documento">
                            <option value="docx">Word (.docx)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
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
                        <p class="ayuda">Una nueva descarga Word reemplaza la versión
                            anterior y requiere volver a subir el PDF firmado.</p>

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
                            Recibimos tu PDF firmado. Para enviar, confirma que
                            corresponde al documento descargado y a los datos actuales.
                        </p>
                        <button id="enviar-solicitud" class="principal" type="button" disabled>
                            {{ ($solicitudCorreccion ?? null) ? 'Reenviar a revisión' : 'Enviar a revisión' }}
                        </button>
                        @unless ($envioDisponible)
                            <p class="ayuda">Inicia sesión para enviar tu solicitud.</p>
                            <a href="{{ route('login') }}">Iniciar sesión</a>
                        @endunless
                        <p id="mensaje-envio" class="seleccion" role="status"></p>
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
        const idSolicitudCorreccion = @json(($solicitudCorreccion ?? null)?->getKey());
        // Aisla todos los temporales de esta corrección, incluidos visor y borradores.
        function fetchSolicitud(url, opciones) {
            const destino = new URL(url, window.location.href);
            if (idSolicitudCorreccion && destino.origin === window.location.origin) {
                destino.searchParams.set('solicitud_correccion', idSolicitudCorreccion);
            }
            return window.fetch(destino.toString(), opciones);
        }
        // Definición publicada del trámite y cursos de la base académica.
        const tramite = @json($tramite);
        const camposPersonales = @json($camposPersonales);
        const datosIniciales = @json($datosIniciales);
        const camposMateriaManual = @json($camposMateriaManual);
        const materiasCatalogo = @json($materias);
        const selectorCatalogo = document.getElementById('materia');
        const filtroPeriodo = document.getElementById('periodo-materias');
        function llenarSelectorMaterias() {
            if (!selectorCatalogo) return;
            selectorCatalogo.replaceChildren(new Option('Selecciona una materia', ''));
            materiasCatalogo.filter(materia => !filtroPeriodo.value || materia.periodo === filtroPeriodo.value)
                .forEach(materia => selectorCatalogo.append(new Option(materia.codigo + ' — ' + materia.nombre + ' (' + materia.periodo + ', ' + materia.paralelo + ')', materia.id)));
        }
        if (selectorCatalogo) {
            [...new Set(materiasCatalogo.map(materia => materia.periodo))].sort((a,b) => a.localeCompare(b, 'es'))
                .forEach(periodo => filtroPeriodo.append(new Option(periodo, periodo)));
            llenarSelectorMaterias();
            filtroPeriodo.addEventListener('change', () => { llenarSelectorMaterias(); pintarMaterias(); });
        }
        const catalogoFacultades = @json($catalogoFacultades);
        // Carrera => modalidad de cada inscripción activa; vacío si la cuenta no tiene ninguna.
        const modalidadesMatricula = @json((object) $modalidadesMatricula);

        function actualizarModalidad() {
            if (!Object.keys(modalidadesMatricula).length) return;
            document.getElementById('modalidad').value = modalidadesMatricula[document.getElementById('carrera').value] ?? '';
        }

        function actualizarCarreras(valorGuardado = '') {
            const facultad = document.getElementById('facultad');
            const carrera = document.getElementById('carrera');
            const opciones = catalogoFacultades[facultad.value] ?? [];
            carrera.replaceChildren(new Option(opciones.length ? 'Selecciona tu carrera' : 'Primero selecciona tu facultad', ''));
            opciones.forEach(nombre => carrera.append(new Option(nombre, nombre)));
            carrera.disabled = opciones.length === 0;
            if (valorGuardado && !opciones.includes(valorGuardado)) {
                const anterior = new Option(`Revisa la carrera guardada: ${valorGuardado}`, valorGuardado);
                anterior.disabled = true;
                carrera.append(anterior);
            }
            carrera.value = valorGuardado;
            validarFacultadYCarrera();
            actualizarModalidad();
        }

        function validarFacultadYCarrera() {
            const facultad = document.getElementById('facultad');
            const carrera = document.getElementById('carrera');
            facultad.setCustomValidity(facultad.value && !Object.hasOwn(catalogoFacultades, facultad.value)
                ? 'Selecciona una facultad de la lista.' : '');
            carrera.setCustomValidity(carrera.value && !(catalogoFacultades[facultad.value] ?? []).includes(carrera.value)
                ? 'Selecciona una carrera que pertenezca a la facultad elegida.' : '');
        }

        document.getElementById('facultad').addEventListener('change', () => actualizarCarreras());
        document.getElementById('carrera').addEventListener('change', () => { validarFacultadYCarrera(); actualizarModalidad(); });
        actualizarCarreras(datosIniciales.carrera ?? '');
        const cambioSolicitado = @json(\App\Services\Solicitudes\DatosFormularioSolicitud::CAMBIOS[$codigoTramite] ?? null);
        const tramiteActual = @json($codigoTramite);
        const relojFormulario = {
            instante: @json(now()->getTimestampMs()), cargado: Date.now(),
            zona: @json(\App\Services\Solicitudes\ReglasCampoSolicitud::zonaHoraria())
        };

        function fechaHoraActual() {
            // La misma zona horaria que valida el servidor, incluso si el navegador usa otra.
            const partes = new Intl.DateTimeFormat('en-CA', {
                timeZone: relojFormulario.zona, year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
            }).formatToParts(new Date(relojFormulario.instante + Date.now() - relojFormulario.cargado));
            const valor = Object.fromEntries(partes.map(parte => [parte.type, parte.value]));
            return { fecha: `${valor.year}-${valor.month}-${valor.day}`, hora: `${valor.hour}:${valor.minute}` };
        }

        function validarRangosYFechas() {
            const ahora = fechaHoraActual();
            document.querySelectorAll('[data-hasta-hoy]').forEach(control => { control.max = ahora.fecha; });
            if (tramiteActual === 'recalificacion') {
                const fecha = document.getElementById('campo-publicacion_fecha');
                document.getElementById('campo-publicacion_hora').max = fecha.value === ahora.fecha ? ahora.hora : '';
            }

            document.querySelectorAll('#paso-2 input[type="number"], #paso-2 input[type="date"], #paso-2 input[type="time"]')
                .forEach(control => {
                    control.setCustomValidity('');
                    if (control.disabled) return;
                    if (control.validity.badInput) {
                        control.setCustomValidity(control.type === 'number' ? 'Ingresa un número válido.' : 'Ingresa una fecha u hora válida.');
                    } else if (control.validity.rangeUnderflow || control.validity.rangeOverflow) {
                        if (control.type === 'number') {
                            control.setCustomValidity(control.min !== '' && control.max !== ''
                                ? `Ingresa un valor entre ${control.min} y ${control.max}.`
                                : `Ingresa un valor ${control.min !== '' ? `mayor o igual que ${control.min}` : `menor o igual que ${control.max}`}.`);
                        } else {
                            control.setCustomValidity(control.type === 'time'
                                ? 'La hora de publicación no puede estar en el futuro.'
                                : 'La fecha no puede estar en el futuro.');
                        }
                    }
                });

            if (tramiteActual === 'tercer-registro') {
                elegidas.forEach((materia, indice) => {
                    const primero = document.getElementById(`materia-${indice}-registro_1_anio`);
                    const segundo = document.getElementById(`materia-${indice}-registro_2_anio`);
                    if (primero.value !== '' && segundo.value !== '' && primero.checkValidity() && segundo.checkValidity()
                        && Number(segundo.value) < Number(primero.value)) {
                        segundo.setCustomValidity('El año del segundo registro no puede ser anterior al del primero.');
                    }
                });
            }
        }

        function limpiarControl(control) {
            if (!(control instanceof HTMLTextAreaElement ||
                (control instanceof HTMLInputElement && ['text', 'email', 'tel'].includes(control.type))) ||
                control.disabled || control.readOnly) return;

            const limpio = control.value.trim();
            if (limpio !== control.value) {
                control.value = limpio;
                // Mantiene también actualizados los datos de las materias y el borrador.
                control.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        function limpiarTextos() {
            document.querySelectorAll('#paso-1 input, #paso-2 input, #paso-2 textarea')
                .forEach(limpiarControl);
        }

        function validarCambioSolicitado() {
            if (!cambioSolicitado) return;
            const controlDe = ruta => document.getElementById(
                ruta.startsWith('campos.') ? `campo-${ruta.slice(7)}` : ruta);
            const comparar = valor => valor.trim().replace(/\s+/gu, ' ').toLowerCase();
            const destino = controlDe(cambioSolicitado.destino);
            const valorDestino = comparar(destino.value);
            const repetido = valorDestino !== '' && cambioSolicitado.actuales.some(ruta => {
                const valorActual = comparar(controlDe(ruta).value);
                return valorActual !== '' && valorActual === valorDestino;
            });
            destino.setCustomValidity(repetido ? cambioSolicitado.mensaje : '');
        }

        // Borrador (RF-2.12): lo escrito se guarda en la cuenta, uno por trámite
        // (en la sesión si la cuenta todavía no puede guardarlo, o si es una corrección).
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

            limpiarTextos();
            validarCambioSolicitado();
            validarFacultadYCarrera();

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
                    if (definicion.min !== undefined) control.min = definicion.min;
                    if (definicion.max !== undefined) control.max = definicion.max;
                    control.step = definicion.step ?? '0.01';
                } else {
                    control.maxLength = definicion.max_length ?? 255;
                }
            }

            control.id = id;
            control.name = nombre;
            control.required = definicion.obligatorio;
            campo.append(etiqueta, control);

            return campo;
        }

        // Materias cuyos datos el estudiante ocultó; sobrevive a cada repintado.
        const materiasCerradas = new WeakSet();

        // Abre la tarjeta de materia que contiene el control antes de señalar su error.
        function abrirMateriaDe(control) {
            const alternar = control.closest('.detalle-materia')?.querySelector('[data-alternar-materia]');
            if (alternar?.getAttribute('aria-expanded') === 'false') alternar.click();
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

                const cuerpo = document.createElement('div');
                cuerpo.id = `materia-${indice}-datos`;

                const resumen = document.createElement('p');
                resumen.className = 'resumen-materia';
                resumen.textContent = [materia.periodo, materia.paralelo && `Paralelo ${materia.paralelo}`, materia.docente]
                    .filter(Boolean).join(' · ');

                const alternar = document.createElement('button');
                alternar.type = 'button';
                alternar.className = 'enlace';
                alternar.dataset.alternarMateria = '';
                alternar.setAttribute('aria-controls', cuerpo.id);
                const aplicarAbierta = abierta => {
                    cuerpo.hidden = !abierta;
                    resumen.hidden = abierta;
                    alternar.textContent = abierta ? 'Ocultar datos' : 'Mostrar datos';
                    alternar.setAttribute('aria-expanded', String(abierta));
                    alternar.setAttribute('aria-label', `${abierta ? 'Ocultar' : 'Mostrar'} los datos de ${materia.nombre}`);
                };
                alternar.addEventListener('click', () => {
                    const abierta = cuerpo.hidden;
                    abierta ? materiasCerradas.delete(materia) : materiasCerradas.add(materia);
                    aplicarAbierta(abierta);
                });

                const quitar = document.createElement('button');
                quitar.type = 'button';
                quitar.className = 'enlace';
                quitar.textContent = 'Quitar';
                quitar.setAttribute('aria-label', `Quitar ${materia.nombre}`);
                quitar.addEventListener('click', () => {
                    elegidas.splice(indice, 1);
                    pintarMaterias();
                    programarGuardado();
                    document.getElementById('agregar-materia').focus();
                });

                const acciones = document.createElement('div');
                acciones.className = 'acciones-materia';
                acciones.append(alternar, quitar);
                encabezado.append(titulo, acciones);

                tarjeta.append(encabezado, resumen, cuerpo);
                aplicarAbierta(!materiasCerradas.has(materia));
                titulo.textContent = materia.nombre ? `${materia.codigo} — ${materia.nombre}` : `Materia ${indice + 1}`;
                const manuales = document.createElement('div');
                manuales.className = 'campos';
                Object.entries(camposMateriaManual).forEach(([clave, [etiqueta, limite]]) => {
                    const campo = crearCampo({ etiqueta, tipo: 'texto', obligatorio: true, max_length: limite },
                        `materia-${indice}-manual-${clave}`, `materias[${indice}][${clave}]`);
                    const control = campo.querySelector('input');
                    control.value = materia[clave] ?? '';
                    control.readOnly = true;
                    control.addEventListener('input', () => {
                        materia[clave] = control.value;
                        titulo.textContent = materia.nombre ? `${materia.codigo} — ${materia.nombre}` : `Materia ${indice + 1}`;
                        validarMateriasRepetidas();
                    });
                    manuales.append(campo);
                });
                cuerpo.append(manuales);

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

                    cuerpo.append(campos);
                }

                contenedorMaterias.append(tarjeta);
            });

            const lleno = elegidas.length >= tramite.max_materias;
            document.getElementById('agregar-materia').disabled = lleno;
            validarMateriasRepetidas();
            validarRangosYFechas();
        }

        function validarMateriasRepetidas() {
            if (!errorMaterias) return;
            const vistas = new Set();
            let repetida = false;
            elegidas.forEach((materia, indice) => {
                const control = document.getElementById(`materia-${indice}-manual-codigo`);
                if (!control) return;
                control.setCustomValidity('');
                const partes = ['codigo', 'periodo', 'paralelo'].map(clave =>
                    (materia[clave] ?? '').trim().replace(/\s+/gu, ' ').toLowerCase());
                if (partes.some(valor => valor === '')) return;
                const clave = JSON.stringify(partes);
                if (vistas.has(clave)) {
                    control.setCustomValidity('Esta materia ya está agregada con el mismo periodo y paralelo.');
                    repetida = true;
                }
                vistas.add(clave);
            });
            errorMaterias.textContent = repetida
                ? 'Quita la materia repetida o revisa su código, periodo y paralelo.' : '';
        }

        if (tramite.max_materias > 0) {
            document.getElementById('agregar-materia')
                .addEventListener('click', () => {
                    if (elegidas.length >= tramite.max_materias) return;
                    const materia = materiasCatalogo.find(item => String(item.id) === selectorCatalogo.value);
                    if (!materia) {
                        errorMaterias.textContent = 'Selecciona una materia del catálogo académico.';
                        selectorCatalogo.focus();
                        return;
                    }
                    if (elegidas.some(item => String(item.id) === String(materia.id))) {
                        errorMaterias.textContent = 'Esta materia ya está agregada.';
                        return;
                    }
                    elegidas.push({ ...materia, datos: {} });
                    errorMaterias.textContent = '';
                    pintarMaterias();
                    document.getElementById(`materia-${elegidas.length - 1}-manual-codigo`).focus();
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
            validarRangosYFechas();
        }

        document.getElementById('campos-tramite')
            .addEventListener('change', actualizarCondicionales);

        actualizarCondicionales();

        // Paso 2 → 3: valida materias y campos visibles del trámite.
        document.getElementById('continuar-respaldos')
            .addEventListener('click', () => {
                limpiarTextos();
                validarCambioSolicitado();
                validarMateriasRepetidas();
                validarRangosYFechas();
                if (tramite.max_materias > 0 && elegidas.length === 0) {
                    errorMaterias.textContent = 'Agrega al menos una materia.';
                    document.getElementById('agregar-materia').focus();
                    return;
                }

                const campos = document.querySelectorAll(
                    '#paso-2 input, #paso-2 select, #paso-2 textarea'
                );

                for (const campo of campos) {
                    if (campo.id !== 'materia' && !campo.disabled && !campo.checkValidity()) {
                        abrirMateriaDe(campo);
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
        const requisitosDisponibles = @json($requisitosMotor['disponible']);
        let requisitoElegido = null;

        const urlRespaldos = @json(route('respaldos-temporales.store'));
        const cabecerasRespaldos = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token())
        };
        const TIPOS_RESPALDO = {
            'application/pdf': 'PDF', 'image/jpeg': 'JPG', 'image/png': 'PNG'
        };

        // Lo que ya está en el servidor: en el borrador de la cuenta o en la sesión.
        const archivos = @json($respaldosSubidos)
            .map(respaldo => ({
                ...respaldo,
                requisito: borrador?.requisitos[respaldo.id] ?? respaldo.requisito ?? ''
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
                    (requisito.obligacion === 'condicional' ? ' (según tu caso)' : (requisito.obligatorio ? ' (obligatorio)' : ' (opcional)'));

                estado.className = 'estado-requisito' + (cantidad ? ' listo' : '');
                estado.textContent = cantidad === 0
                    ? 'Sin adjuntar'
                    : `${cantidad} ${cantidad === 1 ? 'archivo' : 'archivos'}`;

                const adjuntar = document.createElement('button');
                adjuntar.type = 'button';
                adjuntar.className = 'secundario';
                adjuntar.textContent = 'Adjuntar archivo';
                adjuntar.setAttribute('aria-label', `Adjuntar ${requisito.nombre}`);
                adjuntar.disabled = entradaArchivos.disabled;
                adjuntar.addEventListener('click', () => {
                    requisitoElegido = requisito.id;
                    entradaArchivos.value = '';
                    entradaArchivos.click();
                });
                if (requisito.descripcion || requisito.formato) {
                    const detalle = document.createElement('small');
                    detalle.style.display = 'block';
                    detalle.textContent = [requisito.descripcion, requisito.formato].filter(Boolean).join(' · ');
                    nombre.append(detalle);
                }
                fila.append(nombre, estado, adjuntar);
                lista.append(fila);
            });
        }

        async function abrirVisor(url) {
            const respuesta = await fetchSolicitud(url, {
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
            const respuesta = await enCola(() => fetchSolicitud(`${urlRespaldos}/${item.id}`, {
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
                        document.querySelector('#requisitos-respaldo button')?.focus();
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

        async function subirRespaldo(archivo, requisito) {
            const datos = new FormData();
            datos.append('tramite', @json($codigoTramite));
            datos.append('archivo', archivo);
            datos.append('requisito', requisito);

            const respuesta = await enCola(() => fetchSolicitud(urlRespaldos, {
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
                error?.errors?.archivo?.[0] ?? error?.errors?.requisito?.[0] ?? error?.message ??
                'No se pudo subir. Inténtalo de nuevo.';
        }

        // El componente subir-archivo ya descartó lo que no es PDF, JPG o PNG de
        // hasta 10 MB; el servidor lo vuelve a comprobar al guardar.
        document.getElementById('paso-3')
            .addEventListener('subir-archivo:cambio', async evento => {
                const requisito = requisitoElegido;
                if (!requisitosDisponibles || !requisitosRespaldo.some(item => item.id === requisito)) return;
                const nuevos = evento.detail.archivos.filter(archivo =>
                    !archivos.some(item =>
                        item.nombre_original === archivo.name &&
                        item.tamano_bytes === archivo.size && item.requisito === requisito
                    )
                );
                const fallidos = [];

                errorArchivos.textContent = '';
                entradaArchivos.disabled = true;
                pintarRequisitos();

                for (const [indice, archivo] of nuevos.entries()) {
                    estadoArchivos.textContent =
                        `Subiendo ${indice + 1} de ${nuevos.length}: ${archivo.name}…`;

                    try {
                        const subido = await subirRespaldo(archivo, requisito);

                        archivos.push({
                            ...subido,
                            requisito
                        });
                        pintarArchivos();
                    } catch (error) {
                        fallidos.push(`${archivo.name}: ${error.message}`);
                    }
                }

                entradaArchivos.value = '';
                entradaArchivos.disabled = false;
                requisitoElegido = null;
                pintarRequisitos();
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

                if (!requisitosDisponibles) {
                    errorArchivos.textContent = 'No puedes continuar sin consultar los requisitos en Prolog. Recarga la página.';
                    return;
                }

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
                    document.querySelector('#requisitos-respaldo button')?.focus();
                    return;
                }

                errorArchivos.textContent = '';
                pintarResumen();
                mostrarPaso(4);
                consultarPrevalidacion();
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
            pintarLista('lista-datos', Object.entries(camposPersonales).map(([clave, [etiqueta]]) => [etiqueta, textoDe(clave)]));

            const filasTramite = [];

            elegidas.forEach((materia, indice) => {
                filasTramite.push([
                    elegidas.length === 1 ? 'Materia' : `Materia ${indice + 1}`,
                    `${materia.codigo} — ${materia.nombre}`
                ]);
                filasTramite.push([`Periodo, paralelo y docente (${materia.codigo})`, `${materia.periodo} · ${materia.paralelo} · ${materia.docente}`]);

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
                fila.textContent = `${item.nombre_original} — ${requisito?.nombre ?? 'Tipo de documento no vigente: vuelve a asignarlo'}`;
                listaRespaldos.append(fila);
            });

            document.getElementById('mensaje-confirmacion').textContent = '';
        }

        // Pre-validación (RF-2.8): Prolog informa referencias con los datos declarados.
        // Es orientativa: su resultado o un fallo del motor no deshabilitan la confirmación.
        const urlPrevalidacion = @json(route('solicitudes.prevalidacion'));
        const resultadoPrevalidacion = document.getElementById('resultado-prevalidacion');
        let consultaPrevalidacion = 0;

        function mensajePrevalidacion(texto, clase, reintentar = false) {
            const parrafo = document.createElement('p');
            parrafo.className = clase;
            parrafo.textContent = texto;
            resultadoPrevalidacion.replaceChildren(parrafo);

            if (reintentar) {
                parrafo.setAttribute('role', 'alert');
                const boton = document.createElement('button');
                boton.className = 'secundario';
                boton.type = 'button';
                boton.dataset.reintentarPrevalidacion = '';
                boton.textContent = 'Volver a consultar';
                resultadoPrevalidacion.append(boton);
            }
        }

        async function consultarPrevalidacion() {
            // Solo se pinta la última consulta si el estudiante entra varias veces al paso 4.
            const numero = ++consultaPrevalidacion;
            resultadoPrevalidacion.setAttribute('aria-busy', 'true');
            mensajePrevalidacion('Consultando a Prolog…', 'ayuda');

            let html = null;
            let error = 'No se pudo consultar a Prolog. Revisa tu conexión y vuelve a intentarlo.';
            try {
                const respuesta = await fetchSolicitud(urlPrevalidacion, {
                    method: 'POST',
                    headers: { ...cabecerasRespaldos, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tramite: @json($codigoTramite), campos: datosBorrador().campos })
                });
                if (respuesta.ok) {
                    html = await respuesta.text();
                } else if (respuesta.status === 429) {
                    error = 'Hiciste muchas consultas seguidas. Espera un minuto y vuelve a intentarlo.';
                } else {
                    const cuerpo = await respuesta.json().catch(() => ({}));
                    error = Object.values(cuerpo.errors || {}).flat().join(' ') || error;
                }
            } catch {
                // Se conserva el mensaje de error de conexión.
            }

            if (numero !== consultaPrevalidacion) return;
            resultadoPrevalidacion.removeAttribute('aria-busy');

            if (html === null) {
                mensajePrevalidacion(error, 'error', true);
                return;
            }
            resultadoPrevalidacion.innerHTML = html;
        }

        resultadoPrevalidacion.addEventListener('click', evento => {
            if (evento.target.closest('[data-reintentar-prevalidacion]')) consultarPrevalidacion();
        });

        document.querySelectorAll('[data-ir-a]').forEach(boton => {
            boton.addEventListener('click', () => {
                mostrarPaso(Number(boton.dataset.irA));
            });
        });

        document.getElementById('volver-respaldos')
            .addEventListener('click', () => mostrarPaso(3));

        document.getElementById('formato-documento').addEventListener('change', evento => {
            document.getElementById('confirmar-solicitud').textContent =
                evento.target.value === 'pdf' ? 'Descargar documento PDF' : 'Descargar documento Word';
        });

        function validarAntesDeConfirmar() {
            limpiarTextos();
            validarCambioSolicitado();
            validarFacultadYCarrera();
            validarMateriasRepetidas();
            validarRangosYFechas();
            for (const numero of [1, 2]) {
                if (numero === 2 && tramite.max_materias > 0 && elegidas.length === 0) {
                    mostrarPaso(2);
                    errorMaterias.textContent = 'Agrega al menos una materia.';
                    document.getElementById('agregar-materia').focus();
                    return false;
                }
                for (const control of document.querySelectorAll(`#paso-${numero} input, #paso-${numero} select, #paso-${numero} textarea`)) {
                    if (!control.disabled && !control.checkValidity()) {
                        mostrarPaso(numero);
                        abrirMateriaDe(control);
                        control.reportValidity();
                        return false;
                    }
                }
            }
            return true;
        }

        document.getElementById('confirmar-solicitud')
            .addEventListener('click', async () => {
                const declaracion = document.getElementById('declaracion');

                if (!validarAntesDeConfirmar() || !declaracion.reportValidity()) return;

                {
                    const boton = document.getElementById('confirmar-solicitud');
                    const mensaje = document.getElementById('mensaje-confirmacion');
                    if (boton.disabled) return;
                    boton.disabled = true;
                    const formato = document.getElementById('formato-documento').value;
                    mensaje.textContent = 'Preparando el documento…';
                    try {
                        const respuesta = await enCola(() => fetchSolicitud(@json(route('solicitudes.documento')), {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token())},
                            body: JSON.stringify({...datosBorrador(), formato, tramite: @json($codigoTramite), nombre: textoDe('nombre'), codigo: textoDe('codigo'), carrera: textoDe('carrera'), correo: textoDe('correo'), declaracion: declaracion.checked})
                        }));
                        if (!respuesta.ok) {
                            if (respuesta.status === 429) {
                                const segundos = Math.max(1, Number(respuesta.headers.get('Retry-After')) || 60);
                                throw new Error(`Has alcanzado el límite de descargas. Espera ${segundos} segundos antes de intentar otra vez.`);
                            }
                            const error = await respuesta.json();
                            throw new Error(Object.values(error.errors || {}).flat().join(' ') || error.message || 'No se pudo generar el documento.');
                        }
                        versionDocumento = respuesta.headers.get('X-Documento-Version');
                        const url = URL.createObjectURL(await respuesta.blob());
                        const enlace = document.createElement('a');
                        enlace.href = url;
                        enlace.download = 'solicitud-' + @json($codigoTramite) + '.' + formato;
                        enlace.click();
                        setTimeout(() => URL.revokeObjectURL(url), 60000);
                        if (respuesta.headers.get('X-Firmado-Conservado') !== '1') {
                            firmado = null;
                            pintarFirmado();
                        }
                        bloqueFirmado.hidden = false;
                        if (formato === 'pdf') {
                            mensaje.textContent = 'PDF descargado. Revísalo, imprímelo, fírmalo y sube el PDF firmado. Descargar no envía la solicitud.';
                            return;
                        }
                        mensaje.textContent = 'Word descargado con los datos ingresados. Imprímelo, fírmalo y sube abajo el PDF firmado. La descarga no envía la solicitud a revisión.';
                    } catch (error) {
                        mensaje.textContent = error.message;
                    } finally {
                        boton.disabled = false;
                    }
                }
            });

        // PDF firmado (RF-2.11): uno por trámite, en el borrador de la cuenta o en la sesión.
        const urlFirmado = @json(route('firmados-temporales.store', $codigoTramite));
        const bloqueFirmado = document.getElementById('bloque-firmado');
        const entradaFirmado = document.getElementById('firmado');
        const estadoFirmado = document.getElementById('estado-firmado');
        const errorFirmado = document.getElementById('error-firmado');
        let firmado = @json($firmado);
        let versionDocumento = @json($versionDocumento);
        let enviandoSolicitud = false;
        let solicitudEnviada = false;

        function pintarFirmado() {
            const fila = document.getElementById('firmado-subido');

            fila.hidden = firmado === null;
            document.getElementById('recibido-firmado').hidden = firmado === null;
            document.getElementById('enviar-solicitud').disabled = !@json($envioDisponible) || firmado === null
                || versionDocumento === null || enviandoSolicitud || solicitudEnviada;

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

            const respuesta = await enCola(() => fetchSolicitud(urlFirmado, {
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
                const respuesta = await enCola(() => fetchSolicitud(urlFirmado, {
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

        document.getElementById('enviar-solicitud').addEventListener('click', async () => {
            const boton = document.getElementById('enviar-solicitud');
            const mensaje = document.getElementById('mensaje-envio');
            const declaracion = document.getElementById('declaracion');
            if (boton.disabled || enviandoSolicitud || solicitudEnviada) return;
            if (!validarAntesDeConfirmar() || !declaracion.reportValidity()) return;
            const datosEnvio = {...datosBorrador(), tramite: @json($codigoTramite),
                clave_envio: versionDocumento, declaracion: declaracion.checked};
            cancelarGuardado();
            enviandoSolicitud = true;
            boton.disabled = true;
            mensaje.textContent = 'Guardando el expediente y enviando a revisión…';
            try {
                const respuesta = await enCola(() => fetchSolicitud(@json(($solicitudCorreccion ?? null) ? route('solicitudes.reenviar', $solicitudCorreccion) : route('solicitudes.enviar')), {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token())},
                    body: JSON.stringify(datosEnvio)
                }));
                const resultado = await respuesta.json();
                if (!respuesta.ok) throw new Error(Object.values(resultado.errors || {}).flat().join(' ') || resultado.message);
                solicitudEnviada = true;
                window.location.assign(resultado.url);
            } catch (error) {
                mensaje.textContent = error.message || 'No se pudo enviar. Tus datos permanecen guardados.';
            } finally {
                enviandoSolicitud = false;
                pintarFirmado();
            }
        });

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
                    document.getElementById(`campo-${definicion.nombre}`).value.trim();
            });

            archivos.forEach(item => {
                requisitos[item.id] = item.requisito;
            });

            return {
                ...Object.fromEntries(Object.keys(camposPersonales).map(clave => [clave, textoDe(clave)])),
                paso: pasoActual,
                origen_materias: 'catalogo',
                materias: elegidas.map(materia => ({ id: materia.id,
                    ...Object.fromEntries(Object.keys(camposMateriaManual).map(clave => [clave, (materia[clave] ?? '').trim()])),
                    datos: Object.fromEntries(Object.entries(materia.datos).map(([clave, valor]) =>
                        [clave, typeof valor === 'string' ? valor.trim() : valor])) })),
                campos,
                requisitos
            };
        }

        function enviarBorrador(opciones = {}) {
            // Un guardado después del envío crearía un borrador nuevo del mismo trámite.
            if (enviandoSolicitud || solicitudEnviada) return Promise.resolve(null);
            return fetchSolicitud(urlBorrador, {
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

            if (respuesta?.status === 429) {
                const segundos = Math.max(1, Number(respuesta.headers.get('Retry-After')) || 60);
                estadoBorrador.textContent = `No se guardaron los últimos cambios: espera ${segundos} segundos y pulsa Guardar borrador.`;
                return;
            }

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

            if (respuesta === null && (enviandoSolicitud || solicitudEnviada)) return;

            if (!respuesta?.ok) {
                estadoBorrador.textContent = respuesta?.status === 419
                    ? 'Tu sesión expiró y el borrador no se guardó. Recarga la página.'
                    : 'No se pudo guardar el borrador. Se intentará de nuevo con tu próximo cambio.';
                return;
            }

            const guardado = await respuesta.json();
            estadoBorrador.textContent = guardado.en_cuenta
                ? `Borrador guardado en tu cuenta ${cuandoSeGuardo(guardado.guardado_at)}.`
                : `Borrador guardado en este navegador ${cuandoSeGuardo(guardado.guardado_at)}.`;
            botonDescartar.hidden = false;
        }

        const contenido = document.querySelector('.distribucion');
        contenido.addEventListener('input', validarCambioSolicitado);
        contenido.addEventListener('change', validarCambioSolicitado);
        contenido.addEventListener('input', validarRangosYFechas);
        contenido.addEventListener('change', validarRangosYFechas);
        contenido.addEventListener('focusout', evento => {
            limpiarControl(evento.target);
            validarCambioSolicitado();
        });
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

            const respuesta = await enCola(() => fetchSolicitud(urlBorrador, {
                method: 'DELETE',
                headers: cabecerasRespaldos
            }).catch(() => null));

            if (!respuesta?.ok) {
                estadoBorrador.textContent = 'No se pudo descartar el borrador. Inténtalo de nuevo.';
                return;
            }

            cancelarGuardado();
            formularioDatos.reset();
            actualizarCarreras(datosIniciales.carrera ?? '');
            document.getElementById('declaracion').checked = false;
            document.querySelectorAll(
                '#campos-tramite input, #campos-tramite select, #campos-tramite textarea'
            ).forEach(control => {
                control.value = '';
            });
            actualizarCondicionales();

            elegidas.length = 0;
            validarCambioSolicitado();
            archivos.length = 0;

            if (tramite.max_materias > 0) {
                errorMaterias.textContent = '';
                pintarMaterias();
            }

            errorArchivos.textContent = '';
            pintarArchivos();

            firmado = null;
            versionDocumento = null;
            errorFirmado.textContent = '';
            bloqueFirmado.hidden = true;
            pintarFirmado();

            pasoActual = 1;
            mostrarPaso(1);
            botonDescartar.hidden = true;
            estadoBorrador.textContent = 'Borrador descartado.';
        });

        if (borrador) {
            Object.keys(camposPersonales).forEach(clave => {
                const control = document.getElementById(clave);
                if (clave === 'carrera') {
                    actualizarCarreras(borrador[clave] ?? '');
                } else if (!control.readOnly) {
                    const valor = borrador[clave] ?? '';
                    if (clave === 'facultad' && valor && !Object.hasOwn(catalogoFacultades, valor)) {
                        const anterior = new Option(`Revisa la facultad guardada: ${valor}`, valor);
                        anterior.disabled = true;
                        control.append(anterior);
                    }
                    control.value = valor;
                }
            });

            tramite.campos.forEach(definicion => {
                let valorGuardado = borrador.campos?.[definicion.nombre] ?? '';
                if (definicion.tipo === 'lista' && definicion.opciones['sí']) {
                    const normalizado = valorGuardado.trim().toLowerCase();
                    if (normalizado === 'si' || normalizado === 'sí') valorGuardado = 'sí';
                    if (normalizado === 'no') valorGuardado = 'no';
                }
                document.getElementById(`campo-${definicion.nombre}`).value =
                    valorGuardado;
            });
            actualizarCondicionales();

            (borrador.materias ?? []).slice(0, tramite.max_materias).forEach(guardada => {
                const actual = materiasCatalogo.find(item => String(item.id) === String(guardada.id));
                elegidas.push({ ...guardada, ...actual, datos: { ...guardada.datos } });
            });

            if (tramite.max_materias > 0) {
                pintarMaterias();
            }

            // El paso 4 se vuelve a armar al pasar por la validación del paso 3.
            // En cada visita se revisan y confirman los datos personales recuperados.
            pasoActual = 1;

            estadoBorrador.textContent =
                `Recuperamos tu borrador, guardado ${cuandoSeGuardo(borrador.guardado_at)}.`;
            botonDescartar.hidden = false;
        }

        validarCambioSolicitado();
        validarRangosYFechas();
        pintarArchivos();
    </script>
    @include('estudiante.partials.chatbot-flotante')
</div>
@endsection
