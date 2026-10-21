:- encoding(utf8).
% Generado desde el catálogo; no contiene datos personales.
:- discontiguous tramite/2.
:- multifile tramite/2.
:- discontiguous descripcion/2.
:- multifile descripcion/2.
:- discontiguous requisito/3.
:- multifile requisito/3.
:- discontiguous documento_def/5.
:- multifile documento_def/5.
:- discontiguous tramite_documento/4.
:- multifile tramite_documento/4.
:- discontiguous costo/3.
:- multifile costo/3.
:- discontiguous costo_texto/2.
:- multifile costo_texto/2.
:- discontiguous plazo_texto/2.
:- multifile plazo_texto/2.
:- discontiguous parametro/3.
:- multifile parametro/3.
:- discontiguous pendiente_validacion/2.
:- multifile pendiente_validacion/2.
:- discontiguous observacion_conocimiento/2.
:- multifile observacion_conocimiento/2.
kb_version("catalogo-informativo-001").
tramite(alcance_homologacion, "Alcance de homologación").
descripcion(alcance_homologacion, "Añadir una materia a una homologación que ya fue aprobada, cuando faltó un documento de respaldo.").
requisito(alcance_homologacion, 1, "Tener una homologación previamente aprobada.").
requisito(alcance_homologacion, 2, "Solicitar la revisión de una materia no considerada por falta de un respaldo en esa homologación.").
documento_def(alcance_homologacion_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(alcance_homologacion, 1, alcance_homologacion_otro, opcional).
documento_def(alcance_homologacion_resolucion_previa, "Resolución de homologación previa", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(alcance_homologacion, 2, alcance_homologacion_resolucion_previa, opcional).
documento_def(alcance_homologacion_documento_omitido, "Documento omitido", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(alcance_homologacion, 3, alcance_homologacion_documento_omitido, opcional).
costo_texto(alcance_homologacion, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(alcance_homologacion, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(ayudante_catedra, "Ayudante de cátedra").
descripcion(ayudante_catedra, "Solicitar la participación como ayudante de una asignatura durante un periodo académico.").
requisito(ayudante_catedra, 1, "Aceptar las responsabilidades académicas de la ayudantía.").
requisito(ayudante_catedra, 2, "La facultad revisa los antecedentes académicos y autoriza la ayudantía.").
documento_def(ayudante_catedra_recomendaciones_docentes, "Recomendaciones de al menos dos docentes (pueden agruparse en un PDF)", "Adjunta este respaldo.", "PDF, JPG o PNG", sin_url).
tramite_documento(ayudante_catedra, 1, ayudante_catedra_recomendaciones_docentes, obligatorio).
documento_def(ayudante_catedra_solicitud_docente, "Solicitud del docente de la materia de la ayudantía", "Adjunta este respaldo.", "PDF, JPG o PNG", sin_url).
tramite_documento(ayudante_catedra, 2, ayudante_catedra_solicitud_docente, obligatorio).
documento_def(ayudante_catedra_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(ayudante_catedra, 3, ayudante_catedra_otro, opcional).
documento_def(ayudante_catedra_calificaciones, "Información académica o calificaciones", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(ayudante_catedra, 4, ayudante_catedra_calificaciones, opcional).
costo_texto(ayudante_catedra, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(ayudante_catedra, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(cambio_carrera, "Cambio de carrera").
descripcion(cambio_carrera, "Cambiar de la carrera actual a otra carrera y, cuando corresponda, solicitar un nuevo código.").
requisito(cambio_carrera, 1, "Ser estudiante de una carrera de la universidad y solicitar el traslado a otra.").
requisito(cambio_carrera, 2, "El cambio requiere revisión de la carrera de destino y puede afectar las equivalencias de materias.").
documento_def(cambio_carrera_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(cambio_carrera, 1, cambio_carrera_otro, opcional).
documento_def(cambio_carrera_justificacion, "Justificación del cambio", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(cambio_carrera, 2, cambio_carrera_justificacion, opcional).
documento_def(cambio_carrera_documentacion_destino, "Documentación de la carrera de destino", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(cambio_carrera, 3, cambio_carrera_documentacion_destino, opcional).
costo_texto(cambio_carrera, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(cambio_carrera, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(cambio_malla, "Cambio de malla / pénsum").
descripcion(cambio_malla, "Pasar del pénsum anterior a la malla curricular vigente para continuar el registro de materias.").
requisito(cambio_malla, 1, "Solicitar continuar los estudios bajo una versión distinta de la malla curricular.").
requisito(cambio_malla, 2, "El reconocimiento de materias y las asignaturas pendientes dependen de la revisión académica.").
documento_def(cambio_malla_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(cambio_malla, 1, cambio_malla_otro, opcional).
documento_def(cambio_malla_pensum_actual, "Malla o pénsum actual", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(cambio_malla, 2, cambio_malla_pensum_actual, opcional).
costo_texto(cambio_malla, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(cambio_malla, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(cambio_modalidad, "Cambio de modalidad").
descripcion(cambio_modalidad, "Solicitar el cambio a otra modalidad o a una carrera ofrecida en otra modalidad.").
requisito(cambio_modalidad, 1, "Que la carrera esté disponible en la modalidad de destino.").
requisito(cambio_modalidad, 2, "Obtener la aprobación correspondiente para aplicar el cambio.").
documento_def(cambio_modalidad_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(cambio_modalidad, 1, cambio_modalidad_otro, opcional).
documento_def(cambio_modalidad_respaldo_motivo, "Respaldo del motivo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(cambio_modalidad, 2, cambio_modalidad_respaldo_motivo, opcional).
costo_texto(cambio_modalidad, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(cambio_modalidad, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(examen_gracia, "Examen de gracia").
descripcion(examen_gracia, "Solicitar una oportunidad excepcional de evaluación asociada a una pérdida por faltas.").
requisito(examen_gracia, 1, "Solicitarlo para una materia perdida por faltas.").
requisito(examen_gracia, 2, "El material revisado indica al menos 70 % de asistencia y calificación previa mínima de 80; la facultad debe verificar su aplicación al caso.").
documento_def(examen_gracia_respaldo, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_gracia, 1, examen_gracia_respaldo, opcional).
documento_def(examen_gracia_asistencia, "Reporte de asistencia", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_gracia, 2, examen_gracia_asistencia, opcional).
documento_def(examen_gracia_calificaciones, "Calificaciones de la materia", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_gracia, 3, examen_gracia_calificaciones, opcional).
documento_def(examen_gracia_justificacion, "Justificación del caso", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_gracia, 4, examen_gracia_justificacion, opcional).
costo_texto(examen_gracia, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(examen_gracia, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(examen_recuperacion, "Examen de recuperación").
descripcion(examen_recuperacion, "Solicitar un examen de recuperación para una asignatura reprobada.").
requisito(examen_recuperacion, 1, "Solicitar el examen para una materia reprobada; la facultad revisa los antecedentes académicos del caso.").
requisito(examen_recuperacion, 2, "La referencia de GPA general es 75 sobre 100.").
requisito(examen_recuperacion, 3, "El intervalo entre exámenes de recuperación es de 365 días, contados desde la fecha del último examen.").
requisito(examen_recuperacion, 4, "Presentar una sola materia por solicitud.").
documento_def(examen_recuperacion_registro_calificaciones, "Registro de calificaciones o evidencia de la materia reprobada", "Adjunta este respaldo.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_recuperacion, 1, examen_recuperacion_registro_calificaciones, obligatorio).
documento_def(examen_recuperacion_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_recuperacion, 2, examen_recuperacion_otro, opcional).
costo_texto(examen_recuperacion, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(examen_recuperacion, "Consulta el calendario de presentación con tu facultad. El intervalo de 365 días se cuenta desde el último examen de recuperación.").
parametro(examen_recuperacion, gpa_general_minimo, 75).
parametro(examen_recuperacion, intervalo_recuperacion_dias, 365).
tramite(examen_suficiencia, "Examen de suficiencia").
descripcion(examen_suficiencia, "Demostrar conocimientos previos para aprobar una asignatura mediante evaluación.").
requisito(examen_suficiencia, 1, "Contar con conocimientos de la asignatura demostrables mediante estudios previos, experiencia u otro mecanismo aceptado.").
requisito(examen_suficiencia, 2, "La facultad debe autorizar la evaluación y determinar los respaldos y condiciones aplicables.").
documento_def(examen_suficiencia_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_suficiencia, 1, examen_suficiencia_otro, opcional).
documento_def(examen_suficiencia_certificados, "Certificados de estudios", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_suficiencia, 2, examen_suficiencia_certificados, opcional).
documento_def(examen_suficiencia_silabos, "Sílabos o programas", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_suficiencia, 3, examen_suficiencia_silabos, opcional).
documento_def(examen_suficiencia_calificaciones, "Calificaciones de otra institución", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_suficiencia, 4, examen_suficiencia_calificaciones, opcional).
documento_def(examen_suficiencia_experiencia, "Respaldo de experiencia laboral", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_suficiencia, 5, examen_suficiencia_experiencia, opcional).
costo_texto(examen_suficiencia, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(examen_suficiencia, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(examen_supletorio, "Examen supletorio").
descripcion(examen_supletorio, "Solicitar un examen acumulativo cuando no fue posible rendir un examen parcial.").
requisito(examen_supletorio, 1, "No haber podido rendir un examen parcial por una circunstancia que debe someterse a revisión.").
requisito(examen_supletorio, 2, "Obtener autorización académica para rendir el examen. La valoración de la causa y sus respaldos corresponde a la facultad.").
documento_def(examen_supletorio_evidencia_impedimento, "Evidencia que respalde el impedimento para rendir el examen", "Adjunta este respaldo.", "PDF, JPG o PNG", sin_url).
tramite_documento(examen_supletorio, 1, examen_supletorio_evidencia_impedimento, obligatorio).
costo_texto(examen_supletorio, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(examen_supletorio, "El material disponible indica rendir dentro de 30 días desde la autorización. Consulta por separado el plazo para presentar la solicitud.").
tramite(homologacion, "Homologación").
descripcion(homologacion, "Reconocer materias aprobadas previamente en otra institución o dentro de la universidad.").
requisito(homologacion, 1, "Haber aprobado previamente las materias que se desean reconocer.").
requisito(homologacion, 2, "La facultad debe determinar la equivalencia de contenidos y la antigüedad admisible para el programa de destino.").
documento_def(homologacion_identidad, "Documento de identidad", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(homologacion, 1, homologacion_identidad, opcional).
documento_def(homologacion_certificado_academico, "Certificado académico", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(homologacion, 2, homologacion_certificado_academico, opcional).
documento_def(homologacion_silabos, "Sílabos de las materias que se evaluarán", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(homologacion, 3, homologacion_silabos, opcional).
documento_def(homologacion_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(homologacion, 4, homologacion_otro, opcional).
costo_texto(homologacion, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(homologacion, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(incompleto, "Incompleto").
descripcion(incompleto, "Solicitar la condición de incompleto por enfermedad o calamidad que impide continuar temporalmente.").
requisito(incompleto, 1, "Tener una situación grave y verificable que impida completar temporalmente los estudios.").
requisito(incompleto, 2, "Obtener resolución sobre las materias afectadas y las condiciones para retomarlas.").
documento_def(incompleto_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(incompleto, 1, incompleto_otro, opcional).
documento_def(incompleto_evidencia_causa, "Certificado o evidencia de la situación", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(incompleto, 2, incompleto_evidencia_causa, opcional).
documento_def(incompleto_materias_afectadas, "Detalle de las materias afectadas", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(incompleto, 3, incompleto_materias_afectadas, opcional).
costo_texto(incompleto, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(incompleto, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(person_to_person, "Person to Person").
descripcion(person_to_person, "Solicitar una modalidad individual para cursar una materia específica.").
requisito(person_to_person, 1, "Identificar la materia que deseas cursar bajo esta modalidad.").
requisito(person_to_person, 2, "Consultar con tu facultad la disponibilidad y las condiciones de autorización.").
documento_def(person_to_person_otro, "Documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(person_to_person, 1, person_to_person_otro, opcional).
costo_texto(person_to_person, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(person_to_person, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(recalificacion, "Recalificación de examen").
descripcion(recalificacion, "Solicitar la revisión formal de la calificación de un examen de una materia activa.").
requisito(recalificacion, 1, "Solicitar la revisión de una evaluación que admita recalificación y fundamentar el desacuerdo en puntos concretos.").
requisito(recalificacion, 2, "La revisión no garantiza un cambio de calificación.").
documento_def(recalificacion_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(recalificacion, 1, recalificacion_otro, opcional).
documento_def(recalificacion_examen, "Examen y calificación", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(recalificacion, 2, recalificacion_examen, opcional).
documento_def(recalificacion_puntos_revision, "Puntos solicitados para revisión", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(recalificacion, 3, recalificacion_puntos_revision, opcional).
costo_texto(recalificacion, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(recalificacion, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(registro_extemporaneo, "Registro extemporáneo").
descripcion(registro_extemporaneo, "Solicitar el registro tardío de materias a las que el estudiante ha asistido como oyente.").
requisito(registro_extemporaneo, 1, "Solicitar el registro de materias una vez terminado el plazo ordinario.").
requisito(registro_extemporaneo, 2, "La asistencia como oyente está contemplada por el proyecto, pero su exigencia debe confirmarse; no equivale a matrícula ni garantiza autorización.").
documento_def(registro_extemporaneo_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(registro_extemporaneo, 1, registro_extemporaneo_otro, opcional).
documento_def(registro_extemporaneo_asistencia, "Evidencia de asistencia", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(registro_extemporaneo, 2, registro_extemporaneo_asistencia, opcional).
documento_def(registro_extemporaneo_autorizacion_docente, "Autorización del docente", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(registro_extemporaneo, 3, registro_extemporaneo_autorizacion_docente, opcional).
costo_texto(registro_extemporaneo, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(registro_extemporaneo, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(reincorporacion_carrera, "Reincorporación a carrera").
descripcion(reincorporacion_carrera, "Retomar una carrera después de un periodo de inactividad para completar los estudios.").
requisito(reincorporacion_carrera, 1, "Haber cursado la carrera y solicitar el retorno después de un período de inactividad.").
requisito(reincorporacion_carrera, 2, "El retorno depende de la revisión de la malla aplicable y las condiciones académicas y financieras.").
documento_def(reincorporacion_carrera_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(reincorporacion_carrera, 1, reincorporacion_carrera_otro, opcional).
documento_def(reincorporacion_carrera_respaldo_interrupcion, "Respaldo del motivo de interrupción", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(reincorporacion_carrera, 2, reincorporacion_carrera_respaldo_interrupcion, opcional).
costo_texto(reincorporacion_carrera, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(reincorporacion_carrera, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(retiro_carrera, "Retiro de carrera").
descripcion(retiro_carrera, "Permite solicitar dejar la carrera actual y revisar tus obligaciones academicas y financieras.").
requisito(retiro_carrera, 1, "Solicitar dejar la carrera actual y formalizar el retiro ante la facultad.").
requisito(retiro_carrera, 2, "El retiro no elimina automáticamente las obligaciones académicas o financieras pendientes.").
documento_def(retiro_carrera_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(retiro_carrera, 1, retiro_carrera_otro, opcional).
documento_def(retiro_carrera_respaldo_motivo, "Respaldo del motivo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(retiro_carrera, 2, retiro_carrera_respaldo_motivo, opcional).
costo_texto(retiro_carrera, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(retiro_carrera, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(retiro_materia, "Retiro de materia").
descripcion(retiro_materia, "Solicitar el retiro de una materia antes del límite correspondiente.").
requisito(retiro_materia, 1, "Solicitar y formalizar el retiro de una materia dentro del plazo aplicable.").
requisito(retiro_materia, 2, "Dejar de asistir no equivale a retiro. La facultad debe revisar las consecuencias académicas y económicas.").
documento_def(retiro_materia_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(retiro_materia, 1, retiro_materia_otro, opcional).
documento_def(retiro_materia_respaldo_motivo, "Respaldo del motivo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(retiro_materia, 2, retiro_materia_respaldo_motivo, opcional).
costo_texto(retiro_materia, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(retiro_materia, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(retiro_universidad, "Retiro de universidad").
descripcion(retiro_universidad, "Permite solicitar la salida completa de la universidad y la revision de tus materias y obligaciones pendientes.").
requisito(retiro_universidad, 1, "Solicitar y formalizar la salida completa de la universidad, no solo de una carrera.").
requisito(retiro_universidad, 2, "La facultad debe determinar las obligaciones que permanecen después del retiro.").
documento_def(retiro_universidad_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(retiro_universidad, 1, retiro_universidad_otro, opcional).
documento_def(retiro_universidad_respaldo_motivo, "Respaldo del motivo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(retiro_universidad, 2, retiro_universidad_respaldo_motivo, opcional).
costo_texto(retiro_universidad, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(retiro_universidad, "Consulta con tu facultad las fechas aplicables a tu período académico.").
tramite(retiro_extemporaneo, "Retiro extemporáneo").
descripcion(retiro_extemporaneo, "Solicitar fuera del plazo ordinario el retiro de una o varias materias.").
requisito(retiro_extemporaneo, 1, "Solicitar el retiro una vez terminado el plazo ordinario y someter la circunstancia excepcional a revisión académica.").
requisito(retiro_extemporaneo, 2, "La autorización y las consecuencias académicas y financieras dependen de la revisión del caso.").
documento_def(retiro_extemporaneo_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(retiro_extemporaneo, 1, retiro_extemporaneo_otro, opcional).
documento_def(retiro_extemporaneo_evidencia_excepcional, "Evidencia de la circunstancia excepcional", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(retiro_extemporaneo, 2, retiro_extemporaneo_evidencia_excepcional, opcional).
costo_texto(retiro_extemporaneo, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(retiro_extemporaneo, "Se solicita una vez terminado el plazo ordinario. Consulta con la facultad la fecha máxima para revisar tu caso.").
tramite(tercer_registro, "Tercer registro").
descripcion(tercer_registro, "Solicitar autorización para cursar por tercera vez una materia reprobada dos veces.").
requisito(tercer_registro, 1, "Haber reprobado la misma materia en dos registros anteriores.").
requisito(tercer_registro, 2, "El material revisado excluye Medicina y Enfermería y limita los terceros registros durante la carrera; confirmar su aplicación al caso.").
requisito(tercer_registro, 3, "Obtener autorización académica y confirmar la condición de cursar la materia en exclusividad.").
documento_def(tercer_registro_otro, "Otro documento de apoyo", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(tercer_registro, 1, tercer_registro_otro, opcional).
documento_def(tercer_registro_registros_previos, "Calificaciones y periodos de registros anteriores", "Puedes adjuntarlo como apoyo a tu solicitud.", "PDF, JPG o PNG", sin_url).
tramite_documento(tercer_registro, 2, tercer_registro_registros_previos, opcional).
costo_texto(tercer_registro, "Consulta con Secretaría el arancel vigente y los efectos económicos aplicables a tu solicitud.").
plazo_texto(tercer_registro, "Consulta con tu facultad las fechas aplicables a tu período académico.").
