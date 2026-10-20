#!/usr/bin/env bash
# HTTP-01: comprueba que el motor exige credencial. Uso (desde la raiz del proyecto):
#   bash prolog/pruebas/probar_credencial.sh      (requiere swipl y curl)
set -u
PUERTO=8098; U="http://127.0.0.1:$PUERTO"; FALLOS=0
KEY='clave-de-prueba-0123'
B='{"version":2,"tramite_id":"examen_recuperacion","intencion":"consultar_requisitos"}'
ok()   { echo "OK    $1"; }
fallo(){ echo "FALLO $1"; FALLOS=$((FALLOS+1)); }

no_arranca() {   # no_arranca NOMBRE  VAR=valor ...
  local n="$1"; shift
  env -u PROLOG_API_KEY -u PROLOG_ENTORNO -u PROLOG_HOST "$@" timeout 20 swipl prolog/kb/servidor.pl --port=$PUERTO >/tmp/cred.log 2>&1; local rc=$?
  if [ "$rc" -eq 1 ] && grep -q "ERROR DE ARRANQUE" /tmp/cred.log; then ok "$n -> no arranca (rc=1)"
  else fallo "$n -> rc=$rc (esperado 1)"; fi
}
no_arranca "produccion sin clave (por defecto)"
no_arranca "produccion con clave corta" PROLOG_API_KEY=corta
no_arranca "entorno invalido" PROLOG_ENTORNO=prod PROLOG_API_KEY=$KEY
no_arranca "desarrollo sin clave y host no loopback" PROLOG_ENTORNO=desarrollo PROLOG_HOST=0.0.0.0

PID=""; cleanup() { [ -n "$PID" ] && kill "$PID" 2>/dev/null; }; trap cleanup EXIT
arrancar() { env -u PROLOG_API_KEY -u PROLOG_ENTORNO -u PROLOG_HOST "$@" swipl prolog/kb/servidor.pl --port=$PUERTO >/tmp/cred.log 2>&1 & PID=$!; sleep 3; }
parar()    { kill "$PID" 2>/dev/null; wait "$PID" 2>/dev/null; PID=""; }
c() { curl -s -o /dev/null -w '%{http_code}' --max-time 5 "$@"; }
esp() { [ "$1" = "$2" ] && ok "$3 -> $1" || fallo "$3 -> $1 (esperado $2)"; }
J=(-H 'Content-Type: application/json')

arrancar PROLOG_API_KEY=$KEY
esp "$(c "${J[@]}" -d "$B" $U/consulta)" 403 "produccion: sin cabecera"
esp "$(c "${J[@]}" -H 'X-Api-Key: otra-clave-0123456' -d "$B" $U/consulta)" 403 "produccion: clave incorrecta"
esp "$(c "${J[@]}" -H "X-Api-Key: $KEY" -d "$B" $U/consulta)" 200 "produccion: clave valida"
parar

arrancar PROLOG_ENTORNO=desarrollo
esp "$(c "${J[@]}" -d "$B" $U/consulta)" 200 "desarrollo loopback sin clave"
parar

echo; [ "$FALLOS" -eq 0 ] && { echo "RESULTADO: todas las pruebas pasaron"; exit 0; } || { echo "RESULTADO: $FALLOS fallida(s)"; exit 1; }