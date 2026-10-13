# Documentos del portal del estudiante

## Funcionamiento actual

La ficha de recuperación abre el formulario general. Al confirmar la declaración,
se descarga la plantilla DOCX completada con PhpWord. Abrir el archivo en Word
y exportar a PDF permite revisar el formato sin usar la conversión de Dompdf.
La solicitud sigue siendo una demostración: no se guarda, no se envía y no se
almacenan los respaldos del navegador.

Las plantillas originales no se sobrescriben. Las copias temporales son privadas
y se eliminan después de generar la descarga.

## Integración pendiente con el administrador

- Integrar `phpoffice/phpword` en las dependencias compartidas siguiendo la guía.
  Después de sincronizar la rama, cada integrante ejecuta `composer install`.
- Las 20 plantillas oficiales vacías en `storage/app/private/plantillas` están
  habilitadas para incluirse en Git mediante una lista de nombres permitidos.
  Incluirlas en el commit de la entrega. Los demás archivos privados permanecen
  ignorados; no compartir expedientes personales.
- Acordar un conversor DOCX a PDF para el servidor y verificar el formato de cada
  plantilla. El método experimental de Dompdf conserva contenido, pero no
  garantiza el diseño; no se utiliza en el botón del formulario.
- Conectar el perfil autenticado y los catálogos reales, persistencia del expediente,
  identificador SOL, versiones, permisos y carga del PDF firmado.

No modificar `config`, `app/Providers`, los manifiestos compartidos ni el layout
principal sin la coordinación indicada por la guía oficial.
