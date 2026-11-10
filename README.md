# Sistema de Solicitudes Estudiantiles UEES

Aplicación web en Laravel para que los estudiantes de la UEES pidan sus trámites académicos, las facultades los revisen y los lleven al Consejo, con un chatbot que responde con reglas en Prolog. La construyeron 4 grupos, cada uno a cargo de un módulo.

## Índice

### Instalación y puesta en marcha

| Necesito… | Documento |
|---|---|
| Instalar el proyecto en mi computadora | [Instalar el proyecto](docs/instalacion/instalacion-local.md) |
| Encender el servidor Prolog que usa el chatbot | [Servidor Prolog del chatbot](docs/instalacion/servidor-prolog.md) |
| Resolver un error al instalar o después de traer cambios | [Problemas comunes](docs/instalacion/problemas-comunes.md) |

### Uso del sistema

| Necesito… | Documento |
|---|---|
| Entrar al sistema, crear mi contraseña y usar la administración académica | [Manual de acceso y administración](docs/uso/administracion-y-acceso.md) |
| Saber qué comportamientos conocidos no se van a cambiar por ahora | [Limitaciones conocidas](docs/limitaciones-conocidas.md) |

### Para programadores (todos los grupos)

| Necesito… | Documento |
|---|---|
| Poner mi página dentro del marco común (barra superior y menú lateral) | [Marco común de las páginas](docs/desarrollo/marco-comun.md) |
| Proteger mis páginas con el inicio de sesión y los roles, y conocer los usuarios de prueba | [Acceso, registro y roles](docs/desarrollo/acceso-y-roles.md) |
| Agregar una pantalla o un módulo nuevo paso a paso | [Cómo agregar una pantalla](docs/desarrollo/agregar-pantalla.md) |
| Dar estilo a una página con las clases comunes `ui-` | [Estilos generales](docs/desarrollo/estilos-generales.md) |

### Módulo 1 · Administración académica (Grupo 1)

| Necesito… | Documento |
|---|---|
| Conocer las pantallas y los archivos principales del módulo | [Resumen del Módulo 1](docs/modulos/grupo-1-administracion/README.md) |
| Usar los servicios, componentes y permisos del Grupo 1 (contratos C01–C41) | [Contratos del Grupo 1](docs/modulos/grupo-1-administracion/contratos.md) |
| Llenar listas de facultades, carreras o periodos con JavaScript (API C32–C35) | [API interna](docs/modulos/grupo-1-administracion/api.md) |
| Entender las 19 tablas del Grupo 1 y cómo se relacionan | [Modelo de datos](docs/modulos/grupo-1-administracion/modelo-datos.md) |

### Módulo 2 · Portal del estudiante (Grupo 2)

| Necesito… | Documento |
|---|---|
| Entender cómo el portal arma el formulario y genera el documento oficial en Word o PDF | [Documentos del portal](docs/modulos/grupo-2-portal/documentos.md) |
| Configurar la subida de archivos de hasta 10 MB y la descarga en PDF | [Subidas de 10 MB y descarga PDF](docs/modulos/grupo-2-portal/archivos-pdf.md) |
| Avisar al estudiante en la campana y por correo (C52 y C53) | [Notificaciones del portal](docs/modulos/grupo-2-portal/notificaciones.md) |
| Saber qué datos declara el estudiante en lugar de importarlos del SIAC | [Formularios sin importación del SIAC](docs/modulos/grupo-2-portal/datos-manuales.md) |
| Revisar qué campos pide cada uno de los 20 trámites | [Revisión de campos de los trámites](docs/modulos/grupo-2-portal/revision-campos-tramites.md) |

### Módulo 3 · Revisión y Consejo (Grupo 3)

Este módulo todavía no tiene documentación propia.

### Módulo 4 · Catálogo de trámites y chatbot (Grupo 4)

