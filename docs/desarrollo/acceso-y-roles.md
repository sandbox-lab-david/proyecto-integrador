# Acceso, registro y roles · Grupo 1

El inicio de sesión del proyecto usa **Laravel Breeze**: sus controladores están en `app/Http/Controllers/Auth/` y sus rutas en `routes/auth.php`. **Fortify ya no se usa** y se quitó del proyecto.

## Después de traer estos cambios

Cada grupo debe correr lo de la sección [Instalar el proyecto](../instalacion/instalacion-local.md): como mínimo `composer install`, `php artisan migrate:fresh --seed` y `npm run build`.

## Usuarios de prueba

`php artisan migrate:fresh --seed` crea estos usuarios. Todos tienen la clave `password123`.

| Correo | Tipo | Rol | Al iniciar sesión llega a |
|---|---|---|---|
| `admin@uees.edu.ec` | trabajador | `admin` | `/administracion` (G1) |
| `asistente@uees.edu.ec` | trabajador | `asistente` | `/revision/asistente` (G3) |
| `director@uees.edu.ec` | trabajador | `director` | `/revision/director` (G3) |
| `consulta@uees.edu.ec` | trabajador | `consulta` | `/revision/resoluciones` (G3) |
| `estudiante@uees.edu.ec` | estudiante | — | `/estudiante/tramites` (G2) |

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
