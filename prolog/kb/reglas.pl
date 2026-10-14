:- encoding(utf8).

% ============================================================================
% Reglas de consulta y evaluación para el chatbot (contrato JSON v2).
% Conecta el servidor HTTP (servidor.pl) con la base de hechos (hechos.pl).
% ============================================================================

:- use_module(library(pairs)).
:- use_module(library(lists)).

:- multifile consultar/3.
:- multifile evaluar/5.

% ----------------------------------------------------------------------------
% 1. CONSULTAS INFORMATIVAS: consultar(Intencion, Tramite, Salida)
% Responde a las 5 intenciones oficiales del contrato v2.
% ----------------------------------------------------------------------------

% Consulta de descripción general del trámite
consultar(consultar_descripcion, Tramite, Salida) :-
    (   descripcion(Tramite, Desc)
    ->  Items = [Desc]
    ;   Items = []
    ),
    obtener_aviso(Tramite, Aviso),
    Salida = _{
        items: Items,
        documentos: [],
        costo: null,
        plazo: null,
        resultado: null,
        aviso: Aviso
    }.

% Consulta de requisitos ordenados
consultar(consultar_requisitos, Tramite, Salida) :-
    findall(Orden-Texto, requisito(Tramite, Orden, Texto), Pares),
    (   Pares \== []
    ->  keysort(Pares, Ordenados),
        pairs_values(Ordenados, Items)
    ;   Items = []
    ),
    obtener_aviso(Tramite, Aviso),
    Salida = _{
        items: Items,
        documentos: [],
        costo: null,
        plazo: null,
        resultado: null,
        aviso: Aviso
    }.

% Consulta de documentos requeridos y respaldos sugeridos
consultar(consultar_documentos, Tramite, Salida) :-
    findall(Orden-DocDict, (
        tramite_documento(Tramite, Orden, DocId, Obligacion),
        documento_def(DocId, Nombre, Desc, Formato, Url0),
        ( Url0 == sin_url -> Url = null ; Url = Url0 ),
        ( Obligacion == obligatorio -> Oblig = true ; Oblig = false ),
        atom_string(DocId, DocIdStr),
        DocDict = _{
            id: DocIdStr,
            nombre: Nombre,
            descripcion: Desc,
            obligatorio: Oblig,
            formato: Formato,
            url: Url
        }
    ), Pares),
    (   Pares \== []
    ->  keysort(Pares, Ordenados),
        pairs_values(Ordenados, Documentos)
    ;   Documentos = []
    ),
    obtener_aviso(Tramite, Aviso),
    Salida = _{
        items: [],
        documentos: Documentos,
        costo: null,
        plazo: null,
        resultado: null,
        aviso: Aviso
    }.

% Consulta de plazos
consultar(consultar_plazo, Tramite, Salida) :-
    (   plazo_texto(Tramite, TextoPlazo)
    ->  Plazo = _{texto: TextoPlazo, inicio: null, fin: null}
    ;   Plazo = null
    ),
    obtener_aviso(Tramite, Aviso),
    Salida = _{
        items: [],
        documentos: [],
        costo: null,
        plazo: Plazo,
        resultado: null,
        aviso: Aviso
    }.

% Consulta de costos
consultar(consultar_costo, Tramite, Salida) :-
    (   costo(Tramite, Centavos, Moneda)
    ->  Costo = _{centavos: Centavos, moneda: Moneda}
    ;   Costo = null
    ),
    obtener_aviso(Tramite, Aviso),
    Salida = _{
        items: [],
        documentos: [],
        costo: Costo,
        plazo: null,
        resultado: null,
        aviso: Aviso
    }.

% ----------------------------------------------------------------------------
% 2. EVALUACIÓN DE ELEGIBILIDAD: evaluar(Tramite, Perfil, Fecha, Origen, Salida)
% Evalúa los datos recibidos del estudiante según las reglas del reglamento.
% ----------------------------------------------------------------------------

% Caso especial: Examen de Recuperación (reglas específicas)
evaluar(examen_recuperacion, Perfil, _Fecha, _Origen, Salida) :-
    obtener_aviso(examen_recuperacion, Aviso),
    (   ( get_dict(promedio, Perfil, Promedio) ; get_dict(period_gpa, Perfil, Promedio) ; get_dict(gpa, Perfil, Promedio) )
    ->  (   Promedio >= 75
        ->  Estado = "cumple",
            format(string(MsgOk), "Tu GPA (~w) supera o iguala el mínimo de 75.", [Promedio]),
            Motivos = [_{codigo: "promedio_ok", texto: MsgOk}]
        ;   Estado = "no_cumple",
            format(string(MsgNo), "Tu GPA (~w) no alcanza el mínimo requerido de 75.", [Promedio]),
            Motivos = [_{codigo: "promedio_insuficiente", texto: MsgNo}]
        )
    ;   Estado = "requiere_revision",
        Motivos = [_{codigo: "datos_incompletos", texto: "Falta ingresar el promedio o datos requeridos para completar la evaluación."}]
    ),
    Salida = _{
        items: [],
        documentos: [],
        costo: null,
        plazo: null,
        resultado: _{
            estado: Estado,
            motivos: Motivos
        },
        aviso: Aviso
    }.

% Caso general para los demás trámites:
evaluar(Tramite, _Perfil, _Fecha, _Origen, Salida) :-
    Tramite \== examen_recuperacion,
    obtener_aviso(Tramite, Aviso),
    (   pendiente_validacion(Tramite, DetallePendiente)
    ->  TextoMotivo = DetallePendiente
    ;   TextoMotivo = "La solicitud requiere revisión y autorización de la Facultad según la normativa vigente."
    ),
    Salida = _{
        items: [],
        documentos: [],
        costo: null,
        plazo: null,
        resultado: _{
            estado: "requiere_revision",
            motivos: [_{codigo: "revision_facultad", texto: TextoMotivo}]
        },
        aviso: Aviso
    }.

% ----------------------------------------------------------------------------
% Predicados auxiliares
% ----------------------------------------------------------------------------

obtener_aviso(Tramite, Aviso) :-
    (   pendiente_validacion(Tramite, AvisoTexto)
    ->  Aviso = AvisoTexto
    ;   observacion_conocimiento(Tramite, ObsTexto)
    ->  Aviso = ObsTexto
    ;   Aviso = null
    ).
