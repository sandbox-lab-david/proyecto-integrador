# Revisión de campos de los 20 trámites

Revisión del 11 de octubre de 2026 basada en las fichas del repositorio,
`TramitesSimulados` y las 20 plantillas Word. Esta comparación identifica
coherencia interna; no sustituye la confirmación del reglamento por la facultad.
Todos los trámites usan el formulario general. Los datos personales comunes
son nombre, código estudiantil, carrera, correo y celular.

| Trámite | Campos propios revisados | Resultado y pendiente |
| --- | --- | --- |
| Recuperación | Materia, estado, nota, asistencia, GPA, recuperación anterior y observación | Cubiertos. Fecha condicional; una materia. Verificación académica depende de G1/G3. |
| Gracia | Materias, asistencia, calificación y motivo | Cubiertos. Confirmar respaldos; el catálogo de prueba no filtra materias del semestre actual. |
| Supletorio | Materias, parcial y circunstancia | Cubiertos; evidencia del impedimento configurada. Confirmar calendario real. |
| Suficiencia | Materias, tipo de respaldo y fundamento | Cubiertos. La copia generada corrige la referencia errónea a gracia. Tipos de respaldo admitidos pendientes. |
| Tercer registro | Materias y año, periodo y nota de dos registros por materia | Años enteros de cuatro dígitos, sin fechas futuras; periodos limitados a 50 caracteres. Cada materia tiene una tabla con ambos registros. Verificación de intentos/autorizaciones pendiente. |
| Ayudante de cátedra | Materias, calificación, periodo, año, GPA y motivación | Año entero; GPA numérico. Se agregan recomendaciones de al menos dos docentes y solicitud del docente, como indica la ficha. Elegibilidad académica pendiente. |
| Recalificación | Materias, evaluación, fecha y hora de publicación, reclamo | Hora con control y formato HH:mm. La ventana de 48 horas y tipos de evaluación deben verificarse con datos reales. |
| Homologación | Cédula, institución de procedencia, carrera de destino, materias e información adicional | Cubiertos. Se identifican documento de identidad, certificado y sílabos; confirmar obligatoriedad/legalización. La selección de materias externas queda pendiente del catálogo real. |
| Alcance de homologación | Materias, motivo y documento faltante | Cubiertos. Verificación de homologación previamente aprobada pendiente. |
| Incompleto | Materias, tipo de causa y detalle | Cubiertos. Lista de causas y respaldos admitidos pendiente; se mantiene texto libre. |
| Person to Person | Materias y motivo opcional | Cubiertos. Confirmar condiciones, respaldos y plazo. |
| Registro extemporáneo | Materias, asistencia como oyente y motivo | Oyente pasa a selección Sí/No. La declaración no acredita asistencia. |
| Reincorporación | Último periodo opcional y motivo | Cubiertos. Confirmar condiciones y calendario. |
| Cambio de carrera | Carrera de destino, nuevo código y motivo | Nuevo código pasa a selección explícita Sí/No. Catálogo de carreras pendiente. |
| Cambio de malla | Pénsum actual, malla de destino y motivo opcional | Cubiertos. La plantilla denomina los identificadores como años; se mantienen los textos del formulario hasta conocer el catálogo de mallas. |
| Cambio de modalidad | Modalidad actual, solicitada, carrera de destino opcional y motivo | Cubiertos; las modalidades se incluyen en el resumen del Word porque no tienen marcador propio. Catálogo de modalidades pendiente. |
| Retiro de materia | Materias, semana actual y motivo opcional | Semana obligatoria, entera y positiva según la ficha. No se inventa un límite superior ni la semana de exámenes. |
| Retiro extemporáneo | Materias y motivo excepcional | Cubiertos. Confirmar respaldos para las excepciones. |
| Retiro de carrera | Motivo | Cubierto; sin selección de materias. Confirmar requisitos y efectos administrativos. |
| Retiro de universidad | Motivo | Cubierto; sin selección de materias. Confirmar requisitos y efectos administrativos. |

## Correcciones comunes

Las restricciones de tipo se comparten entre borradores, generación Word y envío
mediante `ReglasCampoSolicitud`. Los borradores admiten campos vacíos, pero
validan el formato de los valores presentes. La descarga y el envío exigen
los obligatorios. Texto corto: 255 caracteres; texto largo: 2000, salvo límites
específicos. Los controles muestran el mismo paso y límites numéricos del servidor.

Los borradores de Sí/No escritos antes como `si`, `Sí` o `NO` se recuperan con la
opción equivalente cuando se vuelve a abrir el formulario. Las respuestas
no reconocibles necesitan una selección explícita.

## Alcance pendiente

No se agregaron umbrales nuevos de elegibilidad ni plazos sin confirmar.
Los máximos de materias distintos del caso específico de recuperación siguen
siendo provisionales. Las recomendaciones agrupadas en un PDF no permiten
comprobar automáticamente su autoría o cantidad. Tampoco se verifica identidad
o legalización a partir del nombre de un archivo.

La paginación de los Word, los datos académicos reales y el envío a revisión
requieren sus verificaciones e integraciones respectivas.
