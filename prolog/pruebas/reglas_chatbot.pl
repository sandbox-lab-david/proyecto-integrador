:- encoding(utf8).
:- use_module(library(plunit)).
:- begin_tests(referencias_informativas).
:- ensure_loaded('../kb/hechos.pl').
:- ensure_loaded('../kb/reglas.pl').

test(cien_consultas_sin_dictamen,[forall((tramite(T,_),member(I,[consultar_descripcion,consultar_requisitos,consultar_documentos,consultar_costo,consultar_plazo])))]) :-
    findall(S,consultar(I,T,S),[R]),assertion(R.resultado==null),assertion(R.comprobaciones==[]).
test(todos_los_tramites_sin_aprobacion,[forall(tramite(T,_))]) :-
    findall(S,evaluar(T,_{},"2026-10-20",manual,S),[R]),assertion(R.resultado==null).
test(gpa_comparacion,[forall(member(G,[0,72,74.99,75,80,100]))]) :-
    evaluar(examen_recuperacion,_{gpa_general:G},"2026-10-20",manual,R),R.comprobaciones=[C,_],D is G-75,
    assertion(C.datos.diferencia=:=D),assertion(C.datos.declarado=:=G),assertion(R.resultado==null).
test(ausencia_no_supone_historial) :- evaluar(examen_recuperacion,_{},"2026-10-20",manual,R),R.comprobaciones=[G,D],assertion(G.datos.declarado==null),assertion(D.datos.fecha_declarada==null).
test(intervalos,[forall(member(Dias-Restantes,[0-365,364-1,365-0,366-0]))]) :-
    fecha_dias("2025-10-20",Inicio),Ahora is Inicio+Dias*86400,stamp_date_time(Ahora,AhoraUTC,0),format_time(string(Fecha),"%Y-%m-%d",AhoraUTC,posix),
    evaluar(examen_recuperacion,_{fecha_ultima_recuperacion:"2025-10-20"},Fecha,manual,R),R.comprobaciones=[_,D],
    assertion(D.datos.dias_transcurridos==Dias),assertion(D.datos.dias_restantes==Restantes),assertion(D.datos.fecha_cumplimiento=="2026-10-20").
test(anio_bisiesto) :- evaluar(examen_recuperacion,_{fecha_ultima_recuperacion:"2024-02-29"},"2025-02-28",manual,R),R.comprobaciones=[_,D],assertion(D.datos.dias_transcurridos==365),assertion(D.datos.fecha_cumplimiento=="2025-02-28").
test(fecha_futura,[fail]) :- evaluar(examen_recuperacion,_{fecha_ultima_recuperacion:"2026-10-21"},"2026-10-20",manual,_).
test(datos_locales_no_contaminan_otro_usuario) :- evaluar(examen_recuperacion,_{gpa_general:72},"2026-10-20",manual,_),evaluar(examen_recuperacion,_{gpa_general:90},"2026-10-20",manual,R),R.comprobaciones=[G,_],assertion(G.datos.declarado==90).
test(verificacion_documentos_sin_archivos) :-
    verificar_documentos(examen_recuperacion, [], R),
    assertion(R.cumple == false),
    assertion(R.puede_avanzar == false),
    assertion(R.total_faltantes == 1),
    assertion(sub_string(R.texto, _, _, _, "No puedes avanzar al siguiente paso")),
    assertion(sub_string(R.texto, _, _, _, "Registro de calificaciones o evidencia")).
test(verificacion_documentos_completos) :-
    verificar_documentos(examen_recuperacion, ['examen_recuperacion_registro_calificaciones'], R),
    assertion(R.cumple == true),
    assertion(R.puede_avanzar == true),
    assertion(R.total_faltantes == 0),
    assertion(sub_string(R.texto, _, _, _, "¡Comprobación exitosa!")).
test(verificacion_documentos_multiples_faltantes) :-
    verificar_documentos(ayudante_catedra, [], R),
    assertion(R.cumple == false),
    assertion(R.puede_avanzar == false),
    assertion(R.total_faltantes == 2),
    assertion(sub_string(R.texto, _, _, _, "Recomendaciones de al menos dos docentes")),
    assertion(sub_string(R.texto, _, _, _, "Solicitud del docente")).
test(verificacion_documentos_tramite_sin_obligatorios) :-
    verificar_documentos(cambio_carrera, [], R),
    assertion(R.cumple == true),
    assertion(R.puede_avanzar == true),
    assertion(R.total_obligatorios == 0),
    assertion(sub_string(R.texto, _, _, _, "no exige documentos obligatorios")).
test(evaluar_con_documentos_subidos) :-
    evaluar(examen_recuperacion, _{documentos_subidos: []}, "2026-10-20", manual, R),
    R.comprobaciones = [_, _, DocComp],
    assertion(DocComp.codigo == "verificacion_documentos"),
    assertion(DocComp.datos.puede_avanzar == false).
:- end_tests(referencias_informativas).
