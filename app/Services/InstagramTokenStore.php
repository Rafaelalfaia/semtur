<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class InstagramTokenStore
{
    private const PATH = 'ig_token.json';

    public static function get(): ?array
    {
        if (Storage::exists(self::PATH)) {
            return json_decode(Storage::get(self::PATH), true) ?: null;
        }
        $env = env('IG_ACCESS_TOKEN');
        return $env ? ['access_token' => $env, 'source' => 'env'] : null;
    }

    public static function getToken(): ?string
    {
        $data = self::get();
        return $data['access_token'] ?? null;
    }

    public static function put(string $token, ?int $expiresIn = null): void
    {
        $payload = [
            'access_token' => $token,
            'expires_in'   => $expiresIn,
            'updated_at'   => now()->toIso8601String(),
        ];
        Storage::put(self::PATH, json_encode($payload));
    }
}
