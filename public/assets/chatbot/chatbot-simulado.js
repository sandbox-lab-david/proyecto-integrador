(function () {
    'use strict';

    const KB = 'avance-001';
    const CLAVE_HISTORIAL = 'cb-simulado-historial';

    const categorias = [
        { id: 'evaluaciones', nombre: 'Evaluaciones', descripcion: 'Recuperación, gracia, supletorio' },
        { id: 'homologacion', nombre: 'Homologación', descripcion: 'Reconocer materias aprobadas' },
        { id: 'cambios_academicos', nombre: 'Cambios académicos', descripcion: 'Carrera, modalidad o malla' },
        { id: 'registro', nombre: 'Registro', descripcion: 'Fuera de plazo, tercer registro' },
        { id: 'retiros_continuidad', nombre: 'Retiros y continuidad', descripcion: 'Retiros y reincorporación' },
        { id: 'apoyo_academico', nombre: 'Apoyo académico', descripcion: 'Ayudantía, person to person' },
    ];

    const tramites = [
        ['examen_recuperacion', 'Examen de recuperación', 'evaluaciones', ['recuperacion', 'reprobe', 'recuperar']],
        ['examen_gracia', 'Examen de gracia', 'evaluaciones', ['gracia', 'faltas']],
        ['examen_supletorio', 'Examen supletorio', 'evaluaciones', ['supletorio']],
        ['examen_suficiencia', 'Examen de suficiencia', 'evaluaciones', ['suficiencia']],
        ['recalificacion', 'Recalificación de examen', 'evaluaciones', ['recalificacion', 'recalificar']],
        ['homologacion', 'Homologación', 'homologacion', ['homologar', 'homologacion', 'convalidar']],
        ['alcance_homologacion', 'Alcance de homologación', 'homologacion', ['alcance']],
        ['cambio_carrera', 'Cambio de carrera', 'cambios_academicos', ['cambio de carrera', 'cambiarme de carrera']],
        ['cambio_modalidad', 'Cambio de modalidad', 'cambios_academicos', ['modalidad']],
        ['cambio_malla', 'Cambio de malla', 'cambios_academicos', ['malla', 'pensum']],
        ['registro_extemporaneo', 'Registro extemporáneo', 'registro', ['registro extemporaneo', 'fuera de plazo']],
        ['tercer_registro', 'Tercer registro', 'registro', ['tercer registro', 'tercera matricula', 'tercera']],
        ['incompleto', 'Incompleto', 'registro', ['incompleto']],
        ['retiro_materia', 'Retiro de materia', 'retiros_continuidad', ['retiro de materia', 'retirar materia', 'retirarme de una materia']],
        ['retiro_carrera', 'Retiro de carrera', 'retiros_continuidad', ['retiro de carrera']],
        ['retiro_universidad', 'Retiro de universidad', 'retiros_continuidad', ['retiro de universidad', 'dejar la universidad']],
        ['retiro_extemporaneo', 'Retiro extemporáneo', 'retiros_continuidad', ['retiro extemporaneo']],
        ['reincorporacion_carrera', 'Reincorporación a carrera', 'retiros_continuidad', ['reincorporacion', 'volver a estudiar']],
        ['ayudante_catedra', 'Ayudante de cátedra', 'apoyo_academico', ['ayudante', 'ayudantia']],
        ['person_to_person', 'Person to Person', 'apoyo_academico', ['person to person', 'tutoria']],
    ].map(([id, nombre, categoria_id, claves]) => ({ id, nombre, categoria_id, claves }));

    const materias = [
        { valor: 'UFIS101', etiqueta: 'Física I' },
        { valor: 'UMAT102', etiqueta: 'Cálculo I' },
        { valor: 'UCOM253', etiqueta: 'Comunicación oral' },
    ];

    const camposGenerales = [
        { nombre: 'materia_id', etiqueta: 'Materia', tipo: 'seleccion', requerido: true, min: null, max: null, opciones: materias },
        { nombre: 'gpa', etiqueta: 'Tu GPA (0 a 100)', tipo: 'numero', requerido: true, min: 0, max: 100, opciones: [] },
    ];

    const camposRecuperacion = [
        camposGenerales[0],
        { nombre: 'nota', etiqueta: 'Nota final de la materia (0 a 100)', tipo: 'numero', requerido: true, min: 0, max: 100, opciones: [] },
        camposGenerales[1],
        { nombre: 'fraude', etiqueta: '¿Tuviste algún caso de fraude académico en esta materia?', tipo: 'booleano', requerido: true, min: null, max: null, opciones: [] },
        { nombre: 'ultima_recuperacion', etiqueta: 'Fecha de tu último examen de recuperación', tipo: 'fecha', requerido: false, min: null, max: null, opciones: [] },
    ];

    const intenciones = [
        ['descripcion', 'consultar_descripcion', '¿Qué es este trámite?'],
        ['requisitos', 'consultar_requisitos', '¿Qué requisitos tiene?'],
        ['documentos', 'consultar_documentos', '¿Qué documentos necesito?'],
        ['costo', 'consultar_costo', '¿Cuánto cuesta?'],
        ['plazo', 'consultar_plazo', '¿Hasta cuándo puedo pedirlo?'],
        ['elegibilidad', 'evaluar_elegibilidad', '¿Puedo solicitar este trámite?'],
    ];

    const preguntas = tramites.flatMap((t) => intenciones.map(([sufijo, intencion, texto]) => ({
        id: `${t.id}__${sufijo}`,
        tramite_id: t.id,
        texto,
        intencion,
        origenes_permitidos: sufijo === 'elegibilidad' ? ['manual'] : ['ninguno'],
        campos: sufijo === 'elegibilidad' ? (t.id === 'examen_recuperacion' ? camposRecuperacion : camposGenerales) : [],
    })));

    const recuperacion = {
        descripcion: 'Examen de recuperación para una asignatura reprobada.',
        requisitos: [
            'La materia fue reprobada y no hubo fraude académico.',
            'GPA de al menos 75.',
            'No haber rendido otro examen de recuperación en los últimos 365 días.',
            'Una sola materia por solicitud.',
        ],
        documentos: [
            { id: 'solicitud_recuperacion', nombre: 'Solicitud de examen de recuperación', descripcion: 'Formulario firmado por el estudiante.', obligatorio: true, formato: 'PDF', url: '/formatos/examen-recuperacion.pdf' },
            { id: 'registro_calificaciones', nombre: 'Registro de calificaciones', descripcion: 'Evidencia de la materia reprobada.', obligatorio: true, formato: 'PDF', url: null },
        ],
        costo: { centavos: 2000, moneda: 'USD' },
        plazo: { texto: 'Se solicita al finalizar el semestre. El examen se rinde hasta la primera semana del periodo siguiente.', inicio: '2027-01-15', fin: null },
        aviso: 'La plantilla admite varias materias, pero la regla informada dice una sola. La facultad decide en cada caso.',
    };

    const yaFallaron = new Set();

    const normalizar = (s) => (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    const esperar = (ms) => new Promise((r) => setTimeout(r, ms));
    const tramite = (id) => tramites.find((t) => t.id === id);
    const pregunta = (id) => preguntas.find((p) => p.id === id);

    function base(t, tipo) {
        return {
            version: 2,
            estado: 'respondido',
            tipo,
            tramite_id: t ? t.id : null,
            titulo: null,
            mensaje: null,
            items: [],
            documentos: [],
            costo: null,
            plazo: null,
            resultado: null,
            aviso: null,
            campos_pendientes: [],
            acciones: [],
            sugerencias: [],
            solicitudes: [],
            meta: { kb_version: KB, origen_datos: 'ninguno', verificacion: 'no_aplica' },
        };
    }

    function error(status, codigo, mensaje, reintentable, campos) {
        return { status, body: { version: 2, error: { codigo, mensaje, reintentable, campos: campos || {} } } };
    }

    function iniciar(t) {
        return { tipo: 'iniciar_solicitud', etiqueta: 'Iniciar esta solicitud →', url: `/estudiante/solicitudes/nueva?tramite=${t.id}` };
    }

    function siguientes(t, actual) {
        return intenciones.map(([sufijo]) => `${t.id}__${sufijo}`).filter((id) => !id.endsWith(actual)).slice(0, 3);
    }

    function informativa(t, sufijo) {
        const r = base(t, sufijo);
        const listo = t.id === 'examen_recuperacion';
        r.sugerencias = siguientes(t, sufijo);
        r.acciones = [iniciar(t)];

        if (!listo) {
            r.titulo = t.nombre;
            r.mensaje = 'Todavía no hay información confirmada para este trámite.';
            r.aviso = 'La facultad aún no confirma los requisitos de este trámite. Puedes presentar la solicitud y la revisan caso por caso.';
            return r;
        }

        if (sufijo === 'descripcion') {
            r.titulo = t.nombre;
            r.mensaje = recuperacion.descripcion;
        } else if (sufijo === 'requisitos') {
            r.titulo = 'Requisitos';
            r.mensaje = 'Para el examen de recuperación necesitas cumplir esto:';
            r.items = recuperacion.requisitos;
            r.aviso = recuperacion.aviso;
        } else if (sufijo === 'documentos') {
            r.titulo = 'Documentos requeridos';
            r.mensaje = 'Estos son los documentos registrados para este trámite.';
            r.documentos = recuperacion.documentos;
        } else if (sufijo === 'costo') {
            r.titulo = 'Costo del trámite';
            r.mensaje = 'Se paga una sola vez al registrar la solicitud.';
            r.costo = recuperacion.costo;
        } else if (sufijo === 'plazo') {
            r.titulo = 'Plazo';
            r.plazo = recuperacion.plazo;
        }
        return r;
    }

    function evaluar(t, datos, campos) {
        const errores = {};
        campos.forEach((c) => {
            const v = datos[c.nombre];
            if (c.tipo === 'numero' && v !== undefined && v !== null && (v < c.min || v > c.max)) {
                errores[c.nombre] = [`Debe estar entre ${c.min} y ${c.max}.`];
            }
        });
        if (Object.keys(errores).length) {
            return error(422, 'datos_invalidos', 'Revisa los campos.', false, errores);
        }

        const r = base(t, 'elegibilidad');
        r.titulo = 'Resultado de tu consulta';
        r.meta = { kb_version: KB, origen_datos: 'manual', verificacion: 'declarada' };
        r.acciones = [iniciar(t)];
        const motivos = [];

        if (t.id !== 'examen_recuperacion') {
            r.resultado = { estado: 'revision_manual', motivos: [{ codigo: 'regla_pendiente', texto: 'La facultad todavía no confirma la regla de este trámite.' }] };
            return { status: 200, body: r };
        }

        if (datos.fraude === true) motivos.push({ codigo: 'fraude', texto: 'Hubo un caso de fraude académico en la materia.' });
        if (datos.nota >= 70) motivos.push({ codigo: 'materia_aprobada', texto: `Tu nota de ${datos.nota} no corresponde a una materia reprobada.` });
        if (datos.gpa < 75) motivos.push({ codigo: 'gpa_bajo', texto: `Tu GPA es ${datos.gpa} y el mínimo es 75.` });
        if (datos.ultima_recuperacion) {
            const dias = (Date.now() - new Date(datos.ultima_recuperacion).getTime()) / 86400000;
            if (dias < 365) motivos.push({ codigo: 'recuperacion_reciente', texto: 'Rendiste otro examen de recuperación hace menos de un año.' });
        }

        if (motivos.length) {
            r.resultado = { estado: 'no_cumple', motivos };
            r.acciones = [{ tipo: 'iniciar_solicitud', etiqueta: 'Presentar de todas formas', url: `/estudiante/solicitudes/nueva?tramite=${t.id}` }];
            r.sugerencias = ['examen_gracia__descripcion'];
        } else {
            r.resultado = {
                estado: 'cumple',
                motivos: [
                    { codigo: 'gpa_ok', texto: `Tu GPA de ${datos.gpa} supera el mínimo de 75.` },
                    { codigo: 'sin_recuperacion', texto: 'No registras otro examen de recuperación en el último año.' },
                ],
            };
        }
        return { status: 200, body: r };
    }

    function pedirDatos(t, campos) {
        const r = base(t, 'elegibilidad');
        r.estado = 'requiere_datos';
        r.mensaje = 'Para revisarlo necesito algunos datos. Escríbelos tal como aparecen en tu registro de notas.';
        r.campos_pendientes = campos;
        r.meta = { kb_version: null, origen_datos: 'manual', verificacion: 'declarada' };
        return { status: 200, body: r };
    }

    function detectarTramite(texto) {
        const n = normalizar(texto);
        const porClave = tramites
            .flatMap((t) => t.claves.map((c) => ({ t, c })))
            .sort((a, b) => b.c.length - a.c.length)
            .find(({ c }) => n.includes(c));
        return porClave ? porClave.t : tramites.find((t) => n.includes(normalizar(t.nombre)));
    }

    function detectarIntencion(texto) {
        const n = normalizar(texto);
        if (/documento|papeles|adjuntar/.test(n)) return 'documentos';
        if (/cuesta|costo|precio|pagar|valor/.test(n)) return 'costo';
        if (/plazo|cuando|hasta|fecha/.test(n)) return 'plazo';
        if (/puedo|cumplo|aplico/.test(n)) return 'elegibilidad';
        if (/requisito|necesito|piden/.test(n)) return 'requisitos';
        if (/que es|de que trata|en que consiste/.test(n)) return 'descripcion';
        return null;
    }

    function texto(payload) {
        const n = normalizar(payload.texto);

        if (/estado|mi solicitud|mis solicitudes/.test(n)) {
            const r = base(null, 'estado_solicitud');
            r.titulo = 'Tus solicitudes';
            r.mensaje = 'Estas son las solicitudes que tienes registradas.';
            r.solicitudes = [
                { id: 12, tramite: 'Examen de recuperación', estado: 'En revisión de la facultad', color: 'ambar', url: '/estudiante/solicitudes/12' },
                { id: 8, tramite: 'Retiro de materia', estado: 'Aprobada', color: 'verde', url: '/estudiante/solicitudes/8' },
            ];
            return { status: 200, body: r };
        }

        const t = detectarTramite(payload.texto) || tramite(payload.tramite_id);
        const sufijo = detectarIntencion(payload.texto);

        if (!t || (!sufijo && !detectarTramite(payload.texto))) {
            const r = base(null, 'no_entendido');
            r.mensaje = 'No entendí tu pregunta. Elige una categoría o escribe el nombre del trámite, por ejemplo «supletorio» o «retiro de materia».';
            r.meta.kb_version = KB;
            return { status: 200, body: r };
        }

        if (sufijo === 'elegibilidad') {
            const p = pregunta(`${t.id}__elegibilidad`);
            return payload.datos_usuario && Object.keys(payload.datos_usuario).length
                ? evaluar(t, payload.datos_usuario, p.campos)
                : pedirDatos(t, p.campos);
        }

        return { status: 200, body: informativa(t, sufijo || 'descripcion') };
    }

    function mensaje(payload) {
        const n = normalizar(payload.texto);
        const clave = JSON.stringify(payload);

        if (payload.texto && /error|motor|sesion|lento/.test(n) && !yaFallaron.has(clave)) {
            yaFallaron.add(clave);
            if (n.includes('504')) return error(504, 'tiempo_agotado', 'La consulta tardó demasiado. Prueba otra vez en unos segundos.', true);
            if (n.includes('500')) return error(500, 'error_interno', 'No es un problema tuyo. Si se repite, avisa a la secretaría de la facultad.', false);
            if (n.includes('sesion') || n.includes('401')) return error(401, 'sesion_requerida', 'Tu sesión terminó. Vuelve a entrar para consultar tus solicitudes.', false);
            if (n.includes('motor') || n.includes('503')) return error(503, 'motor_no_disponible', 'El servicio de consultas no está disponible en este momento. Tu pregunta no se perdió.', true);
        }

        if (payload.texto) return texto(payload);

        const p = pregunta(payload.pregunta_id);
        const t = tramite(payload.tramite_id);
        if (!p || !t || p.tramite_id !== t.id || p.intencion !== payload.intencion) {
            return error(422, 'datos_invalidos', 'La pregunta no coincide con el catálogo.', false);
        }

        if (p.intencion === 'evaluar_elegibilidad') {
            return Object.keys(payload.datos_usuario || {}).length
                ? evaluar(t, payload.datos_usuario, p.campos)
                : pedirDatos(t, p.campos);
        }

        return { status: 200, body: informativa(t, p.id.split('__')[1]) };
    }

    function leerHistorial() {
        try {
            return JSON.parse(sessionStorage.getItem(CLAVE_HISTORIAL)) || [];
        } catch (e) {
            return [];
        }
    }

    function guardarHistorial(mensajes) {
        try {
            sessionStorage.setItem(CLAVE_HISTORIAL, JSON.stringify(mensajes));
        } catch (e) {
            return;
        }
    }

    function textoUsuario(payload) {
        if (payload.texto) return payload.texto;
        const p = pregunta(payload.pregunta_id);
        return p ? p.texto : '';
    }

    async function pedir(metodo, url, cuerpo) {
        const ruta = new URL(url, location.href).pathname;
        const lento = cuerpo && /lento/.test(normalizar(cuerpo.texto));
        await esperar(lento ? 4500 : 450 + Math.random() * 450);

        if (ruta.endsWith('/catalogo')) {
            return { status: 200, body: { version: 2, categorias, tramites, preguntas } };
        }

        if (ruta.endsWith('/historial')) {
            if (metodo === 'DELETE') {
                guardarHistorial([]);
                return { status: 200, body: { version: 2, mensajes: [] } };
            }
            return { status: 200, body: { version: 2, mensajes: leerHistorial() } };
        }

        if (ruta.endsWith('/mensaje')) {
            const r = mensaje(cuerpo);
            if (r.status === 200) {
                const historial = leerHistorial();
                if (!cuerpo.datos_usuario || !Object.keys(cuerpo.datos_usuario).length) {
                    historial.push({ emisor: 'usuario', texto: textoUsuario(cuerpo), respuesta: null });
                }
                if (r.body.estado === 'respondido') {
                    historial.push({ emisor: 'bot', texto: r.body.mensaje || r.body.titulo || '', respuesta: r.body });
                }
                guardarHistorial(historial);
            }
            return r;
        }

        return error(404, 'error_interno', 'Ruta no encontrada.', false);
    }

    window.ChatbotSimulado = { pedir };
})();
