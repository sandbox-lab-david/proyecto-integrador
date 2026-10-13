<?php

namespace App\Modules\Chatbot\Services\Prolog;

/**
 * Contrato Laravel -> Prolog. Cada metodo recibe el JSON de entrada del
 * contrato v2 como array y devuelve el JSON validado, o lanza PrologException.
 */
interface PrologClient
{
    /** POST /consulta: ['version'=>2,'tramite_id'=>...,'intencion'=>...] */
    public function consultar(array $entrada): array;

    /** POST /evaluar: version, tramite_id, intencion, fecha, origen_datos, perfil */
    public function evaluar(array $entrada): array;
}