# Contratos del Grupo 1 (C01–C41)

Qué publica el Grupo 1 para los demás grupos, según la hoja **Contratos compartidos** del Excel de distribución. **Regla 3 de la hoja Guía:** si un dato es del Grupo 1, se lee con estos contratos; no escriban consultas propias a nuestras tablas.

Cómo usar un servicio en cualquier controlador o vista:

```php
use App\Services\Academico\CatalogoAcademicoService;

$periodo = app(CatalogoAcademicoService::class)->periodoActual();
```

O recibiéndolo en el método del controlador:

```php
public function create(CatalogoAcademicoService $catalogo)
{
    $periodos = $catalogo->periodos();
}
```

En el código, cada método tiene una marca `// C01`, `// C02`… para encontrarlo rápido.

## Índice

| Grupo | Contratos | Archivo |
|---|---|---|
| Catálogo académico | C01–C10, C31 | `app/Services/Academico/CatalogoAcademicoService.php` |
| Estudiante | C11–C14 | `app/Services/Academico/EstudianteService.php` |
| Permisos | C15–C20 | `app/Services/Academico/AutorizacionService.php` |
| Middleware | C21, C22 | `app/Http/Middleware/VerificarRol.php`, `SoloEstudiante.php` |
| API | C32–C35 | `routes/api.php` · ver [grupo-1-api.md](grupo-1-api.md) |
| Componentes | C37, C38, C39 | `resources/views/components/` |
| Enums | C40 | `app/Enums/` |
| Seeders | C41 | `database/seeders/` |

---

## Catálogo académico · `CatalogoAcademicoService`

| ID | Método | Devuelve | Notas |
|---|---|---|---|
| C01 | `facultades(bool $soloActivas = true)` | `Collection<Facultad>` | |
| C02 | `carrerasPorFacultad(?int $facultadId = null, bool $soloActivas = true)` | `Collection<Carrera>` | Ordenadas por nombre. Sin `$facultadId` devuelve todas |
| C03 | `modalidadesDeCarrera(int $carreraId)` | `Collection<Modalidad>` | Modalidades en que se ofrece la carrera |
| C04 | `mallasDeCarrera(int $carreraId)` | `Collection<Malla>` | La vigente primero |
| C05 | `materiasDeMalla(int $mallaId, ?string $buscar = null)` | `Collection<Materia>` | Solo las activas, ordenadas por nivel. Cada materia trae `->nivel` |
| C06 | `periodos(bool $soloActivos = false)` | `Collection<Periodo>` | El más reciente primero |
| C07 | `periodoActual()` | `?Periodo` | `null` si no hay periodo activo |
| C08 | `semanaActual(?Carbon $fecha = null)` | `?int` | Semana del periodo activo (1, 2, 3…). `null` si la fecha está fuera del periodo |
| C09 | `cursosDeMateria(int $materiaId, int $periodoId)` | `Collection<Curso>` | Con `->profesor` cargado |
| C10 | `docentes(bool $soloActivos = true)` | `Collection<Trabajador>` | Solo quienes tienen el rol `docente`. Uso interno del G1 |
| C31 | `carrerasSinTercerRegistro()` | `Collection<Carrera>` | Medicina y Enfermería en los datos de prueba. La usa el G4 para Prolog |

```php
$catalogo = app(CatalogoAcademicoService::class);

$materias = $catalogo->materiasDeMalla($mallaId, 'progra');
$semana = $catalogo->semanaActual();
```

## Estudiante · `EstudianteService`

