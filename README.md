# Proyecto-integrador

## Contenido

- [Instalación](#instalación) — aporte del Grupo 1, para todos
- [Chatbot (Módulo 4): servidor Prolog](#chatbot-módulo-4-servidor-prolog) — Grupo 4
- [Marco común de las páginas (layout)](#marco-común-de-las-páginas-layout--grupo-1) — Grupo 1
- [Acceso, registro y roles](#acceso-registro-y-roles--grupo-1) — Grupo 1
- [Cómo agregar una pantalla o un módulo](#cómo-agregar-una-pantalla-o-un-módulo--grupo-1) — Grupo 1
- [Módulo 1: Administración académica](#módulo-1-administración-académica--grupo-1) — Grupo 1

## Instalación

> Aporte del Grupo 1. Cada módulo explica abajo lo que necesita aparte (por ejemplo, el servidor Prolog del Módulo 4).

### Requisitos

| Programa | Versión | Nota |
|---|---|---|
| PHP | 8.2 o superior | Con las extensiones `pdo_pgsql`, `mbstring`, `xml`, `zip` y **`gd`** (las dos últimas las pide Laravel Excel) |
| Composer | 2 | |
| Node.js | 20.19 o superior (o 22.12+) | Lo pide Vite 7 |
| PostgreSQL | 14 o superior | Las pruebas usan SQLite en memoria; no necesitan PostgreSQL |

**XAMPP en Windows:** `gd` y `zip` vienen apagadas. Abran `C:\xampp\php\php.ini`, quiten el `;` de `;extension=gd` y de `;extension=zip`, guarden y abran una terminal nueva. Comprueben con:

```bash
php -m | findstr /i "gd zip"
```

### Pasos

```bash
git clone https://github.com/sandbox-lab-david/proyecto-integrador.git
cd proyecto-integrador
git checkout develop

composer install
npm install

cp .env.example .env          # en Windows (cmd): copy .env.example .env
php artisan key:generate
```

En `.env`, pongan los datos de su PostgreSQL (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) y creen esa base vacía. Después:

```bash
php artisan migrate:fresh --seed   # crea las tablas y los datos de prueba (borra lo que haya)
npm run build
php artisan serve
```

Abran <http://localhost:8000> y entren con `admin@uees.edu.ec` / `password123`. Los demás usuarios de prueba están en [Acceso, registro y roles](#acceso-registro-y-roles--grupo-1).

### Para trabajar en el día a día

```bash
composer run dev
```

Levanta a la vez el servidor, **la cola** (`queue:listen`), los registros y Vite. Si prefieren terminales separadas: `php artisan serve`, `php artisan queue:work` y `npm run dev`.

- **La cola** hace falta para importar archivos grandes del SIAC (más de 500 filas) y para algunas notificaciones.
- **Correos:** con `MAIL_MAILER=log` (lo que trae `.env.example`) los correos no se envían: se guardan en `storage/logs/laravel.log`, incluidos los enlaces para crear contraseña. Para enviarlos de verdad, configuren `MAIL_MAILER=smtp` y los datos de su servidor de correo (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`).

### Pruebas

```bash
php artisan test
```

Dos pruebas del catálogo del Módulo 4 necesitan SWI-Prolog instalado; sin él, fallan solo esas dos.

### Problemas comunes

| Error | Solución |
|---|---|
| `requires ext-gd` o `ext-zip` al correr Composer | Activen las extensiones (ver "XAMPP en Windows" arriba) |
| `Failed to open stream: Permission denied` en `vendor/` (Windows) | Cierren `php artisan serve`, `npm run dev`, la cola y Apache. Abran la terminal como administrador y corran `composer clear-cache` y `composer install`. Si sigue fallando, antes de `composer install` pongan `$env:COMPOSER_MAX_PARALLEL_HTTP=1` (PowerShell) o `set COMPOSER_MAX_PARALLEL_HTTP=1` (cmd) |
| `Vite manifest not found` | `npm run build` |
| La página muestra un error después de traer cambios | `php artisan optimize:clear` y, si hubo migraciones nuevas, `php artisan migrate` |
| Una importación grande se queda en "Procesando" | Falta la cola: `php artisan queue:work` |

## Chatbot (Módulo 4): servidor Prolog

El chatbot consulta un servidor SWI-Prolog aparte (`prolog/kb/servidor.pl`).
Requiere [SWI-Prolog](https://www.swi-prolog.org/download/stable) 9 o superior.

El servidor lee sus variables del sistema, no del `.env` de Laravel. Por
defecto arranca en modo producción y **no inicia sin `PROLOG_API_KEY`**
(mínimo 16 caracteres).

### Arrancar en local (sin clave)

Git Bash:

```bash
PROLOG_ENTORNO=desarrollo swipl prolog/kb/servidor.pl
```

PowerShell:

```powershell
$env:PROLOG_ENTORNO = "desarrollo"; swipl prolog/kb/servidor.pl
```

Queda escuchando en `http://127.0.0.1:8081`. En `.env` deja `PROLOG_API_KEY=` vacío.

### Arrancar con clave

Usa la misma clave en el servidor y en el `.env` de Laravel (`PROLOG_API_KEY`):

```bash
PROLOG_API_KEY=una-clave-de-16-o-mas swipl prolog/kb/servidor.pl
```

Otras variables opcionales (puerto, hilos, límites) están descritas al inicio
de `prolog/kb/servidor.pl`.

### Pruebas

Reglas y base de conocimiento (no necesitan el servidor):

```bash
cd prolog/pruebas
swipl -g "consult('reglas_chatbot.pl'), (run_tests -> halt(0) ; halt(1))"
swipl -g "consult('integridad_conocimiento.pl'), (run_tests -> halt(0) ; halt(1))"
```

Servidor HTTP (con el servidor corriendo; en Windows usar Git Bash):

```bash
bash prolog/pruebas/probar.sh
```

Laravel:

```bash
php artisan test tests/Feature/Chatbot
```

## Marco común de las páginas (layout) · Grupo 1

Todas las páginas del sistema comparten **el mismo marco**: la barra superior con el logo de la UEES y el menú lateral. **El diseño es uno solo y lo mantiene el Grupo 1** (contrato C37 del Excel de distribución). **El contenido lo pone cada grupo**: el nombre de su módulo y sus enlaces.

```
┌───────────────────────────────────────────────┐
│ [UEES]  {{ modulo }}               {{ usuario }}│  ← lo pinta el marco
├─────────────┬─────────────────────────────────┤
│ {{ menu }}  │  mensajes de éxito / error      │  ← lo pinta el marco
│             │  ───────────────────────────    │
│             │  CONTENIDO DE TU PÁGINA         │  ← lo pone tu grupo
└─────────────┴─────────────────────────────────┘
```

| Pieza | Archivo |
|---|---|
| Marco completo `<x-layouts.app>` | `resources/views/components/layouts/app.blade.php` |
| Barra superior | `resources/views/components/layouts/barra-superior.blade.php` |
| Menú lateral | `resources/views/components/layouts/menu-lateral.blade.php` |
| Menú del Grupo 1 (ejemplo) | `config/menu_administracion.php` |

**Regla:** el marco **se usa, no se copia ni se edita** (regla 6 de la hoja Guía). Si necesitan algo que el marco no hace, se lo piden al Grupo 1 con un *issue* en GitHub (regla 4).

### Paso 1 · Definan su menú en un archivo propio

Cada grupo crea **su propio archivo** dentro de `config/`. Así nadie toca el archivo de otro y no hay conflictos al juntar ramas.

| Grupo | Archivo | Se usa con |
|---|---|---|
| G1 | `config/menu_administracion.php` (ya existe) | `config('menu_administracion')` |
| G2 | `config/menu_estudiante.php` | `config('menu_estudiante')` |
| G3 | `config/menu_revision.php` | `config('menu_revision')` |
| G4 | `config/menu_chatbot.php` (si usan el marco) | `config('menu_chatbot')` |

Formato: secciones, y dentro de cada sección sus enlaces. Ejemplo para el Grupo 2:

```php
<?php

return [
    'Trámites' => [
        ['texto' => 'Catálogo de trámites', 'ruta' => 'estudiante.catalogo', 'activo' => ['estudiante.catalogo', 'estudiante.tramites*']],
    ],
    'Ayuda' => [
        ['texto' => 'Asistente virtual', 'ruta' => 'chatbot.index', 'activo' => 'chatbot*'],
    ],
];
```

| Campo | Qué es | Ejemplo |
|---|---|---|
| `texto` | Lo que se lee en el menú | `'Catálogo de trámites'` |
| `ruta` | El **nombre** de la ruta (su `->name()`), no la dirección | `'estudiante.catalogo'` |
| `activo` *(opcional)* | Qué rutas dejan el enlace resaltado. `*` = "todo lo que empiece así". Puede ser una lista. Si no se pone, se usa `ruta` | `'cargas.*'` o `['estudiante.catalogo', 'estudiante.tramites*']` |
| `roles` *(opcional)* | Quién puede ver el enlace. Si no se pone, lo ve todo el que entre al módulo | `['asistente', 'director']` |

Lo que el menú hace solo:
- **Resalta** el enlace de la página actual.
- **Esconde** los enlaces con `roles` a quien no tiene ese rol. Lo revisa con `AutorizacionService::tieneRol()` (C15).
- **Salta** los enlaces cuya ruta todavía no existe, en vez de romper la página. Así pueden escribir su menú completo aunque les falten rutas.
- Si el menú queda vacío, no muestra la columna lateral.

Ejemplo para el Grupo 3, que atiende a varios perfiles (las rutas que aún no existen se saltan solas):

```php
<?php

return [
    'Revisión' => [
        ['texto' => 'Bandeja de la asistente', 'ruta' => 'revision.asistente', 'activo' => 'revision.*', 'roles' => ['asistente']],
        ['texto' => 'Casos por aprobar', 'ruta' => 'revision.director', 'roles' => ['director']],
    ],
    'Consejo' => [
        ['texto' => 'Agendas', 'ruta' => 'consejo.agendas', 'activo' => 'consejo.agendas*', 'roles' => ['asistente', 'consulta']],
        ['texto' => 'Resoluciones', 'ruta' => 'consejo.resoluciones', 'activo' => 'consejo.resoluciones*', 'roles' => ['asistente', 'consulta']],
    ],
];
```

Roles válidos (tabla `roles`): `admin`, `asistente`, `director`, `docente`, `consulta`.

### Paso 2 · Envuelvan sus vistas con el marco

Quiten de sus vistas el `<!DOCTYPE html>`, el `<head>`, el `<body>` y cualquier barra propia, y dejen **solo el contenido**, envuelto así:

```blade
<x-layouts.app titulo="Mis solicitudes" modulo="Portal del estudiante" :menu="config('menu_estudiante')">

    <p class="ui-antetitulo">Trámites</p>
    <h1 class="ui-titulo">Mis solicitudes</h1>
    ...

</x-layouts.app>
```

| Dato | Qué es |
|---|---|
| `titulo` *(opcional)* | Sale en la pestaña del navegador: `Mis solicitudes · Portal del estudiante · UEES` |
| `modulo` | El nombre que sale en la barra superior |
| `:menu` *(opcional)* | Su menú del paso 1. Lleva **dos puntos** delante porque es un dato, no un texto |

Las páginas del Grupo 1 en `resources/views/administracion/` son ejemplos que ya funcionan.

**El marco ya trae (no lo repitan):**
- Logo, nombre de la persona que inició sesión y botón de cerrar sesión (o "Iniciar sesión" si no hay sesión).
- Los estilos y scripts (`@vite`).
- Los mensajes: si su controlador hace `->with('exito', '…')` o `->with('error', '…')`, el mensaje aparece arriba solo. También el aviso general de errores de validación.
- La adaptación al celular: el menú pasa arriba de la página.

**Vistas antiguas:** las que todavía usan `@extends('layouts.app')` o `<x-app-layout>` siguen funcionando, pero muestran el menú del Grupo 1. `resources/views/layouts/app.blade.php` quedó como puente para no romperlas. Las vistas nuevas deben usar `<x-layouts.app>`.

### Paso 3 · Usen las clases de estilo comunes

Usen las clases `ui-` de `resources/css/app.css` (guía completa en `docs/estilos-generales.md`) en vez de estilos propios:

| Para… | Clases |
|---|---|
| Títulos | `ui-antetitulo`, `ui-titulo`, `ui-subtitulo` |
| Botones | `ui-btn ui-btn-primario`, `ui-btn ui-btn-secundario` |
| Formularios | `ui-form`, `ui-campo`, `ui-control`, `ui-error-campo` |
| Tablas | `ui-tabla-caja` y, dentro, `ui-tabla` |
| Tarjetas | `ui-tarjetas` y, dentro, `ui-tarjeta` |
| Estados | `ui-badge ui-badge-exito`, `ui-badge-alerta`, `ui-badge-peligro` |
| Avisos | `ui-alerta ui-alerta-exito`, `ui-alerta-error` |
| Lista vacía | `ui-vacio` |

### Paso 4 · Protejan sus rutas (obligatorio)

**Esconder un enlace del menú no protege la página**: cualquiera puede escribir la dirección en el navegador. Protejan sus rutas con los permisos del Grupo 1:

```php
// Solo estudiantes con sesión iniciada (C22)
Route::middleware(['auth', 'estudiante'])->group(function () {
    // rutas del portal
});

// Solo asistentes y directores (C21)
Route::middleware(['auth', 'rol:asistente,director'])->group(function () {
    // bandejas
});
```

### Paso 5 · Prueben antes de subir

- [ ] La página muestra la barra con el **nombre de su módulo**, no "Administración académica".
- [ ] El menú muestra **sus** enlaces y resalta el de la página actual.
- [ ] Con un usuario de otro perfil, los enlaces con `roles` no aparecen y la ruta responde 403.
- [ ] Sin iniciar sesión, la página manda al login.
- [ ] Se ve bien en el celular (390 px de ancho, sin desplazamiento horizontal).
- [ ] No queda ningún `<!DOCTYPE html>`, `<head>` ni barra propia en sus vistas.

Pruebas automáticas del marco:

```bash
php artisan test tests/Feature/LayoutComunTest.php
```

## Acceso, registro y roles · Grupo 1

El inicio de sesión del proyecto usa **Laravel Breeze**: sus controladores están en `app/Http/Controllers/Auth/` y sus rutas en `routes/auth.php`. **Fortify ya no se usa** y se quitó del proyecto.

### Después de traer estos cambios

Cada grupo debe correr lo de la sección [Instalación](#instalación): como mínimo `composer install`, `php artisan migrate:fresh --seed` y `npm run build`.

### Usuarios de prueba

`php artisan migrate:fresh --seed` crea estos usuarios. Todos tienen la clave `password123`.

| Correo | Tipo | Rol | Al iniciar sesión llega a |
|---|---|---|---|
| `admin@uees.edu.ec` | trabajador | `admin` | `/administracion` (G1) |
| `asistente@uees.edu.ec` | trabajador | `asistente` | `/revision/asistente` (G3) |
| `director@uees.edu.ec` | trabajador | `director` | `/revision/asistente` (G3) |
| `consulta@uees.edu.ec` | trabajador | `consulta` | Página "Tu cuenta aún no tiene un módulo asignado" |
| `estudiante@uees.edu.ec` | estudiante | — | `/estudiante/tramites` (G2) |

### Cómo funciona

```
/  ──► sin sesión: /login      con sesión: /dashboard
/dashboard ──► revisa quién es y lo manda a su módulo (tabla de arriba)
```

- **No hay registro público.** Las cuentas se crean así (RF-1.6):
  - **Estudiantes:** al importarlos del SIAC (Administración académica → Importaciones SIAC). La primera vez, el estudiante pulsa **"¿Primera vez? … Crea tu contraseña"** en el login, escribe su correo y recibe un enlace.
  - **Personal:** el administrador lo registra en **Personal** con "Puede entrar al sistema", y le llega el enlace para crear su contraseña.
- **Cuentas desactivadas:** no pueden iniciar sesión y ven el aviso "Tu cuenta está desactivada".
- **Roles:** un usuario `trabajador` tiene su registro en `trabajadores` y uno o más roles en `rol_trabajador`. Se consultan con `AutorizacionService::tieneRol($usuario, 'asistente')` (C15). Roles válidos: `admin`, `asistente`, `director`, `docente`, `consulta`.
- **A dónde llega cada rol** se decide en `app/Http/Controllers/Acceso/PanelController.php`. Si su grupo publica una nueva página de inicio para un rol, pidan al Grupo 1 que la agregue ahí.
- **Sin permiso:** cualquier `abort(403)` o ruta con `rol:` muestra `resources/views/errors/403.blade.php`, en español y con el marco común.

### Cómo proteger sus rutas

```php
// Solo estudiantes con sesión iniciada (C22)
Route::middleware(['auth', 'estudiante'])->group(function () {
    // ...
});

// Solo ciertos roles (C21). Varios roles se separan con comas.
Route::middleware(['auth', 'rol:asistente,director'])->group(function () {
    // ...
});
```

Las páginas del Grupo 1 ya están protegidas con `auth` + `rol:admin`.

Pruebas automáticas:

```bash
php artisan test tests/Feature/AccesoYRolesTest.php tests/Feature/Auth
```

## Cómo agregar una pantalla o un módulo · Grupo 1

Esta es la receta completa para que una pantalla nueva quede **dentro del marco común** y **protegida por el login y los roles**. Primero ubiquen su caso:

| Caso | Ejemplo | Pasos |
|---|---|---|
| **A.** Una página más dentro de su módulo | Detalle de un caso, formulario de una resolución | 1, 2, 3 y 5 |
| **B.** La página de **inicio** de un rol | Bandeja del director, agendas para consulta | 1 a 5 |
| **C.** Un rol que no existe en la tabla `roles` | (poco probable) | Pedir al Grupo 1 el rol y su usuario de prueba; luego 1 a 5 |

### 1 · Ruta protegida y con nombre

En su archivo de rutas, dentro de un grupo con el permiso que corresponde. El nombre (`->name()`) es obligatorio: el menú y el inicio por rol usan el nombre, no la dirección.

```php
Route::middleware(['auth', 'rol:director'])->group(function () {
    Route::get('/revision/director', [RevisionController::class, 'bandejaDirector'])->name('revision.director');
});

Route::middleware(['auth', 'rol:consulta,asistente'])->group(function () {
    Route::get('/consejo/agendas', [AgendaController::class, 'index'])->name('consejo.agendas');
});
```

### 2 · Vista con el marco común

```blade
<x-layouts.app titulo="Casos por aprobar" modulo="Revisión y Consejo" :menu="config('menu_revision')">
    <p class="ui-antetitulo">Director de carrera</p>
    <h1 class="ui-titulo">Casos por aprobar</h1>
    ...
</x-layouts.app>
```

Detalles en [Marco común de las páginas](#marco-común-de-las-páginas-layout--grupo-1).

### 3 · Enlace en su menú (si debe aparecer en la columna lateral)

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

### 4 · Página de inicio del rol (solo caso B)

Al iniciar sesión, `/dashboard` manda a cada persona a su página según su rol. Esa lista vive en `app/Http/Controllers/Acceso/PanelController.php` (Grupo 1). Abran un *issue* al Grupo 1 indicando **el rol y el nombre de la ruta**, por ejemplo:

> "Rol `director` → `revision.director`" · "Rol `consulta` → `consejo.agendas`"

El Grupo 1 agrega una línea a la lista `DESTINOS`:

```php
['roles' => ['director'], 'ruta' => 'revision.director'],
['roles' => ['consulta'], 'ruta' => 'consejo.agendas'],
```

El orden importa: gana la primera línea que coincide. Si una persona tiene dos roles, llega al de más arriba.

### 5 · Prueben con los usuarios de prueba

| Prueba | Resultado esperado |
|---|---|
| Entrar sin sesión a la ruta | Manda a `/login` |
| Entrar con un usuario **sin** el rol | Página 403 "No tienes permiso" |
| Entrar con el usuario **con** el rol | La página abre, con el nombre de su módulo en la barra y el enlace resaltado en el menú |
| (Caso B) Iniciar sesión con ese usuario | Llega directo a su página |

Usuarios: ver la tabla de [Acceso, registro y roles](#acceso-registro-y-roles--grupo-1).

### Qué NO hacer

- No copiar el marco ni crear una barra propia.
- No revisar roles a mano en la vista (`if ($usuario->rol == ...)`): usen `rol:` en la ruta y `roles` en el menú.
- No editar `PanelController`, `menu_administracion.php` ni los componentes del marco: se piden al Grupo 1 (regla 4 del Excel).

## Módulo 1: Administración académica · Grupo 1

Catálogos que usan los demás módulos, personal e importación del SIAC. Todo está en `/administracion` y solo entra el rol `admin`.

| Sección | Pantallas |
|---|---|
| Estructura académica | Facultades · Carreras · Mallas · Periodos académicos · Materias · Paralelos y docentes |
| Personas | Personal · Directores de carrera · Cuentas de acceso · Roles |
| Datos | Importaciones SIAC (plantillas, vista previa, historial y errores en CSV) |

### Documentación

| Documento | Para quién |
|---|---|
| [API interna (C32–C35)](docs/grupo-1-api.md) | Grupos que llenan listas con JavaScript |
| [Contratos del Grupo 1](docs/grupo-1-contratos.md) | Todos los grupos: servicios, middleware, componentes, enums y factories |
| [Manual corto por perfil](docs/grupo-1-manual.md) | Administrador, personal y estudiantes |
| [Modelo de datos](docs/grupo-1-modelo-datos.md) | Diagrama de las 19 tablas del Grupo 1 |

### Archivos principales

| Qué | Dónde |
|---|---|
| Pantallas | `app/Http/Controllers/Administracion/` y `resources/views/administracion/` |
| Servicios (contratos) | `app/Services/Academico/` |
| Importador | `app/Services/Academico/ImportacionService.php`, `app/Services/Academico/Importadores/` y `app/Jobs/ProcesarImportacion.php` |
| Archivos de ejemplo para importar | `database/data/ejemplos/` |
| Menú | `config/menu_administracion.php` (Inicio arma sus tarjetas con este mismo archivo) |
| Pruebas | `tests/Feature/` (`ContratosAcademicosTest`, `ContratosPendientesTest`, `DatosDePruebaTest`, `EstructuraAcademicaTest`, `PersonasTest`, `ImportacionTest`, `PendientesG1Test`, `AccesoYRolesTest`, `LayoutComunTest`) |
