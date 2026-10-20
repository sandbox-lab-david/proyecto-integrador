<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\VerificarRol;
use App\Http\Middleware\SoloEstudiante;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rol' => VerificarRol::class,
            'estudiante' => SoloEstudiante::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('chatbot/*') || $request->is('api/chatbot/*')) {
                $statusCode = 500;
                $codigo = 'error_interno';
                $reintentable = false;
                $campos = [];

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $statusCode = 422;
                    $codigo = 'datos_invalidos';
                    $reintentable = false;
                    $campos = $e->errors();
                } elseif ($e instanceof \App\Modules\Chatbot\Services\Prolog\PrologException) {
                    $statusCode = $e->status;
                    $codigo = $e->codigo;
                    $reintentable = $e->reintentable;
                    $campos = $e->campos;
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();
                    $codigo = match ($statusCode) {
                        422 => 'datos_invalidos',
                        401 => 'sesion_requerida',
                        403 => 'acceso_denegado',
                        502 => 'respuesta_motor_invalida',
                        503 => 'motor_no_disponible',
                        504 => 'tiempo_agotado',
                        default => 'error_interno',
                    };
                    $reintentable = in_array($statusCode, [503, 504]);
                }

                $mensaje = $statusCode === 422 ? 'Revisa los campos.' : ($e->getMessage() ?: 'Error interno del servidor.');

                $errorPayload = [
                    'codigo' => $codigo,
                    'mensaje' => $mensaje,
                    'reintentable' => $reintentable,
                ];

                if (!empty($campos)) {
                    $errorPayload['campos'] = $campos;
                }

                return response()->json([
                    'version' => 2,
                    'error' => $errorPayload,
                ], $statusCode);
            }
        });
    })->create();
