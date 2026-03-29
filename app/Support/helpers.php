<?php

use Illuminate\Http\Request;

if (! function_exists('route_locale')) {
    function route_locale(?string $preferredLocale = null, ?Request $request = null): string
    {
        $supported = array_keys(config('app.supported_locales', ['pt' => []]));
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

if (! function_exists('localized_route')) {
    function localized_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        if (! array_key_exists('locale', $parameters)) {
            $parameters = ['locale' => route_locale()] + $parameters;
        }

        return route($name, $parameters, $absolute);
    }
}
