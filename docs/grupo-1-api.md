# API interna del Grupo 1 (C32–C35)

Rutas en JSON para llenar listas desplegables con JavaScript (por ejemplo, cargar las carreras cuando cambia la facultad). Están en `routes/api.php`.

| | |
|---|---|
| Prefijo | `/api/interno` |
| Acceso | Cualquier usuario con **sesión iniciada** (middleware `web` + `auth`). No necesita token: usa la misma sesión del navegador. |
| Formato | Siempre pidan JSON con la cabecera `Accept: application/json`. |

> Si la página es Blade y solo necesitan una lista, es más simple usar los componentes `<x-select-facultad>`, `<x-select-carrera>` y `<x-select-periodo>` (C38). Ver [grupo-1-contratos.md](grupo-1-contratos.md#c38--selects-compartidos).

## Resumen

| Contrato | Método y ruta | Parámetros | Devuelve |
|---|---|---|---|
| C32 | `GET /api/interno/carreras` | `facultad_id` (obligatorio) | Carreras **activas** de esa facultad |
| C33 | `GET /api/interno/periodos` | — | Todos los periodos, el más reciente primero |
| C33 | `GET /api/interno/periodos/actual` | — | El periodo activo |
| C34 | `GET /api/interno/materias` | `malla_id` (obligatorio), `q` (opcional) | Materias **activas** de una malla, ordenadas por nivel |
| C35 | `GET /api/interno/cursos` | `materia_id` y `periodo_id` (obligatorios) | Paralelos de esa materia en ese periodo, con su docente |

Los ejemplos de abajo son **respuestas reales** con los datos de prueba (`php artisan migrate:fresh --seed`). Los `id` pueden cambiar en su base.

---

## C32 · Carreras de una facultad

```
GET /api/interno/carreras?facultad_id=1
```

| Parámetro | Obligatorio | Descripción |
|---|---|---|
| `facultad_id` | Sí | `id` de la facultad |

**Respuesta 200**

```json
[
  { "id": 3, "nombre": "Ingeniería Ambiental" },
  { "id": 1, "nombre": "Ingeniería Civil" },
  { "id": 5, "nombre": "Ingeniería Industrial" },
  { "id": 4, "nombre": "Ingeniería en Biodiversidad y Recursos Genéticos" },
  { "id": 2, "nombre": "Ingeniería en Computación" }
]
```

Ordenadas por nombre. Las carreras desactivadas no salen. Si la facultad no existe o no tiene carreras, devuelve `[]`.

## C33 · Periodos

```
GET /api/interno/periodos
```

**Respuesta 200**

```json
[
  {
    "id": 2,
    "codigo": "ORD-2-2026",
    "nombre": "Ordinario II 2026",
    "tipo": "ordinario",
    "fecha_inicio": "2026-09-07",
    "fecha_fin": "2026-12-18",
    "activo": true
  },
  {
    "id": 1,
    "codigo": "ORD-1-2026",
    "nombre": "Ordinario I 2026",
    "tipo": "ordinario",
    "fecha_inicio": "2026-05-04",
    "fecha_fin": "2026-08-28",
    "activo": false
  }
]
```

`tipo` puede ser `ordinario`, `extraordinario` o `intensivo`.

## C33 · Periodo activo

```
GET /api/interno/periodos/actual
```

**Respuesta 200**

```json
{
  "id": 2,
  "codigo": "ORD-2-2026",
  "nombre": "Ordinario II 2026",
  "tipo": "ordinario",
  "fecha_inicio": "2026-09-07",
  "fecha_fin": "2026-12-18",
  "semana_primer_parcial": 8
}
```

**Respuesta 404** si ningún periodo está marcado como activo:

```json
{ "message": "No hay un periodo activo." }
```

## C34 · Materias de una malla

```
GET /api/interno/materias?malla_id=1&q=progra
```

| Parámetro | Obligatorio | Descripción |
|---|---|---|
| `malla_id` | Sí | `id` de la malla. La del estudiante sale de C12 `perfil` (`carreras[].malla.id`) |
| `q` | No | Texto a buscar en el código o el nombre (máximo 100 caracteres). Sin `q` devuelve todas |

**Respuesta 200**

```json
[
  { "id": 2, "codigo": "COM101", "nombre": "Programación I" },
  { "id": 5, "codigo": "COM201", "nombre": "Programación II" }
]
```

Ordenadas por nivel y luego por nombre. Las materias desactivadas no salen.

## C35 · Paralelos de una materia en un periodo

```
GET /api/interno/cursos?materia_id=2&periodo_id=2
```

| Parámetro | Obligatorio | Descripción |
|---|---|---|
| `materia_id` | Sí | `id` de la materia (de C34) |
| `periodo_id` | Sí | `id` del periodo (de C33) |

**Respuesta 200**

```json
[
  {
    "id": 2,
    "paralelo": "A1",
    "cupo": 35,
    "profesor": { "id": 6, "nombres": "María", "apellidos": "Andrade" }
  },
  {
    "id": 3,
    "paralelo": "B1",
    "cupo": 35,
    "profesor": { "id": 6, "nombres": "María", "apellidos": "Andrade" }
  }
]
```

`cupo` y `profesor` pueden venir en `null`: un paralelo puede abrirse sin cupo ni docente asignado.

---

## Errores

| Código | Cuándo | Respuesta |
|---|---|---|
| 401 | No hay sesión iniciada | `{"message": "Unauthenticated."}` |
| 422 | Falta un parámetro obligatorio o no es un número | `{"message": "...", "errors": {"facultad_id": ["Indica la facultad (facultad_id)."]}}` |
| 404 | Solo en `/periodos/actual`, si no hay periodo activo | `{"message": "No hay un periodo activo."}` |

Ejemplo de 422:

```
GET /api/interno/carreras
```

```json
{
  "message": "Indica la facultad (facultad_id).",
  "errors": { "facultad_id": ["Indica la facultad (facultad_id)."] }
}
```

## Ejemplo en JavaScript

```js
const respuesta = await fetch(`/api/interno/carreras?facultad_id=${facultadId}`, {
    headers: { Accept: 'application/json' },
    credentials: 'same-origin',
});

if (respuesta.ok) {
    const carreras = await respuesta.json();
}
```

`credentials: 'same-origin'` hace que se envíe la cookie de la sesión. Sin eso, la respuesta será 401.

## Pruebas

`tests/Feature/ContratosAcademicosTest.php` y `tests/Feature/ContratosPendientesTest.php`.
