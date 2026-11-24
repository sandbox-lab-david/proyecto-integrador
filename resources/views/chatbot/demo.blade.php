<x-chat.pagina titulo="Solicitudes">
    <div class="cb-demo">
        <p class="cb-demo-nota">Página de ejemplo para ver el asistente sobre una ficha del portal.</p>

        <p class="cb-eyebrow">Antes de empezar / Evaluaciones</p>
        <h1>Examen de recuperación</h1>
        <p class="cb-demo-intro">Solicitar un examen de recuperación para una asignatura reprobada.</p>

        <div class="cb-demo-cajas">
            <section class="cb-demo-caja">
                <h2>Cuándo aplica</h2>
                <ul class="cb-check">
                    <li>La materia fue reprobada y no hubo fraude académico.</li>
                    <li>El GPA aplicable es de al menos 75.</li>
                    <li>No has rendido otro examen de recuperación en los últimos 365 días.</li>
                </ul>
            </section>
            <section class="cb-demo-caja">
                <h2>Qué necesitas</h2>
                <ul class="cb-check">
                    <li>Registro de calificaciones o evidencia de la materia reprobada.</li>
                </ul>
            </section>
        </div>

        <div class="cb-acciones">
            <a class="cb-btn cb-btn-primario" href="#">Continuar con la solicitud →</a>
            <button type="button" class="cb-btn cb-btn-secundario" data-abrir-chatbot="examen_recuperacion">Consultar chatbot</button>
        </div>
    </div>

    <x-chat-widget modo="flotante" />
</x-chat.pagina>
