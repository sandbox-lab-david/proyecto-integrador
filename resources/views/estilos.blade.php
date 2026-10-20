@php
    $colores = [
        'Marca' => ['vino', 'vino-oscuro', 'vino-suave', 'vino-linea'],
        'Base' => ['fondo', 'superficie', 'texto', 'gris', 'gris-suave', 'linea', 'linea-suave', 'linea-campo'],
        'Estados' => ['exito', 'exito-suave', 'alerta', 'alerta-suave', 'peligro', 'peligro-suave', 'info', 'info-suave'],
    ];

    $textos = ['mini' => '11px', 'chico' => '12.5px', 'cuerpo' => '14px', 'subtitulo' => '16px', 'titulo' => '20px', 'titulo-grande' => '26px', 'portada' => '30px'];
    $espacios = ['minimo' => '4px', 'chico' => '8px', 'medio' => '12px', 'normal' => '16px', 'amplio' => '24px', 'grande' => '32px'];
    $radios = ['control' => '8px', 'caja' => '10px', 'tarjeta' => '12px', 'panel' => '16px'];

    $ejemplos = [
        'botones' => ['Botones', <<<'HTML'
<div class="ui-acciones">
    <button class="ui-btn ui-btn-primario">Iniciar solicitud</button>
    <button class="ui-btn ui-btn-secundario">Ver requisitos</button>
    <button class="ui-btn ui-btn-peligro">Eliminar</button>
    <button class="ui-btn ui-btn-quieto">Cancelar</button>
    <button class="ui-btn ui-btn-primario ui-btn-chico">Revisar</button>
    <button class="ui-btn ui-btn-primario" disabled>No disponible</button>
</div>
HTML],
        'campos' => ['Campos de formulario', <<<'HTML'
<form class="ui-form">
    <div class="ui-campo">
        <label for="m-correo">Correo institucional</label>
        <input id="m-correo" class="ui-control" type="email" placeholder="usuario@uees.edu.ec">
        <p class="ui-ayuda">Usa el correo con el que entras al portal.</p>
    </div>
    <div class="ui-campo">
        <label for="m-materia">Materia</label>
        <select id="m-materia" class="ui-control">
            <option>Selecciona una opción</option>
        </select>
    </div>
    <div class="ui-campo ui-campo-error">
        <label for="m-motivo">Motivo <span class="ui-opcional">(opcional)</span></label>
        <textarea id="m-motivo" class="ui-control"></textarea>
        <p class="ui-error-campo">Escribe al menos 20 caracteres.</p>
    </div>
    <label class="ui-casilla">
        <input type="checkbox">
        <span>Confirmo que la información es correcta.</span>
    </label>
    <p class="ui-resumen-error">Revisa los campos marcados antes de continuar.</p>
</form>
HTML],
        'archivo' => ['Subir archivo', <<<'HTML'
<label class="ui-archivo">
    <input type="file" accept=".pdf,.jpg,.png">
    <strong>Selecciona un archivo</strong>
    <span class="ui-ayuda">PDF, JPG o PNG. Máximo 5 MB.</span>
</label>
HTML],
        'alertas' => ['Alertas', <<<'HTML'
<div class="ui-alerta" role="status"><b>Información</b>El trámite se revisa en un plazo de 5 días hábiles.</div>
<div class="ui-alerta ui-alerta-exito" role="status"><b>Solicitud enviada</b>Te avisaremos cuando cambie de estado.</div>
<div class="ui-alerta ui-alerta-aviso" role="status"><b>Atención</b>Faltan documentos de respaldo.</div>
<div class="ui-alerta ui-alerta-error" role="alert"><b>No se pudo guardar</b>Intenta de nuevo en unos minutos.</div>
HTML],
        'badges' => ['Estados y etiquetas', <<<'HTML'
<div class="ui-acciones">
    <span class="ui-badge">Borrador</span>
    <span class="ui-badge ui-badge-info">En revisión</span>
    <span class="ui-badge ui-badge-alerta">Observada</span>
    <span class="ui-badge ui-badge-exito">Aprobada</span>
    <span class="ui-badge ui-badge-peligro">Rechazada</span>
    <span class="ui-badge ui-badge-vino">Nueva</span>
    <span class="ui-etiqueta">PDF</span>
    <span class="ui-etiqueta ui-etiqueta-vino">Obligatorio</span>
</div>
HTML],
        'tarjetas' => ['Tarjetas', <<<'HTML'
<div class="ui-tarjetas">
    <article class="ui-tarjeta">
        <span class="ui-etiqueta ui-etiqueta-vino">Académico</span>
        <h3 class="ui-subtitulo">Examen de recuperación</h3>
        <p class="ui-texto-gris">Rinde de nuevo la evaluación final de una materia.</p>
        <div class="ui-acciones">
            <a class="ui-btn ui-btn-primario ui-btn-chico" href="#">Ver ficha</a>
        </div>
    </article>
    <a class="ui-tarjeta" href="#">
        <h3 class="ui-subtitulo">Retiro de materia</h3>
        <p class="ui-texto-gris">Toda la tarjeta funciona como enlace.</p>
    </a>
</div>
HTML],
        'datos' => ['Datos clave', <<<'HTML'
<dl class="ui-datos">
    <div class="ui-dato"><dt>Costo</dt><dd>$25,00</dd></div>
    <div class="ui-dato"><dt>Plazo</dt><dd>5 días</dd></div>
    <div class="ui-dato"><dt>Estado</dt><dd>En revisión</dd></div>
</dl>
<ul class="ui-lista-check">
    <li>Tener matrícula activa</li>
    <li>Haber rendido el examen final</li>
</ul>
HTML],
        'tabla' => ['Tabla y paginación', <<<'HTML'
<div class="ui-tabla-caja">
    <table class="ui-tabla">
        <thead>
            <tr><th>Código</th><th>Trámite</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
            <tr>
                <td>SOL-00012</td>
                <td>Examen de recuperación</td>
                <td><span class="ui-badge ui-badge-info">En revisión</span></td>
                <td><button class="ui-btn ui-btn-secundario ui-btn-chico">Ver</button></td>
            </tr>
            <tr>
                <td>SOL-00011</td>
                <td>Retiro de materia</td>
                <td><span class="ui-badge ui-badge-exito">Aprobada</span></td>
                <td><button class="ui-btn ui-btn-secundario ui-btn-chico">Ver</button></td>
            </tr>
        </tbody>
    </table>
</div>
<nav class="ui-paginacion" aria-label="Paginación">
    <span aria-disabled="true">Anterior</span>
    <span aria-current="page">1</span>
    <a href="#">2</a>
    <a href="#">3</a>
    <a href="#">Siguiente</a>
</nav>
HTML],
        'modal' => ['Ventana de confirmación', <<<'HTML'
<div class="ui-acciones">
    <button class="ui-btn ui-btn-peligro" type="button" onclick="document.getElementById('m-modal').showModal()">Eliminar periodo</button>
</div>
<dialog id="m-modal" class="ui-modal">
    <h2>¿Eliminar este periodo?</h2>
    <p>Esta acción no se puede deshacer.</p>
    <form method="dialog" class="ui-acciones">
        <button class="ui-btn ui-btn-secundario">Cancelar</button>
        <button class="ui-btn ui-btn-peligro">Eliminar</button>
    </form>
</dialog>
HTML],
        'pasos' => ['Pasos de un formulario', <<<'HTML'
<ol class="ui-pasos">
    <li class="ui-paso-hecho">Datos personales</li>
    <li aria-current="step">Documentos</li>
    <li>Confirmación</li>
</ol>
HTML],
        'estados' => ['Vacío, cargando y error', <<<'HTML'
<div class="ui-vacio">
    <strong>Todavía no tienes solicitudes</strong>
    <p>Cuando inicies un trámite aparecerá aquí.</p>
</div>
<p class="ui-cargando" role="status">Cargando historial…</p>
<span class="ui-esqueleto" style="width: 60%"></span>
<div class="ui-error-caja" role="alert">
    <div>
        <strong>No pudimos cargar la información</strong>
        <p>Revisa tu conexión e intenta otra vez.</p>
        <div class="ui-acciones" style="margin-top: 10px">
            <button class="ui-btn ui-btn-secundario ui-btn-chico">Reintentar</button>
        </div>
    </div>
</div>
HTML],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estilos generales · UEES</title>
    @vite('resources/css/app.css')
</head>
<body class="ui-pagina">
    <header class="ui-barra">
        <img src="{{ asset('assets/chatbot/img/facultad-ingenieria-uees.png') }}" alt="UEES Facultad de Ingeniería">
        <span class="ui-barra-titulo">Estilos generales</span>
        <a class="ui-barra-enlace" href="{{ url('/') }}">Inicio</a>
    </header>

    <main class="ui-contenedor flex flex-col gap-grande">
        <section>
            <p class="ui-antetitulo">Guía visual</p>
            <h1 class="ui-titulo">Estilos generales del sistema</h1>
            <p class="ui-texto-gris mt-chico max-w-2xl">
                Todo sale de <code>resources/css/app.css</code>. Copia el HTML de cada bloque o usa las variables con
                Tailwind (<code>bg-vino</code>, <code>text-gris</code>, <code>font-titulo</code>) o en CSS normal
                (<code>var(--color-vino)</code>).
            </p>
            <nav class="ui-acciones mt-normal" aria-label="Secciones">
                <a class="ui-btn ui-btn-secundario ui-btn-chico" href="#variables">Variables</a>
                @foreach ($ejemplos as $id => [$titulo])
                    <a class="ui-btn ui-btn-secundario ui-btn-chico" href="#{{ $id }}">{{ $titulo }}</a>
                @endforeach
                <a class="ui-btn ui-btn-secundario ui-btn-chico" href="#resultado">Resultado de evaluación</a>
            </nav>
        </section>

        <section id="variables" class="ui-tarjeta">
            <h2 class="ui-subtitulo">Colores</h2>
            @foreach ($colores as $grupo => $nombres)
                <p class="ui-antetitulo mt-normal">{{ $grupo }}</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-chico">
                    @foreach ($nombres as $nombre)
                        <div class="ui-dato">
                            <span class="block h-10 rounded-control border border-linea" style="background: var(--color-{{ $nombre }})"></span>
                            <p class="text-chico font-semibold mt-chico">{{ $nombre }}</p>
                            <code class="text-mini text-gris break-all">--color-{{ $nombre }}</code>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <h2 class="ui-subtitulo mt-amplio">Fuentes y tamaños</h2>
            <p class="font-titulo text-titulo font-bold">Manrope para títulos · <code class="text-chico font-sans">font-titulo</code></p>
            <p class="text-cuerpo">DM Sans para el texto · <code class="text-chico">font-sans</code></p>
            <div class="ui-tabla-caja">
                <table class="ui-tabla">
                    <thead><tr><th>Tamaño</th><th>Tailwind</th><th>CSS</th><th>Ejemplo</th></tr></thead>
                    <tbody>
                        @foreach ($textos as $nombre => $valor)
                            <tr>
                                <td>{{ $valor }}</td>
                                <td><code>text-{{ $nombre }}</code></td>
                                <td><code>var(--text-{{ $nombre }})</code></td>
                                <td style="font-size: var(--text-{{ $nombre }})">Solicitud académica</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h2 class="ui-subtitulo mt-amplio">Espacios y bordes</h2>
            <div class="grid sm:grid-cols-2 gap-normal">
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead><tr><th>Espacio</th><th>Tailwind</th><th>CSS</th></tr></thead>
                        <tbody>
                            @foreach ($espacios as $nombre => $valor)
                                <tr><td>{{ $valor }}</td><td><code>p-{{ $nombre }}</code>, <code>gap-{{ $nombre }}</code></td><td><code>var(--space-{{ $nombre }})</code></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="ui-tabla-caja">
                    <table class="ui-tabla">
                        <thead><tr><th>Radio</th><th>Tailwind</th><th>CSS</th></tr></thead>
                        <tbody>
                            @foreach ($radios as $nombre => $valor)
                                <tr><td>{{ $valor }}</td><td><code>rounded-{{ $nombre }}</code></td><td><code>var(--radius-{{ $nombre }})</code></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        @foreach ($ejemplos as $id => [$titulo, $html])
            <section id="{{ $id }}" class="ui-tarjeta">
                <h2 class="ui-subtitulo">{{ $titulo }}</h2>
                <div class="flex flex-col gap-medio">{!! $html !!}</div>
                <details>
                    <summary class="ui-enlace text-chico cursor-pointer">Ver HTML</summary>
                    <pre class="mt-chico bg-texto text-white text-chico rounded-caja p-medio overflow-x-auto"><code>{{ $html }}</code></pre>
                </details>
            </section>
        @endforeach

        <section id="resultado" class="ui-tarjeta">
            <h2 class="ui-subtitulo">Resultado de evaluación</h2>
            <p class="ui-texto-gris">Componente del chatbot que se puede usar en cualquier vista.</p>
            <div class="grid md:grid-cols-3 gap-normal">
                <x-resultado-elegibilidad :evaluacion="['resultado' => 'cumple', 'motivos' => ['Tienes matrícula activa', 'Rendiste el examen final']]" />
                <x-resultado-elegibilidad :evaluacion="['resultado' => 'no_cumple', 'motivos' => ['El promedio no alcanza el mínimo']]" />
                <x-resultado-elegibilidad :evaluacion="['resultado' => 'revision_manual', 'motivos' => ['Falta validar un documento']]" />
            </div>
            <pre class="bg-texto text-white text-chico rounded-caja p-medio overflow-x-auto"><code>&lt;x-resultado-elegibilidad :evaluacion="$evaluacion" /&gt;</code></pre>
        </section>
    </main>
</body>
</html>
