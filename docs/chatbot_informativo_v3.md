# Chatbot informativo y catálogo publicado — contrato v3

## Comportamiento

El chat informa sobre 20 trámites. Las consultas de descripción, requisitos, documentos, costos y plazos pasan por Prolog. No emite dictámenes de elegibilidad ni cambia el estado de solicitudes.

Recuperación ofrece dos comprobaciones opcionales, disponibles con sesión: comparación del **GPA general** con la referencia 75 y cálculo de 365 días desde el **último examen de recuperación**. Los datos son declarados. Se muestran diferencias y fechas, sin aprobar o rechazar; faltar un dato no supone ausencia de historial. Se usa la fecha de Guayaquil y aritmética de fechas calendario en UTC para evitar desfases de un día. La comparación puede consultarse sin completar ninguno de los campos.

Las comprobaciones de 48 horas y 30 días quedan fuera. Historial, sanciones, equivalencias y resolución corresponden a revisión institucional. «Mis solicitudes» es el único recorrido de seguimiento; no se reproduce dentro del chat. Se conservan conversaciones y consultas no resueltas, con relación al trámite cuando se reconoce. El reporte administrativo permanece pendiente.

## Catálogo y publicación

- `tipos_tramite.contenido`: borrador del contenido y configuración del formulario. Requisitos, campos de captura y documentos se mantienen separados.
- `documentos_requeridos`: identificadores estables, textos y obligaciones explícitas (`obligatorio`, `opcional`, `condicional`). La obligación condicional exige revisión de su caso; no se infiere un requisito obligatorio.
- `revisiones_catalogo`: instantáneas inmutables; solo una revisión activa se sirve al chat, fichas y formularios.
- `comprobaciones_informativas`: resultado orientativo asociado a una solicitud cuando otro módulo invoca la fachada C72. No es una decisión ni una acreditación académica.
- `database/catalogo/inicial.json`: importación inicial y contenido de arranque mientras todavía no existe una revisión publicada. Sus notas y fuentes son internas; no se incluyen en las respuestas públicas. El seeder no sobrescribe registros existentes.

Instalación coordinada de Laravel y Prolog (v2 se rechaza):

```bash
php artisan migrate
php artisan db:seed --class=CatalogoInformativoSeeder
php artisan kb:exportar
# Iniciar Prolog con la configuración de credenciales del puente.
php artisan kb:exportar --publicar
```

El primer comando de exportación prepara y valida; no activa. `--publicar` valida el candidato real con SWI-Prolog, solicita una recarga al motor y comprueba la versión antes de activar la revisión en BD. La recarga se serializa con las consultas y restaura los hechos anteriores ante fallo. Si Laravel y Prolog presentan versiones diferentes, el puente devuelve un error técnico y no mezcla respuestas. El archivo `storage/prolog/activa` permite recuperar la revisión publicada al reiniciar el motor.

El proceso de Prolog debe compartir acceso de lectura a `storage/prolog/revisiones` y al puntero de arranque. El endpoint interno `POST /recargar` acepta exclusivamente rutas permitidas y utiliza la misma credencial del puente. Las reglas se despliegan en código y requieren reiniciar el motor cuando cambian. No se publican automáticamente los cambios de borrador ni se exporta por cada visita del estudiante.

Antes de modificar esquema o datos de un entorno existente se debe avisar y respaldarlo. Nunca usar `migrate:fresh` sobre la BD local existente. Las pruebas Laravel usan SQLite en memoria; las pruebas del exportador usan directorios temporales separados del motor local.

## Interfaces

`GET /chatbot/catalogo` devuelve `version: 3`, `kb_version`, categorías, trámites y preguntas. Solo recuperación incluye `__referencias` / `comprobar_referencias`; no hay cuestionarios de elegibilidad para los otros 19 trámites.

`POST /chatbot/mensaje` y su equivalente API aceptan texto libre o una pregunta del catálogo. Para una comprobación:

```json
{
  "version": 3,
  "pregunta_id": "examen_recuperacion__referencias",
  "tramite_id": "examen_recuperacion",
  "intencion": "comprobar_referencias",
  "origen_datos": "manual",
  "datos_usuario": {
    "gpa_general": 72,
    "fecha_ultima_recuperacion": "2025-10-20"
  }
}
```

Ambos campos son opcionales. `mostrar_referencias: true` permite enviar el formulario vacío sin volver a abrirlo. GPA fuera de 0–100, fechas imposibles o futuras y campos antiguos/ambiguos se rechazan con 422. La intención y la pregunta deben corresponder al trámite seleccionado. Las consultas generales no reciben perfiles personales.

El puente conserva `POST /consulta`, `POST /evaluar` y `GET /salud`. `/evaluar` recibe `intencion: comprobar_referencias`, fecha calculada por Laravel, origen manual y perfil. Las respuestas v3 incluyen `resultado: null` y `comprobaciones: [{codigo, texto, datos}]`; los documentos incluyen obligación explícita. El validador rechaza resultados anteriores y claves adicionales. El cliente verifica también la versión activa del catálogo. Los fallos de transporte nunca se convierten en una respuesta académica.

`PrologService::evaluar(Solicitud|string, ...)` conserva el nombre de C72 para los módulos existentes, con semántica informativa v3. Para solicitudes reales lee `gpa_declarado` / `detalle.campos.gpa_general` y la fecha de recuperación; nunca toma una nota de materia ni GPA de período. Los antiguos argumentos de verificación no confieren verificación institucional.

## Envío y permisos

El adaptador real usa la cuenta activa, una inscripción activa cuyo nombre de carrera coincida y un período académico activo. No elige al propietario por nombre o correo declarados. Si falta contexto académico devuelve un error comprensible y conserva el borrador. Las materias permanecen declaradas y no se vinculan con cursos inventados.

El envío guarda el expediente, sus archivos, estado `enviada` e historial dentro de la transacción. Los reintentos conservan la clave y devuelven el mismo expediente. El estudiante activo solo ve su propio seguimiento; un director activo puede consultar solicitudes de su carrera mientras su asignación esté vigente. Se muestran únicamente entradas del historial marcadas `visible_estudiante`. Los registros anteriores quedan internos por defecto. Las asignaciones de personal por facultad todavía necesitan completar el contrato de datos existente; no se infieren permisos para esas asignaciones. no se otorgan roles por nombres o dominios de correo.

## Verificación

Ejecutar `php artisan test`, `npm run build`, `node --check public/assets/chatbot/chatbot.js` y, por separado, las suites `prolog/pruebas/reglas_chatbot.pl` e `integridad_conocimiento.pl` con PLUnit. Los scripts HTTP de `prolog/pruebas` verifican transporte, credenciales y límites; el de límites termina con error ante cualquier comprobación fallida.

Las pruebas nuevas cubren publicación fallida y restauración, persistencia del catálogo, los 20 formularios y fichas, envío de los 20 tipos con el adaptador real, idempotencia, permisos, referencias opcionales, rechazo de alias, zona horaria, límites de GPA, 364/365/366 días y años bisiestos. Los datos y archivos de prueba son ficticios. Las pruebas omitidas que ya existían sobre funciones de otros módulos no certifican esas funciones.

Los aranceles, plazos no conciliados y criterios institucionales restantes requieren validación de contenido. Se orienta al estudiante hacia la facultad/Secretaría sin exponer notas internas ni inventar valores.
