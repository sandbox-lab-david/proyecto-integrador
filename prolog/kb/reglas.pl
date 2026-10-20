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

% Contención de recuperación: el perfil actual no representa todas las
% condiciones. Un promedio suficiente nunca basta para devolver cumple.
evaluar(examen_recuperacion, Perfil, _Fecha, _Origen, Salida) :-
    obtener_aviso(examen_recuperacion, Aviso),
    promedio_recuperacion(Perfil, EstadoPromedio),
    resultado_recuperacion(EstadoPromedio, Estado, Motivos),
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
    ;   observacion_conocimiento(Tramite, Observacion)
    ->  TextoMotivo = Observacion
    ;   descripcion(Tramite, Descripcion)
    ->  TextoMotivo = Descripcion
    ;   TextoMotivo = "Consulta los requisitos del trámite para conocer la información disponible."
    ),
    Salida = _{
        items: [],
        documentos: [],
        costo: null,
        plazo: null,
        resultado: _{
            estado: "requiere_revision",
            motivos: [
                _{codigo: "evaluacion_no_disponible", texto: "No hay una evaluación automática implementada para este trámite. Esta respuesta no determina cumplimiento ni incumplimiento."},
                _{codigo: "orientacion_tramite", texto: TextoMotivo}
            ]
        },
        aviso: Aviso
    }.

% ----------------------------------------------------------------------------
% Predicados auxiliares
% ----------------------------------------------------------------------------

% Alias históricos solo para llamadas directas: el puente recibe promedio.
% null equivale a dato ausente, igual que en perfil_sin_nulos/2 del puente.
% No se elige silenciosamente un alias si otro contiene un valor inválido
% o contradictorio. La comparación numérica acepta 80 y 80.0 como iguales.
promedio_recuperacion(Perfil, Estado) :-
    (   is_dict(Perfil)
    ->  findall(V, (member(K, [promedio, period_gpa, gpa]),
                   get_dict(K, Perfil, V), V \== null), Valores),
        (   Valores == []
        ->  Estado = ausente
        ;   \+ maplist(promedio_valido, Valores)
        ->  Estado = invalido
        ;   Valores = [Primero|Resto],
            (   maplist(promedio_igual(Primero), Resto)
            ->  Estado = valido(Primero)
            ;   Estado = contradictorio
            )
        )
    ;   Estado = perfil_invalido
    ).

promedio_valido(V) :- number(V), catch((V >= 0, V =< 100), _, fail).
promedio_igual(A, B) :- A =:= B.

resultado_recuperacion(valido(Promedio), Estado, Motivos) :-
    (   Promedio < 75
    ->  Estado = "no_cumple",
        format(string(Msg), "El promedio declarado (~w) es inferior al mínimo de 75. Los datos son declarados, no verificados institucionalmente.", [Promedio]),
        Motivos = [_{codigo: "promedio_insuficiente", texto: Msg}]
    ;   Estado = "requiere_revision",
        format(string(Msg), "El promedio declarado (~w) alcanza el mínimo de 75; esto solo comprueba el promedio, no la elegibilidad completa.", [Promedio]),
        Motivos = [
            _{codigo: "promedio_ok", texto: Msg},
            _{codigo: "condiciones_no_evaluadas", texto: "No se han comprobado la materia reprobada, ausencia de fraude, historial de recuperación, cantidad de materias ni plazos aplicables. El perfil actual no permite completar esas comprobaciones."}
        ]
    ).
resultado_recuperacion(ausente, "requiere_revision", [
    _{codigo: "promedio_ausente", texto: "Falta el promedio declarado para comprobar el mínimo de 75. No se puede determinar la elegibilidad completa."}
]).
resultado_recuperacion(invalido, "requiere_revision", [
    _{codigo: "promedio_invalido", texto: "El promedio debe ser un número entre 0 y 100; no se evalúa un valor inválido."}
]).
resultado_recuperacion(contradictorio, "requiere_revision", [
    _{codigo: "promedio_contradictorio", texto: "Se recibieron valores distintos para el promedio y sus alias; deben corregirse antes de evaluar."}
]).
resultado_recuperacion(perfil_invalido, "requiere_revision", [
    _{codigo: "perfil_invalido", texto: "El perfil debe ser un objeto de datos declarados."}
]).

obtener_aviso(Tramite, Aviso) :-
    (   pendiente_validacion(Tramite, AvisoTexto)
    ->  Aviso = AvisoTexto
    ;   observacion_conocimiento(Tramite, ObsTexto)
    ->  Aviso = ObsTexto
    ;   Aviso = null
    ).
