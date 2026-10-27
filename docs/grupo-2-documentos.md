# Documentos del portal del estudiante

## Funcionamiento actual

Las 20 fichas abren el formulario general. Al confirmar la declaración,
se descarga la plantilla DOCX del trámite completada por `DocumentoSolicitud`.
DOM y ZipArchive preparan marcadores en una copia de las plantillas originales;
`WordService::rellenarPlantilla` realiza el llenado definitivo con PhpWord.
Los archivos temporales de preparación y llenado se eliminan al terminar.
El formulario permite descargar Word o PDF. LibreOffice convierte el archivo
en el servidor; el estudiante no necesita instalarlo. Si se descarga en PDF,
ese PDF se conserva junto al Word (`documentos_generados.ruta_pdf`), pasa al
expediente al enviar y se muestra como «Documento generado» en el seguimiento
de la solicitud; si se descarga en Word, `ruta_pdf` queda vacío. Los borradores se guardan
en la cuenta con sus respaldos, su Word y su PDF firmado (ver «Borrador en la
cuenta»). Las correcciones usan una copia privada en la cuenta, separada del
expediente observado; requieren la migración propia de G2 descrita abajo.

El documento incluye los datos del estudiante, los campos del trámite y sus
materias. Las tablas de materias se repiten cuando corresponde; tercer registro
incluye una tabla de registros anteriores por materia. Homologación solicita
la cédula porque su plantilla identifica al estudiante con ese dato.
Los campos declarados sin un espacio propio en la plantilla se incorporan
antes de la despedida. La firma permanece vacía para firmarla manualmente.
El paso 1 precarga código, cédula, celular y, si hay una única inscripción,
facultad, carrera y modalidad mediante C12 de G1; el período procede de C07.
Esos datos permanecen editables y los valores del borrador tienen prioridad.
Nombre y correo proceden de la cuenta autenticada y son de solo lectura;
el servidor sustituye cualquier identidad distinta enviada desde el navegador.
La precarga no modifica el perfil ni las inscripciones de G1.

Las plantillas originales no se sobrescriben. Las copias temporales son privadas
y se conservan con los datos de la versión descargada. Al descartar el borrador
se eliminan también el Word y el PDF firmado. Cada descarga crea una nueva
versión, elimina el PDF firmado anterior y requiere volver a adjuntarlo.

## Borrador en la cuenta (RF-2.12)

`BorradoresSolicitud` guarda el borrador de una solicitud nueva como fila de
`solicitudes` en estado `borrador` y sin `enviada_at`, uno por estudiante y
trámite (`detalle.tramite`). La fila lleva la inscripción activa del estudiante
(la que coincide con la carrera declarada o, si no, la primera), el período
vigente (el declarado o `periodoActual()` de G1), el tipo de trámite de G4 y un
código `SOL-AAAA-00000` desde el primer guardado, porque `codigo` y las cuatro
llaves no admiten nulo. `declaracion_veracidad` queda en falso hasta el envío.
Se retoma desde cualquier navegador; los scopes `enviadas()` y `borradores()`
del modelo los separan, y `SolicitudService::delEstudiante()`, «Solicitudes
registradas» y el personal (`SolicitudPolicy::view`) solo ven las enviadas.
`solicitudes.show` de un borrador redirige al formulario. Al enviar,
`EnvioSolicitud` completa esa misma fila y conserva su código; descartar el
borrador borra la fila, así que pueden quedar huecos en la numeración.
Desde la integración de `develop` del PR #20, G3 lee `solicitudes` para sus
bandejas y utiliza su servicio de transiciones, historial y notificaciones.

Sigue en la sesión (`BorradoresTemporales`) el borrador de una cuenta sin
inscripción activa, sin período vigente o sin el estado `borrador` sembrado:
en ese caso el formulario dice «guardado en este navegador», y el primer
guardado posible en la cuenta se lleva lo de la sesión.

### Correcciones guardadas en la cuenta