| ID | Método | Devuelve | Notas |
|---|---|---|---|
| C11 | `estudianteActual()` | `?Estudiante` | El estudiante de la sesión. `null` si quien entró no es estudiante. **No lo busquen con consultas propias** |
| C12 | `perfil(int $estudianteId)` | `array` | Datos personales y carreras activas. **No trae GPA**: lo declara el estudiante (Excel, cambio #2) |
| C13 | `carrerasActivas(int $estudianteId)` | `Collection<CarreraEstudiante>` | Inscripciones con estado `activo`, con carrera, modalidad y malla cargadas |
| C14 | `actualizarContacto(Estudiante $e, string $celular)` | `void` | El estudiante actualiza su celular |

### C12 · `perfil`: respuesta real

```php
$servicio = app(EstudianteService::class);
$perfil = $servicio->perfil($servicio->estudianteActual()->id);
```

```json
{
  "id": 1,
  "codigo": "2026000001",
  "cedula": null,
  "nombres": "Estudiante",
  "apellidos": "de Prueba",
  "nombre_completo": "Estudiante de Prueba",
  "correo": "estudiante@uees.edu.ec",
  "celular": null,
  "carreras": [
    {
      "carrera_estudiante_id": 1,
      "estado": "activo",
      "fecha_inscripcion": "2025-05-05",
      "facultad": { "id": 1, "codigo": "ING", "nombre": "Ingeniería" },
      "carrera": { "id": 2, "codigo": "COMP", "nombre": "Ingeniería en Computación", "nivel": "grado", "admite_tercer_registro": true },
      "modalidad": { "id": 1, "nombre": "Presencial" },
      "malla": { "id": 1, "version": "2024", "vigente": true }
    }
  ]
}
```

- `carrera_estudiante_id` es el que va en `solicitudes.carrera_estudiante_id`.
- `malla.id` es el que se le pasa a C05 o C34 para listar las materias.
- Un estudiante puede tener **más de una** carrera activa: recorran `carreras`.

## Permisos · `AutorizacionService`

| ID | Método | Devuelve | Notas |
|---|---|---|---|
| C15 | `tieneRol(User $u, string\|array $codigos)` | `bool` | `false` si la cuenta o el trabajador están desactivados |
| C16 | `facultadesDe(Trabajador $t)` | `Collection<Facultad>` | Facultades asignadas (alcance de la asistente) |
| C17 | `carrerasQueDirige(Trabajador\|int $t)` | `Collection<Carrera>` | Solo las que dirige **hoy** |
| C18 | `directorDeCarrera(int $carreraId)` | `?Trabajador` | El director **vigente hoy**. `null` si no hay |
| C19 | `asistentesDeFacultad(int $facultadId)` | `Collection<Trabajador>` | Asistentes activas de esa facultad |
| C20 | `puedeVerCarrera(User $u, int $carreraId)` | `bool` | Admin, o director vigente de esa carrera |
| C20 | `puedeVerFacultad(User $u, int $facultadId)` | `bool` | Admin, o trabajador asignado a esa facultad |

"Vigente hoy" quiere decir: empezó en o antes de hoy y no ha terminado, o termina hoy o después. Se usa la hora de Ecuador. C17, C18 y C20 aplican la misma regla.

Ejemplo dentro de una *policy*:

```php
public function view(User $usuario, Solicitud $solicitud): bool
{
    $carrera = $solicitud->carreraEstudiante->carreraModalidad->carrera;

    return $this->autorizacion->puedeVerCarrera($usuario, $carrera->id)
        || $this->autorizacion->puedeVerFacultad($usuario, $carrera->facultad_id);
}
```

## Middleware

| ID | Uso | Qué hace |
|---|---|---|
| C21 | `->middleware(['auth', 'rol:asistente,director'])` | Deja pasar si el usuario tiene **alguno** de los roles. Si no, muestra el error 403 en español |
| C22 | `->middleware(['auth', 'estudiante'])` | Solo usuarios de tipo `estudiante` |

Roles válidos: `admin`, `asistente`, `director`, `docente`, `consulta`. Más detalle en la sección "Acceso, registro y roles" del README.

## Componentes

### C37 · Marco común `<x-layouts.app>`

```blade
<x-layouts.app titulo="Bandeja" modulo="Revisión y Consejo" :menu="config('menu_revision')">
    ...
</x-layouts.app>
```

Incluye la barra superior con la campana de notificaciones del G2 (C54) y el menú lateral. Guía completa en el README.

### C38 · Selects compartidos

| Componente | Atributos | Notas |
|---|---|---|
| `<x-select-facultad>` | `name` (`facultad_id`), `selected`, `solo-activas` (true), `vacio` | |
| `<x-select-carrera>` | `name` (`carrera_id`), `selected`, `facultad-id`, `depende-de`, `vacio` | Con `depende-de="facultad_id"` se recarga solo cuando cambia ese select (usa C32) |
| `<x-select-periodo>` | `name` (`periodo_id`), `selected`, `solo-activos` (false), `vacio` | Si no le pasan `selected`, marca el periodo activo |

Ejemplo de facultad y carrera dependientes:

```blade
<x-select-facultad :selected="old('facultad_id')" />
<x-select-carrera depende-de="facultad_id" :facultad-id="old('facultad_id')" :selected="old('carrera_id')" />
<x-select-periodo />
```

Todos usan la clase `ui-control` y aceptan atributos extra (`required`, `class`, `id`).

### C39 · Tabla, alerta y modal de confirmación

```blade
<x-alerta tipo="aviso" titulo="Falta el periodo">Crea un periodo antes de continuar.</x-alerta>

<x-tabla :columnas="['Código', 'Nombre']" :paginacion="$materias" vacio="No hay materias.">
    @foreach ($materias as $materia)
        <tr><td>{{ $materia->codigo }}</td><td>{{ $materia->nombre }}</td></tr>
    @endforeach
</x-tabla>

<x-modal-confirmar id="borrar-{{ $item->id }}" :accion="route('…', $item)" metodo="DELETE" boton="Borrar" titulo="¿Borrar este registro?">
    Esta acción no se puede deshacer.
</x-modal-confirmar>
```

- `<x-alerta>`: `tipo` puede ser `exito`, `error`, `aviso` o `info`.
- `<x-tabla>`: muestra el texto de `vacio` si no hay filas, y la paginación si recibe un paginador.

## C40 · Enums

| Enum | Valores | Columna |
|---|---|---|
| `App\Enums\NivelCarrera` | `grado`, `posgrado` | `carreras.nivel` |
| `App\Enums\EstadoInscripcion` | `activo`, `inactivo`, `egresado`, `retirado` | `carrera_estudiante.estado` |

```php
use App\Enums\EstadoInscripcion;

$activas = CarreraEstudiante::where('estado', EstadoInscripcion::ACTIVO->value);
```

Cada enum tiene `->etiqueta()` (texto para mostrar) y `::valores()` (lista para validar).

## C41 · Seeders

`php artisan migrate:fresh --seed` corre, en este orden: `RolSeeder`, `FacultadSeeder`, `CarreraSeeder`, `ModalidadSeeder`, `CategoriaMateriaSeeder`, `MallaSeeder`, `PeriodoSeeder`, `UsuarioDemoSeeder` y `CursoSeeder`.

| Qué crea | Cuántos |
|---|---|
| Facultades de grado de la UEES (Word §5.4) | 9 |
| Carreras de grado (Medicina y Enfermería sin tercer registro) | 38 |
| Modalidades | 6 |
| Mallas (Computación 2024 y Medicina 2023) | 2 |
| Materias | 14 |
| Periodos (ORD-1-2026 cerrado y ORD-2-2026 activo) | 2 |
| Paralelos en ORD-2-2026 | 7 |
| Usuarios de prueba (ver README) y 3 docentes sin cuenta | 5 + 3 |

## Factories (para sus pruebas)

Todos los modelos del G1 tienen factory. Las más útiles:

```php
Periodo::factory()->activo()->create();
Trabajador::factory()->conRol('director')->conAcceso()->create();
Malla::factory()->vigente()->create();
DirectorCarrera::factory()->create();
DirectorCarrera::factory()->terminado()->create();
CarreraEstudiante::factory()->create();
```

`CarreraEstudiante::factory()` crea también el estudiante, la carrera y la modalidad.
