<?php

use App\Http\Middleware\VerificarRol;
use App\Http\Middleware\SoloEstudiante;
use App\Services\Chatbot\Prolog\PrologException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
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
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('chatbot/*') || $request->is('api/chatbot/*')) {
                $statusCode = 500;
                $codigo = 'error_interno';
                $reintentable = false;
                $campos = [];

                if ($e instanceof ValidationException) {
                    $statusCode = 422;
                    $codigo = 'datos_invalidos';
                    $reintentable = false;
                    $campos = $e->errors();
                } elseif ($e instanceof PrologException) {
                    $statusCode = $e->status;
                    $codigo = $e->codigo;
                    $reintentable = $e->reintentable;
                    $campos = $e->campos;
                } elseif ($e instanceof HttpExceptionInterface) {
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

                // Fix 10: nunca exponer getMessage() en 500s ni detalles internos.
                // El 419 (CSRF/sesión vencida) se mapea a un mensaje legible.
                if ($statusCode === 419) {
                    $codigo = 'sesion_vencida';
                    $mensaje = 'Tu sesión ha vencido. Recarga la página e intenta de nuevo.';
                } elseif ($statusCode === 422) {
                    $mensaje = 'Revisa los campos enviados.';
                } elseif ($statusCode >= 500) {
                    $mensaje = 'Error interno del servidor. Inténtalo de nuevo más tarde.';
                } else {
                    $mensaje = $e->getMessage() ?: 'Ha ocurrido un error. Inténtalo de nuevo.';
                }

                $errorPayload = [
                    'codigo' => $codigo,
                    'mensaje' => $mensaje,
                    'reintentable' => $reintentable,
                ];

                if (! empty($campos)) {
                    $errorPayload['campos'] = $campos;
                }

                return response()->json([
                    'version' => 3,
                    'error' => $errorPayload,
                ], $statusCode);
            }
        });
    })->create();
