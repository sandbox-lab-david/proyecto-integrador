# Módulo 1: Administración académica · Grupo 1

Catálogos que usan los demás módulos, personal e importación del SIAC. Todo está en `/administracion` y solo entra el rol `admin`.

| Sección | Pantallas |
|---|---|
| Estructura académica | Facultades · Carreras · Mallas · Periodos académicos · Materias · Paralelos y docentes |
| Personas | Personal · Directores de carrera · Cuentas de acceso · Roles |
| Datos | Importaciones SIAC (plantillas, vista previa, historial y errores en CSV) |

## Documentación

| Documento | Para quién |
|---|---|
| [API interna (C32–C35)](api.md) | Grupos que llenan listas con JavaScript |
| [Contratos del Grupo 1](contratos.md) | Todos los grupos: servicios, middleware, componentes, enums y factories |
| [Manual corto por perfil](../../uso/administracion-y-acceso.md) | Administrador, personal y estudiantes |
| [Modelo de datos](modelo-datos.md) | Diagrama de las 19 tablas del Grupo 1 |

## Archivos principales

| Qué | Dónde |
|---|---|
| Pantallas | `app/Http/Controllers/Administracion/` y `resources/views/administracion/` |
| Servicios (contratos) | `app/Services/Academico/` |
| Importador | `app/Services/Academico/ImportacionService.php`, `app/Services/Academico/Importadores/` y `app/Jobs/ProcesarImportacion.php` |
| Archivos de ejemplo para importar | `database/data/ejemplos/` |
| Menú | `config/menu_administracion.php` (Inicio arma sus tarjetas con este mismo archivo) |
| Pruebas | `tests/Feature/` (`ContratosAcademicosTest`, `ContratosPendientesTest`, `DatosDePruebaTest`, `EstructuraAcademicaTest`, `PersonasTest`, `ImportacionTest`, `PendientesG1Test`, `AccesoYRolesTest`, `LayoutComunTest`) |
