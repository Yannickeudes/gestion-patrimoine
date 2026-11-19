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

        $headers = $request->headers->only(['authorization', 'accept', 'content-type']);
        $pending = Http::withHeaders($headers)->timeout(30);

        $content = $request->getContent();
        if ($content !== '' && $content !== false) {
            $pending = $pending->withBody(
                $content,
                $request->header('Content-Type', 'application/json')
            );
        }

        $response = $pending->send($request->method(), $url);

        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type') ?: 'application/json');
    }
}
