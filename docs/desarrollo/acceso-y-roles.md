# Acceso, registro y roles · Grupo 1

El inicio de sesión del proyecto usa **Laravel Breeze**: sus controladores están en `app/Http/Controllers/Auth/` y sus rutas en `routes/auth.php`. **Fortify ya no se usa** y se quitó del proyecto.

## Después de traer estos cambios

Cada grupo debe correr lo de la sección [Instalar el proyecto](../instalacion/instalacion-local.md): como mínimo `composer install`, `php artisan migrate:fresh --seed` y `npm run build`.

## Usuarios de prueba

`php artisan migrate:fresh --seed` crea estos usuarios. Todos tienen la clave `password123`.

Son solo para probar en local y en las pruebas automáticas.

| Correo | Tipo | Rol | Facultad o carrera | Al iniciar sesión llega a |
|---|---|---|---|---|
| `admin@uees.edu.ec` | trabajador | `admin` | — | `/administracion` (G1) |
| `asistente@uees.edu.ec` | trabajador | `asistente` | Facultad de Ingeniería | `/revision/asistente` (G3) |
| `asistente.salud@uees.edu.ec` | trabajador | `asistente` | Facultad de Ciencias de la Salud | `/revision/asistente` (G3) |
| `director@uees.edu.ec` | trabajador | `director` | Ingeniería en Computación | `/revision/director` (G3) |
| `director.civil@uees.edu.ec` | trabajador | `director` | Ingeniería Civil | `/revision/director` (G3) |
| `director.medicina@uees.edu.ec` | trabajador | `director` | Medicina | `/revision/director` (G3) |
| `consulta@uees.edu.ec` | trabajador | `consulta` | — | `/revision/resoluciones` (G3) |
| `estudiante@uees.edu.ec` | estudiante | — | Ingeniería en Computación | `/estudiante/tramites` (G2) |
| `estudiante.civil@uees.edu.ec` | estudiante | — | Ingeniería Civil | `/estudiante/tramites` (G2) |
| `estudiante.medicina@uees.edu.ec` | estudiante | — | Medicina | `/estudiante/tramites` (G2) |

Las tres carreras tienen malla y paralelos de prueba, así que cada estudiante puede elegir materias en los trámites que las piden. La asistente de Ingeniería ve las solicitudes de Computación y Civil; la de Salud, las de Medicina. Cada director ve solo las de su carrera.

### En el servidor (producción)

Con `APP_ENV=production` el seeder **no** crea estos usuarios, ni mallas, periodos o paralelos de prueba: solo roles, modalidades, categorías, estados, facultades, carreras y el catálogo de trámites. El primer administrador se crea una sola vez desde la consola del servidor:

```bash
php artisan admin:crear correo@uees.edu.ec "Nombres" "Apellidos"
```

El comando le pone una contraseña al azar y le envía el enlace para crear la suya. Si el código de empleado `ADM-001` ya existe, se elige otro con `--codigo=ADM-002`. La plantilla del `.env` del servidor está en `.env.production.example`.

## Cómo funciona

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

## Cómo proteger sus rutas

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
