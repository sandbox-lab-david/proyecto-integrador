<style>
/* Estilos limitados a las fichas; la barra y el menú del layout pertenecen a G1. */
.ficha-tramite{--ft-vino:#781c35;--ft-gris:#627086;--ft-linea:#e0e5ed;background:#f5f6fa;color:#202939;font-family:Arial,sans-serif;min-width:0}
.ficha-tramite *,.ficha-tramite *::before,.ficha-tramite *::after{box-sizing:border-box}
.ficha-tramite .ft-contenedor{max-width:1360px;padding:30px 28px 100px;margin:auto}
.ficha-tramite a:focus-visible,.ficha-tramite summary:focus-visible{outline:3px solid #2163a8;outline-offset:5px;border-radius:5px}
.ficha-tramite .ft-migas{display:flex;flex-wrap:wrap;gap:12px;color:var(--ft-gris);font-size:13px;margin-bottom:30px}
.ficha-tramite .ft-migas a{color:var(--ft-gris);text-decoration:none}.ficha-tramite .ft-migas a:hover{text-decoration:underline}
.ficha-tramite .ft-presentacion{display:flex;gap:24px;align-items:center;margin-bottom:30px}
.ficha-tramite .ft-icono{display:inline-flex;flex-shrink:0;align-items:center;justify-content:center;width:48px;height:48px;border-radius:50%;background:#f5edf0;color:var(--ft-vino)}
.ficha-tramite svg{width:25px;height:25px;flex-shrink:0}.ficha-tramite .ft-portada{width:100px;height:100px}.ficha-tramite .ft-portada svg{width:48px;height:48px}
.ficha-tramite .ft-etiqueta{display:inline-block;padding:6px 12px;border-radius:20px;background:#eadde2;color:var(--ft-vino);font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.ficha-tramite h1{font:700 clamp(28px,3vw,42px)/1.15 Georgia,serif;margin:12px 0 8px;overflow-wrap:anywhere}
.ficha-tramite p{margin:0;line-height:1.65}.ficha-tramite .ft-presentacion p{color:var(--ft-gris);font-size:15px;max-width:820px}
.ficha-tramite .ft-distribucion{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(300px,1fr);gap:22px;align-items:start}
.ficha-tramite .ft-principal,.ficha-tramite .ft-lateral{display:grid;gap:20px;min-width:0}
.ficha-tramite .ft-tarjeta{padding:24px;border:1px solid var(--ft-linea);border-radius:14px;background:white;box-shadow:0 2px 5px #20293903;min-width:0}
.ficha-tramite .ft-titulo{display:flex;align-items:center;gap:16px;margin-bottom:20px}.ficha-tramite h2{margin:0;font:700 23px/1.3 Georgia,serif}
.ficha-tramite .ft-titulo p{margin-top:5px;font-size:13px;color:var(--ft-gris)}
.ficha-tramite .lista-requisitos{list-style:none;padding:0;margin:0;counter-reset:requisito}
.ficha-tramite .lista-requisitos li{display:flex;gap:18px;padding:22px 0;border-bottom:1px solid var(--ft-linea);counter-increment:requisito}
.ficha-tramite .lista-requisitos li:first-child{padding-top:8px}.ficha-tramite .lista-requisitos li:last-child{border-bottom:0;padding-bottom:10px}
.ficha-tramite .lista-requisitos li::before{content:counter(requisito);display:grid;place-items:center;flex:0 0 38px;height:38px;border:1.5px solid #a4506b;border-radius:50%;background:#fcf9fa;color:var(--ft-vino);font-weight:700}
.ficha-tramite .lista-requisitos li>div{min-width:0;padding-top:5px}.ficha-tramite .lista-requisitos p{color:#536174;font-size:14px;overflow-wrap:anywhere}
.ficha-tramite .ft-nota{color:var(--ft-gris);font-size:12px;margin-top:18px}
.ficha-tramite .ft-documentos{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;list-style:none;margin:0;padding:0}
.ficha-tramite .ft-documento{display:flex;gap:12px;align-items:flex-start;border:1px solid var(--ft-linea);border-radius:9px;padding:16px;min-width:0}
.ficha-tramite .ft-documento>svg{color:var(--ft-vino);width:26px;height:30px}.ficha-tramite .ft-documento strong{display:block;font-size:13px;line-height:1.45;overflow-wrap:anywhere}
.ficha-tramite .ft-documento p{color:var(--ft-gris);font-size:12px;margin-top:8px}.ficha-tramite .ft-condicion{display:inline-block;font:400 11px/1.4 Arial,sans-serif;color:var(--ft-vino);background:#f3eaee;padding:4px 8px;border-radius:7px;margin-top:8px}
.ficha-tramite .ft-formato{display:block;font-size:11px;color:var(--ft-gris);margin-top:8px}
.ficha-tramite .ft-aviso{display:flex;align-items:flex-start;gap:15px;border:1px solid #ead79a;background:#fff8e6;border-radius:12px;padding:20px 24px}.ficha-tramite .ft-aviso>span{color:#946600}
.ficha-tramite .ft-aviso h2{font:700 16px/1.4 Arial,sans-serif}.ficha-tramite .ft-aviso p{white-space:pre-line;font-size:13px;color:#6e6044;margin-top:5px;overflow-wrap:anywhere}
.ficha-tramite .ft-pasos{list-style:none;padding:0;margin:26px 0;display:grid;gap:28px}.ficha-tramite .ft-pasos li{display:flex;gap:16px;position:relative}
.ficha-tramite .ft-pasos li:not(:last-child)::after{content:'';width:1px;height:16px;background:#d9ccd2;position:absolute;top:55px;left:24px}
.ficha-tramite h3{font:700 14px/1.5 Arial,sans-serif;margin:4px 0}.ficha-tramite .ft-pasos p{color:var(--ft-gris);font-size:13px}
.ficha-tramite .ft-boton{display:flex;align-items:center;justify-content:center;gap:20px;padding:17px;color:white;background:var(--ft-vino);border-radius:8px;text-decoration:none;font-size:15px;font-weight:700;min-height:54px}.ficha-tramite .ft-boton:hover{background:#5e172a}.ficha-tramite .ft-boton span{font-size:23px;line-height:1}
.ficha-tramite .ft-enlace{display:block;width:fit-content;margin:16px auto 20px;color:var(--ft-vino);font-size:13px;text-underline-offset:3px}
.ficha-tramite .ft-borrador{display:flex;align-items:center;gap:10px;border-top:1px solid var(--ft-linea);padding-top:16px;color:var(--ft-gris);font-size:11px}.ficha-tramite .ft-borrador svg{width:18px;height:18px}
.ficha-tramite .ft-detalle summary{display:flex;align-items:center;gap:12px;cursor:pointer;font-size:15px;font-weight:700;list-style:none}.ficha-tramite .ft-detalle summary::-webkit-details-marker{display:none}
.ficha-tramite .ft-detalle summary>span:last-child{margin-left:auto;color:var(--ft-gris)}.ficha-tramite .ft-detalle[open] summary>span:last-child{transform:rotate(45deg)}
.ficha-tramite .ft-detalle p,.ficha-tramite .ft-detalle dl,.ficha-tramite .ft-detalle ul{font-size:13px;color:var(--ft-gris);margin:16px 0 0}.ficha-tramite .ft-detalle ul{padding-left:18px;list-style:disc}.ficha-tramite .ft-detalle li{margin:6px 0}
.ficha-tramite .ft-plazos dt{color:#202939;font-weight:700;margin:16px 0 6px}.ficha-tramite .ft-plazos dd{margin:0;line-height:1.65}
@media(max-width:1000px){.ficha-tramite .ft-distribucion{grid-template-columns:minmax(0,1fr)}}
@media(max-width:600px){.ficha-tramite .ft-contenedor{padding:20px 14px 100px}.ficha-tramite .ft-presentacion{gap:14px;align-items:flex-start}.ficha-tramite .ft-portada{width:58px;height:58px}.ficha-tramite .ft-portada svg{width:29px;height:29px}.ficha-tramite .ft-tarjeta{padding:20px}.ficha-tramite .ft-documentos{grid-template-columns:1fr}.ficha-tramite h2{font-size:21px}.ficha-tramite .ft-migas{margin-bottom:24px;font-size:12px}.ficha-tramite .ft-aviso{padding:18px}}
</style>
