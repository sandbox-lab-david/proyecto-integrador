<?php

namespace App\Http\Controllers;

use App\Services\Notificaciones\EnlaceNotificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        return view('notificaciones.index', [
            'notificaciones' => $request->user()->notifications()->paginate(20),
            'sinLeer' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function resumen(Request $request)
    {
        return response()->json([
            'sin_leer' => $request->user()->unreadNotifications()->count(),
            'notificaciones' => $request->user()->notifications()->limit(20)->get()->map(fn ($aviso) => [
                'id' => $aviso->id,
                'titulo' => $aviso->data['titulo'] ?? 'Notificación',
                'mensaje' => $aviso->data['mensaje'] ?? '',
                'leida' => $aviso->read_at !== null,
                'fecha' => $aviso->created_at->locale('es')->diffForHumans(),
                'abrir' => route('notificaciones.leer', $aviso->id),
            ]),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function leer(Request $request, string $notificacion)
    {
        $aviso = $request->user()->notifications()->findOrFail($notificacion);
        $aviso->markAsRead();

        return redirect(EnlaceNotificacion::local((string) ($aviso->data['url'] ?? ''))
            ?? route('notificaciones.index', [], false));
    }

    public function leerTodas(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $request->expectsJson()
            ? response()->json(['sin_leer' => 0])
            : redirect()->route('notificaciones.index');
    }
}
