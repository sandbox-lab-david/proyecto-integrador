:- encoding(utf8).
:- use_module(library(plunit)).

% Pruebas directas, sin Laravel ni servidor HTTP.
:- begin_tests(reglas_chatbot).
:- ensure_loaded('../kb/hechos.pl').
:- ensure_loaded('../kb/reglas.pl').

evaluacion_unica(T, Perfil, Estado, Motivos) :-
    findall(S, evaluar(T, Perfil, "2026-10-15", manual, S), [Salida]),
    assertion(is_dict(Salida)),
    get_dict(resultado, Salida, Resultado),
    get_dict(estado, Resultado, Estado),
    get_dict(motivos, Resultado, Motivos),
    assertion(memberchk(Estado, ["cumple", "no_cumple", "requiere_revision"])),
    assertion(Motivos \== []),
    forall(member(M, Motivos), (
        assertion(is_dict(M)), get_dict(codigo, M, C), get_dict(texto, M, Texto),
        assertion((string(C), C \== "", string(Texto), Texto \== ""))
    )).

con_motivo(Motivos, Codigo) :-
    member(M, Motivos), get_dict(codigo, M, Codigo), !.

% Mismo orden que reglas.pl. pendiente_validacion/2 y observacion_conocimiento/2
% hoy no tienen hechos, asi que el aviso esperado es null.
aviso_esperado(T, Aviso) :-
    (   pendiente_validacion(T, Aviso) -> true
    ;   observacion_conocimiento(T, Aviso) -> true
    ;   Aviso = null
    ).

orientacion_esperada(T, Texto) :-
    (   pendiente_validacion(T, Texto) -> true
    ;   observacion_conocimiento(T, Texto) -> true
    ;   descripcion(T, Texto)
    ).

test(promedios_inferiores_al_minimo,
     [forall(member(P, [0, 60, 74.99]))]) :-
    evaluacion_unica(examen_recuperacion, _{promedio:P}, "no_cumple", Ms),
    con_motivo(Ms, "promedio_insuficiente").

test(promedio_suficiente_no_aprueba,
     [forall(member(P, [75, 80, 100]))]) :-
    evaluacion_unica(examen_recuperacion, _{promedio:P}, "requiere_revision", Ms),
    con_motivo(Ms, "promedio_ok"), con_motivo(Ms, "condiciones_no_evaluadas").

test(promedio_ausente,
     [forall(member(P, [_{}, _{materia_id:"MAT101"}, _{promedio:null},
                         _{promedio:null, gpa:null, period_gpa:null}]))]) :-
    evaluacion_unica(examen_recuperacion, P, "requiere_revision", Ms),
    con_motivo(Ms, "promedio_ausente").

test(promedio_invalido_no_lanza_error,
     [forall(member(V, [-1, 101, "80", "abc", true, [], _{}]))]) :-
    evaluacion_unica(examen_recuperacion, _{promedio:V}, "requiere_revision", Ms),
    con_motivo(Ms, "promedio_invalido").

test(perfil_invalido,
     [forall(member(P, [null, [], "perfil"]))]) :-
    evaluacion_unica(examen_recuperacion, P, "requiere_revision", Ms),
    con_motivo(Ms, "perfil_invalido").

test(alias_individuales,
     [forall(member(K, [promedio, period_gpa, gpa]))]) :-
    dict_create(P, _, [K-80]),
    evaluacion_unica(examen_recuperacion, P, "requiere_revision", Ms),
    con_motivo(Ms, "promedio_ok").

test(alias_equivalentes_sin_duplicar_respuestas) :-
    evaluacion_unica(examen_recuperacion,
        _{promedio:80, period_gpa:80.0, gpa:80}, "requiere_revision", Ms),
    con_motivo(Ms, "promedio_ok").

test(alias_nulo_no_oculta_valor) :-
    evaluacion_unica(examen_recuperacion,
        _{promedio:null, gpa:80}, "requiere_revision", Ms),
    con_motivo(Ms, "promedio_ok").

test(alias_contradictorios,
     [forall(member(P, [_{promedio:80, gpa:60}, _{gpa:80, period_gpa:60},
                         _{promedio:60, gpa:80}]))]) :-
    evaluacion_unica(examen_recuperacion, P, "requiere_revision", Ms),
    con_motivo(Ms, "promedio_contradictorio").

test(alias_invalido_no_se_ignora) :-
    evaluacion_unica(examen_recuperacion,
        _{promedio:80, gpa:"80"}, "requiere_revision", Ms),
    con_motivo(Ms, "promedio_invalido").

test(campos_actuales_no_completan_condiciones) :-
    evaluacion_unica(examen_recuperacion,
        _{promedio:80, materia_id:"MAT101", asistencia:100,
          tipo_solicitante:"estudiante"}, "requiere_revision", Ms),
    con_motivo(Ms, "condiciones_no_evaluadas").

test(origen_sesion_no_cambia_el_resultado) :-
    findall(Salida, evaluar(examen_recuperacion, _{promedio:80},
        "2026-10-15", sesion, Salida), [S]),
    get_dict(resultado, S, R), get_dict(estado, R, "requiere_revision").

test(evaluaciones_declaradas_sin_estado_compartido) :-
    evaluacion_unica(examen_recuperacion, _{promedio:80}, "requiere_revision", _),
    evaluacion_unica(examen_recuperacion, _{promedio:60}, "no_cumple", _),
    evaluacion_unica(examen_recuperacion, _{}, "requiere_revision", Ms),
    con_motivo(Ms, "promedio_ausente").

test(otros_tramites_explican_limitacion_y_conservan_orientacion,
     [forall((tramite(T, _), T \== examen_recuperacion))]) :-
    forall(member(P, [_{}, _{promedio:100}]), (
        evaluacion_unica(T, P, "requiere_revision", Ms),
        con_motivo(Ms, "evaluacion_no_disponible"),
        orientacion_esperada(T, Orientacion),
        assertion(member(_{codigo:"orientacion_tramite", texto:Orientacion}, Ms))
    )).

test(consultas_informativas_para_cada_tramite,
     [forall((tramite(T, _), member(I, [consultar_descripcion,
        consultar_requisitos, consultar_documentos, consultar_costo,
        consultar_plazo])))]) :-
    findall(S, consultar(I, T, S), [Salida]),
    get_dict(resultado, Salida, null),
    get_dict(aviso, Salida, Aviso), aviso_esperado(T, Aviso),
    comprobar_consulta(I, T, Salida).

comprobar_consulta(consultar_descripcion, T, S) :-
    descripcion(T, D), get_dict(items, S, [D]).
comprobar_consulta(consultar_requisitos, T, S) :-
    findall(Orden-Texto, requisito(T, Orden, Texto), Pares),
    keysort(Pares, Ordenados), pairs_values(Ordenados, Textos),
    get_dict(items, S, Textos).
comprobar_consulta(consultar_documentos, T, S) :-
    get_dict(documentos, S, Docs),
    findall(Orden-Id, tramite_documento(T, Orden, Id, _), Pares),
    keysort(Pares, Ordenados), pairs_values(Ordenados, Ids),
    findall(Id, (member(D, Docs), get_dict(id, D, Texto), atom_string(Id, Texto)), Ids).
comprobar_consulta(consultar_costo, T, S) :-
    (   costo(T, C, M)
    ->  get_dict(costo, S, _{centavos:C, moneda:M})
    ;   get_dict(costo, S, null)
    ).
comprobar_consulta(consultar_plazo, T, S) :-
    plazo_texto(T, Texto),
    get_dict(plazo, S, _{texto:Texto, inicio:null, fin:null}).

:- end_tests(reglas_chatbot).
