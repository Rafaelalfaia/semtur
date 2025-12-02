<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class InstagramScraper
{
    /**
     * Busca os últimos posts do perfil informado (URL ou @username).
     * @return array<int, array{id:string|null,image:string|null,caption:?string,url:?string,timestamp:?int}>
     */
    public static function latest(string $profileUrl, int $limit = 9): array
    {
        $profileUrl = trim($profileUrl ?? '');
        if ($profileUrl === '') return [];

        // Se vier só o @usuario/usuario, monta a URL do perfil
        if (!Str::startsWith($profileUrl, ['http://', 'https://'])) {
            $username   = ltrim($profileUrl, '@/');
            $profileUrl = "https://www.instagram.com/{$username}/";
        }

        $worker = rtrim(config('services.instagram_worker.url') ?? '', '/');
        if ($worker === '') return [];

        // Limita entre 1 e 12 (o Worker já corta em 12 também)
        $n = max(1, min(12, $limit));

        try {
            $resp = Http::timeout(8)->retry(2, 200)->get($worker, [
                'u' => $profileUrl,
                'n' => $n,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return [];
        }

        if (!$resp->ok()) {
            // Útil se aparecer de novo "missing username"
            logger()->warning('instagram worker error', [
                'status' => $resp->status(),
                'body'   => $resp->body(),
            ]);
            return [];
        }

        $data = $resp->json();
        if (!is_array($data)) return [];

        // Normaliza campos esperados na view
        return collect($data)->take($n)->map(function ($i) {
            $short = $i['shortcode'] ?? null;
            return [
                'id'        => $i['id'] ?? $short,
                'image'     => $i['image'] ?? $i['display_url'] ?? null,
                'caption'   => $i['caption'] ?? null,
                'url'       => $i['url'] ?? ($short ? "https://www.instagram.com/p/{$short}/" : null),
                'timestamp' => $i['timestamp'] ?? null,
            ];
        })->filter(fn ($x) => $x['image'] && $x['url'])->values()->all();
    }
}
