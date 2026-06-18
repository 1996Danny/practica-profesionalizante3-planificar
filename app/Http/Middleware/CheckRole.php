<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Si el usuario no está autenticado o su rol no está en la lista permitida
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción.',
                'required_roles' => $roles,
                'your_role' => $request->user()?->role ?? 'none'
            ], 403);
        }

        return $next($request);
    }
}
