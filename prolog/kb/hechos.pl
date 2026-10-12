:- encoding(utf8).

% Fichas informativas de los 20 trámites del proyecto Agenda Anny.
% Fuente principal: agenda-anny.rar / data.js (transactions y guidance).

:- multifile tramite/2.
:- multifile descripcion/2.
:- multifile requisito/3.
:- multifile documento_def/5.
:- multifile tramite_documento/4.
:- multifile costo/3.
:- multifile plazo_texto/2.
:- multifile parametro/3.
:- multifile tipo_solicitante_permitido/2.
:- multifile estado_conocimiento/2.
:- multifile version_conocimiento/1.
:- multifile fuente_conocimiento/2.
:- multifile identificador_agenda/2.
:- multifile campo_formulario/4.
:- multifile pendiente_validacion/2.
:- multifile observacion_conocimiento/2.

version_conocimiento("agenda-anny-002").

% Adaptador de versión existente; no evalúa elegibilidad.
kb_version(Version) :- version_conocimiento(Version).

% ------------------------------------------------------------------
% Catálogo
% ------------------------------------------------------------------
tramite(alcance_homologacion, "Alcance de homologación").
tramite(ayudante_catedra, "Ayudante de cátedra").
tramite(cambio_carrera, "Cambio de carrera").
tramite(cambio_malla, "Cambio de malla / pénsum").
tramite(cambio_modalidad, "Cambio de modalidad").
tramite(examen_gracia, "Examen de gracia").
tramite(examen_recuperacion, "Examen de recuperación").
tramite(examen_suficiencia, "Examen de suficiencia").
tramite(examen_supletorio, "Examen supletorio").
tramite(homologacion, "Homologación").
tramite(incompleto, "Incompleto").
tramite(person_to_person, "Person to Person").
tramite(recalificacion, "Recalificación de examen").
tramite(registro_extemporaneo, "Registro extemporáneo").
tramite(reincorporacion_carrera, "Reincorporación a carrera").
tramite(retiro_carrera, "Retiro de carrera").
tramite(retiro_materia, "Retiro de materia").
tramite(retiro_universidad, "Retiro de universidad").
tramite(retiro_extemporaneo, "Retiro extemporáneo").
tramite(tercer_registro, "Tercer registro").

