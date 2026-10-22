# Manual corto · Módulo 1: Administración académica

Manual por perfil (RNF-9) de lo que hace el Grupo 1. Todas las pantallas están en español y funcionan en celular.

| Perfil | Qué hace en el Módulo 1 |
|---|---|
| [Administrador](#administrador) | Mantiene los catálogos, el personal y las importaciones del SIAC |
| [Personal](#personal-asistente-director-consulta-docente) | Solo inicia sesión; su trabajo está en otros módulos |
| [Estudiante](#estudiante) | Crea su contraseña la primera vez |

---

## Administrador

Entra con su correo y, al iniciar sesión, llega a **Inicio** (`/administracion`): conteos, periodo activo y accesos a todas las secciones. La campana de arriba muestra sus avisos, por ejemplo cuando termina una importación grande.

### Estructura académica

| Pantalla | Para qué | Reglas que debe saber |
|---|---|---|
| **Facultades** | Crear, editar, activar o desactivar facultades y su correo de notificaciones | No se puede desactivar una facultad que tiene carreras activas |
| **Carreras** | Crear y editar carreras: facultad, código, nivel, modalidades y si admiten tercer registro | Debe tener al menos una modalidad. No se puede quitar una modalidad que tiene estudiantes inscritos. No se puede activar una carrera si su facultad está desactivada |
| **Mallas** | Versiones del plan de estudios de cada carrera. Con **Materias** se agregan o quitan materias por nivel | Solo una malla vigente por carrera: al marcar una, la anterior deja de serlo |
| **Periodos académicos** | Crear y editar periodos; **Activar** uno | Solo uno activo a la vez. La "semana del primer parcial" se usa para el costo del retiro de materia |
| **Materias** | Catálogo de asignaturas con créditos y categoría | Desactivar en vez de borrar: una materia desactivada no aparece en formularios nuevos, pero sigue en las solicitudes antiguas |
| **Paralelos y docentes** | Abrir un paralelo de una materia en un periodo, con docente y cupo opcionales | Una materia no puede repetir el nombre de paralelo en el mismo periodo. Un paralelo solo se borra si ninguna solicitud lo usa |

Todas las listas tienen **buscador** y muestran **15 filas por página**. **Editar** llena el mismo formulario de la izquierda; **Cancelar** lo vacía.

### Personas

| Pantalla | Para qué | Reglas que debe saber |
|---|---|---|
| **Personal** | Registrar asistentes, directores, docentes, administradores y personal de consulta, con sus roles y facultades | El correo debe ser @uees.edu.ec y el código de empleado es obligatorio. Una **asistente** necesita al menos una facultad, porque eso decide qué solicitudes ve. Con **"Puede entrar al sistema"** se le crea una cuenta y se le envía un correo para que cree su contraseña. Un docente puede no tener cuenta |
| **Directores de carrera** | Asignar quién dirige cada carrera y desde cuándo | Al asignar uno nuevo, la etapa del anterior se cierra el día antes. **Terminar hoy** cierra una etapa sin poner a nadie. El historial no se borra |
| **Cuentas de acceso** | Ver todas las cuentas (personal y estudiantes), activarlas o desactivarlas y **enviar el enlace de contraseña** | Una cuenta desactivada no puede iniciar sesión. Nadie puede desactivarse a sí mismo |
| **Roles** | Ver los 5 roles y cuántas personas tiene cada uno | Solo lectura: los roles se dan o quitan en **Personal** |

### Importaciones SIAC

1. Entra a **Importaciones SIAC** y descarga la **plantilla** del tipo que vas a cargar. También hay un **ejemplo** con datos ficticios.
2. Llena la plantilla. Las columnas con `*` son obligatorias. Las celdas vienen en formato texto para que no se pierdan los ceros a la izquierda.
3. Sube el archivo (.xlsx, .xls o .csv, hasta 10 MB) y revisa la **vista previa**:
   - **Nueva:** se va a crear.
   - **Se actualiza:** ya existe ese código y se van a reemplazar sus datos.
   - **Error:** no se guarda. Se muestra la fila y el motivo.
4. **Confirmar** guarda las filas buenas. **Cancelar** borra el archivo sin guardar nada.
5. En **Historial** queda quién importó, cuándo y cuántas filas se crearon, actualizaron o rechazaron. En el detalle se pueden **descargar los errores en CSV** para corregirlos y volver a subir el archivo.

**Orden recomendado,** para que las referencias existan: carreras → materias → mallas → docentes → paralelos → estudiantes.

**Archivos grandes:** si el archivo tiene más de 500 filas, se procesa **en segundo plano** y llega un aviso a la campana al terminar. Para eso debe estar corriendo `php artisan queue:work` en el servidor.

Al importar estudiantes se crea su **cuenta de acceso** (con su correo institucional) y su **inscripción** en la carrera, la modalidad y la malla. No se les envía correo: ellos crean su contraseña la primera vez (ver [Estudiante](#estudiante)).

| Tipo | Columnas (* = obligatoria) |
|---|---|
| Carreras | `facultad_codigo*`, `codigo*`, `nombre*`, `nivel`, `admite_tercer_registro` (si/no), `modalidades*` (separadas por `;`) |
| Materias | `codigo*`, `nombre*`, `creditos*`, `categoria` |
| Mallas | `carrera_codigo*`, `version*`, `vigente` (si/no), `materia_codigo*`, `nivel*` (una fila por materia) |
| Docentes | `codigo*`, `nombres*`, `apellidos*`, `correo*`, `facultad_codigo` |
| Paralelos | `materia_codigo*`, `periodo_codigo*`, `paralelo*`, `docente_codigo`, `cupo` |
| Estudiantes | `codigo*` (10 caracteres), `cedula`, `nombres*`, `apellidos*`, `correo*` (@uees.edu.ec), `celular`, `carrera_codigo*`, `modalidad*`, `malla_version` |

---

## Personal (asistente, director, consulta, docente)

1. El administrador lo registra en **Personal** con la casilla "Puede entrar al sistema".
2. Le llega un correo con un enlace para **crear su contraseña**. Si no le llegó o venció, el administrador puede reenviarlo desde **Cuentas de acceso**, o la persona misma puede usar **"¿Olvidaste tu contraseña?"** en el login.
3. Al iniciar sesión llega a la página de su rol:

| Rol | Llega a |
|---|---|
| Asistente o director | Bandeja de revisión (Módulo 3) |
| Consulta | "Tu cuenta aún no tiene un módulo asignado" |
| Docente | Normalmente no tiene cuenta. Si se la dan, llega a "Tu cuenta aún no tiene un módulo asignado" |

Si su cuenta fue desactivada, el login muestra **"Tu cuenta está desactivada. Comunícate con la administración académica."**

---

## Estudiante

Los estudiantes **no se registran**: su cuenta se crea cuando la administración importa el archivo del SIAC (RF-1.6).

**La primera vez:**

1. En el login, pulsa **"¿Primera vez? … Crea tu contraseña"**.
2. Escribe tu correo institucional (`nombre@uees.edu.ec`).
3. Abre el correo que te llega y elige tu contraseña.
4. Inicia sesión: llegarás al catálogo de trámites (Módulo 2).

Si aparece **"No encontramos una cuenta con ese correo"**, todavía no te han importado: comunícate con la administración académica de tu facultad.
