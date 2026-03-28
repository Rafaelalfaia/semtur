<?php

use App\Models\Theme;
use App\Services\ThemeResolver;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

if (! function_exists('theme_asset')) {
    function theme_asset(string $key, ?Theme $theme = null, ?string $context = null): string
    {
        return app(ThemeResolver::class)->asset($key, $theme, $context);
    }
}

if (! function_exists('theme_token')) {
    function theme_token(string $key, mixed $default = null, ?Theme $theme = null): mixed
    {
        $resolvedTheme = $theme ?? (app(ThemeResolver::class)->payload(auth()->user())['theme'] ?? null);

        return $resolvedTheme?->getToken($key, $default) ?? $default;
    }
}

if (! function_exists('site_image_presets')) {
    function site_image_presets(): array
    {
        return [
            'hero' => ['width' => 1600, 'height' => 900, 'fit' => 'cover', 'jpg_quality' => 78, 'webp_quality' => 76],
            'hero-mobile' => ['width' => 960, 'height' => 1280, 'fit' => 'cover', 'jpg_quality' => 76, 'webp_quality' => 74],
            'card' => ['width' => 720, 'height' => 540, 'fit' => 'cover', 'jpg_quality' => 76, 'webp_quality' => 74],
            'mini' => ['width' => 480, 'height' => 360, 'fit' => 'cover', 'jpg_quality' => 74, 'webp_quality' => 72],
            'avatar' => ['width' => 320, 'height' => 320, 'fit' => 'cover', 'jpg_quality' => 78, 'webp_quality' => 76],
            'gallery' => ['width' => 1280, 'height' => 960, 'fit' => 'inside', 'jpg_quality' => 78, 'webp_quality' => 76],
            'logo' => ['width' => 320, 'height' => 160, 'fit' => 'inside', 'jpg_quality' => 82, 'webp_quality' => 80],
        ];
    }
}

if (! function_exists('site_image_sources')) {
    function site_image_sources(?string $source, string $preset = 'card'): array
    {
        $source = trim((string) $source);
        $config = site_image_presets()[$preset] ?? site_image_presets()['card'];

        if ($source === '') {
            return [
                'jpg' => null,
                'webp' => null,
                'avif' => null,
                'width' => $config['width'] ?? null,
                'height' => $config['height'] ?? null,
            ];
        }

        $localPath = site_image_local_path($source);

        if (! $localPath || ! is_file($localPath)) {
            return [
                'jpg' => $source,
                'webp' => null,
                'avif' => null,
                'width' => $config['width'] ?? null,
                'height' => $config['height'] ?? null,
            ];
        }

        $mtime = (string) (filemtime($localPath) ?: 0);
        $signature = sha1($localPath.'|'.$mtime.'|'.$preset.'|'.json_encode($config));
        $directory = public_path('optimized/site/'.$preset);
        $jpgRelative = 'optimized/site/'.$preset.'/'.$signature.'.jpg';
        $webpRelative = 'optimized/site/'.$preset.'/'.$signature.'.webp';
        $jpgTarget = public_path($jpgRelative);
        $webpTarget = public_path($webpRelative);

        if (! is_file($jpgTarget) || ! is_file($webpTarget)) {
            File::ensureDirectoryExists($directory);

            try {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($localPath);

                if (($config['fit'] ?? 'cover') === 'inside') {
                    $image = $image->scaleDown(
                        width: $config['width'] ?? null,
                        height: $config['height'] ?? null
                    );
                } else {
                    $image = $image->cover(
                        $config['width'] ?? 720,
                        $config['height'] ?? 540
                    );
                }

                $image->toJpeg($config['jpg_quality'] ?? 76)->save($jpgTarget);
                $image->toWebp($config['webp_quality'] ?? 74)->save($webpTarget);
            } catch (\Throwable) {
                return [
                    'jpg' => $source,
                    'webp' => null,
                    'avif' => null,
                    'width' => $config['width'] ?? null,
                    'height' => $config['height'] ?? null,
                ];
            }
        }

        return [
            'jpg' => asset($jpgRelative),
            'webp' => asset($webpRelative),
            'avif' => null,
            'width' => $config['width'] ?? null,
            'height' => $config['height'] ?? null,
        ];
    }
}

if (! function_exists('site_image_url')) {
    function site_image_url(?string $source, string $preset = 'card'): ?string
    {
        return site_image_sources($source, $preset)['jpg'] ?? $source;
    }
}

if (! function_exists('site_image_local_path')) {
    function site_image_local_path(string $source): ?string
    {
        $parsed = parse_url($source);
        $path = $parsed['path'] ?? $source;

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (Str::startsWith($path, '/storage/')) {
            $storagePath = storage_path('app/public/'.ltrim(Str::after($path, '/storage/'), '/'));

            return is_file($storagePath) ? $storagePath : public_path(ltrim($path, '/'));
        }

        if (Str::startsWith($path, ['/imagens/', '/images/', '/optimized/'])) {
            $publicPath = public_path(ltrim($path, '/'));

            return is_file($publicPath) ? $publicPath : null;
        }

        if (Str::startsWith($path, '/')) {
            $publicPath = public_path(ltrim($path, '/'));

            return is_file($publicPath) ? $publicPath : null;
        }

        if (preg_match('#^(https?:)?//#i', $source)) {
            return null;
        }

        $publicPath = public_path(ltrim($source, '/'));

        return is_file($publicPath) ? $publicPath : null;
    }
}