| Necesito… | Documento |
|---|---|
| Entender cómo funciona el chatbot y qué cambió en el módulo | [Resumen del chatbot](docs/modulos/grupo-4-chatbot/resumen-cambios.md) |
| Conocer qué responde el chatbot y el contrato del catálogo publicado (v3) | [Chatbot informativo · contrato v3](docs/modulos/grupo-4-chatbot/contrato-v3.md) |
| Administrar el catálogo normalizado de trámites | [Catálogo normalizado del Grupo 4](docs/modulos/grupo-4-chatbot/catalogo-administracion.md) |
| Enviar datos al chatbot para verificar los documentos de respaldo | [Verificación de documentos](docs/modulos/grupo-4-chatbot/verificacion-documentos-frontend.md) |

### Créditos

Integrantes de cada grupo y lo que hizo cada uno: [ir a los créditos](#créditos-de-los-grupos).

---

## Créditos de los grupos

### Grupo 1 · Administración académica

<p>
  <img alt="Módulo 1: Administración académica" src="https://img.shields.io/badge/M%C3%B3dulo%201-Administraci%C3%B3n%20acad%C3%A9mica-781c35">
</p>

<table>
  <tr>
    <td align="center" width="20%">
      <a href="https://github.com/fcostar25"><img src="https://github.com/fcostar25.png?size=120" width="100" alt="Foto de perfil de fcostar25"></a><br>
      <b>Fernando Costa Rosales</b><br>
      <a href="https://github.com/fcostar25">fcostar25</a>
    </td>
    <td align="center" width="20%">
      <a href="https://github.com/GdavidM"><img src="https://github.com/GdavidM.png?size=120" width="100" alt="Foto de perfil de GdavidM"></a><br>
      <b>Guillermo David Motoche Mendez</b><br>
      <img alt="Scrum Master" src="https://img.shields.io/badge/Scrum%20Master-781c35?style=for-the-badge"><br>
      <a href="https://github.com/GdavidM">GdavidM</a>
    </td>
    <td align="center" width="20%">
      <a href="https://github.com/jorgemedinauees"><img src="https://github.com/jorgemedinauees.png?size=120" width="100" alt="Foto de perfil de jorgemedinauees"></a><br>
      <b>Jorge Luis Medina Riofrio</b><br>
      <a href="https://github.com/jorgemedinauees">jorgemedinauees</a>
    </td>
    <td align="center" width="20%">
      <a href="https://github.com/JoseTroya-coder"><img src="https://github.com/JoseTroya-coder.png?size=120" width="100" alt="Foto de perfil de JoseTroya-coder"></a><br>
      <b>Jose Luis Troya Alava</b><br>
      <a href="https://github.com/JoseTroya-coder">JoseTroya-coder</a>
    </td>
    <td align="center" width="20%">
      <a href="https://github.com/jBrito2002"><img src="https://github.com/jBrito2002.png?size=120" width="100" alt="Foto de perfil de jBrito2002"></a><br>
      <b>Jose Rafael Brito Chamorro</b><br>
      <a href="https://github.com/jBrito2002">jBrito2002</a>
    </td>
  </tr>
</table>

**Rama de trabajo:** `grupo-3/modulo-administracion` (los cambios llegan a `develop` por *pull request*).

#### Qué hicimos

Construimos la **Administración académica**: la base sobre la que trabajan los demás módulos. Mantenemos los catálogos académicos, el personal y sus cuentas, la importación de datos del SIAC, el marco común de las páginas y el acceso por roles.

| Función | Qué hace |
|---|---|
| **Marco común** | Barra superior con el logo de la UEES y menú lateral `<x-layouts.app>`, que comparten todas las páginas del sistema; cada grupo solo pone su contenido y su menú |
| **Acceso y roles** | Inicio de sesión con Laravel Breeze, roles reales (`admin`, `asistente`, `director`, `docente`, `consulta`), rutas protegidas y que cada usuario llegue a la pantalla de su módulo |
| **Cuentas sin registro público** | Las cuentas de estudiantes se crean al importarlos del SIAC y las del personal al registrarlo; cada persona recibe un enlace para crear su contraseña |
| **Estructura académica** | Administrar facultades, carreras, mallas, periodos académicos, materias, paralelos y docentes |
| **Personas** | Administrar el personal, los directores de carrera, las cuentas de acceso y los roles |
| **Importaciones SIAC** | Cargar archivos de Excel con plantillas, vista previa, historial y descarga de errores en CSV, mostrando todos los errores de cada fila |
| **Contratos para los otros grupos** | Servicios, middleware, componentes, enums, *factories* y una API interna que los demás módulos usan para leer los datos académicos |
| **Datos de prueba y documentación** | Usuarios y datos de prueba con `migrate:fresh --seed`, manual por perfil y diagrama del modelo de datos (19 tablas) |

#### Agradecimientos

Un enorme agradecimiento a nuestro Scrum Master, **David Motoche**, por toda la gestión y la ayuda durante el desarrollo del proyecto.

---

### Grupo 2 · Portal del estudiante

<p>
  <img alt="Módulo 2: Portal del estudiante" src="https://img.shields.io/badge/M%C3%B3dulo%202-Portal%20del%20estudiante-781c35">
</p>

<table>
  <tr>
    <td align="center" width="25%">
      <a href="https://github.com/Juan04A"><img src="https://github.com/Juan04A.png?size=120" width="100" alt="Foto de perfil de Juan04A"></a><br>
      <b>Juan Sebastián Araujo Pacheco</b><br>
      <a href="https://github.com/Juan04A">Juan04A</a>
    </td>
    <td align="center" width="25%">
      <a href="https://github.com/AlejandraIturralde"><img src="https://github.com/AlejandraIturralde.png?size=120" width="100" alt="Foto de perfil de AlejandraIturralde"></a><br>
      <b>María Alejandra Iturralde Zamora</b><br>
      <a href="https://github.com/AlejandraIturralde">AlejandraIturralde</a>
    </td>
    <td align="center" width="25%">
      <a href="https://github.com/NathalyYcaza"><img src="https://github.com/NathalyYcaza.png?size=120" width="100" alt="Foto de perfil de NathalyYcaza"></a><br>
      <b>Gina Nathaly Ycaza Laínez</b><br>
      <a href="https://github.com/NathalyYcaza">NathalyYcaza</a>
    </td>
    <td align="center" width="25%">
      <a href="https://github.com/MarcusLopez-ING"><img src="https://github.com/MarcusLopez-ING.png?size=120" width="100" alt="Foto de perfil de MarcusLopez-ING"></a><br>
      <b>Marcos Antonio López López</b><br>
      <a href="https://github.com/MarcusLopez-ING">MarcusLopez-ING</a>
    </td>
  </tr>
</table>

**Rama de trabajo:** `grupo-2/modulo-portal-estudiante` (los cambios llegan a `develop` por *pull request*).

#### Qué hicimos

Construimos el **Portal del estudiante**: la parte del sistema donde el estudiante pide sus trámites académicos sin documentos de Word sueltos ni carpetas. Cubre todo el camino, desde que elige el trámite hasta que ve la resolución.

| Función | Qué puede hacer el estudiante |
|---|---|
| **Catálogo y ficha** | Buscar entre los 20 trámites y leer los requisitos y documentos de cada uno, que se consultan a la base de conocimiento en Prolog del Grupo 4, y hacerle preguntas a su chatbot, que también funciona con Prolog |
| **Formulario en 4 pasos** | Llenar sus datos, los del trámite y las materias de su malla. Los campos cambian según el trámite y se validan en el navegador y en el servidor |
| **Respaldos** | Subir varios archivos por cada documento requerido y revisarlos en un visor antes de enviar |
| **Comprobación previa** | Ver, antes de enviar, una revisión informativa de su caso que hace el motor en Prolog del Grupo 4 con los datos que declaró, sin que bloquee el envío |
| **Documento oficial** | Descargar su solicitud en Word o PDF, llenada sobre las plantillas originales de la facultad, firmarla y subirla |
| **Borrador** | Dejar la solicitud a medias: se guarda sola en su cuenta y puede seguirla desde cualquier navegador |
| **Envío y seguimiento** | Recibir un código `SOL-AAAA-00000` y seguir en "Mis solicitudes" el estado, el historial y la resolución |
| **Correcciones** | Corregir y reenviar una solicitud observada con el mismo código, sin perder las versiones anteriores |
| **Notificaciones** | Recibir un aviso en la campanita y por correo cada vez que cambia el estado de su solicitud |

---

### Grupo 3 · Revisión y Consejo

* **William Francisco Balseca Jimenez**
* **Briana Estefania Chang Sanchez**
* **Christian Javier Guerrero Garzon**
* **Carlos Alessandro Lema Armitano**
* **Nicolas Rafael Pazos Altamirano**

---

### Grupo 4 · Catálogo de trámites y chatbot

<p>
  <img alt="Módulo 4: Chatbot con Prolog" src="https://img.shields.io/badge/M%C3%B3dulo%204-Chatbot%20con%20Prolog-781c35">
</p>

<table>
  <tr>
    <td align="center" width="20%">
      <a href="https://github.com/AlexGamerylo"><img src="https://github.com/AlexGamerylo.png?size=120" width="100" alt="Foto de perfil de AlexGamerylo"></a><br>
      <b>Jeremy Llerena</b><br>
      <a href="https://github.com/AlexGamerylo">AlexGamerylo</a>
    </td>
    <td align="center" width="20%">
      <a href="https://github.com/mchavez23MC"><img src="https://github.com/mchavez23MC.png?size=120" width="100" alt="Foto de perfil de mchavez23MC"></a><br>
      <b>Mateo Chávez</b><br>
      <a href="https://github.com/mchavez23MC">mchavez23MC</a>
    </td>
    <td align="center" width="20%">
      <a href="https://github.com/becerrallan"><img src="https://github.com/becerrallan.png?size=120" width="100" alt="Foto de perfil de becerrallan"></a><br>
      <b>Allan Becerra</b><br>
      <a href="https://github.com/becerrallan">becerrallan</a>
    </td>
    <td align="center" width="20%">
      <a href="https://github.com/saskiabrito"><img src="https://github.com/saskiabrito.png?size=120" width="100" alt="Foto de perfil de saskiabrito"></a><br>
      <b>Saskia Brito</b><br>
      <a href="https://github.com/saskiabrito">saskiabrito</a>
    </td>
    <td align="center" width="20%">
      <a href="https://github.com/BryanGrogan24"><img src="https://github.com/BryanGrogan24.png?size=120" width="100" alt="Foto de perfil de BryanGrogan24"></a><br>
      <b>Bryan Grogan</b><br>
      <a href="https://github.com/BryanGrogan24">BryanGrogan24</a>
    </td>
  </tr>
</table>

**Rama de trabajo:** [`grupo-4/modulo-chatbot`](https://github.com/sandbox-lab-david/proyecto-integrador/tree/grupo-4/modulo-chatbot) (los cambios llegan a `develop` por *pull request*).

#### Qué hicimos

Construimos el **Chatbot con Prolog**: el asistente que orienta sobre los trámites académicos, consulta sus requisitos y documentos y permite comprobar previamente los datos de una solicitud. Integramos la base de conocimiento y el motor de reglas en Prolog con Laravel y los demás módulos.

#### Aportes por integrante

| Integrante | GitHub | Aporte |
|---|---|---|
| Jeremy Llerena | [@AlexGamerylo](https://github.com/AlexGamerylo) | Interfaz gráfica del chatbot: vista del chat, componentes, estilos generales, burbuja flotante con Ueesito y visibilidad por roles |
| Mateo Chávez | [@mchavez23MC](https://github.com/mchavez23MC) | Integración con Laravel: catálogo de trámites, contrato v3, categorías y umbrales configurables, seeders y unión con los demás módulos |
| Allan Becerra | [@becerrallan](https://github.com/becerrallan) | API del chatbot: persistencia en base de datos, contrato v2, seguridad de la API, rutas de trámites y verificación de documentos de respaldo en Prolog |
| Saskia Brito | [@saskiabrito](https://github.com/saskiabrito) | Puente HTTP entre Laravel y Prolog: servidor, clave de acceso, límites y pruebas |
| Bryan Grogan | [@BryanGrogan24](https://github.com/BryanGrogan24) | Base de conocimiento en Prolog: catálogo de hechos de los trámites y su documentación |
