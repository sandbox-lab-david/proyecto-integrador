#!/usr/bin/env bash
# Prueba de integracion del puente (HTTP-03). Falla (codigo != 0) si el motor no
# responde, si el estado HTTP no es el esperado o si el cuerpo no coincide.
#
# Uso:  bash prolog/pruebas/probar.sh [URL]
#   PROLOG_API_KEY            si esta definida, se envia como X-Api-Key y ademas
#                             se comprueba que una clave incorrecta da 403.
#   PROLOG_KB_VERSION_ESPERADA  si esta definida, /salud debe informar esa version.
#   bash prolog/pruebas/probar.sh --autoprueba-apagado
#                             comprueba que con el motor apagado el script falla.
set -u

if [ "${1:-}" = "--autoprueba-apagado" ]; then
  bash "$0" http://127.0.0.1:9 >/dev/null 2>&1; rc=$?
  if [ "$rc" -ne 0 ]; then echo "OK    motor apagado -> el script termino con codigo $rc"; exit 0
  else echo "FALLO motor apagado -> el script termino con codigo 0"; exit 1; fi
fi

U="${1:-http://127.0.0.1:8081}"
KEY="${PROLOG_API_KEY:-}"
FALLOS=0
TMP="$(mktemp)"; trap 'rm -f "$TMP"' EXIT

# req NOMBRE ESTADO_ESPERADO PATRON_EN_CUERPO [args de curl...]
req() {
  local nombre="$1" esperado="$2" patron="$3"; shift 3
  local code rc
  code="$(curl -sS --connect-timeout 2 --max-time 10 -o "$TMP" -w '%{http_code}' "$@" 2>"$TMP.err")"; rc=$?
  if [ "$rc" -ne 0 ] || [ "$code" = "000" ]; then
    echo "FALLO $nombre -> sin conexion con el motor (curl rc=$rc): $(cat "$TMP.err")"; rm -f "$TMP.err"
    FALLOS=$((FALLOS+1)); return
  fi
  rm -f "$TMP.err"
  if [ "$code" != "$esperado" ]; then
    echo "FALLO $nombre -> HTTP $code (esperado $esperado): $(head -c 200 "$TMP")"; FALLOS=$((FALLOS+1)); return
  fi
  if [ -n "$patron" ] && ! grep -Eq "$patron" "$TMP"; then
    echo "FALLO $nombre -> HTTP $code correcto pero el cuerpo no cumple /$patron/: $(head -c 200 "$TMP")"
    FALLOS=$((FALLOS+1)); return
  fi
  echo "OK    $nombre -> HTTP $code"
}

H=(-H 'Content-Type: application/json')
[ -n "$KEY" ] && H+=(-H "X-Api-Key: $KEY")

req "salud" 200 '"estado": *"listo"' "$U/salud"
if [ "$FALLOS" -ne 0 ]; then echo "Motor no disponible en $U: se aborta."; exit 1; fi
if [ -n "${PROLOG_KB_VERSION_ESPERADA:-}" ]; then
  req "salud informa kb_version esperada" 200 "\"kb_version\": *\"${PROLOG_KB_VERSION_ESPERADA}\"" "$U/salud"
fi

req "1. consulta informativa" 200 '"resultado": *null' "${H[@]}" \
  -d '{"version":3,"tramite_id":"examen_recuperacion","intencion":"consultar_requisitos"}' "$U/consulta"
req "2. evaluacion con datos faltantes" 200 '"comprobaciones":' "${H[@]}" \
  -d '{"version":3,"tramite_id":"examen_recuperacion","intencion":"comprobar_referencias","fecha":"2026-10-07","origen_datos":"manual","perfil":{}}' "$U/evaluar"
req "3. evaluacion con datos ficticios" 200 '"comprobaciones":' "${H[@]}" \
  -d '{"version":3,"tramite_id":"examen_recuperacion","intencion":"comprobar_referencias","fecha":"2026-10-07","origen_datos":"manual","perfil":{"gpa_general":78}}' "$U/evaluar"
req "4. entrada invalida" 422 '"codigo": *"datos_invalidos"' "${H[@]}" \
  -d '{"version":3,"tramite_id":"examen_recuperacion","intencion":"comprobar_referencias","fecha":"2026-02-30","origen_datos":"manual","perfil":{"gpa_general":150}}' "$U/evaluar"
req "5. GET en /consulta" 405 '"codigo": *"datos_invalidos"' "${H[@]}" "$U/consulta"

if [ -n "$KEY" ]; then
  B='{"version":3,"tramite_id":"examen_recuperacion","intencion":"consultar_requisitos"}'
  req "6. sin credencial" 403 '"codigo": *"acceso_denegado"' -H 'Content-Type: application/json' -d "$B" "$U/consulta"
  req "7. credencial incorrecta" 403 '"codigo": *"acceso_denegado"' -H 'Content-Type: application/json' -H 'X-Api-Key: incorrecta-xxxxxxxx' -d "$B" "$U/consulta"
else
  echo "AVISO PROLOG_API_KEY no definida: no se probaron los rechazos de credencial (6 y 7)."
fi

echo
if [ "$FALLOS" -ne 0 ]; then echo "RESULTADO: $FALLOS prueba(s) fallida(s)"; exit 1; fi
echo "RESULTADO: todas las pruebas pasaron"; exit 0