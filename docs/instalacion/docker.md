# Instalar en el servidor con Docker

Docker enciende el sistema completo en el servidor con un solo comando. Cada pieza va en su propia "caja" (contenedor), que ya trae todo lo que necesita: no hay que instalar PHP, PostgreSQL, SWI-Prolog ni LibreOffice a mano.

> Esta guía es para el **servidor**. Para trabajar en tu computadora sigue usando [Instalar el proyecto](instalacion-local.md).

## Las 5 cajas

| Caja | Qué hace | Equivale en local a… |
|---|---|---|
| `web` (Nginx) | Puerta de entrada: recibe a los usuarios y entrega la página. Es la única abierta hacia afuera (puerto 8080). | `php artisan serve` |
| `app` (PHP 8.4) | Ejecuta Laravel. Trae LibreOffice para pasar los Word a PDF. Al encender corre las migraciones. | PHP de tu computadora |
| `worker` | Procesa la cola: correos, notificaciones e importaciones grandes. | `php artisan queue:work` |
| `prolog` | Motor del chatbot, en modo producción y con clave obligatoria. | `swipl prolog/kb/servidor.pl` |
| `db` (PostgreSQL 18) | Base de datos. | PostgreSQL de tu computadora |

```
Usuario ──► web ──► app ──► db
                     └────► prolog
            worker ─────► db  (misma base y mismos archivos que app)
```

Los datos viven en dos volúmenes que **no se borran** al apagar, actualizar o reconstruir:

| Volumen | Qué guarda |
|---|---|
| `db` | La base de datos |
| `storage` | Archivos subidos, documentos generados, importaciones, catálogo publicado en Prolog y logs |

## Archivos de Docker

| Archivo | Para qué sirve |
|---|---|
| `Dockerfile` | Receta de las cajas `app`, `worker` y `prolog` (una misma imagen), y de `web` |
| `docker-compose.yml` | Lista de las 5 cajas y cómo se conectan |
| `docker/nginx/default.conf` | Puerta de entrada: límite de 13 MB por envío, cabeceras de seguridad |
| `docker/php/php.ini` | PHP: 12 MB por archivo, hora de Ecuador, OPcache |
| `docker/app/entrypoint.sh` | Lo que hace la caja al encender: preparar la caché y, en `app`, migrar |
| `.dockerignore` | Lo que **no** entra en la imagen (`.env`, `vendor`, `node_modules`, `.git`, pruebas) |

## Requisitos

- Docker Engine con Docker Compose (en Windows o Mac: Docker Desktop).
- Unos 4 GB libres. La primera construcción tarda de 10 a 15 minutos porque descarga PHP, PostgreSQL y LibreOffice.

## 1. Preparar el `.env`

Desde la carpeta del proyecto:

```bash
cp .env.production.example .env
```

Completa todos los `CAMBIAR`. Lo mínimo para encender:

| Variable | Qué poner |
|---|---|
| `APP_KEY` | Una clave nueva (comando abajo) |
| `APP_URL` | La dirección pública, por ejemplo `https://solicitudes.uees.edu.ec` |
| `DB_USERNAME` / `DB_PASSWORD` | Usuario y contraseña que tendrá la base. Docker crea la base con estos datos la primera vez. |
| `PROLOG_API_KEY` | Una clave de al menos 16 caracteres (comando abajo) |
| `MAIL_*` | Los datos del servidor de correo que entregue TI |

Para generar `APP_KEY` y `PROLOG_API_KEY` (Git Bash o Linux):

```bash
echo "APP_KEY=base64:$(openssl rand -base64 32)"
echo "PROLOG_API_KEY=$(openssl rand -hex 24)"
```

No hace falta cambiar `DB_HOST` ni `PROLOG_API_URL`: Docker los reemplaza por `db` y `http://prolog:8081`.

> Si una variable obligatoria falta o está vacía, Docker no enciende y dice cuál es (por ejemplo, `Falta DB_PASSWORD en el archivo .env`).

## 2. Encender

```bash
docker compose up -d --build
docker compose ps
```

Las 5 cajas deben aparecer como `running` (la `db`, además, como `healthy`). El sistema queda en `http://IP-DEL-SERVIDOR:8080`. Para usar otro puerto, agrega `PUERTO_WEB=80` al `.env`.

## 3. Primera vez: datos base y primer administrador

```bash
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan admin:crear correo@uees.edu.ec "Nombres" "Apellidos"
```

