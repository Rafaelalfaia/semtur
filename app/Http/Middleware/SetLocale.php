<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = supported_locales();
        $routeLocale = $request->route('locale');
        $preferredLocale = route_locale(null, $request);

        if ($request->route()?->parameterNames() && in_array('locale', $request->route()->parameterNames(), true)) {
            if (! filled($routeLocale) && $request->isMethodSafe() && ! $request->expectsJson()) {
                $path = trim($request->path(), '/');
                $target = '/'.$preferredLocale;

                if ($path !== '' && $path !== '/') {
                    $target .= '/'.$path;
                }

                if ($request->getQueryString()) {
                    $target .= '?'.$request->getQueryString();
                }

                return redirect($target);
            }
        }

        $appLocale = data_get($supported, $preferredLocale.'.app_locale', config('app.locale', 'pt_BR'));

        app()->setLocale($appLocale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $preferredLocale);
        }

        URL::defaults(['locale' => $preferredLocale]);

        view()->share('currentLocale', $preferredLocale);
        view()->share('supportedLocales', $supported);

        return $next($request);
    }
}

