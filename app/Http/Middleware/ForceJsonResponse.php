<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        // Forzamos de manera interna que la petición acepte JSON
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
