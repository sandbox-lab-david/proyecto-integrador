# Documentos del portal del estudiante

## Funcionamiento actual

Las 20 fichas abren el formulario general. Al confirmar la declaración,
se descarga la plantilla DOCX del trámite completada por `DocumentoSolicitud`.
DOM y ZipArchive preparan marcadores en una copia de las plantillas originales;
`WordService::rellenarPlantilla` realiza el llenado definitivo con PhpWord.
Los archivos temporales de preparación y llenado se eliminan al terminar.
Abrir el archivo en Word y exportar a PDF permite revisar el formato.
La solicitud definitiva todavía no se guarda ni se envía: los borradores viven
en la sesión y los respaldos y PDF firmados se almacenan temporalmente en el
servidor, separados por sesión y trámite.

El documento incluye los datos del estudiante, los campos del trámite y sus
materias. Las tablas de materias se repiten cuando corresponde; tercer registro
incluye una tabla de registros anteriores por materia. Homologación solicita
la cédula porque su plantilla identifica al estudiante con ese dato.
Los campos declarados sin un espacio propio en la plantilla se incorporan
antes de la despedida. La firma permanece vacía para firmarla manualmente.

Las plantillas originales no se sobrescriben. Las copias temporales son privadas
y se conservan con los datos de la versión descargada. Al descartar el borrador
se eliminan también el Word y el PDF firmado. Cada descarga crea una nueva
versión, elimina el PDF firmado anterior y requiere volver a adjuntarlo.

## Guardado y envío preparado

`EnvioSolicitud` guarda en una transacción solicitud, materias, registros previos,
respaldos, versión Word y PDF firmado. Copia los archivos al storage privado
definitivo antes de persistir y elimina las copias si falla la transacción.
Los temporales se conservan ante errores y se descartan tras el envío exitoso.
Se valida que el Word y el PDF correspondan a los datos actuales, que los
respaldos pertenezcan a la sesión/trámite y que estén todos los obligatorios.

La ruta `solicitudes.enviar` y el botón están preparados, pero **el envío real
permanece deshabilitado**. No se crean usuarios, carreras, cursos ni estados
ficticios para habilitarlo.

`IntegracionEnvio` es un punto de adaptación nuevo, no un contrato previamente
publicado por otros grupos. Cuando estén sus servicios, implementar y registrar
el adaptador en el contenedor. `resolver` debe validar al usuario autenticado,
su carrera y cursos, elegibilidad, requisitos y los datos del perfil, y devolver
IDs reales de G1/G4 y el estado previo al envío. Debe rechazar datos simulados.
`enviar` debe usar G3 para registrar la transición a Enviado y su historial en
la misma transacción, sin emitir notificaciones externas antes del commit.
También habrá que sustituir `TramitesSimulados` y la validación contra sus
materias de prueba por los catálogos reales; registrar el adaptador solo no basta.

El envío exige sesión autenticada. Mientras falte el adaptador, responde 503
y el formulario explica la integración pendiente sin anunciar un envío exitoso.

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
- Conectar el perfil autenticado y los catálogos reales, persistencia del expediente,
  identificador SOL, versiones, permisos y vinculación definitiva del PDF firmado.

## Verificación

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

No modificar `config`, `app/Providers`, los manifiestos compartidos ni el layout
principal sin la coordinación indicada por la guía oficial.

Configuración local y comprobación de subidas de 10 MB: [Archivos y PDF](grupo-2-archivos-pdf.md).
