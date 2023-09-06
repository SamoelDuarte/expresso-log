<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class AuthTokenMiddleware
{
    
    public function handle($request, Closure $next)
    {
        $expectedToken = env('API_AUTH_TOKEN'); // Substitua pelo seu token esperado
        
        $requestToken = $request->header('Authorization');
        
        if ($requestToken !== $expectedToken) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        return $next($request);
    }
}
