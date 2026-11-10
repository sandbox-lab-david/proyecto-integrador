# Problemas comunes al instalar

| Error | Solución |
|---|---|
| `requires ext-gd` o `ext-zip` al correr Composer | Activen las extensiones (ver "XAMPP en Windows" en [Instalar el proyecto](instalacion-local.md#requisitos)) |
| `Failed to open stream: Permission denied` en `vendor/` (Windows) | Cierren `php artisan serve`, `npm run dev`, la cola y Apache. Abran la terminal como administrador y corran `composer clear-cache` y `composer install`. Si sigue fallando, antes de `composer install` pongan `$env:COMPOSER_MAX_PARALLEL_HTTP=1` (PowerShell) o `set COMPOSER_MAX_PARALLEL_HTTP=1` (cmd) |
| `Vite manifest not found` | `npm run build` |
| La página muestra un error después de traer cambios | `php artisan optimize:clear` y, si hubo migraciones nuevas, `php artisan migrate` |
| Una importación grande se queda en "Procesando" | Falta la cola: `php artisan queue:work` |
