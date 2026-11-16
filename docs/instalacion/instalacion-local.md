# Instalar el proyecto en tu computadora

> Aporte del Grupo 1. Lo que cada módulo necesita aparte está en su propio documento (por ejemplo, el [servidor Prolog del Módulo 4](servidor-prolog.md)). Si algo falla, revisen [Problemas comunes](problemas-comunes.md).

## Requisitos

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

## Pasos

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

Abran <http://localhost:8000> y entren con `admin@uees.edu.ec` / `password123`. Los demás usuarios de prueba están en [Acceso, registro y roles](../desarrollo/acceso-y-roles.md#usuarios-de-prueba).

## Para trabajar en el día a día

```bash
composer run dev
```

Levanta a la vez el servidor, **la cola** (`queue:listen`), los registros y Vite. Si prefieren terminales separadas: `php artisan serve`, `php artisan queue:work` y `npm run dev`.

- **La cola** hace falta para importar archivos grandes del SIAC (más de 500 filas) y para algunas notificaciones.
- **Correos:** con `MAIL_MAILER=log` (lo que trae `.env.example`) los correos no se envían: se guardan en `storage/logs/laravel.log`, incluidos los enlaces para crear contraseña. Para enviarlos de verdad, configuren `MAIL_MAILER=smtp` y los datos de su servidor de correo (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`).

## Pruebas

```bash
php artisan test
```

Dos pruebas del catálogo del Módulo 4 necesitan SWI-Prolog instalado; sin él, fallan solo esas dos.
