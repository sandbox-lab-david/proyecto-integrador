# Cómo agregar una pantalla o un módulo · Grupo 1

Esta es la receta completa para que una pantalla nueva quede **dentro del marco común** y **protegida por el login y los roles**. Primero ubiquen su caso:

| Caso | Ejemplo | Pasos |
|---|---|---|
| **A.** Una página más dentro de su módulo | Detalle de un caso, formulario de una resolución | 1, 2, 3 y 5 |
| **B.** La página de **inicio** de un rol | Bandeja del director, agendas para consulta | 1 a 5 |
| **C.** Un rol que no existe en la tabla `roles` | (poco probable) | Pedir al Grupo 1 el rol y su usuario de prueba; luego 1 a 5 |

## 1 · Ruta protegida y con nombre

En su archivo de rutas, dentro de un grupo con el permiso que corresponde. El nombre (`->name()`) es obligatorio: el menú y el inicio por rol usan el nombre, no la dirección.

```php
Route::middleware(['auth', 'rol:director'])->group(function () {
    Route::get('/revision/director', [RevisionController::class, 'bandejaDirector'])->name('revision.director');
});

Route::middleware(['auth', 'rol:consulta,asistente'])->group(function () {
    Route::get('/consejo/agendas', [AgendaController::class, 'index'])->name('consejo.agendas');
});
```

## 2 · Vista con el marco común

```blade
<x-layouts.app titulo="Casos por aprobar" modulo="Revisión y Consejo" :menu="config('menu_revision')">
    <p class="ui-antetitulo">Director de carrera</p>
    <h1 class="ui-titulo">Casos por aprobar</h1>
    ...
</x-layouts.app>
```

Detalles en [Marco común de las páginas](marco-comun.md).

## 3 · Enlace en su menú (si debe aparecer en la columna lateral)

En su archivo `config/menu_<modulo>.php`, con `roles` para que cada perfil vea solo lo suyo. Un solo menú sirve para varios perfiles:

```php
'Revisión' => [
    ['texto' => 'Bandeja de la asistente', 'ruta' => 'revision.asistente', 'roles' => ['asistente']],
    ['texto' => 'Casos por aprobar', 'ruta' => 'revision.director', 'roles' => ['director']],
],
'Consejo' => [
    ['texto' => 'Agendas', 'ruta' => 'consejo.agendas', 'activo' => 'consejo.*', 'roles' => ['asistente', 'consulta']],
],
```

## 4 · Página de inicio del rol (solo caso B)

Al iniciar sesión, `/dashboard` manda a cada persona a su página según su rol. Esa lista vive en `app/Http/Controllers/Acceso/PanelController.php` (Grupo 1). Abran un *issue* al Grupo 1 indicando **el rol y el nombre de la ruta**, por ejemplo:

> "Rol `director` → `revision.director`" · "Rol `consulta` → `consejo.agendas`"

El Grupo 1 agrega una línea a la lista `DESTINOS`:

```php
['roles' => ['director'], 'ruta' => 'revision.director'],
['roles' => ['consulta'], 'ruta' => 'consejo.agendas'],
```

El orden importa: gana la primera línea que coincide. Si una persona tiene dos roles, llega al de más arriba.

## 5 · Prueben con los usuarios de prueba

| Prueba | Resultado esperado |
|---|---|
| Entrar sin sesión a la ruta | Manda a `/login` |
| Entrar con un usuario **sin** el rol | Página 403 "No tienes permiso" |
| Entrar con el usuario **con** el rol | La página abre, con el nombre de su módulo en la barra y el enlace resaltado en el menú |
| (Caso B) Iniciar sesión con ese usuario | Llega directo a su página |

Usuarios: ver la tabla de [Acceso, registro y roles](acceso-y-roles.md#usuarios-de-prueba).

## Qué NO hacer

- No copiar el marco ni crear una barra propia.
- No revisar roles a mano en la vista (`if ($usuario->rol == ...)`): usen `rol:` en la ruta y `roles` en el menú.
- No editar `PanelController`, `menu_administracion.php` ni los componentes del marco: se piden al Grupo 1 (regla 4 del Excel).
