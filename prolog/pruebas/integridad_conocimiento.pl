:- encoding(utf8).
:- use_module(library(plunit)).
:- begin_tests(integridad_catalogo).
:- ensure_loaded('../kb/hechos.pl').
test(veinte_tramites_unicos) :- findall(T,tramite(T,_),Ts),length(Ts,20),sort(Ts,Us),length(Us,20).
test(version_unica) :- findall(V,kb_version(V),[V]),string(V).
test(documentos_referenciados,[forall(tramite_documento(T,_,Id,Ob))]) :- tramite(T,_),documento_def(Id,_,_,_,_),memberchk(Ob,[obligatorio,opcional,condicional]).
test(textos_publicos_sin_notas_internas,[forall((tramite(T,_),member(P,[descripcion,plazo_texto,costo_texto]),H=..[P,T,Texto],call(H)))]) :-
    string_lower(Texto,L),\+ sub_string(L,_,_,_,"anny"),\+ sub_string(L,_,_,_,"por confirmar"),\+ sub_string(L,_,_,_,"pendiente de confirmar").
test(parametros_acordados) :- parametro(examen_recuperacion,gpa_general_minimo,75),parametro(examen_recuperacion,intervalo_recuperacion_dias,365).
test(requisitos_son_condiciones,[forall(requisito(_,_,R))]) :- \+sub_string(R,_,_,_,"Formulario:"),\+sub_string(R,_,_,_,"Por confirmar").
:- end_tests(integridad_catalogo).
