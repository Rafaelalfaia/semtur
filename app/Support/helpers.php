<?php

use App\Models\Idioma;
use App\Models\TranslationKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

if (! function_exists('supported_locales')) {
    function supported_locales(): array
    {
        static $resolved = null;

        if (is_array($resolved)) {
            return $resolved;
        }

        $fallback = config('app.supported_locales', ['pt' => []]);

        try {
            if (! Schema::hasTable('idiomas')) {
                return $resolved = $fallback;
            }

            $items = Idioma::query()
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('nome')
                ->get();

            if ($items->isEmpty()) {
                return $resolved = $fallback;
            }

            return $resolved = $items->mapWithKeys(function (Idioma $idioma) {
                $code = (string) $idioma->codigo;
                $htmlLang = $idioma->html_lang ?: ($code === 'pt' ? 'pt-BR' : $code);
                $ogLocale = $idioma->og_locale ?: ($code === 'pt' ? 'pt_BR' : strtoupper($code).'_'.strtoupper($code));

                return [
                    $code => [
                        'label' => $idioma->sigla ?: strtoupper($code),
                        'name' => $idioma->nome ?: strtoupper($code),
                        'app_locale' => $code === 'pt' ? 'pt_BR' : $code,
                        'html_lang' => $htmlLang,
                        'hreflang' => $idioma->hreflang ?: $htmlLang,
                        'og_locale' => $ogLocale,
                        'icon' => $idioma->bandeira_url,
                    ],
                ];
            })->all();
        } catch (\Throwable $e) {
            return $resolved = $fallback;
        }
    }
}

if (! function_exists('route_locale')) {
    function route_locale(?string $preferredLocale = null, ?Request $request = null): string
    {
        $supported = array_keys(supported_locales());
        $fallback = config('app.locale_prefix_fallback', 'pt');

        if (is_string($preferredLocale) && in_array($preferredLocale, $supported, true)) {
            return $preferredLocale;
        }

        $request ??= request();

        $routeLocale = $request?->route('locale');
        if (is_string($routeLocale) && in_array($routeLocale, $supported, true)) {
            return $routeLocale;
        }

        $sessionLocale = $request?->hasSession() ? $request->session()->get('locale') : null;
        if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
            return $sessionLocale;
        }

        return $fallback;
    }
}

if (! function_exists('locale_meta')) {
    function locale_meta(?string $locale = null): array
    {
        $supported = supported_locales();
        $resolved = $locale ?: route_locale();

        return $supported[$resolved] ?? $supported[config('app.locale_prefix_fallback', 'pt')] ?? [];
    }
}

if (! function_exists('localized_route')) {
    function localized_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        if (! array_key_exists('locale', $parameters)) {
            $parameters = ['locale' => route_locale()] + $parameters;
        }

        return route($name, $parameters, $absolute);
    }
}

if (! function_exists('ui_text')) {
    function ui_text(string $key, array $replace = [], ?string $locale = null): string
    {
        static $cache = [];

        $locale ??= app()->getLocale();
        $normalizedLocale = strtolower(str_replace('_', '-', $locale));
        $prefix = explode('-', $normalizedLocale)[0] ?: config('app.locale_prefix_fallback', 'pt');
        $fallbackPrefix = config('app.locale_prefix_fallback', 'pt');
        $fallbackLocale = data_get(supported_locales(), $fallbackPrefix.'.app_locale', config('app.fallback_locale', 'pt_BR'));
        $cacheKey = $prefix.'|'.$key;

        if (! array_key_exists($cacheKey, $cache)) {
            $cache[$cacheKey] = null;

            try {
                if (Schema::hasTable('translation_keys') && Schema::hasTable('translation_values') && Schema::hasTable('idiomas')) {
                    $entry = TranslationKey::query()
                        ->with([
                            'values' => function ($query) use ($prefix) {
                                $query->whereHas('idioma', function ($idiomaQuery) use ($prefix) {
                                    $idiomaQuery->where('codigo', $prefix);
                                })->with('idioma');
                            },
                        ])
                        ->where('key', $key)
                        ->where('is_active', true)
                        ->first();

                    if ($entry) {
                        $translated = trim((string) optional($entry->values->first())->text);
                        $cache[$cacheKey] = $translated !== '' ? $translated : (string) $entry->base_text;
                    }
                }
            } catch (\Throwable $e) {
                $cache[$cacheKey] = null;
            }
        }

        $text = $cache[$cacheKey];
        if (! is_string($text) || $text === '' || ui_text_has_encoding_issue($text)) {
            $resolved = __($key, $replace, $locale);
            if ($resolved !== $key) {
                return $resolved;
            }

            if ($locale !== $fallbackLocale) {
                $fallbackText = __($key, $replace, $fallbackLocale);
                if ($fallbackText !== $key) {
                    return $fallbackText;
                }
            }

            return $key;
        }

        if ($replace == []) {
            return $text;
        }

        return strtr($text, collect($replace)->mapWithKeys(fn ($value, $name) => [':'.$name => $value])->all());
    }
}

if (! function_exists('ui_text_has_encoding_issue')) {
    function ui_text_has_encoding_issue(?string $text): bool
    {
        if (! is_string($text) || $text === '') {
            return false;
        }

        return preg_match('/[\x{00C2}\x{00C3}\x{FFFD}]/u', $text) === 1;
    }
}

