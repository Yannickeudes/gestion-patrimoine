<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentification inter-services : le token Bearer (Sanctum, delivre par
 * auth-service) est valide en appelant GET {AUTH_SERVICE_URL}/api/me.
 * L'utilisateur est expose via $request->attributes->get('auth_user').
 */
class ServiceAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Token manquant.'], 401);
        }

        try {
            $response = Http::withToken($token)
                ->timeout(5)
                ->get(rtrim(env('AUTH_SERVICE_URL', 'http://auth-service:8000'), '/').'/api/me');
        } catch (\Throwable) {
            return response()->json(['message' => 'Service d\'authentification injoignable.'], 503);
        }

        if (! $response->successful()) {
            return response()->json(['message' => 'Token invalide ou expire.'], 401);
        }

        $request->attributes->set('auth_user', $response->json());

        return $next($request);
    }
}