`CorreccionesGuardadas` conserva datos, metadatos de respaldos, Word y PDF
firmado en `borradores_correccion_g2`, con una fila por usuario y expediente.
El middleware propio `GuardarCorreccion` restaura la copia antes de cada
petición de corrección y la guarda tras una respuesta exitosa. Los archivos
viven en `correcciones-g2/{usuario}/{solicitud}`, dentro del disco privado.
El expediente observado, su código y sus documentos anteriores no se modifican
hasta confirmar el reenvío. La autorización sigue usando la política existente.

La copia se ofrece en «Mis solicitudes» aunque se haya cerrado la sesión.
Descartarla elimina únicamente esa copia y sus archivos. Reenviar conserva las
versiones originales y elimina la copia de trabajo. Si cambió el expediente,
se advierte que la corrección está desactualizada y se mantiene el rechazo de
reenvíos que sobrescribirían una revisión posterior.

Para activar esta persistencia, ejecutar la migración nueva de G2 sobre una
base que ya tenga las migraciones previas:

```sh
php artisan migrate --path=database/migrations/2026_10_27_000001_create_borradores_correccion_g2_table.php
```

No usa `migrate:fresh`, no carga datos de prueba y no altera tablas de otros
grupos. Sin la tabla nueva, la corrección mantiene el comportamiento anterior
de guardado en la sesión; no se anuncia que quedó guardada en la cuenta.

### Archivos del borrador

Los respaldos, el Word descargado y el PDF firmado de un borrador en la cuenta
también se retoman desde cualquier navegador. `RespaldosTemporales`,
`DocumentosTemporales` y `FirmadosTemporales` conservan su interfaz y eligen
dónde guardar: si `BorradoresSolicitud::enCuentaDe($tramite)` encuentra el
borrador, cada archivo es una fila de `respaldos`, `documentos_generados` o
`documentos_firmados` de ese borrador, con el archivo en
`borradores/<id de la solicitud>/`; si no (corrección o cuenta sin contexto),
siguen en la carpeta temporal de la sesión.

- Un respaldo guarda en `documento_requerido_id` el documento de G4 cuyo
  `codigo` es el requisito elegido al subirlo. El navegador sigue conociendo
  solo el uuid del archivo, que es el nombre del archivo en disco; las rutas
  `respaldos-temporales.show|destroy` buscan ese uuid entre los borradores del
  usuario actual, así que otra cuenta recibe 404.
- Un borrador tiene un solo Word (versión 1, el de la última descarga), su PDF
  si esa descarga fue en PDF y, a lo sumo, un PDF firmado de ese Word.
  Descargar otra vez borra los tres.
  `documentos_generados` no tiene columnas para la versión de descarga
  (`X-Documento-Version`) ni para la huella de los datos, ni
  `documentos_firmados` para el nombre original del PDF: esos tres datos van en
  `detalle._archivos` del borrador. `BorradoresSolicitud::guardar()` los
  conserva entre guardados, el formulario no los recibe y el envío los quita.
- Subir el PDF firmado de un borrador en la cuenta exige haber descargado el
  documento antes (en la sesión se aceptaba sin él y el envío lo rechazaba).
- Lo que la sesión subió antes de que existiera el borrador en la cuenta pasa a
  él la primera vez que se consulta, con el mismo uuid y la misma versión; un
  PDF firmado de otra versión se descarta.
- Al enviar, `EnvioSolicitud` copia los archivos a `expedientes/<uuid>/` como
  antes, reemplaza dentro de la transacción las filas del borrador por las
  definitivas y, tras el commit, borra la carpeta del borrador. Descartar el
  borrador borra sus filas (primero `documentos_firmados`, que no se borra en
  cascada) y su carpeta.

Un borrador abandonado conserva sus archivos hasta que se envía o se descarta,
igual que su fila.

## Guardado y envío institucional

