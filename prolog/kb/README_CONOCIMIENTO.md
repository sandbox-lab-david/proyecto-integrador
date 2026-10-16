# Base de conocimientos del chatbot

## Alcance del avance

Esta entrega contiene:

- Catálogo inicial de los 20 trámites del documento de requerimientos.
- Predicados del contrato de conocimiento del componente 05.
- Estado de preparación del contenido de cada trámite.
- Un caso ficticio de examen de recuperación para integración.
- Identificación de pendientes y consultas de comprobación.

No contiene todavía las fichas normativas completas ni un exportador
desde PostgreSQL.

## Fuente editable

Durante este avance, hechos.pl es la única fuente editable de hechos.

La propuesta para la siguiente etapa es generar este archivo desde
PostgreSQL mediante Laravel. Cuando se implemente la exportación,
hechos.pl dejará de editarse manualmente.

Antes de diseñar tablas, se acordará con el Grupo 1 qué información
ya pertenece a su catálogo.

## Fuentes y vigencia

Catálogo de nombres:
- Documento: Requerimientos · Sistema de Solicitudes Estudiantiles UEES.
- Referencia: sección 6.3.
- Vigencia institucional: pendiente de confirmación.

Estructura de hechos:
- Documento: 05_Base_conocimientos.pdf.
- Referencia: contrato conocimiento-reglas.
- Comunicación JSON: versión 2.

Caso examen_recuperacion:
- Descripción, requisito, documento y parámetro: datos de demostración.
- Uso: pruebas de estructura e integración.
- Vigencia normativa: no confirmada.
- No presentar estos datos como requisitos oficiales.

## Contrato

Los identificadores son átomos.
Los textos y la versión son strings.
El orden de requisitos y documentos es un entero positivo.
La obligatoriedad es obligatorio u opcional.
La URL es un string o el átomo sin_url.
El estado del conocimiento es listo, parcial o pendiente.

Un documento asociado a un trámite debe existir en documento_def/5.

No interpretar información ausente como:
- Costo cero.
- Condición cumplida.
- Solicitante permitido.
- Dato personal verificado.

## Compatibilidad de versión

El PDF define version_conocimiento/1.
El servidor actual consulta kb_version/1.

Se incluye un adaptador kb_version/1 que utiliza la misma versión.
No son dos versiones independientes.

El nombre definitivo debe acordarse con el responsable del puente.

## Separación de responsabilidades

Este archivo contiene información y parámetros.

Las comparaciones de promedio, fechas y otras condiciones corresponden
a reglas.pl, a cargo del responsable de reglas.

El responsable del puente carga hechos.pl y reglas.pl desde prolog/kb/.

El historial personal del estudiante no se almacena en este archivo.

## Comprobaciones manuales

Con SWI-Prolog instalado:

    swipl -q -s prolog/kb/hechos.pl

En la consola de Prolog:

    version_conocimiento(V).

Resultado esperado:

    V = "avance-001".

Consultar cantidad de trámites:

    findall(Id, tramite(Id, _), Tramites), length(Tramites, N).

Resultado esperado:

    N = 20.

Comprobar referencias de documentos:

    forall(
        tramite_documento(_, _, Documento, _),
        documento_def(Documento, _, _, _, _)
    ).

Resultado esperado:

    true.

Consultar requisitos ordenados:

    findall(
        Orden-Texto,
        requisito(examen_recuperacion, Orden, Texto),
        Requisitos
    ),
    keysort(Requisitos, Ordenados).

Consultar documentos del caso de demostración:

    tramite_documento(examen_recuperacion, Orden, Documento, Obligacion),
    documento_def(Documento, Nombre, Descripcion, Formato, Url).

Salir:

    halt.

## Pendientes

1. Confirmar identificadores con interfaz, Laravel y reglas.
2. Obtener las fuentes oficiales y la base completa mencionada
   en los requerimientos generales.
3. Completar fichas de los 20 trámites.
4. Registrar fuente, vigencia y dudas por cada ficha.
5. Acordar tipos de solicitantes.
6. Acordar nuevos parámetros y unidades con reglas.
7. Definir dónde se conserva la información con el Grupo 1.
8. Implementar exportación y validación antes de publicar.
9. Conservar la última versión válida si una actualización falla.
10. Probar integración con reglas.pl y el servidor HTTP.