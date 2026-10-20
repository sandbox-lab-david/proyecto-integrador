(function () {
    'use strict';

    const ESPERA_VISIBLE = 300;
    const ESPERA_LENTA = 3000;
    const LIMITE = 10000;

    const TITULOS_ERROR = {
        datos_invalidos: 'Revisa los datos',
        sesion_requerida: 'Inicia sesión para continuar',
        acceso_denegado: 'No tienes acceso a esta consulta',
        motor_no_disponible: 'No pude obtener la respuesta',
        tiempo_agotado: 'La consulta tardó demasiado',
        respuesta_motor_invalida: 'Algo salió mal',
        error_interno: 'Algo salió mal',
        sin_conexion: 'No hay conexión',
    };

    const OBLIGACIONES = {
        obligatorio: { texto: 'Obligatorio', clase: 'cb-etq-vino' },
        opcional: { texto: 'Opcional', clase: null },
        condicional: { texto: 'Según tu caso', clase: null },
        por_confirmar: { texto: 'Por confirmar', clase: 'cb-etq-pendiente' },
    };
    const POR_CONFIRMAR = 'Por confirmar';

    const ERROR_SUAVE = ['sesion_requerida', 'tiempo_agotado', 'sin_conexion'];
    const ICONO_ERROR = { sesion_requerida: 'i', tiempo_agotado: '↻', sin_conexion: '↻' };

    const dinero = new Intl.NumberFormat('es-EC', { style: 'currency', currency: 'USD' });
    const fechaLarga = new Intl.DateTimeFormat('es-EC', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });

    const normalizar = (s) => (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
    const formatearFecha = (f) => (f ? fechaLarga.format(new Date(`${f}T00:00:00Z`)) : null);
    const urlSegura = (u) => (typeof u === 'string' && (/^\/(?!\/)/.test(u) || /^https:\/\//i.test(u)) ? u : null);
    const errorSobre = (codigo, mensaje, reintentable) => ({ version: 2, error: { codigo, mensaje, reintentable, campos: {} } });

    const obligacionDe = (d) => {
        if (OBLIGACIONES[d.obligacion]) return d.obligacion;
        return d.obligatorio === true ? 'obligatorio' : 'por_confirmar';
    };

    const estadoResultado = (resultado) => {
        const codigos = (resultado.motivos || []).map((m) => (m && m.codigo) || '');
        if (codigos.includes('evaluacion_no_disponible')) return 'sin_evaluacion';
        if (resultado.estado === 'requiere_revision' && codigos.includes('condiciones_no_evaluadas')) return 'parcial';
        return resultado.estado === 'requiere_revision' ? 'revision_manual' : resultado.estado;
    };

    const memoria = {
        clave: 'cb-flotante-abierto',
        leer() {
            try {
                return sessionStorage.getItem(this.clave) === '1';
            } catch (e) {
                return false;
            }
        },
        guardar(abierto) {
            try {
                sessionStorage.setItem(this.clave, abierto ? '1' : '0');
            } catch (e) {
                return;
            }
        },
    };

    let contador = 0;

    class Chat {
        constructor(raiz) {
            this.raiz = raiz;
            this.id = `cb${++contador}`;
            this.mensajes = this.parte('mensajes');
            this.conversacion = this.parte('conversacion');
            this.entrada = this.parte('entrada');
            this.botonEnviar = this.parte('enviar');
            this.panel = this.parte('panel-buscar');
            this.urls = {
                catalogo: raiz.dataset.urlCatalogo,
                mensaje: raiz.dataset.urlMensaje,
                login: raiz.dataset.urlLogin,
            };
            this.simulado = raiz.dataset.simulado === '1' && window.ChatbotSimulado;
            this.plantillas = {};
            raiz.querySelectorAll('template[data-plantilla]').forEach((t) => {
                this.plantillas[t.dataset.plantilla] = t;
            });

            this.catalogo = null;
            this.tramite = null;
            this.ocupado = false;
            this.cargado = false;
            this.tramitePendiente = null;
            this.flotante = raiz.classList.contains('cb-modo-flotante');
            this.contenedor = raiz.closest('[data-cb-flotante]');
            this.lanzador = this.contenedor ? this.contenedor.querySelector('[data-cb-abrir]') : null;
            this.ampliar = this.parte('ampliar');

            this.escuchar();
            if (this.flotante) {
                this.escucharFlotante();
                if (memoria.leer()) this.abrir(null, { sinFoco: true });
            } else {
                this.iniciarCarga();
            }
        }

        iniciarCarga() {
            if (this.cargado) return;
            this.cargado = true;
            this.cargarCatalogo();
        }

        escucharFlotante() {
            this.lanzador.addEventListener('click', () => this.abrir());
            this.parte('cerrar').addEventListener('click', () => this.cerrar());
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !this.raiz.hidden && this.panel.hidden) this.cerrar();
            });
        }

        abrir(tramiteId, opciones = {}) {
            if (tramiteId) {
                if (this.catalogo) {
                    this.irATramite(tramiteId);
                } else {
                    this.tramitePendiente = tramiteId;
                }
            }
            if (this.flotante) {
                this.raiz.hidden = false;
                this.contenedor.classList.add('cb-abierto');
                this.lanzador.setAttribute('aria-expanded', 'true');
                document.documentElement.classList.add('cb-chat-abierto');
                memoria.guardar(true);
            }
            this.iniciarCarga();
            if (!opciones.sinFoco && window.matchMedia('(min-width: 601px)').matches) {
                requestAnimationFrame(() => this.entrada.focus());
            }
        }

        cerrar() {
            if (!this.flotante || this.raiz.hidden) return;
            this.raiz.hidden = true;
            this.contenedor.classList.remove('cb-abierto');
            this.lanzador.setAttribute('aria-expanded', 'false');
            document.documentElement.classList.remove('cb-chat-abierto');
            memoria.guardar(false);
            this.lanzador.focus();
        }

        irATramite(id) {
            if (!this.porId.tramites[id] || (this.tramite && this.tramite.id === id)) return;
            this.elegirTramite(id, { silencioso: true });
        }

        parte(nombre, dentro) {
            return (dentro || this.raiz).querySelector(`[data-cb="${nombre}"]`);
        }

        clonar(nombre) {
            return this.plantillas[nombre].content.firstElementChild.cloneNode(true);
        }

        llenar(el, nombre, valor) {
            const destino = this.parte(nombre, el);
            if (!destino) return null;
            destino.textContent = valor ?? '';
            destino.hidden = valor === null || valor === undefined || valor === '';
            return destino;
        }

        escuchar() {
            this.parte('composer').addEventListener('submit', (e) => {
                e.preventDefault();
                const texto = this.entrada.value.trim();
                if (!texto || this.ocupado) return;
                this.entrada.value = '';
                this.agregarUsuario(texto);
                this.enviar({ version: 2, texto, tramite_id: this.tramite ? this.tramite.id : null });
            });

            this.parte('nueva').addEventListener('click', () => this.nuevaConsulta());
            this.parte('cambiar').addEventListener('click', () => this.abrirBuscar());
            this.parte('cerrar-buscar').addEventListener('click', () => this.cerrarBuscar());

            this.raiz.querySelectorAll('[data-cb="buscar"]').forEach((input) => {
                input.addEventListener('input', () => {
                    const lista = input.closest('aside, [data-cb="panel-buscar"]').querySelector('[data-cb="lista-tramites"]');
                    this.pintarLista(lista, input.value);
                });
            });

            this.panel.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                e.stopPropagation();
                this.cerrarBuscar();
            });
        }

        async pedir(metodo, url, cuerpo) {
            if (this.simulado) return window.ChatbotSimulado.pedir(metodo, url, cuerpo);

            const control = new AbortController();
            const reloj = setTimeout(() => control.abort(), LIMITE);
            try {
                const res = await fetch(url, {
                    method: metodo,
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.raiz.dataset.csrf || '',
                    },
                    credentials: 'same-origin',
                    body: cuerpo ? JSON.stringify(cuerpo) : undefined,
                    signal: control.signal,
                });
                let body;
                try {
                    body = await res.json();
                } catch (e) {
                    return { status: 502, body: errorSobre('respuesta_motor_invalida', 'La respuesta del servidor no se pudo leer.', false) };
                }
                return { status: res.status, body };
            } catch (e) {
                if (e.name === 'AbortError') {
                    return { status: 504, body: errorSobre('tiempo_agotado', 'La consulta tardó demasiado. Prueba otra vez en unos segundos.', true) };
                }
                return { status: 0, body: errorSobre('sin_conexion', 'Revisa tu conexión a internet y vuelve a intentarlo.', true) };
            } finally {
                clearTimeout(reloj);
            }
        }

        async cargarCatalogo() {
            const espera = this.agregarEspera();
            const r = await this.pedir('GET', this.urls.catalogo);
            espera.quitar();

            if (r.body && r.body.error) {
                this.agregarError(r.body.error, () => this.cargarCatalogo());
                return;
            }

            this.catalogo = r.body;
            this.porId = {
                tramites: Object.fromEntries(this.catalogo.tramites.map((t) => [t.id, t])),
                preguntas: Object.fromEntries(this.catalogo.preguntas.map((p) => [p.id, p])),
            };
            this.raiz.querySelectorAll('[data-cb="lista-tramites"]').forEach((l) => this.pintarLista(l, ''));

            const pendiente = this.tramitePendiente;
            this.tramitePendiente = null;

            const inicial = this.porId.tramites[pendiente || this.raiz.dataset.tramite];
            if (inicial) {
                this.elegirTramite(inicial.id, { silencioso: true });
            } else {
                this.bienvenida();
            }
        }

        bienvenida() {
            this.agregarBot([
                'Hola. Te ayudo con los trámites de la facultad: requisitos, documentos, costos y plazos. En algunos trámites también reviso de forma orientativa si cumples las condiciones.',
                'Elige una categoría o escribe tu pregunta.',
            ]);
            this.agregarSugerencias(this.opcionesCategorias(), (c) => this.elegirCategoria(c));
        }

        opcionesCategorias() {
            return this.catalogo.categorias.map((c) => ({ id: c.id, texto: c.nombre, detalle: c.descripcion, valor: c }));
        }

        elegirCategoria(categoria) {
            this.agregarUsuario(categoria.nombre);
            const lista = this.catalogo.tramites.filter((t) => t.categoria_id === categoria.id);
            this.agregarBot(`Estos son los trámites de ${categoria.nombre}. ¿Cuál te interesa?`);
            this.agregarSugerencias(lista.map((t) => ({ id: t.id, texto: t.nombre, valor: t })), (t) => this.elegirTramite(t.id));
        }

        elegirTramite(id, opciones = {}) {
            const t = this.porId.tramites[id];
            if (!t) return;
            this.fijarTramite(id);
            this.cerrarBuscar();
            if (!opciones.silencioso) this.agregarUsuario(t.nombre);
            this.agregarBot(`¿Qué quieres saber sobre ${t.nombre}?`);
            const lista = this.catalogo.preguntas.filter((p) => p.tramite_id === id);
            this.agregarSugerencias(lista.map((p) => ({ id: p.id, texto: p.texto, valor: p })), (p) => this.preguntar(p));
        }

        fijarTramite(id) {
            this.tramite = this.porId.tramites[id] || null;
            this.parte('chip-texto').textContent = this.tramite ? this.tramite.nombre : 'Elegir trámite';
            this.parte('cab-tramite').textContent = this.tramite ? this.tramite.nombre : (this.raiz.dataset.facultad || 'Facultad de Ingeniería');
            this.raiz.querySelectorAll('[data-tramite-id]').forEach((b) => {
                b.setAttribute('aria-current', this.tramite && b.dataset.tramiteId === this.tramite.id ? 'true' : 'false');
            });
            if (this.ampliar) {
                const url = new URL(this.ampliar.href, location.href);
                if (this.tramite) {
                    url.searchParams.set('tramite', this.tramite.id);
                } else {
                    url.searchParams.delete('tramite');
                }
                this.ampliar.href = url.pathname + url.search;
            }
        }

        preguntar(p) {
            this.agregarUsuario(p.texto);
            this.enviar({
                version: 2,
                pregunta_id: p.id,
                tramite_id: p.tramite_id,
                intencion: p.intencion,
                origen_datos: 'ninguno',
                datos_usuario: {},
            });
        }

        origenPara(payload) {
            const p = payload.pregunta_id ? this.porId.preguntas[payload.pregunta_id] : null;
            const permitidos = p ? p.origenes_permitidos : ['manual'];
            return permitidos.includes('manual') ? 'manual' : permitidos[0];
        }

        async enviar(payload, opciones = {}) {
            if (this.ocupado) return;
            this.bloquear(true);
            const espera = this.agregarEspera();
            const r = await this.pedir('POST', this.urls.mensaje, payload);
            espera.quitar();
            this.bloquear(false);

            if (r.body && r.body.error) {
                if (r.body.error.codigo === 'datos_invalidos' && opciones.formulario) {
                    opciones.formulario.desbloquear();
                    opciones.formulario.marcar(r.body.error.campos || {});
                    return;
                }
                this.agregarError(r.body.error, () => this.enviar(payload, opciones));
                return;
            }

            if (opciones.formulario) opciones.formulario.cerrar();

            const resp = r.body;
            if (resp.tramite_id && this.porId.tramites[resp.tramite_id] && (!this.tramite || this.tramite.id !== resp.tramite_id)) {
                this.fijarTramite(resp.tramite_id);
            }

            if (resp.estado === 'requiere_datos') {
                this.pedirDatos(resp, payload);
            } else {
                this.pintarRespuesta(resp);
            }
            if (window.matchMedia('(min-width: 601px)').matches) this.entrada.focus();
        }

        bloquear(activo) {
            this.ocupado = activo;
            this.raiz.classList.toggle('cb-ocupado', activo);
            this.botonEnviar.disabled = activo;
            this.conversacion.setAttribute('aria-busy', activo ? 'true' : 'false');
        }

        agregar(el) {
            this.mensajes.append(el);
            this.conversacion.scrollTop = this.conversacion.scrollHeight;
            return el;
        }

        agregarBot(contenido) {
            const el = this.clonar('bot');
            const cuerpo = this.parte('cuerpo', el);
            const partes = Array.isArray(contenido) ? contenido : [contenido];
            partes.forEach((c) => {
                if (typeof c === 'string') {
                    const p = document.createElement('p');
                    p.textContent = c;
                    cuerpo.append(p);
                } else if (c) {
                    cuerpo.append(c);
                }
            });
            return this.agregar(el);
        }

        agregarUsuario(texto, nota) {
            const el = this.clonar('usuario');
            this.llenar(el, 'texto', texto);
            this.llenar(el, 'nota', nota || null);
            return this.agregar(el);
        }

        agregarSugerencias(opciones, alElegir) {
            if (!opciones.length) return null;
            const el = this.clonar('sugerencias');
            opciones.forEach((o) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'cb-sug';
                b.textContent = o.texto;
                if (o.detalle) {
                    b.classList.add('cb-sug-categoria');
                    const small = document.createElement('small');
                    small.textContent = o.detalle;
                    b.append(small);
                }
                b.addEventListener('click', () => {
                    if (this.ocupado) return;
                    alElegir(o.valor);
                });
                el.append(b);
            });
            return this.agregar(el);
        }

        agregarEspera() {
            const el = this.clonar('espera');
            el.hidden = true;
            this.agregar(el);
            const mostrar = setTimeout(() => {
                el.hidden = false;
                this.conversacion.scrollTop = this.conversacion.scrollHeight;
            }, ESPERA_VISIBLE);
            const lenta = setTimeout(() => {
                this.parte('lento', el).hidden = false;
            }, ESPERA_LENTA);
            return {
                quitar: () => {
                    clearTimeout(mostrar);
                    clearTimeout(lenta);
                    el.remove();
                },
            };
        }

        agregarError(error, reintentar) {
            const el = this.clonar('error');
            const codigo = error.codigo || 'error_interno';
            this.llenar(el, 'error-titulo', TITULOS_ERROR[codigo] || TITULOS_ERROR.error_interno);
            this.llenar(el, 'error-mensaje', error.mensaje || 'Ocurrió un problema inesperado.');
            this.parte('error-icono', el).textContent = ICONO_ERROR[codigo] || '!';
            el.classList.toggle('cb-sistema-suave', ERROR_SUAVE.includes(codigo));

            const boton = this.parte('reintentar', el);
            boton.hidden = !error.reintentable;
            boton.addEventListener('click', () => {
                if (this.ocupado) return;
                el.remove();
                reintentar();
            });

            if (codigo === 'sesion_requerida') {
                const enlace = this.parte('error-enlace', el);
                enlace.textContent = 'Iniciar sesión';
                enlace.href = this.urls.login;
                enlace.hidden = false;
            }
            return this.agregar(el);
        }

        pintarRespuesta(resp, opciones = {}) {
            if (resp.tipo === 'no_entendido') {
                this.agregarBot(resp.mensaje || 'No entendí tu pregunta. Elige una categoría o escribe el nombre del trámite.');
                if (!opciones.sinSugerencias) {
                    this.agregarSugerencias(this.opcionesCategorias(), (c) => this.elegirCategoria(c));
                }
                return;
            }

            const nodo = this.clonar('respuesta');
            this.llenar(nodo, 'titulo', resp.titulo);
            this.llenar(nodo, 'mensaje', resp.mensaje);

            const items = this.parte('items', nodo);
            (resp.items || []).forEach((i) => {
                const li = document.createElement('li');
                li.textContent = i;
                items.append(li);
            });
            items.hidden = !(resp.items || []).length;

            this.pintarDocumentos(nodo, resp.documentos || []);
            this.pintarDatos(nodo, resp.costo, resp.plazo, resp.tipo);
            this.pintarResultado(this.parte('resultado', nodo), resp.resultado, resp.meta);
            this.pintarSolicitudes(nodo, resp.solicitudes || []);
            this.llenar(nodo, 'aviso-texto', resp.aviso);
            this.parte('aviso', nodo).hidden = !resp.aviso;
            this.pintarAcciones(nodo, resp.acciones || []);

            this.agregarBot(nodo);

            if (!opciones.sinSugerencias) {
                const siguientes = (resp.sugerencias || []).map((id) => this.porId.preguntas[id]).filter(Boolean);
                this.agregarSugerencias(siguientes.map((p) => ({
                    id: p.id,
                    texto: p.tramite_id === (this.tramite && this.tramite.id) ? p.texto : `${p.texto} (${this.porId.tramites[p.tramite_id].nombre})`,
                    valor: p,
                })), (p) => {
                    if (!this.tramite || this.tramite.id !== p.tramite_id) this.fijarTramite(p.tramite_id);
                    this.preguntar(p);
                });
            }
        }

        pintarDocumentos(nodo, documentos) {
            const lista = this.parte('documentos', nodo);
            documentos.forEach((d) => {
                const el = this.clonar('documento');
                this.llenar(el, 'doc-nombre', d.nombre);
                this.llenar(el, 'doc-formato', d.formato);
                this.llenar(el, 'doc-descripcion', d.descripcion);
                const etiqueta = this.parte('doc-obligatorio', el);
                const obligacion = OBLIGACIONES[obligacionDe(d)];
                etiqueta.textContent = obligacion.texto;
                etiqueta.classList.remove('cb-etq-vino', 'cb-etq-pendiente');
                if (obligacion.clase) etiqueta.classList.add(obligacion.clase);
                const enlace = this.parte('doc-enlace', el);
                const url = urlSegura(d.url);
                enlace.hidden = !url;
                if (url) enlace.href = url;
                lista.append(el);
            });
            lista.hidden = !documentos.length;
        }

        pintarDatos(nodo, costo, plazo, tipo) {
            const verCosto = !!costo || tipo === 'costo';
            const verPlazo = !!plazo || tipo === 'plazo';
            this.parte('datos', nodo).hidden = !verCosto && !verPlazo;

            this.parte('dato-costo', nodo).hidden = !verCosto;
            if (verCosto) {
                this.llenar(nodo, 'costo-valor', costo ? dinero.format(costo.centavos / 100) : POR_CONFIRMAR);
                this.llenar(nodo, 'costo-moneda', costo ? costo.moneda : null);
                this.parte('costo-nota', nodo).hidden = !!costo;
            }

            const p = plazo || {};
            this.parte('dato-inicio', nodo).hidden = !p.inicio;
            this.parte('dato-fin', nodo).hidden = !verPlazo;
            this.parte('dato-plazo', nodo).hidden = !p.texto;
            if (verPlazo) {
                this.llenar(nodo, 'plazo-inicio', formatearFecha(p.inicio));
                this.llenar(nodo, 'plazo-fin', formatearFecha(p.fin) || POR_CONFIRMAR);
                this.parte('plazo-fin-nota', nodo).hidden = !!p.fin;
                this.llenar(nodo, 'plazo-texto', p.texto);
            }
        }

        pintarResultado(caja, resultado, meta) {
            caja.hidden = !resultado;
            if (!resultado) return;
            const estado = estadoResultado(resultado);

            this.parte('resultado-caja', caja).className = `cb-resultado ${caja.getAttribute(`data-clase-${estado}`) || ''}`;
            this.parte('resultado-icono', caja).textContent = caja.getAttribute(`data-icono-${estado}`) || '';
            this.parte('resultado-titulo', caja).textContent = caja.getAttribute(`data-titulo-${estado}`) || '';

            const motivos = this.parte('resultado-motivos', caja);
            motivos.classList.toggle('cb-motivos', estado !== 'cumple');
            (resultado.motivos || []).forEach((m) => {
                const li = document.createElement('li');
                li.textContent = typeof m === 'string' ? m : m.texto;
                motivos.append(li);
            });

            this.parte('resultado-orientativo', caja).hidden = estado === 'sin_evaluacion' || !!(meta && meta.verificacion === 'institucional');
        }

        pintarSolicitudes(nodo, solicitudes) {
            const lista = this.parte('solicitudes', nodo);
            solicitudes.forEach((s) => {
                const el = this.clonar('solicitud');
                this.llenar(el, 'sol-tramite', s.tramite);
                const estado = this.parte('sol-estado', el);
                estado.textContent = s.estado;
                estado.className = `cb-estado cb-estado-${s.color || 'gris'}`;
                const enlace = this.parte('sol-enlace', el);
                const url = urlSegura(s.url);
                enlace.hidden = !url;
                if (url) enlace.href = url;
                lista.append(el);
            });
            lista.hidden = !solicitudes.length;
        }

        pintarAcciones(nodo, acciones) {
            const caja = this.parte('acciones', nodo);
            acciones.forEach((a) => {
                const url = urlSegura(a.url);
                if (!url) return;
                const enlace = document.createElement('a');
                enlace.className = `cb-btn ${a.tipo === 'iniciar_solicitud' ? 'cb-btn-primario' : 'cb-btn-secundario'}`;
                enlace.href = url;
                enlace.textContent = a.etiqueta;
                caja.append(enlace);
            });
            caja.hidden = !caja.children.length;
        }

        pedirDatos(resp, payload) {
            const form = this.clonar('formulario');
            const contenedor = this.parte('form-campos', form);
            const campos = resp.campos_pendientes || [];

            campos.forEach((c) => contenedor.append(this.crearCampo(c)));

            const burbuja = this.agregarBot([resp.mensaje || 'Para responderte necesito estos datos.', form]);

            const control = {
                bloquear: () => form.querySelectorAll('input, select, button').forEach((e) => { e.disabled = true; }),
                desbloquear: () => form.querySelectorAll('input, select, button').forEach((e) => { e.disabled = false; }),
                marcar: (errores) => this.marcarErrores(form, errores),
                cerrar: () => {
                    const nota = document.createElement('p');
                    nota.className = 'cb-nota';
                    nota.textContent = 'Datos enviados.';
                    form.replaceWith(nota);
                },
            };

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (this.ocupado) return;
                const { valores, errores, resumen } = this.leerFormulario(form, campos);
                if (Object.keys(errores).length) {
                    this.marcarErrores(form, errores);
                    return;
                }
                this.marcarErrores(form, {});
                control.bloquear();
                this.agregarUsuario(resumen || 'Datos enviados', 'Datos declarados por mí');
                this.enviar({ ...payload, origen_datos: this.origenPara(payload), datos_usuario: valores }, { formulario: control });
            });

            const primero = form.querySelector('[data-cb="campo-control"]');
            if (primero && window.matchMedia('(min-width: 601px)').matches) primero.focus();
            return burbuja;
        }

        crearCampo(c) {
            const tipo = this.plantillas[`campo-${c.tipo}`] ? c.tipo : 'texto';
            const el = this.clonar(`campo-${tipo}`);
            const id = `${this.id}-${c.nombre}-${++contador}`;
            const etiqueta = this.parte('campo-etiqueta', el);
            const controles = el.querySelectorAll('[data-cb="campo-control"]');
            const ayuda = this.parte('campo-ayuda', el);
            const error = this.parte('campo-error', el);

            el.dataset.nombre = c.nombre;
            etiqueta.textContent = c.etiqueta;
            if (!c.requerido) {
                const opc = document.createElement('span');
                opc.className = 'cb-opc';
                opc.textContent = ' (opcional)';
                etiqueta.append(opc);
            }
            ayuda.id = `${id}-ayuda`;
            error.id = `${id}-error`;

            controles.forEach((ctrl, i) => {
                ctrl.name = c.nombre;
                ctrl.id = i === 0 ? id : `${id}-${i}`;
                if (c.requerido) ctrl.required = true;
            });
            if (tipo !== 'booleano') etiqueta.htmlFor = id;

            if (tipo === 'numero') {
                if (c.min !== null && c.min !== undefined) controles[0].min = c.min;
                if (c.max !== null && c.max !== undefined) controles[0].max = c.max;
                if (c.min !== null && c.max !== null && c.min !== undefined && c.max !== undefined) {
                    ayuda.textContent = `Entre ${c.min} y ${c.max}.`;
                    ayuda.hidden = false;
                    controles[0].setAttribute('aria-describedby', ayuda.id);
                }
            }

            if (tipo === 'seleccion') {
                (c.opciones || []).forEach((o) => {
                    const opt = document.createElement('option');
                    opt.value = o.valor;
                    opt.textContent = o.etiqueta;
                    controles[0].append(opt);
                });
            }

            if (tipo === 'fecha') {
                controles[0].max = new Date().toISOString().slice(0, 10);
            }

            return el;
        }

        leerFormulario(form, campos) {
            const valores = {};
            const errores = {};
            const resumen = [];

            campos.forEach((c) => {
                const el = form.querySelector(`[data-cb="campo"][data-nombre="${CSS.escape(c.nombre)}"]`);
                const tipo = el.dataset.tipo;
                let crudo;
                if (tipo === 'booleano') {
                    const marcado = el.querySelector('input:checked');
                    crudo = marcado ? marcado.value : '';
                } else {
                    crudo = el.querySelector('[data-cb="campo-control"]').value.trim();
                }

                if (crudo === '') {
                    if (c.requerido) errores[c.nombre] = ['Este dato es obligatorio.'];
                    return;
                }

                if (tipo === 'numero') {
                    const n = Number(crudo.replace(',', '.'));
                    if (Number.isNaN(n)) {
                        errores[c.nombre] = ['Escribe un número.'];
                        return;
                    }
                    if ((c.min !== null && c.min !== undefined && n < c.min) || (c.max !== null && c.max !== undefined && n > c.max)) {
                        errores[c.nombre] = [`Debe estar entre ${c.min} y ${c.max}.`];
                        return;
                    }
                    valores[c.nombre] = n;
                    resumen.push(`${c.etiqueta.replace(/\s*\(.*\)$/, '')}: ${n}`);
                } else if (tipo === 'booleano') {
                    valores[c.nombre] = crudo === '1';
                    resumen.push(`${c.etiqueta.replace(/^¿|\?$/g, '')}: ${crudo === '1' ? 'sí' : 'no'}`);
                } else if (tipo === 'seleccion') {
                    const opcion = (c.opciones || []).find((o) => o.valor === crudo);
                    if (!opcion) {
                        errores[c.nombre] = ['Elige una opción de la lista.'];
                        return;
                    }
                    valores[c.nombre] = crudo;
                    resumen.push(opcion.etiqueta);
                } else if (tipo === 'fecha') {
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(crudo)) {
                        errores[c.nombre] = ['Escribe una fecha válida.'];
                        return;
                    }
                    valores[c.nombre] = crudo;
                    resumen.push(`${c.etiqueta}: ${formatearFecha(crudo)}`);
                } else {
                    valores[c.nombre] = crudo.slice(0, 300);
                    resumen.push(crudo.slice(0, 60));
                }
            });

            return { valores, errores, resumen: resumen.join(' · ') };
        }

        marcarErrores(form, errores) {
            let primero = null;
            form.querySelectorAll('[data-cb="campo"]').forEach((el) => {
                const mensajes = errores[el.dataset.nombre];
                const error = this.parte('campo-error', el);
                const controles = el.querySelectorAll('[data-cb="campo-control"]');
                el.classList.toggle('cb-campo-error', !!mensajes);
                error.textContent = mensajes ? mensajes[0] : '';
                error.hidden = !mensajes;
                controles.forEach((ctrl) => {
                    if (mensajes) {
                        ctrl.setAttribute('aria-invalid', 'true');
                        ctrl.setAttribute('aria-describedby', error.id);
                    } else {
                        ctrl.removeAttribute('aria-invalid');
                    }
                });
                if (mensajes && !primero) primero = controles[0];
            });
            this.parte('form-error', form).hidden = !primero;
            if (primero) primero.focus();
        }

        pintarLista(lista, filtro) {
            if (!this.catalogo) return;
            const palabras = normalizar(filtro).split(/\s+/).filter(Boolean);
            const coincide = (t) => {
                const texto = normalizar([t.nombre, ...(t.claves || [])].join(' '));
                return palabras.every((p) => texto.includes(p));
            };

            lista.replaceChildren();
            let total = 0;
            this.catalogo.categorias.forEach((cat) => {
                const tramites = this.catalogo.tramites.filter((t) => t.categoria_id === cat.id && coincide(t));
                if (!tramites.length) return;
                total += tramites.length;

                const titulo = document.createElement('p');
                titulo.className = 'cb-grupo';
                titulo.textContent = cat.nombre;
                const ul = document.createElement('ul');
                ul.className = 'cb-lista';
                tramites.forEach((t) => {
                    const li = document.createElement('li');
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.textContent = t.nombre;
                    b.dataset.tramiteId = t.id;
                    b.setAttribute('aria-current', this.tramite && this.tramite.id === t.id ? 'true' : 'false');
                    b.addEventListener('click', () => {
                        if (this.ocupado) return;
                        this.elegirTramite(t.id);
                    });
                    li.append(b);
                    ul.append(li);
                });
                lista.append(titulo, ul);
            });

            if (!total) {
                const vacio = document.createElement('p');
                vacio.className = 'cb-lista-vacia';
                vacio.textContent = 'No encontré trámites con ese nombre. Prueba con otra palabra.';
                lista.append(vacio);
            }
        }

        abrirBuscar() {
            const lateral = this.raiz.querySelector('.cb-lateral');
            if (lateral && getComputedStyle(lateral).display !== 'none') {
                this.parte('buscar', lateral).focus();
                return;
            }
            this.panel.hidden = false;
            this.parte('buscar', this.panel).focus();
        }

        cerrarBuscar() {
            if (this.panel.hidden) return;
            this.panel.hidden = true;
            this.parte('cambiar').focus();
        }

        nuevaConsulta() {
            if (this.ocupado || !this.catalogo) return;
            this.mensajes.replaceChildren();
            this.fijarTramite(null);
            this.bienvenida();
        }
    }

    function iniciar() {
        document.querySelectorAll('[data-chatbot]').forEach((raiz) => {
            if (!raiz.chatbot) raiz.chatbot = new Chat(raiz);
        });

        document.addEventListener('click', (e) => {
            const disparador = e.target.closest('[data-abrir-chatbot]');
            if (!disparador) return;
            const raiz = document.querySelector('.cb-modo-flotante[data-chatbot]') || document.querySelector('[data-chatbot]');
            if (!raiz || !raiz.chatbot) return;
            e.preventDefault();
            raiz.chatbot.abrir(disparador.dataset.abrirChatbot || null);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
