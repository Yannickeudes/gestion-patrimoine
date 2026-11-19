<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Client HTTP vers asset-service pour valider qu'un actif existe.
 */
class AssetClient
{
    public static function actifExiste(int $actifId, ?string $token = null): bool
    {
        try {
            $response = Http::withToken($token)
                ->timeout(5)
                ->get(rtrim(env('ASSET_SERVICE_URL', 'http://asset-service:8000'), '/')."/api/actifs/{$actifId}");

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
