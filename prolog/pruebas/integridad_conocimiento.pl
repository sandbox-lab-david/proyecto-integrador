:- encoding(utf8).
:- use_module(library(plunit)).

% Solo carga hechos: no arranca HTTP ni ejecuta reglas de elegibilidad.
:- begin_tests(integridad_conocimiento).
:- ensure_loaded('../kb/hechos.pl').

texto(T) :- string(T), string_length(T, N), N > 0.
positivo(N) :- integer(N), N > 0.
unicos(Xs) :- sort(Xs, Unicos), same_length(Xs, Unicos).
ordenes_validos(Pares) :-
    forall(member(_-Orden, Pares), positivo(Orden)), unicos(Pares).
referencia_documento(T, D) :- tramite(T, _), documento_def(D, _, _, _, _).

test(catalogo_actual_sin_perdidas_ni_duplicados) :-
    findall(T, tramite(T, _), Ts), unicos(Ts),
    sort(Ts, [alcance_homologacion, ayudante_catedra, cambio_carrera,
        cambio_malla, cambio_modalidad, examen_gracia, examen_recuperacion,
        examen_suficiencia, examen_supletorio, homologacion, incompleto,
        person_to_person, recalificacion, registro_extemporaneo,
        reincorporacion_carrera, retiro_carrera, retiro_extemporaneo,
        retiro_materia, retiro_universidad, tercer_registro]).

test(tipos_catalogo) :-
    forall(tramite(T, N), assertion((atom(T), texto(N)))).

test(version_unica_y_adaptador) :-
    findall(V, version_conocimiento(V), [Version]), texto(Version),
    findall(V, kb_version(V), [Version]).

test(fichas_con_metadatos_unicos) :-
    forall(tramite(T, _), (
        findall(D, descripcion(T, D), [Desc]), assertion(texto(Desc)),
        findall(E, estado_conocimiento(T, E), [Estado]),
        assertion(memberchk(Estado, [listo, parcial, pendiente])),
        findall(F, fuente_conocimiento(T, F), [Fuente]), assertion(texto(Fuente)),
        findall(A, identificador_agenda(T, A), [Agenda]), assertion(texto(Agenda))
    )).

test(referencias_a_tramites_existentes) :-
    forall(member(P, [descripcion/2, requisito/3, tramite_documento/4,
        costo/3, plazo_texto/2, parametro/3, tipo_solicitante_permitido/2,
        estado_conocimiento/2, fuente_conocimiento/2, identificador_agenda/2,
        campo_formulario/4, pendiente_validacion/2, observacion_conocimiento/2]), (
        P = Nombre/Aridad, functor(Hecho, Nombre, Aridad),
        forall(call(Hecho), (arg(1, Hecho, T), assertion(tramite(T, _))))
    )).

test(textos_informativos) :-
    forall(member(Nombre, [descripcion, plazo_texto, fuente_conocimiento,
        pendiente_validacion, observacion_conocimiento]), (
        Hecho =.. [Nombre, _, Texto],
        forall(call(Hecho), assertion(texto(Texto)))
    )),
    forall(requisito(_, _, R), assertion(texto(R))).

test(orden_requisitos) :-
    findall(T-I, requisito(T, I, _), Pares), ordenes_validos(Pares).

test(orden_documentos) :-
    findall(T-I, tramite_documento(T, I, _, _), Pares), ordenes_validos(Pares).

test(documentos_unicos_y_tipados) :-
    findall(D, documento_def(D, _, _, _, _), Ds), unicos(Ds),
    forall(documento_def(D, N, Desc, Formato, Url),
        assertion((atom(D), texto(N), texto(Desc), texto(Formato),
            (Url == sin_url ; texto(Url))))).

test(asociaciones_documentos_validas) :-
    findall(T-D, tramite_documento(T, _, D, _), Pares), unicos(Pares),
    forall(tramite_documento(T, _, D, Obligacion),
        assertion((referencia_documento(T, D),
            memberchk(Obligacion, [obligatorio, opcional])))).

test(campos_unicos_y_tipados) :-
    findall(T-C, campo_formulario(T, C, _, _), Pares), unicos(Pares),
    forall(campo_formulario(_, C, Etiqueta, Obligacion),
        assertion((atom(C), texto(Etiqueta),
            memberchk(Obligacion, [obligatorio, opcional])))).

test(correspondencias_agenda_no_ambiguas) :-
    findall(A, identificador_agenda(_, A), As), unicos(As).

test(contenido_incompleto_con_pendiente_explicito) :-
    forall((estado_conocimiento(T, E), memberchk(E, [parcial, pendiente])),
        assertion((pendiente_validacion(T, Texto), texto(Texto)))).

test(costos_tipados_si_existen) :-
    forall(costo(_, Centavos, Moneda),
        assertion((integer(Centavos), Centavos >= 0, texto(Moneda)))).

test(parametros_tipados_si_existen) :-
    forall(parametro(_, Nombre, Valor),
        assertion((atom(Nombre), ground(Valor),
            (number(Valor) ; atom(Valor) ; string(Valor))))),
    forall(tipo_solicitante_permitido(_, Tipo), assertion(atom(Tipo))).

% Casos negativos: demuestran que los controles rechazan datos inválidos.
% Se prueban con valores aislados, sin alterar los hechos de producción.
test(rechaza_identificador_duplicado, [fail]) :- unicos([tramite_a, tramite_a]).
test(rechaza_orden_duplicado, [fail]) :- ordenes_validos([a-1, a-1]).
test(rechaza_orden_no_positivo, [fail]) :- ordenes_validos([a-0]).
test(permite_mismo_orden_en_distintos_tramites) :- ordenes_validos([a-1, b-1]).
test(rechaza_documento_huerfano, [fail]) :-
    referencia_documento(examen_recuperacion, documento_inexistente_prueba).
test(rechaza_texto_vacio, [fail]) :- texto("").

:- end_tests(integridad_conocimiento).