% ------------------------------------------------------------------
% Descripciones
% ------------------------------------------------------------------
descripcion(alcance_homologacion, "Añadir una materia a una homologación que ya fue aprobada, cuando faltó un documento de respaldo. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(ayudante_catedra, "Solicitar la participación como ayudante de una asignatura durante un periodo académico. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(cambio_carrera, "Cambiar de la carrera actual a otra carrera y, cuando corresponda, solicitar un nuevo código. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(cambio_malla, "Pasar del pénsum anterior a la malla curricular vigente para continuar el registro de materias. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(cambio_modalidad, "Solicitar el cambio a otra modalidad o a una carrera ofrecida en otra modalidad. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(examen_gracia, "Solicitar una oportunidad excepcional de evaluación asociada a una pérdida por faltas. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(examen_recuperacion, "Solicitar un examen de recuperación para una asignatura reprobada. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(examen_suficiencia, "Demostrar conocimientos previos para aprobar una asignatura mediante evaluación. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(examen_supletorio, "Solicitar un examen acumulativo cuando no fue posible rendir un examen parcial. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(homologacion, "Reconocer materias aprobadas previamente en otra institución o dentro de la universidad. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(incompleto, "Solicitar la condición de incompleto por enfermedad o calamidad que impide continuar temporalmente. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(person_to_person, "Solicitar una modalidad individual para cursar una materia específica. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(recalificacion, "Solicitar la revisión formal de la calificación de un examen de una materia activa. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(registro_extemporaneo, "Solicitar el registro tardío de materias a las que el estudiante ha asistido como oyente. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(reincorporacion_carrera, "Retomar una carrera después de un periodo de inactividad para completar los estudios. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(retiro_carrera, "Retirarse de la carrera actual y solicitar el ajuste o eliminación de la tabla de pagos. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(retiro_materia, "Solicitar el retiro de una materia antes del límite correspondiente. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(retiro_universidad, "Solicitar la salida de la universidad y la eliminación de la tabla de pagos. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(retiro_extemporaneo, "Solicitar fuera del plazo ordinario el retiro de una o varias materias. Información del proyecto Agenda Anny, pendiente de validación institucional.").
descripcion(tercer_registro, "Solicitar autorización para cursar por tercera vez una materia reprobada dos veces. Información del proyecto Agenda Anny, pendiente de validación institucional.").

% ------------------------------------------------------------------
% Estado editorial
% ------------------------------------------------------------------
estado_conocimiento(alcance_homologacion, parcial).
estado_conocimiento(ayudante_catedra, parcial).
estado_conocimiento(cambio_carrera, parcial).
estado_conocimiento(cambio_malla, parcial).
estado_conocimiento(cambio_modalidad, parcial).
estado_conocimiento(examen_gracia, parcial).
estado_conocimiento(examen_recuperacion, parcial).
estado_conocimiento(examen_suficiencia, parcial).
estado_conocimiento(examen_supletorio, parcial).
estado_conocimiento(homologacion, parcial).
estado_conocimiento(incompleto, parcial).
estado_conocimiento(person_to_person, pendiente).
estado_conocimiento(recalificacion, parcial).
estado_conocimiento(registro_extemporaneo, parcial).
estado_conocimiento(reincorporacion_carrera, parcial).
estado_conocimiento(retiro_carrera, parcial).
estado_conocimiento(retiro_materia, parcial).
estado_conocimiento(retiro_universidad, parcial).
estado_conocimiento(retiro_extemporaneo, parcial).
estado_conocimiento(tercer_registro, parcial).

% ------------------------------------------------------------------
% Fuentes
% ------------------------------------------------------------------
fuente_conocimiento(alcance_homologacion, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(ayudante_catedra, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(cambio_carrera, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(cambio_malla, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(cambio_modalidad, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(examen_gracia, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(examen_recuperacion, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(examen_suficiencia, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(examen_supletorio, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(homologacion, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(incompleto, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(person_to_person, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(recalificacion, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(registro_extemporaneo, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(reincorporacion_carrera, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(retiro_carrera, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(retiro_materia, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(retiro_universidad, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(retiro_extemporaneo, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").
fuente_conocimiento(tercer_registro, "agenda-anny.rar: data.js, transactions y guidance; revisión 2026-10-11").

% ------------------------------------------------------------------
% Correspondencia con el portal
% ------------------------------------------------------------------
identificador_agenda(alcance_homologacion, "alcance-homologacion").
identificador_agenda(ayudante_catedra, "ayudante-catedra").
identificador_agenda(cambio_carrera, "cambio-carrera").
identificador_agenda(cambio_malla, "cambio-malla").
identificador_agenda(cambio_modalidad, "cambio-modalidad").
identificador_agenda(examen_gracia, "examen-gracia").
identificador_agenda(examen_recuperacion, "examen-recuperacion").
identificador_agenda(examen_suficiencia, "examen-suficiencia").
identificador_agenda(examen_supletorio, "examen-supletorio").
identificador_agenda(homologacion, "homologacion").
identificador_agenda(incompleto, "incompleto").
identificador_agenda(person_to_person, "person-to-person").
identificador_agenda(recalificacion, "recalificacion").
identificador_agenda(registro_extemporaneo, "registro-extemporaneo").
identificador_agenda(reincorporacion_carrera, "reincorporacion").
identificador_agenda(retiro_carrera, "retiro-carrera").
identificador_agenda(retiro_materia, "retiro-materia").
identificador_agenda(retiro_universidad, "retiro-universidad").
identificador_agenda(retiro_extemporaneo, "retiro-extemporaneo").
identificador_agenda(tercer_registro, "tercer-registro").

% ------------------------------------------------------------------
% Condiciones y datos del formulario; orden de presentación
% ------------------------------------------------------------------
requisito(alcance_homologacion, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(alcance_homologacion, 2, "Ya existe una homologación aprobada.").
requisito(alcance_homologacion, 3, "Una materia o documento no fue considerado en esa homologación.").
requisito(alcance_homologacion, 4, "Puedes identificar la materia y explicar qué respaldo faltó.").
requisito(alcance_homologacion, 5, "Formulario: Materia(s) (obligatorio).").
requisito(alcance_homologacion, 6, "Formulario: Motivo y documento que faltó (obligatorio).").
requisito(alcance_homologacion, 7, "Por confirmar con la Facultad: Falta confirmar plazo, autoridad que valida el documento y causales de rechazo.").
requisito(ayudante_catedra, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(ayudante_catedra, 2, "Puedes identificar la materia y el periodo en que deseas participar.").
requisito(ayudante_catedra, 3, "Aceptas las responsabilidades indicadas en la plantilla.").
requisito(ayudante_catedra, 4, "La selección final debe ser aprobada por la Facultad.").
requisito(ayudante_catedra, 5, "Formulario: Materia(s) (obligatorio).").
requisito(ayudante_catedra, 6, "Formulario: Periodo y año (obligatorio).").
requisito(ayudante_catedra, 7, "Formulario: GPA general (obligatorio).").
requisito(ayudante_catedra, 8, "Formulario: Calificación en la materia (obligatorio).").
requisito(ayudante_catedra, 9, "Formulario: Motivación (opcional).").
requisito(ayudante_catedra, 10, "Por confirmar con la Facultad: Falta confirmar promedio mínimo, quién puede postular, cupos, funciones y criterio de selección.").
requisito(ayudante_catedra, 11, "El motor del proyecto contempla GPA general mínimo de 90, materia aprobada con nota mínima de 90 y ausencia de materias reprobadas. Son criterios de implementación pendientes de validación; la plantilla menciona retribución de 5 puntos.").
requisito(cambio_carrera, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(cambio_carrera, 2, "Actualmente constas en una carrera de la universidad.").
requisito(cambio_carrera, 3, "Has definido la carrera a la que deseas cambiarte.").
requisito(cambio_carrera, 4, "Entiendes que pueden cambiar tu malla, homologaciones y código estudiantil.").
requisito(cambio_carrera, 5, "Formulario: Carrera de destino (obligatorio).").
requisito(cambio_carrera, 6, "Formulario: Solicito también la creación de un nuevo código (opcional). La casilla no exige estar marcada; no equivale a condición académica verificada.").
requisito(cambio_carrera, 7, "Formulario: Motivo del cambio (obligatorio).").
requisito(cambio_carrera, 8, "Por confirmar con la Facultad: Falta confirmar restricciones académicas, financieras y fechas límite.").
requisito(cambio_malla, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(cambio_malla, 2, "Puedes identificar tu pénsum actual y la malla de destino.").
requisito(cambio_malla, 3, "Deseas continuar tus estudios bajo una versión curricular diferente.").
requisito(cambio_malla, 4, "Aceptas que pueden cambiar equivalencias y materias pendientes.").
requisito(cambio_malla, 5, "Formulario: Año o versión del pénsum actual (obligatorio).").
requisito(cambio_malla, 6, "Formulario: Año o versión de la nueva malla (obligatorio).").
requisito(cambio_malla, 7, "Formulario: Motivo (opcional).").
requisito(cambio_malla, 8, "Por confirmar con la Facultad: Falta confirmar condiciones, equivalencias, costos y efectos académicos.").
requisito(cambio_modalidad, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(cambio_modalidad, 2, "La carrera está disponible en la modalidad solicitada.").
requisito(cambio_modalidad, 3, "Puedes indicar modalidad actual, modalidad de destino y motivo.").
requisito(cambio_modalidad, 4, "Comprendes que el cambio requiere aprobación.").
requisito(cambio_modalidad, 5, "Formulario: Modalidad actual (obligatorio). Opciones del portal: Presencial; Semipresencial; En línea; Híbrida; A distancia; Dual.").
requisito(cambio_modalidad, 6, "Formulario: Modalidad solicitada (obligatorio). Opciones del portal: Presencial; Semipresencial; En línea; Híbrida; A distancia; Dual.").
requisito(cambio_modalidad, 7, "Formulario: Carrera de destino (opcional).").
requisito(cambio_modalidad, 8, "Formulario: Motivo del cambio (obligatorio).").
requisito(cambio_modalidad, 9, "Por confirmar con la Facultad: La plantilla original no pide expresamente la modalidad de destino; debe validarse su versión definitiva.").
requisito(cambio_modalidad, 10, "Advertencia de la plantilla: La plantilla original no pide explícitamente la modalidad de destino; el prototipo agrega ese campo para evitar ambigüedades.").
requisito(examen_gracia, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(examen_gracia, 2, "La solicitud se relaciona con una materia perdida por faltas.").
requisito(examen_gracia, 3, "Como referencia preliminar, registras al menos 70 % de asistencia.").
requisito(examen_gracia, 4, "Como referencia preliminar, tenías una calificación mínima de 80 antes de perderla por faltas.").
requisito(examen_gracia, 5, "Formulario: Materia(s) (obligatorio).").
requisito(examen_gracia, 6, "Formulario: Porcentaje de asistencia (opcional).").
requisito(examen_gracia, 7, "Formulario: Calificación actual (opcional).").
requisito(examen_gracia, 8, "Formulario: Motivo y justificación (obligatorio).").
requisito(examen_gracia, 9, "Por confirmar con la Facultad: Debe confirmarse con Anny que esta interpretación corresponde al examen de gracia usado por la Facultad.").
requisito(examen_gracia, 10, "El handbook distingue nota previa mínima de 80 y nota de 90 para aprobar el examen. El motor no verifica asistencia y usa SEMESTRE ACTUAL; validar pérdida por faltas.").
requisito(examen_recuperacion, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(examen_recuperacion, 2, "La materia fue reprobada y no hubo fraude académico.").
requisito(examen_recuperacion, 3, "El GPA aplicable es de al menos 75.").
requisito(examen_recuperacion, 4, "No has rendido otro examen de recuperación durante los últimos 365 días.").
requisito(examen_recuperacion, 5, "La solicitud se presenta al finalizar el semestre; el examen se rendiría hasta la primera semana del periodo siguiente.").
requisito(examen_recuperacion, 6, "Formulario: Materia(s) (obligatorio).").
requisito(examen_recuperacion, 7, "Formulario: GPA del periodo (opcional).").
requisito(examen_recuperacion, 8, "Formulario: Fecha del último examen de recuperación (opcional).").
requisito(examen_recuperacion, 9, "Formulario: Observación (opcional).").
requisito(examen_recuperacion, 10, "Por confirmar con la Facultad: Debe resolverse la contradicción entre la plantilla con varias materias y la regla informada de una sola materia.").
requisito(examen_recuperacion, 11, "Advertencia de la plantilla: La plantilla permite varias materias, pero la información levantada indica una sola materia por cada 365 días. El MVP limita el caso a una materia.").
requisito(examen_recuperacion, 12, "Conflicto con el handbook 2023: el proyecto usa 365 días y el handbook cuenta de mayo a abril; el motor usa GPA general y el handbook GPA del período con excepción para una o dos materias. No automatizar hasta acordar el criterio.").
requisito(examen_suficiencia, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(examen_suficiencia, 2, "Ya posees conocimientos demostrables de la materia.").
requisito(examen_suficiencia, 3, "Puedes sustentarlos con estudios previos, experiencia laboral relevante u otro mecanismo aceptado.").
requisito(examen_suficiencia, 4, "Solicitas ser evaluado para acreditar la asignatura.").
requisito(examen_suficiencia, 5, "Formulario: Materia(s) (obligatorio).").
requisito(examen_suficiencia, 6, "Formulario: Tipo de respaldo (obligatorio). Opciones del portal: Materia aprobada en otra universidad; Experiencia laboral de al menos 5 años; Proceso de convalidación; Otro.").
requisito(examen_suficiencia, 7, "Formulario: Fundamento de la solicitud (obligatorio).").
requisito(examen_suficiencia, 8, "Por confirmar con la Facultad: Falta confirmar materias excluidas, nota mínima, número de oportunidades y costo.").
requisito(examen_suficiencia, 9, "Advertencia de la plantilla: La plantilla original contiene un posible error de copia: menciona “examen de gracia”. La copia conserva ese texto oficial; confirma su corrección con la Facultad antes de enviarla.").
requisito(examen_supletorio, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(examen_supletorio, 2, "No pudiste rendir uno de los exámenes parciales por una causa justificable.").
requisito(examen_supletorio, 3, "Puedes respaldar la calamidad o impedimento.").
requisito(examen_supletorio, 4, "El examen debe rendirse dentro de los 30 días posteriores a la autorización.").
requisito(examen_supletorio, 5, "Formulario: Materia(s) (obligatorio).").
requisito(examen_supletorio, 6, "Formulario: Parcial no rendido (obligatorio). Opciones del portal: Primer parcial; Segundo parcial.").
requisito(examen_supletorio, 7, "Formulario: Circunstancia que impidió rendir el examen (obligatorio).").
requisito(examen_supletorio, 8, "Por confirmar con la Facultad: Falta confirmar el plazo exacto para presentar inicialmente la solicitud.").
requisito(homologacion, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(homologacion, 2, "Aprobaste materias en otra institución o programa.").
requisito(homologacion, 3, "Los contenidos pueden alcanzar una equivalencia mínima aproximada del 80 %.").
requisito(homologacion, 4, "Normalmente las materias no superan 10 años de antigüedad.").
requisito(homologacion, 5, "Formulario: Universidad o institución de procedencia (obligatorio).").
requisito(homologacion, 6, "Formulario: Carrera de destino en UEES (obligatorio).").
requisito(homologacion, 7, "Formulario: Materia(s) (opcional).").
requisito(homologacion, 8, "Formulario: Información adicional (opcional).").
requisito(homologacion, 9, "Por confirmar con la Facultad: La Facultad debe confirmar excepciones, costos y documentos según la institución de procedencia.").
requisito(incompleto, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(incompleto, 2, "Una enfermedad, calamidad u otra situación verificable impide completar temporalmente la materia.").
requisito(incompleto, 3, "Puedes identificar las materias afectadas.").
requisito(incompleto, 4, "Puedes presentar evidencia de la causa.").
requisito(incompleto, 5, "Formulario: Materia(s) (obligatorio).").
requisito(incompleto, 6, "Formulario: Tipo de causa (obligatorio). Opciones del portal: Enfermedad o accidente; Embarazo o parto; Calamidad doméstica; Situación judicial; Pérdida de ingresos; Otra causa verificable.").
requisito(incompleto, 7, "Formulario: Detalle de la situación (obligatorio).").
requisito(incompleto, 8, "Por confirmar con la Facultad: Falta confirmar el plazo exacto aplicable a cada tipo de periodo académico.").
requisito(incompleto, 9, "El handbook indica solicitud hasta un mes después de terminar el período y alcance sobre todas las materias salvo excepción del Consejo; conciliar con los plazos y selección del portal.").
requisito(person_to_person, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(person_to_person, 2, "Puedes identificar la materia que deseas cursar bajo esta modalidad.").
requisito(person_to_person, 3, "Antes de continuar debes consultar a Anny, porque los criterios aún no están documentados.").
requisito(person_to_person, 4, "Formulario: Materia(s) (obligatorio).").
requisito(person_to_person, 5, "Formulario: Motivo (opcional).").
requisito(person_to_person, 6, "Por confirmar con la Facultad: No se encontraron reglas suficientes sobre quién califica, disponibilidad, costo, duración ni aprobación.").
requisito(recalificacion, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(recalificacion, 2, "Se trata de un examen de una materia activa en el periodo.").
requisito(recalificacion, 3, "La solicitud se presenta idealmente dentro de 48 horas desde la entrega o publicación de la nota.").
requisito(recalificacion, 4, "No corresponde a un examen de recuperación ni supletorio.").
requisito(recalificacion, 5, "Puedes señalar puntos concretos que justifican la revisión.").
requisito(recalificacion, 6, "Formulario: Materia(s) (obligatorio).").
requisito(recalificacion, 7, "Formulario: Evaluación (obligatorio). Opciones del portal: Primer parcial; Segundo parcial; Otra evaluación activa.").
requisito(recalificacion, 8, "Formulario: Fecha y hora de entrega o publicación de la nota (obligatorio).").
requisito(recalificacion, 9, "Formulario: Puntos específicos que fundamentan el reclamo (obligatorio).").
requisito(recalificacion, 10, "Por confirmar con la Facultad: Falta confirmar cómo se cuentan las 48 horas y el procedimiento de revisión.").
requisito(recalificacion, 11, "El handbook limita la recalificación a evaluaciones finales del parcial. El portal permite otra evaluación activa; confirmar alcance y si las 48 horas son límite obligatorio.").
requisito(registro_extemporaneo, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(registro_extemporaneo, 2, "No registraste una o varias materias dentro del plazo ordinario.").
requisito(registro_extemporaneo, 3, "Has asistido como oyente a las materias indicadas.").
requisito(registro_extemporaneo, 4, "Puedes explicar por qué no realizaste el registro a tiempo.").
requisito(registro_extemporaneo, 5, "Formulario: Materia(s) (obligatorio).").
requisito(registro_extemporaneo, 6, "Formulario: He asistido como oyente a las materias indicadas (opcional). La casilla no exige estar marcada; no equivale a condición académica verificada.").
requisito(registro_extemporaneo, 7, "Formulario: Motivo por el que no se realizó el registro a tiempo (obligatorio).").
requisito(registro_extemporaneo, 8, "Por confirmar con la Facultad: Falta confirmar fecha límite, aprobación docente, cupos, pagos y restricciones.").
requisito(reincorporacion_carrera, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(reincorporacion_carrera, 2, "Fuiste estudiante de la carrera y estuviste inactivo durante uno o más periodos.").
requisito(reincorporacion_carrera, 3, "Deseas retomar los estudios en esa carrera.").
requisito(reincorporacion_carrera, 4, "Puedes indicar tu último periodo activo y el motivo de la interrupción.").
requisito(reincorporacion_carrera, 5, "Formulario: Último periodo cursado (opcional).").
requisito(reincorporacion_carrera, 6, "Formulario: Motivo de la interrupción y reincorporación (obligatorio).").
requisito(reincorporacion_carrera, 7, "Por confirmar con la Facultad: Falta confirmar tiempo máximo de inactividad, malla aplicable, deudas y proceso financiero.").
requisito(retiro_carrera, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(retiro_carrera, 2, "Deseas dejar tu carrera actual, aunque podrías permanecer en la universidad.").
requisito(retiro_carrera, 3, "Puedes explicar el motivo del retiro.").
requisito(retiro_carrera, 4, "Comprendes que puede requerirse ajustar o eliminar la tabla de pagos.").
requisito(retiro_carrera, 5, "Formulario: Motivo del retiro (obligatorio).").
requisito(retiro_carrera, 6, "Por confirmar con la Facultad: Debe diferenciarse formalmente de cambio de carrera y retiro de universidad.").
requisito(retiro_materia, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(retiro_materia, 2, "Solicitas retirar una materia específica.").
requisito(retiro_materia, 3, "La petición se presenta antes de la semana de exámenes del primer parcial.").
requisito(retiro_materia, 4, "Comprendes que existen consecuencias académicas y económicas según la semana.").
requisito(retiro_materia, 5, "Formulario: Materia(s) (obligatorio).").
requisito(retiro_materia, 6, "Formulario: Semana del periodo en la que solicita el retiro (opcional).").
requisito(retiro_materia, 7, "Formulario: Motivo (opcional).").
requisito(retiro_materia, 8, "Por confirmar con la Facultad: Debe confirmarse el porcentaje económico: la plantilla indica 100 %, pero las respuestas describen 25 %, 50 % y 100 % según la semana.").
requisito(retiro_materia, 9, "Advertencia de la plantilla: La plantilla menciona cobro del 100 %, pero la información levantada indica 25 % en la primera semana, 50 % en la segunda y 100 % desde la tercera. Debe validarse antes de automatizar el aviso.").
requisito(retiro_materia, 10, "Conflicto con el handbook: describe reintegros de hasta 80 % en la primera semana y 50 % en la segunda. Confirmar el criterio económico antes de informar un valor.").
requisito(retiro_universidad, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(retiro_universidad, 2, "Deseas dejar completamente la universidad, no solamente una carrera.").
requisito(retiro_universidad, 3, "Puedes explicar el motivo.").
requisito(retiro_universidad, 4, "Comprendes que se revisará la tabla de pagos y otras obligaciones.").
requisito(retiro_universidad, 5, "Formulario: Motivo del retiro (obligatorio).").
requisito(retiro_universidad, 6, "Por confirmar con la Facultad: La redacción de la plantilla debe revisarse porque utiliza términos similares al retiro de carrera.").
requisito(retiro_extemporaneo, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(retiro_extemporaneo, 2, "El plazo ordinario para retirar la materia ya terminó.").
requisito(retiro_extemporaneo, 3, "Existe una circunstancia excepcional que puedes justificar.").
requisito(retiro_extemporaneo, 4, "Comprendes que la plantilla señala cobro del 100 % y pérdida de créditos.").
requisito(retiro_extemporaneo, 5, "Formulario: Materia(s) (obligatorio).").
requisito(retiro_extemporaneo, 6, "Formulario: Motivo excepcional del retiro (obligatorio).").
requisito(retiro_extemporaneo, 7, "Por confirmar con la Facultad: Falta confirmar qué situaciones excepcionales se aceptan y quién las valida.").
requisito(tercer_registro, 1, "Orientación preliminar del portal Agenda Anny. La Facultad confirma los requisitos y autoriza la solicitud.").
requisito(tercer_registro, 2, "Reprobaste la misma materia en dos registros anteriores.").
requisito(tercer_registro, 3, "Tu carrera no es Medicina ni Enfermería.").
requisito(tercer_registro, 4, "No has excedido el máximo de tres materias con tercer registro durante la carrera.").
requisito(tercer_registro, 5, "Debes estar preparado para cursar exclusivamente la materia autorizada, según la información levantada.").
requisito(tercer_registro, 6, "Formulario: Materia(s) (obligatorio).").
requisito(tercer_registro, 7, "Formulario: Registros anteriores (obligatorio). Completar exactamente dos registros con año, sesión y puntaje; registrar las dos reprobaciones previas.").
requisito(tercer_registro, 8, "Por confirmar con la Facultad: Falta confirmar excepciones, simultaneidad con otras materias y consecuencias de una nueva reprobación.").

% ------------------------------------------------------------------
% Campos y obligatoriedad de captura, no elegibilidad
% ------------------------------------------------------------------
campo_formulario(alcance_homologacion, subjects, "Materia(s)", obligatorio).
campo_formulario(alcance_homologacion, reason, "Motivo y documento que faltó", obligatorio).
campo_formulario(ayudante_catedra, subjects, "Materia(s)", obligatorio).
campo_formulario(ayudante_catedra, academic_period, "Periodo y año", obligatorio).
campo_formulario(ayudante_catedra, gpa_general, "GPA general", obligatorio).
campo_formulario(ayudante_catedra, subject_grade, "Calificación en la materia", obligatorio).
campo_formulario(ayudante_catedra, reason, "Motivación", opcional).
campo_formulario(cambio_carrera, target_career, "Carrera de destino", obligatorio).
campo_formulario(cambio_carrera, new_code, "Solicito también la creación de un nuevo código", opcional).
campo_formulario(cambio_carrera, reason, "Motivo del cambio", obligatorio).
campo_formulario(cambio_malla, old_pensum, "Año o versión del pénsum actual", obligatorio).
campo_formulario(cambio_malla, new_pensum, "Año o versión de la nueva malla", obligatorio).
campo_formulario(cambio_malla, reason, "Motivo", opcional).
campo_formulario(cambio_modalidad, current_mode, "Modalidad actual", obligatorio).
campo_formulario(cambio_modalidad, target_mode, "Modalidad solicitada", obligatorio).
campo_formulario(cambio_modalidad, target_career, "Carrera de destino", opcional).
campo_formulario(cambio_modalidad, reason, "Motivo del cambio", obligatorio).
campo_formulario(examen_gracia, subjects, "Materia(s)", obligatorio).
campo_formulario(examen_gracia, attendance, "Porcentaje de asistencia", opcional).
campo_formulario(examen_gracia, current_grade, "Calificación actual", opcional).
campo_formulario(examen_gracia, reason, "Motivo y justificación", obligatorio).
campo_formulario(examen_recuperacion, subjects, "Materia(s)", obligatorio).
campo_formulario(examen_recuperacion, period_gpa, "GPA del periodo", opcional).
campo_formulario(examen_recuperacion, last_recovery_date, "Fecha del último examen de recuperación", opcional).
campo_formulario(examen_recuperacion, reason, "Observación", opcional).
campo_formulario(examen_suficiencia, subjects, "Materia(s)", obligatorio).
campo_formulario(examen_suficiencia, support_type, "Tipo de respaldo", obligatorio).
campo_formulario(examen_suficiencia, reason, "Fundamento de la solicitud", obligatorio).
campo_formulario(examen_supletorio, subjects, "Materia(s)", obligatorio).
campo_formulario(examen_supletorio, partial, "Parcial no rendido", obligatorio).
campo_formulario(examen_supletorio, reason, "Circunstancia que impidió rendir el examen", obligatorio).
campo_formulario(homologacion, origin_university, "Universidad o institución de procedencia", obligatorio).
campo_formulario(homologacion, target_career, "Carrera de destino en UEES", obligatorio).
campo_formulario(homologacion, subjects, "Materia(s)", opcional).
campo_formulario(homologacion, reason, "Información adicional", opcional).
campo_formulario(incompleto, subjects, "Materia(s)", obligatorio).
campo_formulario(incompleto, cause_type, "Tipo de causa", obligatorio).
campo_formulario(incompleto, reason, "Detalle de la situación", obligatorio).
campo_formulario(person_to_person, subjects, "Materia(s)", obligatorio).
campo_formulario(person_to_person, reason, "Motivo", opcional).
campo_formulario(recalificacion, subjects, "Materia(s)", obligatorio).
campo_formulario(recalificacion, partial, "Evaluación", obligatorio).
campo_formulario(recalificacion, grade_published_at, "Fecha y hora de entrega o publicación de la nota", obligatorio).
campo_formulario(recalificacion, reason, "Puntos específicos que fundamentan el reclamo", obligatorio).
campo_formulario(registro_extemporaneo, subjects, "Materia(s)", obligatorio).
campo_formulario(registro_extemporaneo, attended_as_listener, "He asistido como oyente a las materias indicadas", opcional).
campo_formulario(registro_extemporaneo, reason, "Motivo por el que no se realizó el registro a tiempo", obligatorio).
campo_formulario(reincorporacion_carrera, last_active_period, "Último periodo cursado", opcional).
campo_formulario(reincorporacion_carrera, reason, "Motivo de la interrupción y reincorporación", obligatorio).
campo_formulario(retiro_carrera, reason, "Motivo del retiro", obligatorio).
campo_formulario(retiro_materia, subjects, "Materia(s)", obligatorio).
campo_formulario(retiro_materia, withdrawal_week, "Semana del periodo en la que solicita el retiro", opcional).
campo_formulario(retiro_materia, reason, "Motivo", opcional).
campo_formulario(retiro_universidad, reason, "Motivo del retiro", obligatorio).
campo_formulario(retiro_extemporaneo, subjects, "Materia(s)", obligatorio).
campo_formulario(retiro_extemporaneo, reason, "Motivo excepcional del retiro", obligatorio).
campo_formulario(tercer_registro, subjects, "Materia(s)", obligatorio).
campo_formulario(tercer_registro, attempts, "Registros anteriores", obligatorio).

% ------------------------------------------------------------------
% Definiciones de respaldos sugeridos
% ------------------------------------------------------------------
documento_def(alcance_homologacion_respaldo_1, "Resolución o antecedente de la homologación previa.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(alcance_homologacion_respaldo_2, "Documento que faltó o evidencia que sustenta el alcance.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(ayudante_catedra_respaldo_1, "Información académica o calificaciones, si la Facultad las solicita.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(cambio_carrera_respaldo_1, "Justificación del cambio.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(cambio_carrera_respaldo_2, "Documentación adicional que solicite la carrera de destino.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(cambio_malla_respaldo_1, "Detalle de la malla o pénsum actual, si lo solicita la Facultad.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(cambio_modalidad_respaldo_1, "Respaldo del motivo, si corresponde.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(examen_gracia_respaldo_1, "Reporte de asistencia.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(examen_gracia_respaldo_2, "Calificaciones de la materia.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(examen_gracia_respaldo_3, "Justificación y respaldos del caso.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(examen_recuperacion_respaldo_1, "Registro de calificaciones o evidencia de la materia reprobada.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(examen_suficiencia_respaldo_1, "Certificados, sílabos o calificaciones de otra institución; o respaldo de experiencia laboral.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(examen_supletorio_respaldo_1, "Evidencia médica, laboral, familiar o del hecho que impidió asistir.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(homologacion_respaldo_1, "Documento de identidad.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(homologacion_respaldo_2, "Certificado oficial de calificaciones.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(homologacion_respaldo_3, "Sílabos o programas de las materias.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(homologacion_respaldo_4, "Documentos legalizados cuando corresponda.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(incompleto_respaldo_1, "Certificado o evidencia verificable de la situación.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(incompleto_respaldo_2, "Detalle de las materias afectadas.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(person_to_person_respaldo_1, "Documentación que Anny indique después de revisar el caso.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(recalificacion_respaldo_1, "Copia o evidencia del examen y su calificación.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(recalificacion_respaldo_2, "Detalle de los puntos que solicitas revisar.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(registro_extemporaneo_respaldo_1, "Evidencia de asistencia o autorización del docente, si está disponible.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(reincorporacion_carrera_respaldo_1, "Respaldos del motivo de interrupción, si corresponde.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(retiro_carrera_respaldo_1, "Respaldo del motivo, si corresponde.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(retiro_materia_respaldo_1, "Respaldo del motivo, si corresponde.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(retiro_universidad_respaldo_1, "Respaldo del motivo, si corresponde.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(retiro_extemporaneo_respaldo_1, "Evidencias de la circunstancia excepcional.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).
documento_def(tercer_registro_respaldo_1, "Calificaciones y periodos de los dos registros anteriores.", "Respaldo orientativo del portal; exigencia y formato por confirmar con la Facultad.", "Por confirmar", sin_url).

% ------------------------------------------------------------------
% Asociaciones de documentos (exigencia institucional pendiente)
% ------------------------------------------------------------------
tramite_documento(alcance_homologacion, 1, alcance_homologacion_respaldo_1, opcional).
tramite_documento(alcance_homologacion, 2, alcance_homologacion_respaldo_2, opcional).
tramite_documento(ayudante_catedra, 1, ayudante_catedra_respaldo_1, opcional).
tramite_documento(cambio_carrera, 1, cambio_carrera_respaldo_1, opcional).
tramite_documento(cambio_carrera, 2, cambio_carrera_respaldo_2, opcional).
tramite_documento(cambio_malla, 1, cambio_malla_respaldo_1, opcional).
tramite_documento(cambio_modalidad, 1, cambio_modalidad_respaldo_1, opcional).
tramite_documento(examen_gracia, 1, examen_gracia_respaldo_1, opcional).
tramite_documento(examen_gracia, 2, examen_gracia_respaldo_2, opcional).
tramite_documento(examen_gracia, 3, examen_gracia_respaldo_3, opcional).
tramite_documento(examen_recuperacion, 1, examen_recuperacion_respaldo_1, opcional).
tramite_documento(examen_suficiencia, 1, examen_suficiencia_respaldo_1, opcional).
tramite_documento(examen_supletorio, 1, examen_supletorio_respaldo_1, opcional).
tramite_documento(homologacion, 1, homologacion_respaldo_1, opcional).
tramite_documento(homologacion, 2, homologacion_respaldo_2, opcional).
tramite_documento(homologacion, 3, homologacion_respaldo_3, opcional).
tramite_documento(homologacion, 4, homologacion_respaldo_4, opcional).
tramite_documento(incompleto, 1, incompleto_respaldo_1, opcional).
tramite_documento(incompleto, 2, incompleto_respaldo_2, opcional).
tramite_documento(person_to_person, 1, person_to_person_respaldo_1, opcional).
tramite_documento(recalificacion, 1, recalificacion_respaldo_1, opcional).
tramite_documento(recalificacion, 2, recalificacion_respaldo_2, opcional).
tramite_documento(registro_extemporaneo, 1, registro_extemporaneo_respaldo_1, opcional).
tramite_documento(reincorporacion_carrera, 1, reincorporacion_carrera_respaldo_1, opcional).
tramite_documento(retiro_carrera, 1, retiro_carrera_respaldo_1, opcional).
tramite_documento(retiro_materia, 1, retiro_materia_respaldo_1, opcional).
tramite_documento(retiro_universidad, 1, retiro_universidad_respaldo_1, opcional).
tramite_documento(retiro_extemporaneo, 1, retiro_extemporaneo_respaldo_1, opcional).
tramite_documento(tercer_registro, 1, tercer_registro_respaldo_1, opcional).

% ------------------------------------------------------------------
% Plazos informativos
% ------------------------------------------------------------------
plazo_texto(alcance_homologacion, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(ayudante_catedra, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(cambio_carrera, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(cambio_malla, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(cambio_modalidad, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(examen_gracia, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(examen_recuperacion, "Según el portal: solicitar al finalizar el semestre y rendir hasta la primera semana siguiente. Los 365 días describen frecuencia, no plazo de presentación; criterio pendiente de conciliación con el handbook.").
plazo_texto(examen_suficiencia, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(examen_supletorio, "Rendir dentro de 30 días desde la autorización. El plazo inicial de solicitud sigue pendiente.").
plazo_texto(homologacion, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(incompleto, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(person_to_person, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(recalificacion, "El portal recomienda idealmente 48 horas desde entrega o publicación de la nota. Confirmar inicio del cómputo y si es límite obligatorio.").
plazo_texto(registro_extemporaneo, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(reincorporacion_carrera, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(retiro_carrera, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(retiro_materia, "Según el portal: antes de la semana de exámenes del primer parcial. Confirmar calendario y límite institucional.").
plazo_texto(retiro_universidad, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").
plazo_texto(retiro_extemporaneo, "Después del plazo ordinario de retiro; fecha límite excepcional no definida.").
plazo_texto(tercer_registro, "Plazo de solicitud no definido en el portal; confirmar con la Facultad.").

% ------------------------------------------------------------------
% Pendientes de validación
% ------------------------------------------------------------------
pendiente_validacion(alcance_homologacion, "Falta confirmar plazo, autoridad que valida el documento y causales de rechazo.").
pendiente_validacion(ayudante_catedra, "Falta confirmar promedio mínimo, quién puede postular, cupos, funciones y criterio de selección. El motor del proyecto contempla GPA general mínimo de 90, materia aprobada con nota mínima de 90 y ausencia de materias reprobadas. Son criterios de implementación pendientes de validación; la plantilla menciona retribución de 5 puntos.").
pendiente_validacion(cambio_carrera, "Falta confirmar restricciones académicas, financieras y fechas límite.").
pendiente_validacion(cambio_malla, "Falta confirmar condiciones, equivalencias, costos y efectos académicos.").
pendiente_validacion(cambio_modalidad, "La plantilla original no pide expresamente la modalidad de destino; debe validarse su versión definitiva.").
pendiente_validacion(examen_gracia, "Debe confirmarse con Anny que esta interpretación corresponde al examen de gracia usado por la Facultad. El handbook distingue nota previa mínima de 80 y nota de 90 para aprobar el examen. El motor no verifica asistencia y usa SEMESTRE ACTUAL; validar pérdida por faltas.").
pendiente_validacion(examen_recuperacion, "Debe resolverse la contradicción entre la plantilla con varias materias y la regla informada de una sola materia. Conflicto con el handbook 2023: el proyecto usa 365 días y el handbook cuenta de mayo a abril; el motor usa GPA general y el handbook GPA del período con excepción para una o dos materias. No automatizar hasta acordar el criterio.").
pendiente_validacion(examen_suficiencia, "Falta confirmar materias excluidas, nota mínima, número de oportunidades y costo.").
pendiente_validacion(examen_supletorio, "Falta confirmar el plazo exacto para presentar inicialmente la solicitud.").
pendiente_validacion(homologacion, "La Facultad debe confirmar excepciones, costos y documentos según la institución de procedencia.").
pendiente_validacion(incompleto, "Falta confirmar el plazo exacto aplicable a cada tipo de periodo académico. El handbook indica solicitud hasta un mes después de terminar el período y alcance sobre todas las materias salvo excepción del Consejo; conciliar con los plazos y selección del portal.").
pendiente_validacion(person_to_person, "No se encontraron reglas suficientes sobre quién califica, disponibilidad, costo, duración ni aprobación.").
pendiente_validacion(recalificacion, "Falta confirmar cómo se cuentan las 48 horas y el procedimiento de revisión. El handbook limita la recalificación a evaluaciones finales del parcial. El portal permite otra evaluación activa; confirmar alcance y si las 48 horas son límite obligatorio.").
pendiente_validacion(registro_extemporaneo, "Falta confirmar fecha límite, aprobación docente, cupos, pagos y restricciones.").
pendiente_validacion(reincorporacion_carrera, "Falta confirmar tiempo máximo de inactividad, malla aplicable, deudas y proceso financiero.").
pendiente_validacion(retiro_carrera, "Debe diferenciarse formalmente de cambio de carrera y retiro de universidad.").
pendiente_validacion(retiro_materia, "Debe confirmarse el porcentaje económico: la plantilla indica 100 %, pero las respuestas describen 25 %, 50 % y 100 % según la semana. Conflicto con el handbook: describe reintegros de hasta 80 % en la primera semana y 50 % en la segunda. Confirmar el criterio económico antes de informar un valor.").
pendiente_validacion(retiro_universidad, "La redacción de la plantilla debe revisarse porque utiliza términos similares al retiro de carrera.").
pendiente_validacion(retiro_extemporaneo, "Falta confirmar qué situaciones excepcionales se aceptan y quién las valida.").
pendiente_validacion(tercer_registro, "Falta confirmar excepciones, simultaneidad con otras materias y consecuencias de una nueva reprobación.").

% ------------------------------------------------------------------
% Resumen del proyecto
% ------------------------------------------------------------------
observacion_conocimiento(alcance_homologacion, "Requiere una homologación previa aprobada y explicar qué documento faltó.").
observacion_conocimiento(ayudante_catedra, "La plantilla menciona una retribución de 5 puntos; los criterios de selección aún deben validarse.").
observacion_conocimiento(cambio_carrera, "Puede implicar homologación de materias y creación de un nuevo código estudiantil.").
observacion_conocimiento(cambio_malla, "Falta validar las condiciones y efectos académicos del cambio.").
observacion_conocimiento(cambio_modalidad, "Requiere justificación y resolución del Consejo; la oferta debe existir en la modalidad de destino.").
observacion_conocimiento(examen_gracia, "Debe confirmarse que corresponde a la dispensa del art. 46: al menos 70 % de asistencia y nota mínima previa de 80.").
observacion_conocimiento(examen_recuperacion, "Presencial; una materia y un examen cada 365 días. El GPA aplicable debe ser al menos 75 y no procede por fraude.").
observacion_conocimiento(examen_suficiencia, "Puede respaldarse con una materia aprobada en otra universidad o con experiencia laboral relevante.").
observacion_conocimiento(examen_supletorio, "Debe enviarse con evidencias y rendirse dentro de 30 días desde la autorización.").
observacion_conocimiento(homologacion, "Se comparan contenidos con equivalencia mínima del 80 % y, normalmente, una antigüedad máxima de 10 años.").
observacion_conocimiento(incompleto, "Requiere respaldo verificable y se aplican plazos distintos según el tipo de periodo.").
observacion_conocimiento(person_to_person, "La plantilla identifica la materia, pero todavía faltan reglas, requisitos, costos y condiciones.").
observacion_conocimiento(recalificacion, "Debe presentarse idealmente dentro de 48 horas; no aplica a recuperación ni supletorio.").
observacion_conocimiento(registro_extemporaneo, "La plantilla confirma que se refiere a registro de materias, no a consignación atrasada de notas.").
observacion_conocimiento(reincorporacion_carrera, "La agenda de ejemplo incluye además el ajuste de la tabla de pagos.").
observacion_conocimiento(retiro_carrera, "En la agenda suele estar asociado a un cambio de carrera, pero puede presentarse por otros motivos.").
observacion_conocimiento(retiro_materia, "Antes de exámenes del primer parcial. El costo reportado varía por semana; después del límite la materia cuenta como reprobada.").
observacion_conocimiento(retiro_universidad, "Debe diferenciarse formalmente del retiro de carrera antes de automatizar la decisión.").
observacion_conocimiento(retiro_extemporaneo, "Implica cobro del 100 % y pérdida de créditos según la plantilla; suele requerir justificación excepcional.").
observacion_conocimiento(tercer_registro, "No aplica a Medicina ni Enfermería; el Consejo puede autorizar hasta tres asignaturas durante la carrera.").

% No hay costo/3: montos no confirmados; porcentajes no son centavos.
% No hay parametro/3 ni tipos permitidos: acordarlos con el responsable de reglas.