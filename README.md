# Proyecto-integrador

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
