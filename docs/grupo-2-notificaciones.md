# Notificaciones del portal (RF-2.15)

Implementado por Grupo 2:

- `App\Services\Notificaciones\NotificacionService`: contratos C52 y C53.
- `NotificacionPortal`: canales Laravel `database` y `mail`, en cola después del commit, tres intentos.
- `<x-campana-notificaciones>`: contador, últimos 20 avisos, acceso al historial paginado, lectura individual y colectiva. Incluido en catálogo, formulario y encabezado común de las fichas y solicitudes. Se puede reutilizar en otros perfiles.
- `/notificaciones`: requiere autenticación; todas las consultas y modificaciones se limitan a las notificaciones del usuario conectado. Abrir un aviso utiliza POST con CSRF. La página usa el marco común de G1 (`<x-layouts.app>`): al estudiante le muestra el módulo «Portal del estudiante» con `config('menu_estudiante')`, que incluye la entrada «Notificaciones»; al personal, que llega desde la campana de la barra superior de G1, el marco sin el menú del portal.

## Conexión con los cambios de estado del Grupo 3

El servicio no modifica el estado ni crea transiciones por su cuenta. Dentro de la transacción que guarda la transición, después de actualizar la solicitud, llamar una sola vez:

```php
app(\App\Services\Notificaciones\NotificacionService::class)
    ->notificarCambioEstado($solicitud, $nuevoEstado, $observacionVisibleParaElEstudiante);
```

La observación debe ser la pública, nunca una nota interna. El destinatario se resuelve desde la inscripción de la solicitud, no desde el operador que realiza el cambio. Se lee por `carrera_estudiante.estudiante_id` → `estudiantes.user_id`. Si no existe un propietario identificable, se produce un error para que no se anuncie un aviso enviado a nadie.

Los trabajos se despachan únicamente al confirmar la transacción. Un rollback descarta los avisos pendientes. El servicio de transiciones todavía debe incorporar esta llamada: agregar este código no activa automáticamente las notificaciones de todos los cambios de estado. La transición también debe impedir que un reintento del mismo envío cree una segunda transición y aviso.

Para avisos generales a usuarios registrados:

```php
app(\App\Services\Notificaciones\NotificacionService::class)
    ->notificarUsuarios($usuarios, 'Título', 'Mensaje', route('solicitudes.index'));
```

Los enlaces deben pertenecer al portal. Los destinatarios repetidos se notifican una sola vez por llamada.

## Condiciones para probar el flujo real

1. Aplicar las migraciones pendientes en el entorno de desarrollo, incluida la tabla `notifications` ya existente en el proyecto.
2. Tener una sesión autenticada del Módulo 1 y una inscripción asociada al propietario. Las pantallas de demostración sin sesión no muestran la campana.
3. Conectar la llamada del Grupo 3 y el detalle de solicitudes con sus datos reales.
4. Configurar el correo y la URL pública de la aplicación en el entorno correspondiente. Si la cola usa `database`, ejecutar el worker (`php artisan queue:work`) para procesar tanto el aviso como el correo. Revisar los trabajos fallidos si el proveedor de correo no responde.

Esta implementación no modifica `.env`, no ejecuta migraciones sobre la base local y no envía correos reales durante las pruebas. `NotificacionesTest` comprueba ambos canales con correo en memoria, transacciones, acceso por propietario y lectura de avisos; `EnvioSolicitudTest` comprueba también que GPA `0` se conserve como `0.00`.
