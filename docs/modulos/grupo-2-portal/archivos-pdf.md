# Subidas de 10 MB y descarga PDF

Verificación del 20 de octubre de 2026.

## Configuración de PHP

La aplicación acepta PDF, JPG y PNG de hasta 10 MiB cada uno (10.485.760 bytes),
verificando el tipo por su contenido. El navegador sube cada archivo en una petición
separada. Para que PHP permita llegar a ese límite, el `php.ini` cargado debe incluir:

```ini
upload_max_filesize = 10M
post_max_size = 12M
```

Se ajustaron esas dos directivas en `C:\php\php.ini` en este equipo. El margen de
`post_max_size` incluye los campos y las cabeceras multipart. Se conservó el archivo
original en una carpeta `%TEMP%\solicitudes-config-php-...`; el manifiesto
`%TEMP%\solicitudes-cambio-php.json` identifica su ubicación.

El cambio se aplica a los procesos PHP que se inicien después del ajuste. Detener
el servidor anterior con Ctrl+C y volver a ejecutar, desde el proyecto:

```powershell
php artisan serve
```

Estas directivas pertenecen al equipo y no se distribuyen al hacer pull. En otro
servidor se deben aplicar límites equivalentes, también en su proxy web si lo usa.

## Conversión a PDF

Se preparó una copia local de LibreOffice en
`%LOCALAPPDATA%\Programs\LibreOffice`, usando el instalador x86-64 26.8.1 de la
[descarga oficial](https://www.libreoffice.org/download/). Se verificó el SHA-256
contra el publicado por The Document Foundation antes de extraer sus archivos.
Esta copia permite ejecutar el conversor sin cambiar las asociaciones de Word.

`WordService` detecta instalaciones del sistema, del usuario y del PATH. Conserva
un perfil y una carpeta temporal independientes por conversión. En Windows pasa
explícitamente las carpetas temporales y las variables de rutas necesarias al
subproceso, tanto desde consola como desde una petición web.

El formulario permite elegir Word o PDF. Un fallo de conversión devuelve un error
503 con un mensaje comprensible y mantiene disponible la descarga Word. Los archivos
intermedios se eliminan al terminar. Las plantillas oficiales originales se conservan.

## Comprobaciones realizadas

- Subida HTTP multipart de un PDF de exactamente 10 MiB: HTTP 201, conservando sus
  10.485.760 bytes, tanto en respaldos como en PDF firmado.
- Archivo de 10 MiB más 1 KiB: HTTP 422.
- Texto HTML llamado `falso.pdf`: HTTP 422.
- Descarga PDF desde la ruta real del formulario: HTTP 200, tipo `application/pdf`
  y nombre `solicitud-gracia.pdf`.
- Apertura, extracción de datos y renderizado del PDF de prueba. Se comprobaron
  nombre, código con ceros iniciales, cédula y materia. La muestra de una materia
  conserva la firma y el contacto en una página.
- `DocumentoSolicitudTest`: 44 pruebas correctas, incluidas la descarga PDF,
  limpieza de temporales, alternativa Word ante fallo de conversión y las veinte
  plantillas sin modificación del original.
- Suite general: 205 pruebas correctas y 20 omitidas por integraciones pendientes.

Las comprobaciones HTTP usaron un servidor temporal en localhost, una carpeta
aislada y datos ficticios. La conversión utiliza las mismas plantillas y servicios
que el formulario del estudiante.
