# Catálogo normalizado de G4: integración para administración

El catálogo sigue siendo informativo. G4 mantiene los datos, las interfaces existentes
y la publicación a Prolog. Las pantallas administrativas corresponden a G1. Esta
implementación no cambia código de G1, G2 ni G3.

## Tablas y relaciones

`tipos_tramite` conserva `codigo`, `nombre`, `slug` y `contenido`. Añade
`categoria_tramite_id` y `catalogo_normalizado`. Para un trámite normalizado,
requisitos, campos, parámetros y categoría se leen de las relaciones. Las copias
de esos datos en `contenido` son el respaldo anterior y **no se usan para reponer
elementos eliminados**. La descripción, costo y plazo informativos, instrucciones,
máximo de materias, fuentes y notas continúan en `contenido`.

| Tabla | Campos editables y reglas |
|---|---|
| `categorias_tramite` | `codigo` único, `nombre`, `descripcion`, `orden`. Se relaciona con los trámites por `categoria_tramite_id`. No borrar categorías utilizadas. |
| `requisitos_tramite` | `tipo_tramite_id`, `texto`, `orden`. Cada fila es un requisito informativo; un documento es un concepto separado. |
| `campos_tramite` | `tipo_tramite_id`, `ambito` (`general` o `materia`), `nombre`, `etiqueta`, `tipo`, `obligatorio`, `orden`, `configuracion` JSON. La combinación trámite, ámbito y nombre es única. |
| `umbrales_tramite` | `tipo_tramite_id`, `codigo`, `valor`, `orden`. Código único por trámite. Son referencias informativas; no decisiones de elegibilidad. |
| `facultad_tipo_tramite` | Par único `facultad_id`, `tipo_tramite_id`, con fechas de creación/actualización. Se consulta desde el modelo de G4; no requiere modificar `Facultad`. |
| `palabras_clave` | `tipo_tramite_id`, `expresion`. Expresión única por trámite. |

Las últimas dos tablas están preparadas para uso futuro. Sus registros no filtran
el catálogo, no cambian permisos y no intervienen en el reconocimiento de texto.
Inicialmente no tienen asociaciones. `situaciones` está fuera del alcance.

Los modelos de G4 son `TipoTramite`, `CategoriaTramite`, `RequisitoTramite`,
`CampoTramite`, `UmbralTramite` y `PalabraClave`. `TipoTramite` ofrece las relaciones
`categoria`, `requisitos`, `campos`, `umbrales`, `documentos`, `facultades` y
`palabrasClave`. Requisitos, campos y umbrales se ordenan por `orden` y, ante empate,
por `id`. Los documentos conservan el orden por `id` existente.

## Edición de campos y requisitos

Las vistas de G1 deberán guardar mediante una transacción y validar sus entradas.
Un campo general se guarda con `ambito = general`; los campos repetidos por materia,
con `ambito = materia`. Se mantienen estos tipos existentes, como cadenas:
`texto`, `texto_largo`, `numero`, `fecha`, `hora` y `lista`.

`configuracion` contiene únicamente los atributos adicionales que necesite el
campo: `min`, `max`, `entero`, `step`, `max_length`, `ayuda`, `opciones` y
`mostrar_si`. Para un campo sin configuración adicional guardar `{}` o `[]`.
Los atributos principales pertenecen a sus columnas, no deben duplicarse en el JSON.

Ejemplo de la condición que ya utiliza recuperación:

```json
{
  "mostrar_si": {"campo": "historial_recuperacion", "valor": "con_fecha"}
}
```

Las opciones de listas conservan el mapa de valor y etiqueta, por ejemplo
`{"sí":"Sí","no":"No"}`. Al editar o eliminar un campo, G1 debe considerar las
condiciones que lo referencian. Cambiar un nombre puede afectar formularios o datos
de solicitudes anteriores; es preferible conservar los nombres existentes.

Los campos del cuestionario opcional del chatbot siguen limitados al GPA general y
la fecha de última recuperación. Agregar un campo del formulario no añade una
comprobación académica al motor. Agregar tipos nuevos o trámites completos requiere
otro cambio: se conserva el catálogo actual de 20 trámites.

