<?php

namespace App\Services\Notificaciones;

final class EnlaceNotificacion
{
    /** Acepta rutas locales o enlaces del propio portal; nunca redirige a otro sitio. */
    public static function local(string $enlace): ?string
    {
        $decodificado = rawurldecode($enlace);
        if (preg_match('/[\x00-\x20\x7f\\\\]/', $decodificado)) {
            return null;
        }

        if (str_starts_with($enlace, '/')) {
            return str_starts_with($decodificado, '//') ? null : $enlace;
        }

        $destino = parse_url($enlace);
        $portal = parse_url(url('/'));
        if (! is_array($destino) || ! is_array($portal)
            || isset($destino['user']) || isset($destino['pass'])) {
            return null;
        }
        foreach (['scheme', 'host', 'port'] as $parte) {
            if (($destino[$parte] ?? null) !== ($portal[$parte] ?? null)) {
                return null;
            }
        }

        return self::local(($destino['path'] ?? '/')
            .(isset($destino['query']) ? '?'.$destino['query'] : '')
            .(isset($destino['fragment']) ? '#'.$destino['fragment'] : ''));
    }
}
