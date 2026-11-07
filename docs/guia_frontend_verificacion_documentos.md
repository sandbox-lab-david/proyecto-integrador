# Guía de Integración Frontend: Verificación de Documentos con el Chatbot

Esta guía explica qué datos debe enviar el frontend (o cualquier componente cliente) al **Chatbot / Motor Prolog** para que realice la verificación dinámica de documentos de respaldo y devuelva las banderas de bloqueo y los mensajes descriptivos.

---

## 1. Endpoints Disponibles

La verificación se puede consumir de dos maneras:

### A. Vía HTTP (Para el Frontend, JavaScript o AJAX)
- **URL con sesión:** `POST /chatbot/mensaje`
- **URL API:** `POST /api/chatbot/mensaje`
- **Headers:**
  - `Content-Type: application/json`
  - `X-CSRF-TOKEN: <csrf_token>` (para rutas web en formularios Blade)

### B. Vía Backend PHP (Para Controladores o Servicios de Laravel)
- Usando el servicio inyectado `App\Services\Chatbot\PrologService`:
  ```php
  $resultado = app(PrologService::class)->verificarDocumentos($tramiteId, $documentosSubidos);
  ```

---

## 2. ¿Qué debe enviar el Frontend? (Request Payload)

Para solicitar la verificación de documentos, se debe enviar un JSON con la intención `comprobar_referencias` y el array de identificadores de documentos subidos dentro de `datos_usuario.documentos_subidos`:

### Estructura de la Petición

```json
{
  "version": 3,
  "tramite_id": "examen_recuperacion",
  "pregunta_id": "examen_recuperacion__referencias",
  "intencion": "comprobar_referencias",
  "origen_datos": "manual",
  "datos_usuario": {
    "documentos_subidos": [
      "examen_recuperacion_registro_calificaciones"
    ]
  }
}
```

### Detalle de Campos Obligatorios

| Campo | Tipo | Valor esperado | Descripción |
|---|---|---|---|
| `version` | Entero | `3` | Versión del contrato del chatbot. |
| `tramite_id` | String | Código del trámite (ej. `examen_recuperacion`, `ayudante_catedra`, `examen_supletorio`) | Identificador oficial del trámite en la base de hechos. |
| `pregunta_id` | String | `<tramite_id>__referencias` | Identificador de la acción en el catálogo. |
| `intencion` | String | `"comprobar_referencias"` | Intención que activa la inferencia lógica del motor. |
| `origen_datos` | String | `"manual"` | Indica que son datos provistos por el estudiante. |
| `datos_usuario.documentos_subidos` | Array de Strings | Lista de IDs de documentos asignados (ej. `["id_doc_1", "id_doc_2"]`) o `[]` si aún no ha subido ninguno. | **Clave principal:** contiene los identificadores de los documentos que el estudiante ya tiene adjuntos. |

> [!TIP]
> Si el estudiante aún no ha subido ningún archivo, debes enviar `"documentos_subidos": []`. Prolog identificará inmediatamente todos los documentos obligatorios que le faltan.

---

## 3. ¿Qué responde el Chatbot? (Response Payload)

El chatbot devuelve un objeto JSON estructurado. Dentro de la propiedad `comprobaciones` se incluye el bloque con código `"verificacion_documentos"`.

### Ejemplo 1: Faltan documentos (Bloqueo de avance)

```json
{
  "version": 3,
  "kb_version": "catalogo-informativo-001",
  "tipo": "referencias",
  "tramite_id": "examen_recuperacion",
  "titulo": "Referencias para tu trámite",
  "mensaje": "No puedes avanzar al siguiente paso. Te falta adjuntar el siguiente documento obligatorio: Registro de calificaciones o evidencia de la materia reprobada.",
  "comprobaciones": [
    {
      "codigo": "verificacion_documentos",
      "texto": "No puedes avanzar al siguiente paso. Te falta adjuntar el siguiente documento obligatorio: Registro de calificaciones o evidencia de la materia reprobada.",
      "datos": {
        "cumple": false,
        "puede_avanzar": false,
        "total_obligatorios": 1,
        "total_faltantes": 1,
        "documentos_faltantes": [
          "Registro de calificaciones o evidencia de la materia reprobada"
        ]
      }
    }
  ]
}
```

### Ejemplo 2: Documentos completos (Permiso de avance)

```json
{
  "version": 3,
  "kb_version": "catalogo-informativo-001",
  "tipo": "referencias",
  "tramite_id": "examen_recuperacion",
  "titulo": "Referencias para tu trámite",
  "mensaje": "¡Comprobación exitosa! Has presentado todos los documentos obligatorios requeridos para «Examen de recuperación». Puedes continuar al siguiente paso.",
  "comprobaciones": [
    {
      "codigo": "verificacion_documentos",
      "texto": "¡Comprobación exitosa! Has presentado todos los documentos obligatorios requeridos para «Examen de recuperación». Puedes continuar al siguiente paso.",
      "datos": {
        "cumple": true,
        "puede_avanzar": true,
        "total_obligatorios": 1,
        "total_faltantes": 0,
        "documentos_faltantes": []
      }
    }
  ]
}
```

---

## 4. ¿Cómo debe consumir la respuesta el Frontend?

En tu código JavaScript del formulario o del widget del chat, debes inspeccionar la comprobación de documentos:

```javascript
// Ejemplo de llamada desde JavaScript en el Paso de Respaldos:
async function verificarArchivosConProlog(tramiteId, documentosAsignadosIds) {
    const respuesta = await fetch('/chatbot/mensaje', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            version: 3,
            tramite_id: tramiteId,
            pregunta_id: `${tramiteId}__referencias`,
            intencion: 'comprobar_referencias',
            origen_datos: 'manual',
            datos_usuario: {
                documentos_subidos: documentosAsignadosIds
            }
        })
    });

    const data = await respuesta.json();
    
    // Buscar la comprobación de documentos generada por Prolog:
    const verificacion = data.comprobaciones?.find(c => c.codigo === 'verificacion_documentos');
    
    if (verificacion) {
        const { puede_avanzar, cumple, texto, documentos_faltantes } = verificacion.datos;
        
        if (!puede_avanzar) {
            // 1. Mostrar el mensaje dinámico de Prolog al usuario
            mostrarAlertaEnFormulario(verificacion.texto);
            
            // 2. Bloquear el botón de continuar
            document.getElementById('boton-continuar').disabled = true;
            return false;
        } else {
            // Todo en orden: habilitar avance
            mostrarMensajeExito(verificacion.texto);
            document.getElementById('boton-continuar').disabled = false;
            return true;
        }
    }
}
```

---

## 5. Tabla de Identificadores de Trámites y Documentos Obligatorios Comunes

Para referencia del frontend al mapear los archivos:

| Trámite (`tramite_id`) | Documentos Obligatorios (`documentos_subidos`) |
|---|---|
| `examen_recuperacion` | `examen_recuperacion_registro_calificaciones` |
| `ayudante_catedra` | `ayudante_catedra_recomendaciones_docentes`, `ayudante_catedra_solicitud_docente` |
| `examen_supletorio` | `examen_supletorio_evidencia_impedimento` |
| `cambio_carrera` | *(Sin obligatorios, solo opcionales)* |
| `cambio_malla` | *(Sin obligatorios, solo opcionales)* |
| `tercer_registro` | *(Sin obligatorios, solo opcionales)* |