Los documentos continúan en `documentos_requeridos`, con sus obligaciones existentes
`obligatorio`, `opcional` o `condicional`.

## Publicación y compatibilidad

G1 puede editar los registros del borrador. Los lectores continúan usando
`CatalogoTramites::publicado()` / `buscar()` y `ChatbotCatalogoService`; mantienen
el formato v3 que usan los otros módulos. No deben leer las relaciones del borrador
para mostrar contenido público antes de su publicación.

```bash
php artisan kb:exportar
php artisan kb:exportar --publicar
```

El primer comando genera y valida una candidata con SWI-Prolog. El segundo vuelve
a validarla, solicita la recarga y activa la revisión solo tras confirmación del
motor. Un fallo conserva la revisión anterior. No publicar automáticamente por
cada edición ni ejecutar la exportación por cada visita.

Cada revisión guarda una instantánea de los trámites **y categorías**. Editar una
categoría también cambia la versión de la candidata. Las revisiones anteriores,
que no tienen categorías, se leen con las seis definiciones originales.

Los parámetros actuales son `gpa_general_minimo` (número entre 0 y 100) e
`intervalo_recuperacion_dias` (entero positivo), ambos de `examen_recuperacion`.
La validación admite editar estas referencias; sus valores iniciales siguen siendo
75 y 365. Los textos generales y los requisitos son contenido editorial separado:
si mencionan una referencia modificada, deben actualizarse en el mismo borrador.
Las preguntas sugeridas y el mensaje de captura de referencias del chatbot utilizan
los valores de la revisión publicada.

## Instalación y actualización de una BD existente

Antes de aplicar cambios de esquema, avisar al responsable del entorno y obtener
un respaldo completo de la BD mediante su herramienta habitual. No usar
`migrate:fresh`. Revisar primero las migraciones pendientes con
`php artisan migrate:status`; la nueva migración de G4 es
`2026_10_28_000001_normalizar_catalogo_g4.php`.

1. Coordinar una ventana sin edición del catálogo. Aplicar la nueva migración con
   `php artisan migrate --path=database/migrations/2026_10_28_000001_normalizar_catalogo_g4.php`.
2. La migración transforma el **borrador actual de cada trámite en BD**, incluidas
   sus ediciones. Conserva el contenido original y las revisiones publicadas. Si
   encuentra un borrador incompleto, falla en lugar de sustituirlo por datos iniciales.
3. En instalaciones nuevas o sin catálogo, ejecutar
   `php artisan db:seed --class=CatalogoInformativoSeeder` tras las migraciones
   habituales. El seeder solo importa trámites nuevos; repetirlo no sobrescribe
   registros ni restaura requisitos, campos, umbrales o documentos eliminados.
4. Comprobar el borrador y generar una candidata. Publicar de forma explícita
   cuando Laravel y Prolog estén preparados; la migración no recarga el motor.

El archivo `database/catalogo/inicial.json` continúa siendo la carga inicial y el
respaldo de arranque cuando no hay revisión publicada. Editarlo no actualiza los
trámites existentes. `database/catalogo/categorias.json` conserva las seis categorías
originales para instalación y lectura de revisiones antiguas.

## Recuperación

Preferir restaurar el respaldo completo si se necesita recuperar todos los datos.
La reversión de la migración recompone `tipos_tramite.contenido` con las ediciones
de requisitos, campos y umbrales antes de quitar las tablas nuevas. No elimina
solicitudes, documentos ni revisiones publicadas.

Las asociaciones de facultades, palabras clave y los metadatos editados de categorías
no tienen un equivalente completo en el esquema anterior y se pierden al quitar
sus tablas: exportarlos o conservar el respaldo antes de revertir. Coordinar también
el retorno a la versión anterior del código. No ejecutar un rollback global que
revierta migraciones de otros grupos; identificar y revertir únicamente la migración
de G4 con `--path` y el lote correspondiente, primero en una copia de la BD.

Las pruebas de G4 usan SQLite en memoria y un directorio temporal de publicación.
No migran la BD local ni recargan el motor local.
