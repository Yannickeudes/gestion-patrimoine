<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Proxy inverse léger : expose /api/v1/{auth|maintenance}/* sur ce service
 * pour offrir une URL unique (asset-service fait office de gateway).
 * Les appels /api/v1/assets/* sont assurés par un alias de routes interne.
 */
class GatewayProxyController extends Controller
{
    public function handle(Request $request, string $service, ?string $path = null)
    {
        // Les services upstream (Render free tier) peuvent mettre ~60 s a demarrer
        set_time_limit(120);

        $base = $service === 'auth'
            ? env('AUTH_SERVICE_URL')
            : env('MAINTENANCE_SERVICE_URL');

        if (! $base) {
            return response()->json(['message' => 'Service upstream non configuré'], 502);
        }

        $url = rtrim($base, '/') . '/api/' . ltrim((string) $path, '/');
        if ($request->getQueryString()) {
            $url .= '?' . $request->getQueryString();
        }

        $headers = [];
        foreach (['authorization', 'accept', 'content-type'] as $name) {
            if ($value = $request->header($name)) {
                $headers[$name] = $value;
            }
        }

        $pending = Http::withHeaders($headers)->timeout(90);

        $content = $request->getContent();
        if (is_string($content) && $content !== '') {
            $pending = $pending->withBody(
                $content,
                $request->header('Content-Type', 'application/json')
            );
        }

        try {
            $response = $pending->send($request->method(), $url);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Service upstream injoignable',
                'error' => $e->getMessage(),
            ], 502);
        }

        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type') ?: 'application/json');
    }
}
