:- encoding(utf8).

% Contrato de conocimiento - revision 004 (2026-10-20).
% PUBLICO:
%   descripcion/2: finalidad del tramite.
%   requisito/3: condiciones informativas, no campos ni reglas ejecutables.
%   campo_formulario/4: datos de captura y obligatoriedad SOLO en el portal.
%   documento_def/5 y tramite_documento/4: respaldos sugeridos.
%   costo/3: exclusivamente montos confirmados en centavos (hoy sin hechos).
%   costo_texto/2: disponibilidad de tarifa/efectos economicos; no fija montos.
%   plazo_texto/2: plazos de solicitud o ejecucion, con incertidumbre explicita.
% INTERNO: estado, fuente, equivalencias, nota_interna/2, resumen_interno/2
%   y archivos *_original_interno. No contienen datos personales reales.
% El estado parcial/pendiente NO equivale a cumplimiento del estudiante.
% Compatibilidad: pendiente_validacion/2 y observacion_conocimiento/2
% permanecen declarados SIN hechos: reglas.pl devuelve aviso:null.
% Nunca cargar simultaneamente otra copia de hechos.pl.
% No se validan institucionalmente criterios por cambiar su redaccion.
% costo_texto/2 y campo_formulario/4 son datos disponibles para integrar:
% las reglas actuales no los incorporan a su respuesta HTTP por si solos.


:- multifile tramite/2.
:- discontiguous tramite/2.
:- multifile descripcion/2.
:- discontiguous descripcion/2.
:- multifile requisito/3.
:- discontiguous requisito/3.
:- multifile documento_def/5.
:- discontiguous documento_def/5.
:- multifile tramite_documento/4.
:- discontiguous tramite_documento/4.
:- multifile costo_texto/2.
:- discontiguous costo_texto/2.
:- multifile costo/3.
:- discontiguous costo/3.
:- multifile plazo_texto/2.
:- discontiguous plazo_texto/2.
:- multifile parametro/3.
:- discontiguous parametro/3.
:- multifile tipo_solicitante_permitido/2.
:- discontiguous tipo_solicitante_permitido/2.
:- multifile estado_conocimiento/2.
:- discontiguous estado_conocimiento/2.
:- multifile version_conocimiento/1.
:- discontiguous version_conocimiento/1.
:- multifile fuente_conocimiento/2.
:- discontiguous fuente_conocimiento/2.
:- multifile identificador_agenda/2.
:- discontiguous identificador_agenda/2.
:- multifile campo_formulario/4.
:- discontiguous campo_formulario/4.
:- multifile pendiente_validacion/2.
:- discontiguous pendiente_validacion/2.
:- multifile observacion_conocimiento/2.
:- discontiguous observacion_conocimiento/2.
:- multifile nota_interna/2.
:- discontiguous nota_interna/2.
:- multifile resumen_interno/2.
:- discontiguous resumen_interno/2.
:- multifile requisito_original_interno/3.
:- discontiguous requisito_original_interno/3.
:- multifile plazo_original_interno/2.
:- discontiguous plazo_original_interno/2.

version_conocimiento("revision-publica-004").
kb_version(Version) :- version_conocimiento(Version).

