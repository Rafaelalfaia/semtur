<?php

use App\Models\Theme;
use App\Services\ThemeResolver;

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
