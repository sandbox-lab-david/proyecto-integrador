# Estilos generales

Los colores, fuentes, tamaños, espacios y bordes del sistema están en
`resources/css/app.css`. Son los mismos del chatbot. Si un valor cambia ahí,
cambia en todas las vistas que cargan ese archivo, incluido el chat.

La página `/estilos` muestra cada pieza con el HTML listo para copiar.

## Cómo cargarlo

En el `<head>` de la vista o del layout:

```blade
@vite('resources/css/app.css')
```

El layout `layouts/app.blade.php` ya lo carga. Las vistas que tienen su propio
`<html>` deben agregarlo. No hace falta el CDN de Tailwind: Vite ya compila
Tailwind con estas variables, y el CDN reemplaza la fuente y los colores.

Después de cambiar `app.css` se ejecuta `npm run dev` o `npm run build`.

## Variables

| Grupo | Tailwind | CSS normal |
|---|---|---|
| Colores | `bg-vino`, `text-gris`, `border-linea` | `var(--color-vino)` |
| Fuentes | `font-sans`, `font-titulo` | `var(--font-titulo)` |
| Tamaños | `text-mini`, `text-chico`, `text-cuerpo`, `text-subtitulo`, `text-titulo` | `var(--text-cuerpo)` |
| Espacios | `p-chico`, `gap-normal`, `mt-amplio` | `var(--space-normal)` |
| Bordes | `rounded-control`, `rounded-tarjeta` | `var(--radius-tarjeta)` |

Colores disponibles: `vino`, `vino-oscuro`, `vino-suave`, `vino-linea`, `fondo`,
`superficie`, `texto`, `gris`, `gris-suave`, `linea`, `linea-suave`,
`linea-campo`, `exito`, `exito-suave`, `alerta`, `alerta-suave`, `peligro`,
`peligro-suave`, `info`, `info-suave` y `foco`.

## Clases comunes

Todas empiezan con `ui-` para no chocar con las clases que ya existen en cada
módulo.

| Pieza | Clases |
|---|---|
| Página | `ui-pagina`, `ui-contenedor`, `ui-barra`, `ui-antetitulo`, `ui-titulo`, `ui-subtitulo`, `ui-texto-gris` |
| Botones | `ui-btn` + `ui-btn-primario`, `ui-btn-secundario`, `ui-btn-peligro`, `ui-btn-quieto`, `ui-btn-chico` |
| Formularios | `ui-form`, `ui-campo`, `ui-control`, `ui-ayuda`, `ui-error-campo`, `ui-campo-error`, `ui-casilla`, `ui-resumen-error`, `ui-archivo` |
| Avisos | `ui-alerta` + `ui-alerta-exito`, `ui-alerta-aviso`, `ui-alerta-error` |
| Estados | `ui-badge` + `ui-badge-exito`, `ui-badge-alerta`, `ui-badge-peligro`, `ui-badge-info`, `ui-badge-vino`; `ui-etiqueta` |
| Contenido | `ui-tarjeta`, `ui-tarjetas`, `ui-datos`, `ui-dato`, `ui-lista-check` |
| Tablas | `ui-tabla-caja`, `ui-tabla`, `ui-paginacion` |
| Ventanas | `ui-modal` sobre un `<dialog>` |
| Formularios por pasos | `ui-pasos`, `ui-paso-hecho`, `aria-current="step"` |
| Carga y errores | `ui-vacio`, `ui-cargando`, `ui-esqueleto`, `ui-error-caja` |
| Accesibilidad | `ui-sr-oculto` |

El resultado de una evaluación se muestra con el componente del chatbot:

```blade
<x-resultado-elegibilidad :evaluacion="$evaluacion" />
```

## Cambios por módulo

- Layout principal: quitar `cdn.tailwindcss.com` y dejar solo `@vite`.
  Cambiar `bg-[#7A1C30]` por `bg-vino`.
- Portal del estudiante: agregar `@vite('resources/css/app.css')` en las vistas y
  reemplazar `#781c35` por `var(--color-vino)` y `Arial` por `var(--font-sans)`.
  Las clases propias pueden pasar a las `ui-` de a poco.
- Administración: cambiar `bg-blue-600` por `ui-btn ui-btn-primario` o `bg-vino`,
  y los colores de `x-alerta` por `ui-alerta-*`.
- Revisión: las vistas `revision/*` cargan solo el CDN; deben usar `@vite`.
