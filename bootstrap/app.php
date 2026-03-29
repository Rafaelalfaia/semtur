<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'coordenador.permission' => \App\Http\Middleware\EnsureCoordenadorAreaPermission::class,
            'app.setLocale'      => \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            return localized_route('login', ['locale' => route_locale(null, $request)]);
        });
    })

    ->withExceptions(function ($exceptions) {
        //
    })->create();
