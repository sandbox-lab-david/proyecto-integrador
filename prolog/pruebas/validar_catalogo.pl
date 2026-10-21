:- use_module(library(http/json)).
:- initialization(main, main).
main :- current_prolog_flag(argv,[Hechos,Reglas]),
    load_files([Hechos,Reglas],[silent(true)]),
    findall(T,tramite(T,_),Ts),length(Ts,20),sort(Ts,Unicos),length(Unicos,20),
    forall(member(T,Ts),(forall(member(I,[consultar_descripcion,consultar_requisitos,consultar_documentos,consultar_costo,consultar_plazo]),(once(consultar(I,T,S)),is_dict(S),with_output_to(string(_),json_write_dict(current_output,S,[])))),true)),
    parametro(examen_recuperacion,gpa_general_minimo,75),parametro(examen_recuperacion,intervalo_recuperacion_dias,365),
    once(evaluar(examen_recuperacion,_{gpa_general:72,fecha_ultima_recuperacion:"2025-10-20"},"2026-10-20",manual,R)),
    R.resultado==null,length(R.comprobaciones,2),
    writeln(catalogo_valido),halt(0).
main :- halt(1).
