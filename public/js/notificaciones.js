(() => {
    'use strict';

    document.querySelectorAll('[data-campana-notificaciones]').forEach((campana) => {
        const resumen = campana.querySelector('summary');
        const contador = campana.querySelector('[data-contador]');
        const lista = campana.querySelector('[data-lista]');
        const estado = campana.querySelector('[data-estado]');
        const todas = campana.querySelector('[data-leer-todas]');
        let cargando = false;

        async function cargar() {
            if (cargando) return;
            cargando = true;
            try {
                const respuesta = await fetch(campana.dataset.resumen, {
                    headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store',
                });
                if (!respuesta.ok) throw new Error('No disponible');
                const datos = await respuesta.json();
                contador.textContent = datos.sin_leer > 99 ? '99+' : String(datos.sin_leer);
                contador.hidden = datos.sin_leer === 0;
                resumen.setAttribute('aria-label', `Notificaciones: ${datos.sin_leer} sin leer`);
                lista.replaceChildren();
                datos.notificaciones.forEach((aviso) => {
                    const item = document.createElement('li');
                    item.className = aviso.leida ? '' : 'aviso-sin-leer';
                    const formulario = document.createElement('form');
                    formulario.method = 'POST';
                    formulario.action = aviso.abrir;
                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = campana.dataset.csrf;
                    const boton = document.createElement('button');
                    boton.type = 'submit';
                    boton.className = 'aviso-abrir';
                    const titulo = document.createElement('b');
                    titulo.textContent = aviso.titulo;
                    const mensaje = document.createElement('span');
                    mensaje.textContent = aviso.mensaje;
                    const fecha = document.createElement('small');
                    fecha.textContent = `${aviso.leida ? 'Leída' : 'Sin leer'} · ${aviso.fecha}`;
                    boton.append(titulo, mensaje, fecha);
                    formulario.append(token, boton);
                    item.append(formulario);
                    lista.append(item);
                });
                estado.textContent = datos.notificaciones.length ? '' : 'No tienes notificaciones.';
                todas.querySelector('button').disabled = datos.sin_leer === 0;
            } catch {
                estado.textContent = 'No se pudieron cargar los avisos. Vuelve a abrir la campana para reintentar.';
            } finally {
                cargando = false;
            }
        }

        campana.addEventListener('toggle', () => { if (campana.open) cargar(); });
        campana.addEventListener('keydown', (evento) => {
            if (evento.key === 'Escape') {
                campana.open = false;
                resumen.focus();
            }
        });
        // La lectura usa formularios POST normales y funciona también sin JavaScript.
        document.addEventListener('visibilitychange', () => { if (!document.hidden) cargar(); });
        cargar();
    });
})();
