#!/usr/bin/env bash
# Prueba minima de integracion del puente. Uso: bash prolog/pruebas/probar.sh [URL]
# (el servidor debe estar corriendo: swipl prolog/kb/servidor.pl)
U="${1:-http://127.0.0.1:8081}"; H='Content-Type: application/json'
p() { echo "### $1"; shift; curl -s -w '\n[HTTP %{http_code}]\n' -H "$H" "$@"; echo; }
echo "### salud"; curl -s -w '\n[HTTP %{http_code}]\n' "$U/salud"; echo
p "1. consulta informativa"   -d '{"version":2,"tramite_id":"examen_recuperacion","intencion":"consultar_requisitos"}' "$U/consulta"
p "2. evaluacion con datos faltantes" -d '{"version":2,"tramite_id":"examen_recuperacion","intencion":"evaluar_elegibilidad","fecha":"2026-10-07","origen_datos":"manual","perfil":{"materia_id":"MAT101"}}' "$U/evaluar"
p "3. evaluacion con datos ficticios" -d '{"version":2,"tramite_id":"examen_recuperacion","intencion":"evaluar_elegibilidad","fecha":"2026-10-07","origen_datos":"manual","perfil":{"materia_id":"MAT101","promedio":78}}' "$U/evaluar"
p "4. entrada invalida (422)" -d '{"version":2,"tramite_id":"examen_recuperacion","intencion":"evaluar_elegibilidad","fecha":"2026-02-30","origen_datos":"manual","perfil":{"promedio":150}}' "$U/evaluar"
echo ">> 5. Motor apagado: detene el servidor (Ctrl+C) y repeti cualquier peticion: curl dara 'connection refused'"
echo "   y HttpPrologClient lo convierte en PrologException 503 motor_no_disponible."