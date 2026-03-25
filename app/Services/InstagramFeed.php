<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class InstagramFeed
{
    protected string $workerUrl;
    protected int $ttl;

    public function __construct()
    {
        // .env
        $this->workerUrl = rtrim(config('services.instagram.worker_url', env('INSTAGRAM_WORKER_URL', '')), '/');
        $this->ttl       = (int) (config('services.instagram.feed_ttl', env('IG_FEED_TTL', 1800)));
    }

    public function getProfileFeed(string $profileUrl, int $limit = 10): array
    {
        if (empty($this->workerUrl) || empty($profileUrl)) {
            return [];
        }

        $cacheKey = 'ig:feed:' . md5($profileUrl) . ":l{$limit}";

        return Cache::remember($cacheKey, $this->ttl, function () use ($profileUrl, $limit) {
            try {
                $resp = Http::timeout(10)
                    ->acceptJson()
                    ->get($this->workerUrl, ['u' => $profileUrl]);

                if (!$resp->ok()) {
                    return [];
                }

                $data = $resp->json();
                if (!is_array($data)) {
                    return [];
                }

                // Normaliza/limita
                $items = array_slice($data, 0, $limit);

                // Garante chaves esperadas na view
                return array_values(array_map(function ($item) {
                    $image = $item['image']
                        ?? $item['display_url']
                        ?? $item['media_url']
                        ?? $item['thumbnail_url']
                        ?? $item['thumbnail_src']
                        ?? data_get($item, 'image_versions2.candidates.0.url')
                        ?? data_get($item, 'images.standard_resolution.url')
                        ?? null;

                    return [
                        'id'      => $item['id']      ?? Str::uuid()->toString(),
                        'image'   => $image,
                        'caption' => $item['caption'] ?? null,
                        'url'     => $item['url']
                            ?? $item['permalink']
                            ?? (isset($item['shortcode']) ? "https://www.instagram.com/p/{$item['shortcode']}/" : null),
                    ];
                }, $items));
            } catch (\Throwable $e) {
                // Silencioso: se cair aqui, devolve vazio e a Home continua
                return [];
            }
        });
    }
}