- `db:seed` carga facultades, carreras, roles, estados y el catálogo de trámites. Como `APP_ENV=production`, **no** crea usuarios, mallas, periodos ni paralelos de prueba.
- `admin:crear` envía al correo el enlace para crear la contraseña. Se puede usar `--codigo=ADM-002` si el código `ADM-001` ya existe.

Después, el administrador carga los datos reales desde **Importaciones SIAC**. Cada importación necesita que ya exista lo que menciona, así que van en este orden:

| Orden | Qué cargar | Necesita antes |
|---|---|---|
| 1 | Materias | — |
| 2 | Mallas | Materias |
| 3 | Docentes | — |
| 4 | Periodo (se crea a mano en **Periodos**) | — |
| 5 | Paralelos | Materias, periodo y docentes |
| 6 | Estudiantes | Carrera (ya cargada por `db:seed`) y la malla, si el archivo trae `malla_version` |

Si el SIAC trae carreras que no están en el seeder, se importan antes que todo lo demás. Un archivo de 5000 estudiantes tarda alrededor de un minuto en la cola, y la vista previa de un archivo así tarda unos 20 segundos en abrir.

## 4. Actualizar a una versión nueva

```bash
git pull
docker compose up -d --build
```

La caja `app` corre las migraciones nuevas sola al encender. Los volúmenes `db` y `storage` se conservan.

## Comandos útiles

| Para… | Comando |
|---|---|
| Ver el estado de las cajas | `docker compose ps` |
| Ver errores de Laravel | `docker compose exec app tail -n 50 storage/logs/laravel-$(date +%F).log` |
| Ver lo que muestra una caja | `docker compose logs --tail=50 app` (o `web`, `worker`, `prolog`, `db`) |
| Correr un comando de Laravel | `docker compose exec app php artisan …` |
| Reiniciar la cola tras un cambio | `docker compose restart worker` |
| Apagar todo (los datos se conservan) | `docker compose down` |
| Respaldar la base | `docker compose exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' > respaldo-$(date +%F).sql` |
| Restaurar un respaldo | `docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" "$POSTGRES_DB"' < respaldo.sql` |

> **Cuidado:** `docker compose down -v` borra los volúmenes, es decir, **toda la base y todos los archivos subidos**. No usar en el servidor.

## https

Docker atiende en `http`. El candado (`https`) lo pone quien administra el servidor, delante de Docker: un proxy de la universidad, Nginx o Caddy en el servidor, o un balanceador.

- Laravel confía en la cabecera `X-Forwarded-Proto` que manda ese proxy (`bootstrap/app.php`, `trustProxies`), así que los enlaces de los correos salen con `https://`.
- Laravel solo acepta visitas al dominio de `APP_URL` (`trustHosts`). Una visita con otro nombre recibe un error 400. Por eso `APP_URL` debe ser exactamente la dirección pública.
- Mientras no haya `https`, deja `SESSION_SECURE_COOKIE=false`. Si no, el inicio de sesión no funciona (la sesión solo viaja por `https`).
- Cuando haya proxy, conviene que la caja `web` reciba la IP real del usuario: en `docker/nginx/default.conf` se agrega `set_real_ip_from IP-DEL-PROXY;` y `real_ip_header X-Forwarded-For;`.

## Probar Docker en tu computadora

Sirve para ver, antes de subir al servidor, que todo enciende. En el `.env` de prueba usa:

```
APP_URL=http://localhost:8080
SESSION_SECURE_COOKIE=false
MAIL_MAILER=log
```

y cualquier valor de prueba para `DB_USERNAME`, `DB_PASSWORD` y `PROLOG_API_KEY`. Luego sigue los pasos 2 y 3, y entra a `http://localhost:8080`. Los correos (incluido el enlace del admin) quedan en el log de Laravel.

Para borrar la prueba por completo: `docker compose down -v`.

## Valores que fija Docker

| Valor | Dónde | Por qué |
|---|---|---|
| 12 MB por archivo, 13 MB por envío | `docker/php/php.ini`, `docker/nginx/default.conf` | Laravel rechaza lo que pasa de 10 MB con su propio mensaje en español; el margen evita una página de error del servidor |
| `DB_QUEUE_RETRY_AFTER=660` | `docker-compose.yml` | La importación puede durar hasta 600 s (`app/Jobs/ProcesarImportacion.php`). Con el valor por defecto (90 s), la cola la repetiría mientras sigue corriendo. **Si se instala sin Docker, poner este valor en el `.env`.** |
| `queue:work --timeout=600 --max-time=3600` | `docker-compose.yml` | Igual que el tiempo de la importación; el worker se renueva cada hora |
| PostgreSQL 18 | `docker-compose.yml` | La misma versión con la que se desarrolló |
