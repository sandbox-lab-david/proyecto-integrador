:- encoding(utf8).
:- use_module(library(pairs)).
:- use_module(library(date)).

% Todos los perfiles son argumentos locales de la consulta. No se almacenan hechos personales.
base_respuesta(_{items:[],documentos:[],costo:null,plazo:null,resultado:null,comprobaciones:[],aviso:null}).
consultar(consultar_descripcion,T,S) :- descripcion(T,D),base_respuesta(B),S=B.put(items,[D]).
consultar(consultar_requisitos,T,S) :- findall(O-R,requisito(T,O,R),Ps),keysort(Ps,Orden),pairs_values(Orden,Rs),base_respuesta(B),S=B.put(items,Rs).
consultar(consultar_documentos,T,S) :-
    findall(O-_{id:I,nombre:N,descripcion:D,formato:F,url:Url,obligatorio:Ob,obligacion:OS},
        (tramite_documento(T,O,Id,Oblig),documento_def(Id,N,D,F,Url0),(Url0==sin_url->Url=null;Url=Url0),atom_string(Id,I),atom_string(Oblig,OS),(Oblig==obligatorio->Ob=true;Ob=false)),Ps),
    keysort(Ps,Orden),pairs_values(Orden,Ds),base_respuesta(B),S=B.put(documentos,Ds).
consultar(consultar_costo,T,S) :- costo_texto(T,Texto),base_respuesta(B),S=B.put(items,[Texto]).
consultar(consultar_plazo,T,S) :- plazo_texto(T,Texto),base_respuesta(B),S=B.put(plazo,_{texto:Texto,inicio:null,fin:null}).

evaluar(examen_recuperacion,P,Fecha,manual,S) :-
    parametro(examen_recuperacion,gpa_general_minimo,Min),parametro(examen_recuperacion,intervalo_recuperacion_dias,Intervalo),
    referencia_gpa(P,Min,G),referencia_dias(P,Fecha,Intervalo,D),
    ( get_dict(documentos_subidos, P, _)
    -> comprobacion_documentos(examen_recuperacion, P, DocComp), Cs = [G, D, DocComp]
    ;  Cs = [G, D] ),
    base_respuesta(B),S=B.put(comprobaciones,Cs).
evaluar(T,P,_,manual,S) :-
    T\==examen_recuperacion,
    ( get_dict(documentos_subidos, P, _)
    -> comprobacion_documentos(T, P, DocComp), base_respuesta(B), S=B.put(comprobaciones,[DocComp])
    ;  base_respuesta(B),S=B.put(items,["Consulta los requisitos y documentos del trámite. La facultad revisa los antecedentes de tu solicitud."]) ).

documentos_obligatorios_tramite(Tramite, Docs) :-
    findall(DocId-Nombre,
        ( tramite_documento(Tramite, _, DocId, obligatorio),
          documento_def(DocId, Nombre, _, _, _) ),
        Docs).

comprobacion_documentos(Tramite, Perfil, Comp) :-
    documentos_obligatorios_tramite(Tramite, Obligatorios),
    ( get_dict(documentos_subidos, Perfil, Subidos0), is_list(Subidos0)
    -> maplist(atom_string, Subidos, Subidos0)
    ; Subidos = []
    ),
    findall(Nombre,
        ( member(DocId-Nombre, Obligatorios),
          \+ member(DocId, Subidos) ),
        Faltantes),
    ( tramite(Tramite, NombreTramite) -> true ; NombreTramite = Tramite ),
    length(Obligatorios, TotalOblig),
    length(Faltantes, TotalFaltantes),
    ( TotalOblig == 0 ->
        PuedeAvanzar = true,
        Cumple = true,
        format(string(Texto), "El trámite «~w» no exige documentos obligatorios de respaldo. Puedes continuar con tu solicitud.", [NombreTramite])
    ; TotalFaltantes == 0 ->
        PuedeAvanzar = true,
        Cumple = true,
        format(string(Texto), "¡Comprobación exitosa! Has presentado todos los documentos obligatorios requeridos para «~w». Puedes continuar al siguiente paso.", [NombreTramite])
    ; TotalFaltantes == 1, Faltantes = [Unico] ->
        PuedeAvanzar = false,
        Cumple = false,
        format(string(Texto), "No puedes avanzar al siguiente paso. Te falta adjuntar el siguiente documento obligatorio: ~w.", [Unico])
    ;
        PuedeAvanzar = false,
        Cumple = false,
        atomic_list_concat(Faltantes, ", ", FaltantesTexto),
        format(string(Texto), "No puedes avanzar al siguiente paso. Te faltan adjuntar los siguientes documentos obligatorios: ~w.", [FaltantesTexto])
    ),
    Comp = _{
        codigo: "verificacion_documentos",
        texto: Texto,
        datos: _{
            cumple: Cumple,
            puede_avanzar: PuedeAvanzar,
            total_obligatorios: TotalOblig,
            total_faltantes: TotalFaltantes,
            documentos_faltantes: Faltantes
        }
    }.

verificar_documentos(Tramite, Subidos, Resultado) :-
    comprobacion_documentos(Tramite, _{documentos_subidos: Subidos}, Comp),
    Resultado = Comp.datos.put(_{texto: Comp.texto, codigo: Comp.codigo}).


referencia_gpa(P,Min,R) :-
    (get_dict(gpa_general,P,G),G\==null -> number(G),G>=0,G=<100,
      Dif is G-Min,format(string(Texto),"La referencia de GPA general es ~w. El GPA general que indicaste es ~w; la diferencia respecto a la referencia es ~w puntos.",[Min,G,Dif]),
      Datos=_{referencia:Min,declarado:G,diferencia:Dif}
    ; format(string(Texto),"La referencia de GPA general es ~w sobre 100. Puedes consultar los requisitos sin declarar tu GPA.",[Min]),Datos=_{referencia:Min,declarado:null,diferencia:null}),
    R=_{codigo:"gpa_general",texto:Texto,datos:Datos}.

fecha_dias(F,Stamp) :- split_string(F,"-","",[Ys,Ms,Ds]),maplist(number_string,[Y,M,D],[Ys,Ms,Ds]),
    date_time_stamp(date(Y,M,D,0,0,0,0,-,-),Stamp),stamp_date_time(Stamp,date(Y,M,D,_,_,_,_,_,_),0).
referencia_dias(P,Fecha,Intervalo,R) :-
    (get_dict(fecha_ultima_recuperacion,P,F),F\==null -> fecha_dias(F,Inicio),fecha_dias(Fecha,Hoy),Inicio=<Hoy,
      Dias is floor((Hoy-Inicio)/86400),Restantes is max(0,Intervalo-Dias),Fin is Inicio+Intervalo*86400,
      stamp_date_time(Fin,FinUTC,0),format_time(string(Completa),"%Y-%m-%d",FinUTC,posix),
      format(string(Texto),"Desde el último examen que indicaste han transcurrido ~w días. El intervalo de ~w días se completa el ~s; quedan ~w días.",[Dias,Intervalo,Completa,Restantes]),
      Datos=_{intervalo:Intervalo,fecha_declarada:F,dias_transcurridos:Dias,dias_restantes:Restantes,fecha_cumplimiento:Completa}
    ; format(string(Texto),"El intervalo es de ~w días desde la fecha del último examen de recuperación. Si no conoces la fecha, consulta tus antecedentes con la facultad.",[Intervalo]),
      Datos=_{intervalo:Intervalo,fecha_declarada:null,dias_transcurridos:null,dias_restantes:null,fecha_cumplimiento:null}),
    R=_{codigo:"intervalo_recuperacion",texto:Texto,datos:Datos}.
