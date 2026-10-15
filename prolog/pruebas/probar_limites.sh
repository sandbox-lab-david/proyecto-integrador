#!/usr/bin/env bash
# Prueba las protecciones del servidor. Arranca su propio servidor en el puerto 8099
# con limites bajos. Uso (desde la raiz del proyecto):  bash prolog/pruebas/probar_limites.sh
# Requiere hechos.pl y reglas.pl en prolog/kb/ (los de prueba sirven).
export PROLOG_API_KEY=clave-de-prueba PROLOG_RATE_MAX=5 PROLOG_RATE_VENTANA=60 PROLOG_MAX_BODY=300
swipl prolog/kb/servidor.pl --port=8099 >/tmp/probar_limites.log 2>&1 &
PID=$!; trap 'kill $PID 2>/dev/null' EXIT; sleep 3
U=http://127.0.0.1:8099; H='Content-Type: application/json'; K='X-Api-Key: clave-de-prueba'
B='{"version":2,"tramite_id":"examen_recuperacion","intencion":"consultar_requisitos"}'
c() { curl -s -o /dev/null -w '%{http_code}' "$@"; }
ok() { [ "$1" = "$2" ] && echo "OK    $3 -> $1" || echo "FALLO $3 -> $1 (esperado $2)"; }
ok "$(c $U/salud)" 200 "salud sin clave"
ok "$(c -H "$H" -d "$B" $U/consulta)" 403 "consulta sin clave"
ok "$(c -H "$H" -H 'X-Api-Key: otra' -d "$B" $U/consulta)" 403 "clave incorrecta"
ok "$(c -H 'Content-Type: text/plain' -H "$K" -d "$B" $U/consulta)" 422 "content-type incorrecto"
BIG=$(printf '{"version":2,"tramite_id":"%0400d","intencion":"consultar_costo"}' 0)
ok "$(c -H "$H" -H "$K" -d "$BIG" $U/consulta)" 422 "cuerpo demasiado grande"
ok "$(c -H "$K" $U/consulta)" 405 "GET en /consulta"
ok "$(c -H "$H" -H "$K" -d "$B" $U/consulta)" 200 "consulta valida"
# el limite (5) cuenta TODAS las peticiones POST, tambien las rechazadas
for i in 1 2 3 4 5 6; do c -H "$H" -H "$K" -d "$B" $U/consulta >/dev/null; done
ok "$(c -H "$H" -H "$K" -d "$B" $U/consulta)" 503 "supera el limite de 5 por ventana"
ok "$(c $U/salud)" 200 "salud sigue disponible bajo limite"