<style>
    /* No altera el menú ni la barra del layout de G1. */
    .portal-estudiante { font-family: Arial, sans-serif; background: #f5f6fa; color: #202939; min-width: 0; }
    .portal-estudiante h1, .portal-estudiante h2, .portal-estudiante h3 { font-weight: bold; }
    .portal-estudiante p { margin-top: 1em; margin-bottom: 1em; }
    .portal-estudiante .portal-herramientas { display:flex; align-items:center; justify-content:flex-end; gap:12px; margin-bottom:20px; min-width:0; }
    .portal-estudiante .portal-mis-solicitudes { display:inline-flex; align-items:center; justify-content:center; gap:14px; min-height:44px; padding:10px 16px; border:1px solid #e4d9df; border-radius:12px; background:#fff; color:#781c35; font-size:14px; font-weight:600; text-decoration:none; }
    .portal-estudiante .portal-mis-solicitudes:hover { background:#f6edf1; border-color:#c79aaa; }
    .portal-estudiante .portal-herramientas .avisos-campana { flex:0 0 auto; width:max-content; margin:0; }
    .portal-estudiante .portal-herramientas .avisos-campana > summary { position:relative; display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; padding:0; border:1px solid #e4d9df; border-radius:12px; background:#fff; box-shadow:0 2px 5px #20293905; }
    .portal-estudiante .portal-herramientas .avisos-campana > summary:hover,
    .portal-estudiante .portal-herramientas .avisos-campana[open] > summary { background:#f6edf1; border-color:#c79aaa; }
    .portal-estudiante .portal-herramientas .avisos-campana > summary svg { width:21px; height:21px; }
    .portal-estudiante .portal-herramientas .avisos-contador { position:absolute; top:-6px; right:-6px; min-width:19px; height:19px; padding:0 4px; border:2px solid #f5f6fa; font-size:10px; line-height:15px; }
    .portal-estudiante .portal-mis-solicitudes:focus-visible { outline:3px solid #781c35; outline-offset:3px; }
</style>
