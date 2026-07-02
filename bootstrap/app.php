<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
        \App\Http\Middleware\ForceJsonResponse::class,
    ]);
        // 1. Registrar tus alias de middleware
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // 2. SOLUCIÓN DEFINITIVA PARA API:
        // Si el usuario no está autenticado, no redirigir.
        // Laravel detectará automáticamente que es una API y devolverá 401 JSON.
        $middleware->redirectGuestsTo(fn(Request $request) => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 3. OPCIONAL: Personalizar la respuesta de error de autenticación
        // Esto asegura que siempre sea JSON aunque olvides el header 'Accept: application/json'
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
    })->create();
