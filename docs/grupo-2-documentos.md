# Documentos del portal del estudiante

## Funcionamiento actual

Las 20 fichas abren el formulario general. Al confirmar la declaración,
se descarga la plantilla DOCX del trámite completada por `DocumentoSolicitud`.
DOM y ZipArchive preparan marcadores en una copia de las plantillas originales;
`WordService::rellenarPlantilla` realiza el llenado definitivo con PhpWord.
Los archivos temporales de preparación y llenado se eliminan al terminar.
El formulario permite descargar Word o PDF. LibreOffice convierte el archivo
en el servidor; el estudiante no necesita instalarlo. Los borradores viven
en la sesión y los respaldos y PDF firmados se almacenan temporalmente hasta
el envío, separados por sesión, trámite y expediente en corrección.

El documento incluye los datos del estudiante, los campos del trámite y sus
materias. Las tablas de materias se repiten cuando corresponde; tercer registro
incluye una tabla de registros anteriores por materia. Homologación solicita
la cédula porque su plantilla identifica al estudiante con ese dato.
Los campos declarados sin un espacio propio en la plantilla se incorporan
antes de la despedida. La firma permanece vacía para firmarla manualmente.
Los datos personales del portal se escriben a mano, incluso con sesión iniciada.
El nombre y correo declarados no sustituyen la identidad autenticada del dueño.

Las plantillas originales no se sobrescriben. Las copias temporales son privadas
y se conservan con los datos de la versión descargada. Al descartar el borrador
se eliminan también el Word y el PDF firmado. Cada descarga crea una nueva
versión, elimina el PDF firmado anterior y requiere volver a adjuntarlo.

## Guardado y envío institucional

`EnvioSolicitud` guarda en una transacción solicitud, materias, registros previos,
respaldos, versión Word y PDF firmado. Copia los archivos al storage privado
definitivo antes de persistir y elimina las copias si falla la transacción.
Los temporales se conservan ante errores y se descartan tras el envío exitoso.
Se valida que el Word y el PDF correspondan a los datos actuales, que los
respaldos pertenezcan a la sesión/trámite y que estén todos los obligatorios.

`solicitudes.enviar` utiliza `IntegracionEnvioInstitucional`, registrado por la
integración recibida en el pull. Exige un estudiante activo, inscripción propia,
carrera y período vigentes, y catálogo publicado. La relación de propiedad es
`carrera_estudiante.user_id`. El envío registra el estado `enviada` y un historial
visible. La clave de versión evita duplicar el expediente al repetir el envío.

`MateriasSolicitud` consulta cursos, materias, períodos y docentes de G1, sin
modificarlos. El formulario envía `origen_materias=catalogo`: el servidor valida
los IDs y recupera sus metadatos de la base. Si no hay cursos, informa la ausencia;
no ofrece las materias ficticias que conserva `TramitesSimulados` para fixtures.

`RequisitosSolicitud` consulta Prolog v3 mediante `PrologClient`, utilizando el
ID del catálogo publicado. Los requisitos, documentos y aviso amarillo proceden
del motor. Cada documento tiene su botón de adjuntar; el servidor vuelve a
consultar los obligatorios al enviar. Un documento condicional se distingue de
uno opcional. Si Prolog falla o no coincide la versión de la base de conocimiento,
se conserva el borrador y no se anuncia un envío exitoso. El motor es informativo:
no aprueba ni rechaza la solicitud. Recuperación conserva el GPA general.

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

## Preparación para el PR #12 de G1

Las vistas activas de G2 usan `estudiante.layouts.marco`. Cuando está disponible
`components.layouts.app`, el puente consume ese componente con el módulo
«Portal del estudiante» y `config('menu_estudiante')`. Mientras esta rama no haya
integrado el PR, utiliza el layout anterior sin copiar los componentes de G1.
Se retiraron las barras duplicadas de nuestras páginas y se conservan los
estilos de las fichas, el chatbot y el acceso a notificaciones.

Las rutas de fichas, formularios, borradores, respaldos, firmados y seguimiento
están agrupadas bajo `auth` y `estudiante`. El acceso antiguo redirige a `/login`;
el visor de archivos sigue utilizando enlaces firmados porque también lo usa
el personal de revisión. No se cambiaron el middleware ni el login de G1.

### Contratos que deben confirmar G1 y G2

- C11 identifica al estudiante autenticado; C13 debe entregar sus inscripciones
  activas con relaciones funcionales. En `origin/develop` revisado, C13 solicita
  `carreraModalidad` y `malla`, que el modelo `CarreraEstudiante` todavía no define.
  No sustituir las consultas actuales hasta que modelo y servicio sean coherentes.
- C05 sigue devolviendo una colección vacía. C12 y C34 están pendientes de entrega.
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
Los datos personales del formulario seguirán siendo manuales; C12 no debe
sobrescribirlos ni permitir escoger al propietario mediante nombre o correo.

### GPA pendiente de acuerdo

El motor de G4 calcula referencias sobre GPA general. La plantilla DOCX actual
de recuperación no incluye un campo GPA, y el catálogo publicado documenta una
discrepancia con el GPA del período del handbook. Confirmar con G1/G4 y la docente
si se debe añadir un dato declarado de período para el documento, separado del
general. No renombrar el dato de Prolog ni cambiar `gpa_declarado` sin acordar su
significado y comprobar ambos contratos.

Esta preparación no integra ramas, instala dependencias, ejecuta seeders ni
modifica tablas. La verificación del marco #12 usó copias temporales de sus tres
componentes, sin escribirlos en `resources/views/components` del proyecto.

## Cambios de integración autorizados

- El menú de `layouts/app.blade.php` incorpora «Catálogo de trámites» y «Mis
  solicitudes» por solicitud expresa. Conserva sus opciones y diseño anteriores.
- `2026_10_21_230000_completar_tipo_y_activo_en_users.php` agrega únicamente las
  columnas faltantes en bases que ejecutaron la migración original antes del pull.
  No modifica el archivo original ni borra usuarios. Si las columnas existen,
  conserva sus valores; la reversión no elimina columnas de la migración de G1.
  Coordinar esta reparación con G1 al integrar las ramas.
- El chatbot flotante recibe el trámite tanto desde una ficha como desde las
  rutas directas del formulario. Sus respuestas siguen usando el servicio de G4.
- Las pruebas de datos personales verifican el llenado manual solicitado. La
  propiedad del expediente sigue dependiendo del usuario autenticado, no del
  nombre o correo declarados.

Para revisar antes del push, usar `git diff --check`, `git diff --stat` y
`php artisan test`. La prueba general de estilos espera `build/assets/app-`;
con Vite activo (`public/hot`) la página usa el servidor de desarrollo. Comprobar
también los estilos compilados; no versionar `public/hot` ni `public/build`.
Los datos académicos y las inscripciones de G1 se cargan en la base de cada
entorno; no se distribuyen con el push del portal.

Configuración local y comprobación de subidas de 10 MB: [Archivos y PDF](grupo-2-archivos-pdf.md).