`EnvioSolicitud` guarda en una transacción solicitud, materias, registros previos,
respaldos, versión Word (con su PDF, si se descargó en PDF) y PDF firmado. Copia los archivos al storage privado
definitivo antes de persistir y elimina las copias si falla la transacción.
Los temporales se conservan ante errores y se descartan tras el envío exitoso.
Se valida que el Word y el PDF correspondan a los datos actuales, que los
respaldos pertenezcan al borrador (o a la sesión) y al trámite, y que estén
todos los obligatorios. Cada respaldo queda con el requisito asignado al final
en el formulario, aunque al subirlo se haya indicado otro.

`solicitudes.enviar` utiliza `IntegracionEnvioInstitucional`, registrado por la
integración recibida en el pull. Exige un estudiante activo, inscripción propia,
carrera y período vigentes, y catálogo publicado. La propiedad se lee por
`carrera_estudiante.estudiante_id` → `estudiantes.user_id` (tabla de G1 según el
Excel, PR #14). El envío registra el estado `enviada` y un historial
visible. La clave de versión evita duplicar el expediente al repetir el envío.
Tras confirmar, G2 invoca su servicio de notificaciones una sola vez por envío
o reenvío; una caída del canal de avisos se registra sin invalidar el expediente.

`MateriasSolicitud` consulta cursos, materias, períodos y docentes de G1, sin
modificarlos. Si el estudiante tiene inscripciones activas con malla (C11, C13),
solo ofrece cursos de las materias activas de esas mallas (C05, RF-2.5).
Sin perfil, inscripción activa o malla, devuelve una lista vacía y explica qué
información académica falta; nunca ofrece todo el catálogo como alternativa.
El filtro por período organiza esos cursos y conserva períodos anteriores,
que pueden ser necesarios para recuperación o correcciones. El formulario envía
`origen_materias=catalogo`: el servidor valida los IDs contra esa misma lista y
recupera sus metadatos de la base. Si no hay cursos, informa la ausencia;
no ofrece las materias ficticias que conserva `TramitesSimulados` para fixtures.
Todavía no se usa el historial del estudiante: no existe en el modelo de datos.

`RequisitosSolicitud` consulta Prolog v3 mediante `PrologClient`, utilizando el
ID del catálogo publicado. Los requisitos, documentos y aviso amarillo proceden
del motor. Cada documento tiene su botón de adjuntar; el servidor vuelve a
consultar los obligatorios al enviar. Un documento condicional se distingue de
uno opcional. Si Prolog falla o no coincide la versión de la base de conocimiento,
se conserva el borrador y no se anuncia un envío exitoso. El motor es informativo:
no aprueba ni rechaza la solicitud. Recuperación conserva el GPA general.

### Comprobación previa del paso 4 (RF-2.8)

Al entrar al paso 4, el formulario llama a `POST solicitudes.prevalidacion`
(`PrevalidacionController`, `PrevalidarSolicitudRequest`), que usa
`PrevalidacionSolicitud::consultar()` sobre la fachada C72 de G4,
`PrologService::evaluar(tramite_id, perfil)`. La respuesta es un fragmento HTML
(`partials/prevalidacion`) que reutiliza `<x-resultado-elegibilidad>` para las
comprobaciones y añade los textos y el aviso del motor.

- El motor v3 de G4 no emite dictámenes: el validador del puente rechaza cualquier
  `resultado`. Por eso no se muestra «cumple / no cumple / revisión manual», como
  pedía RF-2.8, sino las comprobaciones informativas de G4: en recuperación, la
  diferencia del GPA general con la referencia (75) y los días desde el último
  examen de recuperación (365); en los otros 19 trámites, el texto general del motor.
- Solo viajan al motor los datos que `PrologService::camposFormulario()` admite para
  el trámite y que el formulario muestra. Hoy son únicamente `gpa_general` y
  `fecha_ultima_recuperacion` de recuperación; la fecha no viaja si
  «Exámenes de recuperación anteriores» no es «con_fecha». El GPA de ayudante de
  cátedra no se envía.
- La comprobación no bloquea nada: ni su resultado ni un fallo de Prolog deshabilitan
  la descarga ni el envío. Si Prolog no responde, el bloque lo dice y ofrece
  «Volver a consultar». No hay «reglas firmes» que bloqueen (pregunta 27 sin respuesta).
- Al enviar o reenviar, `EnvioSolicitud` llama a `PrevalidacionSolicitud::registrar()`
  después del commit: `PrologService::evaluar($solicitud)` guarda la respuesta en
  `comprobaciones_informativas` (G4), visible para revisión mediante
  `Solicitud::evaluacionesElegibilidad()`. Un reintento idempotente no la repite y un
  fallo del motor en ese momento solo se registra en el log.
- Ruta limitada a 30 consultas por minuto; las pruebas están en
  `PrevalidacionSolicitudTest` y en `EnvioSolicitudTest`.

`solicitudes.edit` y `solicitudes.reenviar` permiten al propietario activo corregir
una solicitud observada. Conservan su código, las versiones y archivos anteriores;
exigen una nueva descarga y PDF firmado, y registran el reenvío desde el estado
real anterior. Una revisión concurrente impide sobrescribir un expediente cambiado.

## Integración pendiente con el administrador

- Las 20 plantillas oficiales vacías en `storage/app/private/plantillas` están
  incluidas en el repositorio. Sus originales no se modifican al descargar.
- `WordService::convertirAPdf` utiliza LibreOffice en el servidor. El formulario
  permite seleccionar Word o PDF. Detecta LibreOffice en Program Files,
  Program Files (x86), `%LOCALAPPDATA%/Programs/LibreOffice` o el PATH de Windows;
  en Linux busca `libreoffice`/`soffice` en el PATH y `/usr/bin/libreoffice`.
  Cada conversión tiene su propio directorio temporal del sistema y
  perfil; el controlador elimina las copias al terminar. La instalación del
  conversor no se distribuye con Git. Validar visualmente las 20 plantillas
  antes de la entrega; LibreOffice puede tener diferencias respecto a Word.
- En Windows se transmiten explícitamente las rutas del sistema y las carpetas
  temporales al proceso de conversión: el entorno de una petición HTTP puede
  omitirlas aunque la conversión desde la terminal funcione. Los errores técnicos
  quedan encadenados para el registro; el estudiante recibe un mensaje que permite
  seguir descargando Word. La firma/contacto se mantienen juntos con un espaciado
  compacto y el correo se alinea a la izquierda después de resolver los marcadores.
- El entorno local necesita las migraciones y catálogos académicos publicados por
  G1. Esta adaptación no ejecuta migraciones ni crea datos en la base local.
- Prolog debe ejecutar `prolog/kb/servidor.pl` v3 y la misma revisión publicada del
  catálogo. Publicar modificaciones del catálogo requiere su flujo coordinado de
  exportación y recarga; no basta con editar requisitos en el navegador.

## Verificación

`SeguimientoEstudianteTest` comprueba el listado propio con estados, el bloqueo
de expedientes ajenos y cuentas inactivas, las observaciones públicas escapadas,
su orden y la resolución con número, fecha y enlace firmado al PDF. La vista usa
los campos actuales de G3: `numero_resolucion`, `fecha_resolucion` y `documento_pdf`.
Estas verificaciones usan una base SQLite de pruebas; no crean expedientes ni
resoluciones en la base local.

`tests/Feature/DocumentoSolicitudTest.php` cubre las 20 plantillas, datos con
acentos y caracteres XML, múltiples materias, conservación del original,
descarga y rechazo de datos incompletos. La revisión visual de paginación
se realizó con LibreOffice para los 20 trámites, con hasta dos materias de prueba.
Se revisaron logo, datos, tablas, marcadores y páginas, y se corrigieron páginas
finales vacías, separación de firma/contacto y espaciado de los textos añadidos.
El número de páginas puede aumentar con motivos largos o hasta diez materias;
estas muestras no garantizan un resultado idéntico en todas las versiones de Word.

`tests/Feature/EnvioSolicitudTest.php` prepara casos de guardado completo,
reversión de registros/archivos, firma desactualizada e integración ausente.
Los identificadores del adaptador de prueba existen solo en las pruebas.

No modificar los archivos compartidos de `config`, `app/Providers`, los manifiestos
ni los componentes del layout de G1. El README del PR #12 permite el menú propio
de G2 en `config/menu_estudiante.php`.

## Integración con el marco de G1 (PR #12)

Las vistas activas de G2 usan `estudiante.layouts.marco`, que consume el
componente `<x-layouts.app>` de G1 con el módulo «Portal del estudiante» y
`config('menu_estudiante')`, sin copiar sus componentes. Se retiraron las barras duplicadas de nuestras páginas y se conservan los
estilos de las fichas, el chatbot y el acceso a notificaciones.

Las rutas de fichas, formularios, borradores, respaldos, firmados y seguimiento
están agrupadas bajo `auth` y `estudiante`. El acceso antiguo redirige a `/login`;
el visor de archivos sigue utilizando enlaces firmados porque también lo usa
el personal de revisión. No se cambiaron el middleware ni el login de G1.

### Contratos que deben confirmar G1 y G2

- Desde el PR #14 de G1 (22/10/2026), `carrera_estudiante` sigue el Excel
  (`estudiante_id`, `carrera_modalidad_id`, `malla_id`) y `CarreraEstudiante`
  define `estudiante()`, `carreraModalidad()` y `malla()`. G2 usa C11, C13 y C05
  para el selector de materias y lee la propiedad por `estudiante()`. Los
  accesores `user_id` y `carrera_id` del modelo son de compatibilidad de G1; no
  depender de ellos.
- C12 (`perfil()`) precarga los datos editables del paso 1 mediante
  `PerfilFormularioSolicitud`. Ante varias inscripciones no se escoge una
  carrera arbitraria. Nombre y correo se toman de la cuenta autenticada.
- La selección final actual de G2 representa un **curso**, no solo una materia.
  Propuesta de respuesta para normalizar en el adaptador de G2:

```json
{
  "data": [
    {
      "materia_id": 12,
      "codigo": "BD401",
      "nombre": "Bases de datos",
      "curso_id": 25,
      "periodo_id": 4,
      "periodo": "Ordinario II 2026",
      "paralelo": "A",
      "docente": "Nombre del docente"
    }
  ]
}
```

Si C34 devuelve únicamente materias de la malla, G2 deberá pedir sus cursos con
C35, pasando `materia_id` y `periodo_id`. No confundir esos IDs: en el formulario
actual `materias[].id` es el ID del curso. La API y el servicio deben limitar los
datos al estudiante autenticado y validar su inscripción, malla y período.
Los datos personales precargados con C12 siguen siendo editables, excepto
nombre y correo de la cuenta. Un borrador recuperado conserva sus valores
editables. La precarga nunca permite escoger al propietario de la solicitud.

### GPA pendiente de acuerdo

El motor de G4 calcula referencias sobre GPA general. La plantilla DOCX actual
de recuperación no incluye un campo GPA, y el catálogo publicado documenta una
discrepancia con el GPA del período del handbook. Confirmar con G1/G4 y la docente
si se debe añadir un dato declarado de período para el documento, separado del
general. No renombrar el dato de Prolog ni cambiar `gpa_declarado` sin acordar su
significado y comprobar ambos contratos.

Esta rama ya integra el PR #12 (Breeze como único login, sin Fortify). No
ejecuta seeders ni modifica tablas de otros grupos.

## Cambios de integración autorizados

- G2 no modifica `layouts/app.blade.php` ni la tabla `users`: el menú del portal
  vive en `config/menu_estudiante.php` y las columnas `tipo` y `activo` las crea
  la migración original de G1. Una base local creada antes del PR #12 se
  actualiza con `php artisan migrate:fresh --seed` (borra los datos locales).
- El chatbot flotante recibe el trámite tanto desde una ficha como desde las
  rutas directas del formulario. Sus respuestas siguen usando el servicio de G4.
- Las pruebas de datos personales verifican la precarga de C12, la edición de
  los demás datos y el rechazo de identidades manipuladas. La propiedad del
  expediente sigue dependiendo del usuario autenticado.

Para revisar antes del push, usar `git diff --check`, `git diff --stat` y
`php artisan test`. La prueba general de estilos espera `build/assets/app-`;
con Vite activo (`public/hot`) la página usa el servidor de desarrollo. Comprobar
también los estilos compilados; no versionar `public/hot` ni `public/build`.
Los datos académicos y las inscripciones de G1 se cargan en la base de cada
entorno; no se distribuyen con el push del portal.

Configuración local y comprobación de subidas de 10 MB: [Archivos y PDF](grupo-2-archivos-pdf.md).

## Pendientes que requieren coordinación entre grupos

- G1 ya entregó los servicios académicos que consume G2. Una cuenta sin perfil,
  inscripción, malla o cursos necesita que esos datos estén cargados en su
  entorno; G2 no crea matrículas ni materias ficticias para llenar el selector.
- Las bandejas y transiciones reales de G3 ya están integradas desde el PR #20;
  los avisos posteriores se invocan allí. G2 confirma su envío y reenvío una
  sola vez, incluso ante reintentos HTTP.
- G4 debe incorporar `CatalogoInformativoSeeder` a `DatabaseSeeder` para que
  una instalación limpia tenga los trámites y sus documentos requeridos.
  Esta integración de G2 no modifica el seeder compartido.
- El estado intermedio «Documento generado» requiere acordar el catálogo y las
  transiciones institucionales. G2 conserva el archivo y su versión, pero no
  agrega estados por su cuenta.
- La prevalidación muestra las comprobaciones y advertencias que devuelve
  Prolog v3; no inventa un dictamen automático de aprobación.
- Sigue pendiente el acuerdo del GPA del período frente al GPA general del
  motor, explicado arriba. No se cambió el significado de `gpa_declarado`.

## Verificación de la integración de G2 (27/10/2026)

La rama de integración parte de `develop` (`bbf3aa4`, PR #20) y recupera los
commits `1313b8c` y `fe801ba` de G2. Los dos se aplicaron sin conflictos; la
rama original se conserva como respaldo. El arreglo de `PrologServiceTest`
crea sus registros referenciados mediante factories: no elimina las llaves.

Suite completa con PHP 8.4.27, SQLite y correo en memoria: **533 pruebas
pasaron, con 4696 aserciones y sin fallos ni pruebas omitidas**. Las dependencias
locales se sincronizaron con `composer.lock` sin modificar los manifiestos.
PHP 8.4 y su configuración de GD/SQLite se utilizaron desde una carpeta temporal;
no se cambió el PHP global ni se migró o sembró la base local.

Las pruebas cubren selección por malla, ausencia de perfil/malla, materias
inactivas, períodos históricos, recuperación de correcciones con archivos entre
sesiones, descarte sin borrar el expediente, acceso por propietario, revisión
concurrente, reenvío HTTP con versiones y avisos sin duplicados. El filtro de
período también se comprobó ejecutando su JavaScript con un DOM mínimo.
La precarga de C12 se comprueba con un perfil real, varias inscripciones y
ausencia de perfil. Los 20 formularios mantienen nombre/correo de la cuenta;
los borradores, documentos y reenvíos ignoran identidades manipuladas y
conservan los demás datos editables y las versiones anteriores.
`git diff --check` y `npm run build` pasaron; la compilación se hizo en una
carpeta temporal. Las pantallas activas no muestran el aviso «en desarrollo».

La migración auxiliar de correcciones queda pendiente de aplicar en cada
entorno. Para una instalación limpia sigue pendiente que G4 incluya
`CatalogoInformativoSeeder` en `DatabaseSeeder`; este PR no modifica ese archivo
compartido, los proveedores, los manifiestos ni la configuración de otros grupos.