% ==================================================================
% 01. Alcance de homologación
% ==================================================================
tramite(alcance_homologacion, "Alcance de homologación").
descripcion(alcance_homologacion, "Añadir una materia a una homologación que ya fue aprobada, cuando faltó un documento de respaldo.").
requisito(alcance_homologacion, 1, "Tener una homologación previamente aprobada.").
requisito(alcance_homologacion, 2, "Solicitar la revisión de una materia no considerada por falta de un respaldo en esa homologación.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(alcance_homologacion, subjects, "Materia(s)", obligatorio).
campo_formulario(alcance_homologacion, reason, "Motivo y documento que faltó", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(alcance_homologacion_respaldo_1, "Resolución o antecedente de la homologación previa.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
documento_def(alcance_homologacion_respaldo_2, "Documento que faltó o evidencia que sustenta el alcance.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(alcance_homologacion, 1, alcance_homologacion_respaldo_1, opcional).
tramite_documento(alcance_homologacion, 2, alcance_homologacion_respaldo_2, opcional).
costo_texto(alcance_homologacion, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(alcance_homologacion, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(alcance_homologacion, parcial).
fuente_conocimiento(alcance_homologacion, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(alcance_homologacion, "alcance-homologacion").
nota_interna(alcance_homologacion, "Falta confirmar plazo, autoridad que valida el documento y causales de rechazo.").
resumen_interno(alcance_homologacion, "Requiere una homologación previa aprobada y explicar qué documento faltó.").

% ==================================================================
% 02. Ayudante de cátedra
% ==================================================================
tramite(ayudante_catedra, "Ayudante de cátedra").
descripcion(ayudante_catedra, "Solicitar la participación como ayudante de una asignatura durante un periodo académico.").
requisito(ayudante_catedra, 1, "Aceptar las responsabilidades académicas de la ayudantía.").
requisito(ayudante_catedra, 2, "La selección depende de la evaluación y autorización de la facultad; los criterios académicos de selección deben confirmarse.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(ayudante_catedra, subjects, "Materia(s)", obligatorio).
campo_formulario(ayudante_catedra, academic_period, "Periodo y año", obligatorio).
campo_formulario(ayudante_catedra, gpa_general, "GPA general", obligatorio).
campo_formulario(ayudante_catedra, subject_grade, "Calificación en la materia", obligatorio).
campo_formulario(ayudante_catedra, reason, "Motivación", opcional).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(ayudante_catedra_respaldo_1, "Información académica o calificaciones, si la Facultad las solicita.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(ayudante_catedra, 1, ayudante_catedra_respaldo_1, opcional).
costo_texto(ayudante_catedra, "No hay tarifa confirmada. La posible retribución académica indicada por el proyecto no es un costo monetario.").
plazo_texto(ayudante_catedra, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(ayudante_catedra, parcial).
fuente_conocimiento(ayudante_catedra, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(ayudante_catedra, "ayudante-catedra").
nota_interna(ayudante_catedra, "Falta confirmar promedio mínimo, quién puede postular, cupos, funciones y criterio de selección. El motor del proyecto contempla GPA general mínimo de 90, materia aprobada con nota mínima de 90 y ausencia de materias reprobadas. Son criterios de implementación pendientes de validación; la plantilla menciona retribución de 5 puntos.").
resumen_interno(ayudante_catedra, "La plantilla menciona una retribución de 5 puntos; los criterios de selección aún deben validarse.").

% ==================================================================
% 03. Cambio de carrera
% ==================================================================
tramite(cambio_carrera, "Cambio de carrera").
descripcion(cambio_carrera, "Cambiar de la carrera actual a otra carrera y, cuando corresponda, solicitar un nuevo código.").
requisito(cambio_carrera, 1, "Ser estudiante de una carrera de la universidad y solicitar el traslado a otra.").
requisito(cambio_carrera, 2, "El cambio requiere revisión de la carrera de destino y puede afectar las equivalencias de materias.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(cambio_carrera, target_career, "Carrera de destino", obligatorio).
campo_formulario(cambio_carrera, new_code, "Solicito también la creación de un nuevo código", opcional).
campo_formulario(cambio_carrera, reason, "Motivo del cambio", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(cambio_carrera_respaldo_1, "Justificación del cambio.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
documento_def(cambio_carrera_respaldo_2, "Documentación adicional que solicite la carrera de destino.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(cambio_carrera, 1, cambio_carrera_respaldo_1, opcional).
tramite_documento(cambio_carrera, 2, cambio_carrera_respaldo_2, opcional).
costo_texto(cambio_carrera, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(cambio_carrera, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(cambio_carrera, parcial).
fuente_conocimiento(cambio_carrera, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(cambio_carrera, "cambio-carrera").
nota_interna(cambio_carrera, "Falta confirmar restricciones académicas, financieras y fechas límite.").
resumen_interno(cambio_carrera, "Puede implicar homologación de materias y creación de un nuevo código estudiantil.").

% ==================================================================
% 04. Cambio de malla / pénsum
% ==================================================================
tramite(cambio_malla, "Cambio de malla / pénsum").
descripcion(cambio_malla, "Pasar del pénsum anterior a la malla curricular vigente para continuar el registro de materias.").
requisito(cambio_malla, 1, "Solicitar continuar los estudios bajo una versión distinta de la malla curricular.").
requisito(cambio_malla, 2, "El reconocimiento de materias y las asignaturas pendientes dependen de la revisión académica.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(cambio_malla, old_pensum, "Año o versión del pénsum actual", obligatorio).
campo_formulario(cambio_malla, new_pensum, "Año o versión de la nueva malla", obligatorio).
campo_formulario(cambio_malla, reason, "Motivo", opcional).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(cambio_malla_respaldo_1, "Detalle de la malla o pénsum actual, si lo solicita la Facultad.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(cambio_malla, 1, cambio_malla_respaldo_1, opcional).
costo_texto(cambio_malla, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(cambio_malla, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(cambio_malla, parcial).
fuente_conocimiento(cambio_malla, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(cambio_malla, "cambio-malla").
nota_interna(cambio_malla, "Falta confirmar condiciones, equivalencias, costos y efectos académicos.").
resumen_interno(cambio_malla, "Falta validar las condiciones y efectos académicos del cambio.").

% ==================================================================
% 05. Cambio de modalidad
% ==================================================================
tramite(cambio_modalidad, "Cambio de modalidad").
descripcion(cambio_modalidad, "Solicitar el cambio a otra modalidad o a una carrera ofrecida en otra modalidad.").
requisito(cambio_modalidad, 1, "Que la carrera esté disponible en la modalidad de destino.").
requisito(cambio_modalidad, 2, "Obtener la aprobación correspondiente para aplicar el cambio.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(cambio_modalidad, current_mode, "Modalidad actual", obligatorio).
campo_formulario(cambio_modalidad, target_mode, "Modalidad solicitada", obligatorio).
campo_formulario(cambio_modalidad, target_career, "Carrera de destino", opcional).
campo_formulario(cambio_modalidad, reason, "Motivo del cambio", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(cambio_modalidad_respaldo_1, "Respaldo del motivo, si corresponde.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(cambio_modalidad, 1, cambio_modalidad_respaldo_1, opcional).
costo_texto(cambio_modalidad, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(cambio_modalidad, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(cambio_modalidad, parcial).
fuente_conocimiento(cambio_modalidad, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(cambio_modalidad, "cambio-modalidad").
nota_interna(cambio_modalidad, "La plantilla original no pide expresamente la modalidad de destino; debe validarse su versión definitiva.").
resumen_interno(cambio_modalidad, "Requiere justificación y resolución del Consejo; la oferta debe existir en la modalidad de destino.").

% ==================================================================
% 06. Examen de gracia
% ==================================================================
tramite(examen_gracia, "Examen de gracia").
descripcion(examen_gracia, "Solicitar una oportunidad excepcional de evaluación asociada a una pérdida por faltas.").
requisito(examen_gracia, 1, "Solicitarlo para una materia perdida por faltas.").
requisito(examen_gracia, 2, "El material revisado indica al menos 70 % de asistencia y calificación previa mínima de 80; la facultad debe verificar su aplicación al caso.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(examen_gracia, subjects, "Materia(s)", obligatorio).
campo_formulario(examen_gracia, attendance, "Porcentaje de asistencia", opcional).
campo_formulario(examen_gracia, current_grade, "Calificación actual", opcional).
campo_formulario(examen_gracia, reason, "Motivo y justificación", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(examen_gracia_respaldo_1, "Reporte de asistencia.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
documento_def(examen_gracia_respaldo_2, "Calificaciones de la materia.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
documento_def(examen_gracia_respaldo_3, "Justificación y respaldos del caso.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(examen_gracia, 1, examen_gracia_respaldo_1, opcional).
tramite_documento(examen_gracia, 2, examen_gracia_respaldo_2, opcional).
tramite_documento(examen_gracia, 3, examen_gracia_respaldo_3, opcional).
costo_texto(examen_gracia, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(examen_gracia, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(examen_gracia, parcial).
fuente_conocimiento(examen_gracia, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(examen_gracia, "examen-gracia").
nota_interna(examen_gracia, "Debe confirmarse con Anny que esta interpretación corresponde al examen de gracia usado por la Facultad. El handbook distingue nota previa mínima de 80 y nota de 90 para aprobar el examen. El motor no verifica asistencia y usa SEMESTRE ACTUAL; validar pérdida por faltas.").
resumen_interno(examen_gracia, "Debe confirmarse que corresponde a la dispensa del art. 46: al menos 70 % de asistencia y nota mínima previa de 80.").

% ==================================================================
% 07. Examen de recuperación
% ==================================================================
tramite(examen_recuperacion, "Examen de recuperación").
descripcion(examen_recuperacion, "Solicitar un examen de recuperación para una asignatura reprobada.").
requisito(examen_recuperacion, 1, "Haber reprobado una asignatura sin fraude o deshonestidad académica.").
requisito(examen_recuperacion, 2, "El material revisado indica promedio aplicable mínimo de 75; la facultad debe confirmar qué período corresponde utilizar.").
requisito(examen_recuperacion, 3, "Cumplir el límite de recuperaciones aplicable. El período de cómputo requiere confirmación antes de evaluar el caso.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(examen_recuperacion, subjects, "Materia(s)", obligatorio).
campo_formulario(examen_recuperacion, period_gpa, "GPA del periodo", opcional).
campo_formulario(examen_recuperacion, last_recovery_date, "Fecha del último examen de recuperación", opcional).
campo_formulario(examen_recuperacion, reason, "Observación", opcional).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(examen_recuperacion_respaldo_1, "Registro de calificaciones o evidencia de la materia reprobada.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(examen_recuperacion, 1, examen_recuperacion_respaldo_1, opcional).
costo_texto(examen_recuperacion, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(examen_recuperacion, "Consulta con la facultad la fecha para presentar la solicitud y rendir el examen. El límite de recuperaciones y el período que se cuenta deben verificarse para tu caso.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(examen_recuperacion, parcial).
fuente_conocimiento(examen_recuperacion, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(examen_recuperacion, "examen-recuperacion").
nota_interna(examen_recuperacion, "Debe resolverse la contradicción entre la plantilla con varias materias y la regla informada de una sola materia. Conflicto con el handbook 2023: el proyecto usa 365 días y el handbook cuenta de mayo a abril; el motor usa GPA general y el handbook GPA del período con excepción para una o dos materias. No automatizar hasta acordar el criterio.").
resumen_interno(examen_recuperacion, "Presencial; una materia y un examen cada 365 días. El GPA aplicable debe ser al menos 75 y no procede por fraude.").

% ==================================================================
% 08. Examen de suficiencia
% ==================================================================
tramite(examen_suficiencia, "Examen de suficiencia").
descripcion(examen_suficiencia, "Demostrar conocimientos previos para aprobar una asignatura mediante evaluación.").
requisito(examen_suficiencia, 1, "Contar con conocimientos de la asignatura demostrables mediante estudios previos, experiencia u otro mecanismo aceptado.").
requisito(examen_suficiencia, 2, "La facultad debe autorizar la evaluación y determinar los respaldos y condiciones aplicables.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(examen_suficiencia, subjects, "Materia(s)", obligatorio).
campo_formulario(examen_suficiencia, support_type, "Tipo de respaldo", obligatorio).
campo_formulario(examen_suficiencia, reason, "Fundamento de la solicitud", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(examen_suficiencia_respaldo_1, "Certificados, sílabos o calificaciones de otra institución; o respaldo de experiencia laboral.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(examen_suficiencia, 1, examen_suficiencia_respaldo_1, opcional).
costo_texto(examen_suficiencia, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(examen_suficiencia, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(examen_suficiencia, parcial).
fuente_conocimiento(examen_suficiencia, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(examen_suficiencia, "examen-suficiencia").
nota_interna(examen_suficiencia, "Falta confirmar materias excluidas, nota mínima, número de oportunidades y costo.").
resumen_interno(examen_suficiencia, "Puede respaldarse con una materia aprobada en otra universidad o con experiencia laboral relevante.").

% ==================================================================
% 09. Examen supletorio
% ==================================================================
tramite(examen_supletorio, "Examen supletorio").
descripcion(examen_supletorio, "Solicitar un examen acumulativo cuando no fue posible rendir un examen parcial.").
requisito(examen_supletorio, 1, "No haber podido rendir un examen parcial por una circunstancia que debe someterse a revisión.").
requisito(examen_supletorio, 2, "Obtener autorización académica para rendir el examen. La valoración de la causa y sus respaldos corresponde a la facultad.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(examen_supletorio, subjects, "Materia(s)", obligatorio).
campo_formulario(examen_supletorio, partial, "Parcial no rendido", obligatorio).
campo_formulario(examen_supletorio, reason, "Circunstancia que impidió rendir el examen", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(examen_supletorio_respaldo_1, "Evidencia médica, laboral, familiar o del hecho que impidió asistir.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(examen_supletorio, 1, examen_supletorio_respaldo_1, opcional).
costo_texto(examen_supletorio, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(examen_supletorio, "El material disponible indica rendir dentro de 30 días desde la autorización. Consulta por separado el plazo para presentar la solicitud.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(examen_supletorio, parcial).
fuente_conocimiento(examen_supletorio, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(examen_supletorio, "examen-supletorio").
nota_interna(examen_supletorio, "Falta confirmar el plazo exacto para presentar inicialmente la solicitud.").
resumen_interno(examen_supletorio, "Debe enviarse con evidencias y rendirse dentro de 30 días desde la autorización.").

% ==================================================================
% 10. Homologación
% ==================================================================
tramite(homologacion, "Homologación").
descripcion(homologacion, "Reconocer materias aprobadas previamente en otra institución o dentro de la universidad.").
requisito(homologacion, 1, "Haber aprobado previamente las materias que se desean reconocer.").
requisito(homologacion, 2, "La facultad debe determinar la equivalencia de contenidos y la antigüedad admisible para el programa de destino.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(homologacion, origin_university, "Universidad o institución de procedencia", obligatorio).
campo_formulario(homologacion, target_career, "Carrera de destino en UEES", obligatorio).
campo_formulario(homologacion, subjects, "Materia(s)", opcional).
campo_formulario(homologacion, reason, "Información adicional", opcional).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(homologacion_respaldo_1, "Documento de identidad.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
documento_def(homologacion_respaldo_2, "Certificado oficial de calificaciones.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
documento_def(homologacion_respaldo_3, "Sílabos o programas de las materias.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
documento_def(homologacion_respaldo_4, "Documentos legalizados cuando corresponda.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(homologacion, 1, homologacion_respaldo_1, opcional).
tramite_documento(homologacion, 2, homologacion_respaldo_2, opcional).
tramite_documento(homologacion, 3, homologacion_respaldo_3, opcional).
tramite_documento(homologacion, 4, homologacion_respaldo_4, opcional).
costo_texto(homologacion, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(homologacion, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(homologacion, parcial).
fuente_conocimiento(homologacion, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(homologacion, "homologacion").
nota_interna(homologacion, "La Facultad debe confirmar excepciones, costos y documentos según la institución de procedencia.").
resumen_interno(homologacion, "Se comparan contenidos con equivalencia mínima del 80 % y, normalmente, una antigüedad máxima de 10 años.").

% ==================================================================
% 11. Incompleto
% ==================================================================
tramite(incompleto, "Incompleto").
descripcion(incompleto, "Solicitar la condición de incompleto por enfermedad o calamidad que impide continuar temporalmente.").
requisito(incompleto, 1, "Tener una situación grave y verificable que impida completar temporalmente los estudios.").
requisito(incompleto, 2, "Obtener resolución sobre las materias afectadas y las condiciones para retomarlas.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(incompleto, subjects, "Materia(s)", obligatorio).
campo_formulario(incompleto, cause_type, "Tipo de causa", obligatorio).
campo_formulario(incompleto, reason, "Detalle de la situación", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(incompleto_respaldo_1, "Certificado o evidencia verificable de la situación.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
documento_def(incompleto_respaldo_2, "Detalle de las materias afectadas.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(incompleto, 1, incompleto_respaldo_1, opcional).
tramite_documento(incompleto, 2, incompleto_respaldo_2, opcional).
costo_texto(incompleto, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(incompleto, "Consulta el plazo de tu período académico. El material disponible menciona hasta un mes después de terminar el semestre o ciclo; confirma su aplicación a tu caso.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(incompleto, parcial).
fuente_conocimiento(incompleto, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(incompleto, "incompleto").
nota_interna(incompleto, "Falta confirmar el plazo exacto aplicable a cada tipo de periodo académico. El handbook indica solicitud hasta un mes después de terminar el período y alcance sobre todas las materias salvo excepción del Consejo; conciliar con los plazos y selección del portal.").
resumen_interno(incompleto, "Requiere respaldo verificable y se aplican plazos distintos según el tipo de periodo.").

% ==================================================================
% 12. Person to Person
% ==================================================================
tramite(person_to_person, "Person to Person").
descripcion(person_to_person, "Solicitar una modalidad individual para cursar una materia específica.").
requisito(person_to_person, 1, "La disponibilidad y las condiciones para cursar la materia de forma individual deben ser determinadas por la facultad antes de continuar.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(person_to_person, subjects, "Materia(s)", obligatorio).
campo_formulario(person_to_person, reason, "Motivo", opcional).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(person_to_person_respaldo_1, "Documentación que la facultad indique después de revisar tu caso.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(person_to_person, 1, person_to_person_respaldo_1, opcional).
costo_texto(person_to_person, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(person_to_person, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(person_to_person, pendiente).
fuente_conocimiento(person_to_person, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(person_to_person, "person-to-person").
nota_interna(person_to_person, "No se encontraron reglas suficientes sobre quién califica, disponibilidad, costo, duración ni aprobación.").
resumen_interno(person_to_person, "La plantilla identifica la materia, pero todavía faltan reglas, requisitos, costos y condiciones.").

% ==================================================================
% 13. Recalificación de examen
% ==================================================================
tramite(recalificacion, "Recalificación de examen").
descripcion(recalificacion, "Solicitar la revisión formal de la calificación de un examen de una materia activa.").
requisito(recalificacion, 1, "Solicitar la revisión de una evaluación que admita recalificación y fundamentar el desacuerdo en puntos concretos.").
requisito(recalificacion, 2, "La revisión no garantiza un cambio de calificación.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(recalificacion, subjects, "Materia(s)", obligatorio).
campo_formulario(recalificacion, partial, "Evaluación", obligatorio).
campo_formulario(recalificacion, grade_published_at, "Fecha y hora de entrega o publicación de la nota", obligatorio).
campo_formulario(recalificacion, reason, "Puntos específicos que fundamentan el reclamo", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(recalificacion_respaldo_1, "Copia o evidencia del examen y su calificación.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
documento_def(recalificacion_respaldo_2, "Detalle de los puntos que solicitas revisar.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(recalificacion, 1, recalificacion_respaldo_1, opcional).
tramite_documento(recalificacion, 2, recalificacion_respaldo_2, opcional).
costo_texto(recalificacion, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(recalificacion, "Presenta el reclamo cuanto antes. La información disponible menciona 48 horas desde la entrega o publicación; confirma el plazo y su forma de cómputo con la facultad.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(recalificacion, parcial).
fuente_conocimiento(recalificacion, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(recalificacion, "recalificacion").
nota_interna(recalificacion, "Falta confirmar cómo se cuentan las 48 horas y el procedimiento de revisión. El handbook limita la recalificación a evaluaciones finales del parcial. El portal permite otra evaluación activa; confirmar alcance y si las 48 horas son límite obligatorio.").
resumen_interno(recalificacion, "Debe presentarse idealmente dentro de 48 horas; no aplica a recuperación ni supletorio.").

% ==================================================================
% 14. Registro extemporáneo
% ==================================================================
tramite(registro_extemporaneo, "Registro extemporáneo").
descripcion(registro_extemporaneo, "Solicitar el registro tardío de materias a las que el estudiante ha asistido como oyente.").
requisito(registro_extemporaneo, 1, "Solicitar el registro de materias una vez terminado el plazo ordinario.").
requisito(registro_extemporaneo, 2, "La asistencia como oyente está contemplada por el proyecto, pero su exigencia debe confirmarse; no equivale a matrícula ni garantiza autorización.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(registro_extemporaneo, subjects, "Materia(s)", obligatorio).
campo_formulario(registro_extemporaneo, attended_as_listener, "He asistido como oyente a las materias indicadas", opcional).
campo_formulario(registro_extemporaneo, reason, "Motivo por el que no se realizó el registro a tiempo", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(registro_extemporaneo_respaldo_1, "Evidencia de asistencia o autorización del docente, si está disponible.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(registro_extemporaneo, 1, registro_extemporaneo_respaldo_1, opcional).
costo_texto(registro_extemporaneo, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(registro_extemporaneo, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(registro_extemporaneo, parcial).
fuente_conocimiento(registro_extemporaneo, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(registro_extemporaneo, "registro-extemporaneo").
nota_interna(registro_extemporaneo, "Falta confirmar fecha límite, aprobación docente, cupos, pagos y restricciones.").
resumen_interno(registro_extemporaneo, "La plantilla confirma que se refiere a registro de materias, no a consignación atrasada de notas.").

% ==================================================================
% 15. Reincorporación a carrera
% ==================================================================
tramite(reincorporacion_carrera, "Reincorporación a carrera").
descripcion(reincorporacion_carrera, "Retomar una carrera después de un periodo de inactividad para completar los estudios.").
requisito(reincorporacion_carrera, 1, "Haber cursado la carrera y solicitar el retorno después de un período de inactividad.").
requisito(reincorporacion_carrera, 2, "El retorno depende de la revisión de la malla aplicable y las condiciones académicas y financieras.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(reincorporacion_carrera, last_active_period, "Último periodo cursado", opcional).
campo_formulario(reincorporacion_carrera, reason, "Motivo de la interrupción y reincorporación", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(reincorporacion_carrera_respaldo_1, "Respaldos del motivo de interrupción, si corresponde.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(reincorporacion_carrera, 1, reincorporacion_carrera_respaldo_1, opcional).
costo_texto(reincorporacion_carrera, "No hay tarifa de solicitud confirmada. El proyecto menciona revisión de la tabla de pagos; consultar la liquidación aplicable.").
plazo_texto(reincorporacion_carrera, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(reincorporacion_carrera, parcial).
fuente_conocimiento(reincorporacion_carrera, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(reincorporacion_carrera, "reincorporacion").
nota_interna(reincorporacion_carrera, "Falta confirmar tiempo máximo de inactividad, malla aplicable, deudas y proceso financiero.").
resumen_interno(reincorporacion_carrera, "La agenda de ejemplo incluye además el ajuste de la tabla de pagos.").

% ==================================================================
% 16. Retiro de carrera
% ==================================================================
tramite(retiro_carrera, "Retiro de carrera").
descripcion(retiro_carrera, "Permite solicitar dejar la carrera actual y revisar tus obligaciones academicas y financieras.").
requisito(retiro_carrera, 1, "Solicitar dejar la carrera actual y formalizar el retiro ante la facultad.").
requisito(retiro_carrera, 2, "El retiro no elimina automáticamente las obligaciones académicas o financieras pendientes.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(retiro_carrera, reason, "Motivo del retiro", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(retiro_carrera_respaldo_1, "Respaldo del motivo, si corresponde.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(retiro_carrera, 1, retiro_carrera_respaldo_1, opcional).
costo_texto(retiro_carrera, "No hay tarifa de solicitud confirmada. El ajuste de la tabla de pagos requiere revisión y no equivale a eliminación automática de deudas.").
plazo_texto(retiro_carrera, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(retiro_carrera, parcial).
fuente_conocimiento(retiro_carrera, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(retiro_carrera, "retiro-carrera").
nota_interna(retiro_carrera, "Debe diferenciarse formalmente de cambio de carrera y retiro de universidad.").
resumen_interno(retiro_carrera, "En la agenda suele estar asociado a un cambio de carrera, pero puede presentarse por otros motivos.").

% ==================================================================
% 17. Retiro de materia
% ==================================================================
tramite(retiro_materia, "Retiro de materia").
descripcion(retiro_materia, "Solicitar el retiro de una materia antes del límite correspondiente.").
requisito(retiro_materia, 1, "Solicitar y formalizar el retiro de una materia dentro del plazo aplicable.").
requisito(retiro_materia, 2, "Dejar de asistir no equivale a retiro. La facultad debe revisar las consecuencias académicas y económicas.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(retiro_materia, subjects, "Materia(s)", obligatorio).
campo_formulario(retiro_materia, withdrawal_week, "Semana del periodo en la que solicita el retiro", opcional).
campo_formulario(retiro_materia, reason, "Motivo", opcional).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(retiro_materia_respaldo_1, "Respaldo del motivo, si corresponde.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(retiro_materia, 1, retiro_materia_respaldo_1, opcional).
costo_texto(retiro_materia, "Valor por confirmar con la facultad. Los cargos y reintegros dependen del momento del retiro y presentan diferencias en las fuentes; no hay una tarifa fija validada.").
plazo_texto(retiro_materia, "Consulta el calendario antes de solicitarlo. La información disponible lo relaciona con el primer parcial; la facultad confirmará el límite aplicable.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(retiro_materia, parcial).
fuente_conocimiento(retiro_materia, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(retiro_materia, "retiro-materia").
nota_interna(retiro_materia, "Debe confirmarse el porcentaje económico: la plantilla indica 100 %, pero las respuestas describen 25 %, 50 % y 100 % según la semana. Conflicto con el handbook: describe reintegros de hasta 80 % en la primera semana y 50 % en la segunda. Confirmar el criterio económico antes de informar un valor.").
resumen_interno(retiro_materia, "Antes de exámenes del primer parcial. El costo reportado varía por semana; después del límite la materia cuenta como reprobada.").

% ==================================================================
% 18. Retiro de universidad
% ==================================================================
tramite(retiro_universidad, "Retiro de universidad").
descripcion(retiro_universidad, "Permite solicitar la salida completa de la universidad y la revision de tus materias y obligaciones pendientes.").
requisito(retiro_universidad, 1, "Solicitar y formalizar la salida completa de la universidad, no solo de una carrera.").
requisito(retiro_universidad, 2, "La facultad debe determinar las obligaciones que permanecen después del retiro.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(retiro_universidad, reason, "Motivo del retiro", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(retiro_universidad_respaldo_1, "Respaldo del motivo, si corresponde.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(retiro_universidad, 1, retiro_universidad_respaldo_1, opcional).
costo_texto(retiro_universidad, "No hay tarifa de solicitud confirmada. La salida no elimina automáticamente obligaciones pendientes.").
plazo_texto(retiro_universidad, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(retiro_universidad, parcial).
fuente_conocimiento(retiro_universidad, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(retiro_universidad, "retiro-universidad").
nota_interna(retiro_universidad, "La redacción de la plantilla debe revisarse porque utiliza términos similares al retiro de carrera.").
resumen_interno(retiro_universidad, "Debe diferenciarse formalmente del retiro de carrera antes de automatizar la decisión.").

% ==================================================================
% 19. Retiro extemporáneo
% ==================================================================
tramite(retiro_extemporaneo, "Retiro extemporáneo").
descripcion(retiro_extemporaneo, "Solicitar fuera del plazo ordinario el retiro de una o varias materias.").
requisito(retiro_extemporaneo, 1, "Solicitar el retiro una vez terminado el plazo ordinario y someter la circunstancia excepcional a revisión académica.").
requisito(retiro_extemporaneo, 2, "La autorización y las consecuencias académicas y financieras dependen de la revisión del caso.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(retiro_extemporaneo, subjects, "Materia(s)", obligatorio).
campo_formulario(retiro_extemporaneo, reason, "Motivo excepcional del retiro", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(retiro_extemporaneo_respaldo_1, "Evidencias de la circunstancia excepcional.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(retiro_extemporaneo, 1, retiro_extemporaneo_respaldo_1, opcional).
costo_texto(retiro_extemporaneo, "El material revisado menciona cobro del valor total de la asignatura; el monto y las excepciones requieren liquidación de la facultad. No es una tarifa fija del trámite.").
plazo_texto(retiro_extemporaneo, "Se solicita una vez terminado el plazo ordinario. Consulta con la facultad la fecha máxima para revisar tu caso.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(retiro_extemporaneo, parcial).
fuente_conocimiento(retiro_extemporaneo, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(retiro_extemporaneo, "retiro-extemporaneo").
nota_interna(retiro_extemporaneo, "Falta confirmar qué situaciones excepcionales se aceptan y quién las valida.").
resumen_interno(retiro_extemporaneo, "Implica cobro del 100 % y pérdida de créditos según la plantilla; suele requerir justificación excepcional.").

% ==================================================================
% 20. Tercer registro
% ==================================================================
tramite(tercer_registro, "Tercer registro").
descripcion(tercer_registro, "Solicitar autorización para cursar por tercera vez una materia reprobada dos veces.").
requisito(tercer_registro, 1, "Haber reprobado la misma materia en dos registros anteriores.").
requisito(tercer_registro, 2, "El material revisado excluye Medicina y Enfermería y limita los terceros registros durante la carrera; confirmar su aplicación al caso.").
requisito(tercer_registro, 3, "Obtener autorización académica y confirmar la condición de cursar la materia en exclusividad.").

% Datos de captura (obligatorios u opcionales en el portal).
campo_formulario(tercer_registro, subjects, "Materia(s)", obligatorio).
campo_formulario(tercer_registro, attempts, "Registros anteriores", obligatorio).

% Respaldos sugeridos; no son plantillas adjuntas ni archivos personales.
documento_def(tercer_registro_respaldo_1, "Calificaciones y periodos de los dos registros anteriores.", "Consulta con la facultad si debes adjuntar este respaldo y en qué formato.", "Por confirmar", sin_url).
tramite_documento(tercer_registro, 1, tercer_registro_respaldo_1, opcional).
costo_texto(tercer_registro, "No hay un monto confirmado para este trámite en la información disponible. Consulta el arancel con la facultad; no se puede afirmar que sea gratuito.").
plazo_texto(tercer_registro, "Consulta con la facultad la fecha límite para tu período académico; no hay un plazo confirmado en la información disponible.").

% Metadatos de revision; no son mensajes para el estudiante.
estado_conocimiento(tercer_registro, parcial).
fuente_conocimiento(tercer_registro, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
identificador_agenda(tercer_registro, "tercer-registro").
nota_interna(tercer_registro, "Falta confirmar excepciones, simultaneidad con otras materias y consecuencias de una nueva reprobación.").
resumen_interno(tercer_registro, "No aplica a Medicina ni Enfermería; el Consejo puede autorizar hasta tres asignaturas durante la carrera.").

% Archivo de trazabilidad para el equipo; las reglas actuales no lo consultan.
requisito_original_interno(alcance_homologacion, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(alcance_homologacion, 2, "Ya existe una homologación aprobada.").
requisito_original_interno(alcance_homologacion, 3, "Una materia o documento no fue considerado en esa homologación.").
requisito_original_interno(alcance_homologacion, 4, "Puedes identificar la materia y explicar qué respaldo faltó.").
requisito_original_interno(alcance_homologacion, 5, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(alcance_homologacion, 6, "Formulario: Motivo y documento que faltó (obligatorio).").
requisito_original_interno(alcance_homologacion, 7, "Por confirmar con la Facultad: Falta confirmar plazo, autoridad que valida el documento y causales de rechazo.").
requisito_original_interno(ayudante_catedra, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(ayudante_catedra, 2, "Puedes identificar la materia y el periodo en que deseas participar.").
requisito_original_interno(ayudante_catedra, 3, "Aceptas las responsabilidades indicadas en la plantilla.").
requisito_original_interno(ayudante_catedra, 4, "La selección final debe ser aprobada por la Facultad.").
requisito_original_interno(ayudante_catedra, 5, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(ayudante_catedra, 6, "Formulario: Periodo y año (obligatorio).").
requisito_original_interno(ayudante_catedra, 7, "Formulario: GPA general (obligatorio).").
requisito_original_interno(ayudante_catedra, 8, "Formulario: Calificación en la materia (obligatorio).").
requisito_original_interno(ayudante_catedra, 9, "Formulario: Motivación (opcional).").
requisito_original_interno(ayudante_catedra, 10, "Por confirmar con la Facultad: Falta confirmar promedio mínimo, quién puede postular, cupos, funciones y criterio de selección.").
requisito_original_interno(ayudante_catedra, 11, "El motor del proyecto contempla GPA general mínimo de 90, materia aprobada con nota mínima de 90 y ausencia de materias reprobadas. Son criterios de implementación pendientes de validación; la plantilla menciona retribución de 5 puntos.").
requisito_original_interno(cambio_carrera, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(cambio_carrera, 2, "Actualmente constas en una carrera de la universidad.").
requisito_original_interno(cambio_carrera, 3, "Has definido la carrera a la que deseas cambiarte.").
requisito_original_interno(cambio_carrera, 4, "Entiendes que pueden cambiar tu malla, homologaciones y código estudiantil.").
requisito_original_interno(cambio_carrera, 5, "Formulario: Carrera de destino (obligatorio).").
requisito_original_interno(cambio_carrera, 6, "Formulario: Solicito también la creación de un nuevo código (opcional). La casilla no exige estar marcada; no equivale a condición académica verificada.").
requisito_original_interno(cambio_carrera, 7, "Formulario: Motivo del cambio (obligatorio).").
requisito_original_interno(cambio_carrera, 8, "Por confirmar con la Facultad: Falta confirmar restricciones académicas, financieras y fechas límite.").
requisito_original_interno(cambio_malla, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(cambio_malla, 2, "Puedes identificar tu pénsum actual y la malla de destino.").
requisito_original_interno(cambio_malla, 3, "Deseas continuar tus estudios bajo una versión curricular diferente.").
requisito_original_interno(cambio_malla, 4, "Aceptas que pueden cambiar equivalencias y materias pendientes.").
requisito_original_interno(cambio_malla, 5, "Formulario: Año o versión del pénsum actual (obligatorio).").
requisito_original_interno(cambio_malla, 6, "Formulario: Año o versión de la nueva malla (obligatorio).").
requisito_original_interno(cambio_malla, 7, "Formulario: Motivo (opcional).").
requisito_original_interno(cambio_malla, 8, "Por confirmar con la Facultad: Falta confirmar condiciones, equivalencias, costos y efectos académicos.").
requisito_original_interno(cambio_modalidad, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(cambio_modalidad, 2, "La carrera está disponible en la modalidad solicitada.").
requisito_original_interno(cambio_modalidad, 3, "Puedes indicar modalidad actual, modalidad de destino y motivo.").
requisito_original_interno(cambio_modalidad, 4, "Comprendes que el cambio requiere aprobación.").
requisito_original_interno(cambio_modalidad, 5, "Formulario: Modalidad actual (obligatorio). Opciones del portal: Presencial; Semipresencial; En línea; Híbrida; A distancia; Dual.").
requisito_original_interno(cambio_modalidad, 6, "Formulario: Modalidad solicitada (obligatorio). Opciones del portal: Presencial; Semipresencial; En línea; Híbrida; A distancia; Dual.").
requisito_original_interno(cambio_modalidad, 7, "Formulario: Carrera de destino (opcional).").
requisito_original_interno(cambio_modalidad, 8, "Formulario: Motivo del cambio (obligatorio).").
requisito_original_interno(cambio_modalidad, 9, "Por confirmar con la Facultad: La plantilla original no pide expresamente la modalidad de destino; debe validarse su versión definitiva.").
requisito_original_interno(cambio_modalidad, 10, "Advertencia de la plantilla: La plantilla original no pide explícitamente la modalidad de destino; el prototipo agrega ese campo para evitar ambigüedades.").
requisito_original_interno(examen_gracia, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(examen_gracia, 2, "La solicitud se relaciona con una materia perdida por faltas.").
requisito_original_interno(examen_gracia, 3, "Como referencia preliminar, registras al menos 70 % de asistencia.").
requisito_original_interno(examen_gracia, 4, "Como referencia preliminar, tenías una calificación mínima de 80 antes de perderla por faltas.").
requisito_original_interno(examen_gracia, 5, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(examen_gracia, 6, "Formulario: Porcentaje de asistencia (opcional).").
requisito_original_interno(examen_gracia, 7, "Formulario: Calificación actual (opcional).").
requisito_original_interno(examen_gracia, 8, "Formulario: Motivo y justificación (obligatorio).").
requisito_original_interno(examen_gracia, 9, "Por confirmar con la Facultad: Debe confirmarse con Anny que esta interpretación corresponde al examen de gracia usado por la Facultad.").
requisito_original_interno(examen_gracia, 10, "El handbook distingue nota previa mínima de 80 y nota de 90 para aprobar el examen. El motor no verifica asistencia y usa SEMESTRE ACTUAL; validar pérdida por faltas.").
requisito_original_interno(examen_recuperacion, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(examen_recuperacion, 2, "La materia fue reprobada y no hubo fraude académico.").
requisito_original_interno(examen_recuperacion, 3, "El GPA aplicable es de al menos 75.").
requisito_original_interno(examen_recuperacion, 4, "No has rendido otro examen de recuperación durante los últimos 365 días.").
requisito_original_interno(examen_recuperacion, 5, "La solicitud se presenta al finalizar el semestre; el examen se rendiría hasta la primera semana del periodo siguiente.").
requisito_original_interno(examen_recuperacion, 6, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(examen_recuperacion, 7, "Formulario: GPA del periodo (opcional).").
requisito_original_interno(examen_recuperacion, 8, "Formulario: Fecha del último examen de recuperación (opcional).").
requisito_original_interno(examen_recuperacion, 9, "Formulario: Observación (opcional).").
requisito_original_interno(examen_recuperacion, 10, "Por confirmar con la Facultad: Debe resolverse la contradicción entre la plantilla con varias materias y la regla informada de una sola materia.").
requisito_original_interno(examen_recuperacion, 11, "Advertencia de la plantilla: La plantilla permite varias materias, pero la información levantada indica una sola materia por cada 365 días. El MVP limita el caso a una materia.").
requisito_original_interno(examen_recuperacion, 12, "Conflicto con el handbook 2023: el proyecto usa 365 días y el handbook cuenta de mayo a abril; el motor usa GPA general y el handbook GPA del período con excepción para una o dos materias. No automatizar hasta acordar el criterio.").
requisito_original_interno(examen_suficiencia, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(examen_suficiencia, 2, "Ya posees conocimientos demostrables de la materia.").
requisito_original_interno(examen_suficiencia, 3, "Puedes sustentarlos con estudios previos, experiencia laboral relevante u otro mecanismo aceptado.").
requisito_original_interno(examen_suficiencia, 4, "Solicitas ser evaluado para acreditar la asignatura.").
requisito_original_interno(examen_suficiencia, 5, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(examen_suficiencia, 6, "Formulario: Tipo de respaldo (obligatorio). Opciones del portal: Materia aprobada en otra universidad; Experiencia laboral de al menos 5 años; Proceso de convalidación; Otro.").
requisito_original_interno(examen_suficiencia, 7, "Formulario: Fundamento de la solicitud (obligatorio).").
requisito_original_interno(examen_suficiencia, 8, "Por confirmar con la Facultad: Falta confirmar materias excluidas, nota mínima, número de oportunidades y costo.").
requisito_original_interno(examen_suficiencia, 9, "Advertencia de la plantilla: La plantilla original contiene un posible error de copia: menciona “examen de gracia”. La copia conserva ese texto oficial; confirma su corrección con la Facultad antes de enviarla.").
requisito_original_interno(examen_supletorio, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(examen_supletorio, 2, "No pudiste rendir uno de los exámenes parciales por una causa justificable.").
requisito_original_interno(examen_supletorio, 3, "Puedes respaldar la calamidad o impedimento.").
requisito_original_interno(examen_supletorio, 4, "El examen debe rendirse dentro de los 30 días posteriores a la autorización.").
requisito_original_interno(examen_supletorio, 5, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(examen_supletorio, 6, "Formulario: Parcial no rendido (obligatorio). Opciones del portal: Primer parcial; Segundo parcial.").
requisito_original_interno(examen_supletorio, 7, "Formulario: Circunstancia que impidió rendir el examen (obligatorio).").
requisito_original_interno(examen_supletorio, 8, "Por confirmar con la Facultad: Falta confirmar el plazo exacto para presentar inicialmente la solicitud.").
requisito_original_interno(homologacion, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(homologacion, 2, "Aprobaste materias en otra institución o programa.").
requisito_original_interno(homologacion, 3, "Los contenidos pueden alcanzar una equivalencia mínima aproximada del 80 %.").
requisito_original_interno(homologacion, 4, "Normalmente las materias no superan 10 años de antigüedad.").
requisito_original_interno(homologacion, 5, "Formulario: Universidad o institución de procedencia (obligatorio).").
requisito_original_interno(homologacion, 6, "Formulario: Carrera de destino en UEES (obligatorio).").
requisito_original_interno(homologacion, 7, "Formulario: Materia(s) (opcional).").
requisito_original_interno(homologacion, 8, "Formulario: Información adicional (opcional).").
requisito_original_interno(homologacion, 9, "Por confirmar con la Facultad: La Facultad debe confirmar excepciones, costos y documentos según la institución de procedencia.").
requisito_original_interno(incompleto, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(incompleto, 2, "Una enfermedad, calamidad u otra situación verificable impide completar temporalmente la materia.").
requisito_original_interno(incompleto, 3, "Puedes identificar las materias afectadas.").
requisito_original_interno(incompleto, 4, "Puedes presentar evidencia de la causa.").
requisito_original_interno(incompleto, 5, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(incompleto, 6, "Formulario: Tipo de causa (obligatorio). Opciones del portal: Enfermedad o accidente; Embarazo o parto; Calamidad doméstica; Situación judicial; Pérdida de ingresos; Otra causa verificable.").
requisito_original_interno(incompleto, 7, "Formulario: Detalle de la situación (obligatorio).").
requisito_original_interno(incompleto, 8, "Por confirmar con la Facultad: Falta confirmar el plazo exacto aplicable a cada tipo de periodo académico.").
requisito_original_interno(incompleto, 9, "El handbook indica solicitud hasta un mes después de terminar el período y alcance sobre todas las materias salvo excepción del Consejo; conciliar con los plazos y selección del portal.").
requisito_original_interno(person_to_person, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(person_to_person, 2, "Puedes identificar la materia que deseas cursar bajo esta modalidad.").
requisito_original_interno(person_to_person, 3, "Antes de continuar debes consultar a Anny, porque los criterios aún no están documentados.").
requisito_original_interno(person_to_person, 4, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(person_to_person, 5, "Formulario: Motivo (opcional).").
requisito_original_interno(person_to_person, 6, "Por confirmar con la Facultad: No se encontraron reglas suficientes sobre quién califica, disponibilidad, costo, duración ni aprobación.").
requisito_original_interno(recalificacion, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(recalificacion, 2, "Se trata de un examen de una materia activa en el periodo.").
requisito_original_interno(recalificacion, 3, "La solicitud se presenta idealmente dentro de 48 horas desde la entrega o publicación de la nota.").
requisito_original_interno(recalificacion, 4, "No corresponde a un examen de recuperación ni supletorio.").
requisito_original_interno(recalificacion, 5, "Puedes señalar puntos concretos que justifican la revisión.").
requisito_original_interno(recalificacion, 6, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(recalificacion, 7, "Formulario: Evaluación (obligatorio). Opciones del portal: Primer parcial; Segundo parcial; Otra evaluación activa.").
requisito_original_interno(recalificacion, 8, "Formulario: Fecha y hora de entrega o publicación de la nota (obligatorio).").
requisito_original_interno(recalificacion, 9, "Formulario: Puntos específicos que fundamentan el reclamo (obligatorio).").
requisito_original_interno(recalificacion, 10, "Por confirmar con la Facultad: Falta confirmar cómo se cuentan las 48 horas y el procedimiento de revisión.").
requisito_original_interno(recalificacion, 11, "El handbook limita la recalificación a evaluaciones finales del parcial. El portal permite otra evaluación activa; confirmar alcance y si las 48 horas son límite obligatorio.").
requisito_original_interno(registro_extemporaneo, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(registro_extemporaneo, 2, "No registraste una o varias materias dentro del plazo ordinario.").
requisito_original_interno(registro_extemporaneo, 3, "Has asistido como oyente a las materias indicadas.").
requisito_original_interno(registro_extemporaneo, 4, "Puedes explicar por qué no realizaste el registro a tiempo.").
requisito_original_interno(registro_extemporaneo, 5, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(registro_extemporaneo, 6, "Formulario: He asistido como oyente a las materias indicadas (opcional). La casilla no exige estar marcada; no equivale a condición académica verificada.").
requisito_original_interno(registro_extemporaneo, 7, "Formulario: Motivo por el que no se realizó el registro a tiempo (obligatorio).").
requisito_original_interno(registro_extemporaneo, 8, "Por confirmar con la Facultad: Falta confirmar fecha límite, aprobación docente, cupos, pagos y restricciones.").
requisito_original_interno(reincorporacion_carrera, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(reincorporacion_carrera, 2, "Fuiste estudiante de la carrera y estuviste inactivo durante uno o más periodos.").
requisito_original_interno(reincorporacion_carrera, 3, "Deseas retomar los estudios en esa carrera.").
requisito_original_interno(reincorporacion_carrera, 4, "Puedes indicar tu último periodo activo y el motivo de la interrupción.").
requisito_original_interno(reincorporacion_carrera, 5, "Formulario: Último periodo cursado (opcional).").
requisito_original_interno(reincorporacion_carrera, 6, "Formulario: Motivo de la interrupción y reincorporación (obligatorio).").
requisito_original_interno(reincorporacion_carrera, 7, "Por confirmar con la Facultad: Falta confirmar tiempo máximo de inactividad, malla aplicable, deudas y proceso financiero.").
requisito_original_interno(retiro_carrera, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(retiro_carrera, 2, "Deseas dejar tu carrera actual, aunque podrías permanecer en la universidad.").
requisito_original_interno(retiro_carrera, 3, "Puedes explicar el motivo del retiro.").
requisito_original_interno(retiro_carrera, 4, "Comprendes que puede requerirse ajustar o eliminar la tabla de pagos.").
requisito_original_interno(retiro_carrera, 5, "Formulario: Motivo del retiro (obligatorio).").
requisito_original_interno(retiro_carrera, 6, "Por confirmar con la Facultad: Debe diferenciarse formalmente de cambio de carrera y retiro de universidad.").
requisito_original_interno(retiro_materia, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(retiro_materia, 2, "Solicitas retirar una materia específica.").
requisito_original_interno(retiro_materia, 3, "La petición se presenta antes de la semana de exámenes del primer parcial.").
requisito_original_interno(retiro_materia, 4, "Comprendes que existen consecuencias académicas y económicas según la semana.").
requisito_original_interno(retiro_materia, 5, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(retiro_materia, 6, "Formulario: Semana del periodo en la que solicita el retiro (opcional).").
requisito_original_interno(retiro_materia, 7, "Formulario: Motivo (opcional).").
requisito_original_interno(retiro_materia, 8, "Por confirmar con la Facultad: Debe confirmarse el porcentaje económico: la plantilla indica 100 %, pero las respuestas describen 25 %, 50 % y 100 % según la semana.").
requisito_original_interno(retiro_materia, 9, "Advertencia de la plantilla: La plantilla menciona cobro del 100 %, pero la información levantada indica 25 % en la primera semana, 50 % en la segunda y 100 % desde la tercera. Debe validarse antes de automatizar el aviso.").
requisito_original_interno(retiro_materia, 10, "Conflicto con el handbook: describe reintegros de hasta 80 % en la primera semana y 50 % en la segunda. Confirmar el criterio económico antes de informar un valor.").
requisito_original_interno(retiro_universidad, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(retiro_universidad, 2, "Deseas dejar completamente la universidad, no solamente una carrera.").
requisito_original_interno(retiro_universidad, 3, "Puedes explicar el motivo.").
requisito_original_interno(retiro_universidad, 4, "Comprendes que se revisará la tabla de pagos y otras obligaciones.").
requisito_original_interno(retiro_universidad, 5, "Formulario: Motivo del retiro (obligatorio).").
requisito_original_interno(retiro_universidad, 6, "Por confirmar con la Facultad: La redacción de la plantilla debe revisarse porque utiliza términos similares al retiro de carrera.").
requisito_original_interno(retiro_extemporaneo, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(retiro_extemporaneo, 2, "El plazo ordinario para retirar la materia ya terminó.").
requisito_original_interno(retiro_extemporaneo, 3, "Existe una circunstancia excepcional que puedes justificar.").
requisito_original_interno(retiro_extemporaneo, 4, "Comprendes que la plantilla señala cobro del 100 % y pérdida de créditos.").
requisito_original_interno(retiro_extemporaneo, 5, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(retiro_extemporaneo, 6, "Formulario: Motivo excepcional del retiro (obligatorio).").
requisito_original_interno(retiro_extemporaneo, 7, "Por confirmar con la Facultad: Falta confirmar qué situaciones excepcionales se aceptan y quién las valida.").
requisito_original_interno(tercer_registro, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito_original_interno(tercer_registro, 2, "Reprobaste la misma materia en dos registros anteriores.").
requisito_original_interno(tercer_registro, 3, "Tu carrera no es Medicina ni Enfermería.").
requisito_original_interno(tercer_registro, 4, "No has excedido el máximo de tres materias con tercer registro durante la carrera.").
requisito_original_interno(tercer_registro, 5, "Debes estar preparado para cursar exclusivamente la materia autorizada, según la información levantada.").
requisito_original_interno(tercer_registro, 6, "Formulario: Materia(s) (obligatorio).").
requisito_original_interno(tercer_registro, 7, "Formulario: Registros anteriores (obligatorio). Completar exactamente dos registros con año, sesión y puntaje; registrar las dos reprobaciones previas.").
requisito_original_interno(tercer_registro, 8, "Por confirmar con la Facultad: Falta confirmar excepciones, simultaneidad con otras materias y consecuencias de una nueva reprobación.").
plazo_original_interno(alcance_homologacion, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(ayudante_catedra, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(cambio_carrera, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(cambio_malla, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(cambio_modalidad, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(examen_gracia, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(examen_recuperacion, "Según el portal: solicitar al finalizar el semestre y rendir hasta la primera semana siguiente. Los 365 días describen frecuencia, no plazo de presentación; criterio pendiente de conciliación con el handbook.").
plazo_original_interno(examen_suficiencia, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(examen_supletorio, "Rendir dentro de 30 días desde la autorización. El plazo inicial de solicitud sigue pendiente.").
plazo_original_interno(homologacion, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(incompleto, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(person_to_person, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(recalificacion, "El portal recomienda idealmente 48 horas desde entrega o publicación de la nota. Confirmar inicio del cómputo y si es límite obligatorio.").
plazo_original_interno(registro_extemporaneo, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(reincorporacion_carrera, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(retiro_carrera, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(retiro_materia, "Según el portal: antes de la semana de exámenes del primer parcial. Confirmar calendario y límite institucional.").
plazo_original_interno(retiro_universidad, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_original_interno(retiro_extemporaneo, "Después del plazo ordinario de retiro; fecha límite excepcional no definida.").
plazo_original_interno(tercer_registro, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
