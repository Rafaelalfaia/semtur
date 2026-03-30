<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-console-theme="{{ $resolvedThemeDataTheme ?? 'default' }}" data-console-mode="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'SEMTUR Console')</title>

    <script>
        (function () {
            var root = document.documentElement;
            var key = 'console-mode';
            var mode = 'light';
            var locksMode = @json((bool) ($resolvedThemeHasCustomConsoleTheme ?? false));

            if (!locksMode) {
                try {
                    var stored = window.localStorage.getItem(key);
                    if (stored === 'light' || stored === 'dark') {
                        mode = stored;
                    }
                } catch (error) {
                    mode = 'light';
                }
            } else {
                try {
                    window.localStorage.setItem(key, 'light');
                } catch (error) {
                    mode = 'light';
                }
            }

            root.dataset.consoleMode = mode;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(! empty($resolvedThemeCssVariables))
        <style>
            :root {
@foreach($resolvedThemeCssVariables as $variable => $value)
                {{ $variable }}: {{ $value }};
@endforeach
            }
        </style>
    @endif

    @stack('head')
</head>
<body x-data="{ consoleNavOpen: false }" x-on:keydown.escape.window="consoleNavOpen = false" class="ui-console-shell antialiased">
@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();

    $resolveRoute = function (array $candidates): ?string {
        foreach ($candidates as $name) {
            if (Route::has($name)) {
                return route($name);
            }
        }

        return null;
    };

    $routeExists = function (array $candidates): bool {
        foreach ($candidates as $name) {
            if (Route::has($name)) {
                return true;
            }
        }

        return false;
    };

    $isRouteActive = function (array $patterns): bool {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };

    $canAccess = function (?string $permission = null, ?array $roles = null) use ($user): bool {
        if (! $user) {
            return false;
        }

        if ($roles && ! $user->hasAnyRole($roles)) {
            return false;
        }

        if ($permission) {
            return $user->can($permission);
        }

        return true;
    };

    $roleBase = match (true) {
        $user?->hasRole('Admin') => 'Admin',
        $user?->hasRole('Coordenador') => 'Coordenador',
        $user?->hasRole('Tecnico') => 'Tecnico',
        default => 'Console',
    };

    $dashboardRoute = match ($roleBase) {
        'Admin' => $resolveRoute(['admin.dashboard']),
        'Coordenador' => $resolveRoute(['coordenador.dashboard']),
        'Tecnico' => $resolveRoute(['tecnico.dashboard']),
        default => $resolveRoute(['admin.dashboard', 'coordenador.dashboard', 'tecnico.dashboard']),
    };

    $profileUrl = match ($roleBase) {
        'Admin' => $resolveRoute(['admin.config.perfil.edit', 'admin.config.perfil', 'admin.perfil.edit', 'admin.profile.edit']) ?: url('/admin/config/perfil'),
        'Coordenador' => $resolveRoute(['coordenador.config.perfil.edit', 'coordenador.config.perfil', 'coordenador.perfil.edit', 'coordenador.profile.edit']) ?: url('/coordenador/config/perfil'),
        'Tecnico' => $resolveRoute(['tecnico.config.perfil.edit', 'tecnico.config.perfil', 'tecnico.perfil.edit', 'tecnico.profile.edit']) ?: url('/tecnico/config/perfil'),
        default => null,
    };

    if ($roleBase === 'Admin') {
        $menuSections = [
            [
                'title' => 'Geral',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'permission' => null,
                        'roles' => ['Admin'],
                        'routes' => ['admin.dashboard'],
                        'patterns' => ['admin.dashboard'],
                    ],
                ],
            ],
            [
                'title' => 'Administração',
                'items' => [
                    [
                        'label' => 'Usuários',
                        'permission' => 'usuarios.manage',
                        'roles' => ['Admin'],
                        'routes' => ['admin.usuarios.index'],
                        'patterns' => ['admin.usuarios.*'],
                    ],
                    [
                        'label' => 'Idiomas',
                        'permission' => null,
                        'roles' => ['Admin'],
                        'routes' => ['admin.idiomas.index'],
                        'patterns' => ['admin.idiomas.*'],
                    ],
                    [
                        'label' => 'Traduções',
                        'permission' => null,
                        'roles' => ['Admin'],
                        'routes' => ['admin.traducoes.index'],
                        'patterns' => ['admin.traducoes.*'],
                    ],
                    [
                        'label' => 'Temas',
                        'permission' => 'themes.view',
                        'roles' => ['Admin'],
                        'routes' => ['admin.temas.index'],
                        'patterns' => ['admin.temas.*'],
                    ],
                    [
                        'label' => 'Sistema',
                        'permission' => null,
                        'roles' => ['Admin'],
                        'routes' => ['admin.backups.index'],
                        'patterns' => ['admin.backups.*'],
                    ],
                ],
            ],
            [
                'title' => 'Conta',
                'items' => [
                    [
                        'label' => 'Meu perfil',
                        'permission' => null,
                        'roles' => ['Admin'],
                        'routes' => ['admin.config.perfil.edit', 'admin.config.perfil', 'admin.perfil.edit', 'admin.profile.edit'],
                        'patterns' => ['admin.config.perfil.*', 'admin.perfil.*', 'admin.profile.*'],
                        'url_fallback' => url('/admin/config/perfil'),
                    ],
                ],
            ],
        ];
    } elseif ($roleBase === 'Coordenador') {
        $menuSections = [
            [
                'title' => 'Geral',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'permission' => null,
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.dashboard'],
                        'patterns' => ['coordenador.dashboard'],
                    ],
                    [
                        'label' => 'Temas',
                        'permission' => 'themes.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.temas.index'],
                        'patterns' => ['coordenador.temas.*'],
                    ],
                ],
            ],
            [
                'title' => 'Conteúdo',
                'items' => [
                    [
                        'label' => 'Banner Principal',
                        'permission' => 'banners_destaque.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.banners-destaque.index', 'coordenador.banners_destaque.index'],
                        'patterns' => ['coordenador.banners-destaque.*', 'coordenador.banners_destaque.*'],
                    ],
                    [
                        'label' => 'Banners',
                        'permission' => 'banners.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.banners.index'],
                        'patterns' => ['coordenador.banners.*'],
                    ],
                    [
                        'label' => 'Avisos',
                        'permission' => 'avisos.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.avisos.index'],
                        'patterns' => ['coordenador.avisos.*'],
                    ],
                    [
                        'label' => 'Categorias',
                        'permission' => 'categorias.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.categorias.index'],
                        'patterns' => ['coordenador.categorias.*'],
                    ],
                    [
                        'label' => 'Empresas',
                        'permission' => 'empresas.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.empresas.index'],
                        'patterns' => ['coordenador.empresas.*'],
                    ],
                    [
                        'label' => 'Pontos Turísticos',
                        'permission' => 'pontos.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.pontos.index', 'coordenador.pontos-turisticos.index'],
                        'patterns' => ['coordenador.pontos.*', 'coordenador.pontos-turisticos.*'],
                    ],
                    [
                        'label' => 'Roteiros',
                        'permission' => 'roteiros.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.roteiros.index'],
                        'patterns' => ['coordenador.roteiros.*'],
                    ],
                    [
                        'label' => 'Guias e Revistas',
                        'permission' => 'guias.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.guias.index', 'coordenador.guias-revistas.index'],
                        'patterns' => ['coordenador.guias.*', 'coordenador.guias-revistas.*'],
                    ],
                    [
                        'label' => 'Museus e Teatros',
                        'permission' => 'espacos_culturais.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.espacos-culturais.index', 'coordenador.espacos_culturais.index'],
                        'patterns' => ['coordenador.espacos-culturais.*', 'coordenador.espacos_culturais.*'],
                    ],
                    [
                        'label' => 'Agendamentos',
                        'permission' => 'espacos_culturais.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.espacos-culturais.agendamentos.index'],
                        'patterns' => ['coordenador.espacos-culturais.agendamentos.*'],
                    ],
                    [
                        'label' => 'Onde comer',
                        'permission' => 'onde_comer.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.onde_comer.edit'],
                        'patterns' => ['coordenador.onde_comer.*'],
                    ],
                    [
                        'label' => 'Vídeos',
                        'permission' => 'videos.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.videos.index'],
                        'patterns' => ['coordenador.videos.*'],
                    ],
                    [
                        'label' => 'Onde ficar',
                        'permission' => 'onde_ficar.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.onde_ficar.edit'],
                        'patterns' => ['coordenador.onde_ficar.*'],
                    ],
                    [
                        'label' => 'Eventos',
                        'permission' => 'eventos.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.eventos.index'],
                        'patterns' => ['coordenador.eventos.*'],
                    ],
                    [
                        'label' => 'Jogos Indígenas',
                        'permission' => 'jogos_indigenas.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.jogos-indigenas.index'],
                        'patterns' => ['coordenador.jogos-indigenas.*'],
                    ],
                    [
                        'label' => 'Rota do Cacau',
                        'permission' => 'rota_do_cacau.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.rota-do-cacau.index', 'coordenador.rota_do_cacau.index'],
                        'patterns' => ['coordenador.rota-do-cacau.*', 'coordenador.rota_do_cacau.*'],
                    ],
                    [
                        'label' => 'Secretaria',
                        'permission' => 'secretaria.edit',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.secretaria.edit'],
                        'patterns' => ['coordenador.secretaria.*'],
                    ],
                    [
                        'label' => 'Equipe',
                        'permission' => 'equipe.manage',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.equipe.index'],
                        'patterns' => ['coordenador.equipe.*'],
                    ],
                ],
            ],
            [
                'title' => 'Operação',
                'items' => [
                    [
                        'label' => 'Relatórios',
                        'permission' => 'relatorios.view',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.coord.relatorios.index'],
                        'patterns' => ['coordenador.coord.relatorios.*'],
                    ],
                    [
                        'label' => 'Técnicos',
                        'permission' => 'tecnicos.manage',
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.tecnicos.index'],
                        'patterns' => ['coordenador.tecnicos.*'],
                    ],
                ],
            ],
            [
                'title' => 'Conta',
                'items' => [
                    [
                        'label' => 'Meu perfil',
                        'permission' => null,
                        'roles' => ['Coordenador'],
                        'routes' => ['coordenador.config.perfil.edit', 'coordenador.config.perfil', 'coordenador.perfil.edit', 'coordenador.profile.edit'],
                        'patterns' => ['coordenador.config.perfil.*', 'coordenador.perfil.*', 'coordenador.profile.*'],
                        'url_fallback' => url('/coordenador/config/perfil'),
                    ],
                ],
            ],
        ];
    } elseif ($roleBase === 'Tecnico') {
        $menuSections = [
            [
                'title' => 'Geral',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'permission' => null,
                        'roles' => ['Tecnico'],
                        'routes' => ['tecnico.dashboard'],
                        'patterns' => ['tecnico.dashboard'],
                    ],
                ],
            ],
            [
                'title' => 'Conteúdo',
                'items' => [
                    [
                        'label' => 'Banner Principal',
                        'permission' => 'banners_destaque.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.banners-destaque.index', 'coordenador.banners_destaque.index'],
                        'patterns' => ['coordenador.banners-destaque.*', 'coordenador.banners_destaque.*'],
                    ],
                    [
                        'label' => 'Banners',
                        'permission' => 'banners.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.banners.index'],
                        'patterns' => ['coordenador.banners.*'],
                    ],
                    [
                        'label' => 'Avisos',
                        'permission' => 'avisos.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.avisos.index'],
                        'patterns' => ['coordenador.avisos.*'],
                    ],
                    [
                        'label' => 'Categorias',
                        'permission' => 'categorias.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.categorias.index'],
                        'patterns' => ['coordenador.categorias.*'],
                    ],
                    [
                        'label' => 'Empresas',
                        'permission' => 'empresas.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.empresas.index'],
                        'patterns' => ['coordenador.empresas.*'],
                    ],
                    [
                        'label' => 'Pontos Turísticos',
                        'permission' => 'pontos.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.pontos.index', 'coordenador.pontos-turisticos.index'],
                        'patterns' => ['coordenador.pontos.*', 'coordenador.pontos-turisticos.*'],
                    ],
                    [
                        'label' => 'Roteiros',
                        'permission' => 'roteiros.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.roteiros.index'],
                        'patterns' => ['coordenador.roteiros.*'],
                    ],
                    [
                        'label' => 'Guias e Revistas',
                        'permission' => 'guias.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.guias.index', 'coordenador.guias-revistas.index'],
                        'patterns' => ['coordenador.guias.*', 'coordenador.guias-revistas.*'],
                    ],
                    [
                        'label' => 'Museus e Teatros',
                        'permission' => 'espacos_culturais.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.espacos-culturais.index', 'coordenador.espacos_culturais.index'],
                        'patterns' => ['coordenador.espacos-culturais.*', 'coordenador.espacos_culturais.*'],
                    ],
                    [
                        'label' => 'Agendamentos',
                        'permission' => 'espacos_culturais.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.espacos-culturais.agendamentos.index'],
                        'patterns' => ['coordenador.espacos-culturais.agendamentos.*'],
                    ],
                    [
                        'label' => 'Onde comer',
                        'permission' => 'onde_comer.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.onde_comer.edit'],
                        'patterns' => ['coordenador.onde_comer.*'],
                    ],
                    [
                        'label' => 'Vídeos',
                        'permission' => 'videos.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.videos.index'],
                        'patterns' => ['coordenador.videos.*'],
                    ],
                    [
                        'label' => 'Onde ficar',
                        'permission' => 'onde_ficar.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.onde_ficar.edit'],
                        'patterns' => ['coordenador.onde_ficar.*'],
                    ],
                    [
                        'label' => 'Eventos',
                        'permission' => 'eventos.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.eventos.index'],
                        'patterns' => ['coordenador.eventos.*'],
                    ],
                    [
                        'label' => 'Jogos Indigenas',
                        'permission' => 'jogos_indigenas.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.jogos-indigenas.index'],
                        'patterns' => ['coordenador.jogos-indigenas.*'],
                    ],
                    [
                        'label' => 'Rota do Cacau',
                        'permission' => 'rota_do_cacau.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.rota-do-cacau.index', 'coordenador.rota_do_cacau.index'],
                        'patterns' => ['coordenador.rota-do-cacau.*', 'coordenador.rota_do_cacau.*'],
                    ],
                    [
                        'label' => 'Secretaria',
                        'permission' => 'secretaria.edit',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.secretaria.edit'],
                        'patterns' => ['coordenador.secretaria.*'],
                    ],
                    [
                        'label' => 'Equipe',
                        'permission' => 'equipe.manage',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.equipe.index'],
                        'patterns' => ['coordenador.equipe.*'],
                    ],
                ],
            ],
            [
                'title' => 'Operação',
                'items' => [
                    [
                        'label' => 'Relatórios',
                        'permission' => 'relatorios.view',
                        'roles' => ['Tecnico'],
                        'routes' => ['coordenador.coord.relatorios.index'],
                        'patterns' => ['coordenador.coord.relatorios.*'],
                    ],
                ],
            ],
            [
                'title' => 'Conta',
                'items' => [
                    [
                        'label' => 'Meu perfil',
                        'permission' => null,
                        'roles' => ['Tecnico'],
                        'routes' => ['tecnico.config.perfil.edit', 'tecnico.config.perfil', 'tecnico.perfil.edit', 'tecnico.profile.edit'],
                        'patterns' => ['tecnico.config.perfil.*', 'tecnico.perfil.*', 'tecnico.profile.*'],
                        'url_fallback' => url('/tecnico/config/perfil'),
                    ],
                ],
            ],
        ];
    } else {
        $menuSections = [];
    }

    $iconFor = function (array $item): string {
        $patterns = implode(' ', $item['patterns'] ?? []);
        $label = mb_strtolower($item['label'] ?? '');

        return match (true) {
            str_contains($patterns, 'dashboard') => 'dashboard',
            str_contains($patterns, 'usuarios') || str_contains($label, 'usu') => 'users',
            str_contains($patterns, 'temas') || str_contains($label, 'tema') => 'sparkles',
            str_contains($patterns, 'backups') || str_contains($label, 'backup') => 'reports',
            str_contains($patterns, 'tecnicos') || str_contains($label, 'técnic') || str_contains($label, 'tecnic') => 'team',
            str_contains($patterns, 'relatorios') || str_contains($label, 'relat') => 'reports',
            str_contains($patterns, 'banners-destaque') || str_contains($patterns, 'banners_destaque') => 'sparkles',
            str_contains($patterns, 'banners') => 'image',
            str_contains($patterns, 'avisos') => 'bell',
            str_contains($patterns, 'categorias') => 'grid',
            str_contains($patterns, 'empresas') => 'briefcase',
            str_contains($patterns, 'pontos') => 'pin',
            str_contains($patterns, 'roteiros') => 'route',
            str_contains($patterns, 'guias') => 'book',
            str_contains($patterns, 'espacos-culturais') || str_contains($patterns, 'espacos_culturais') => 'building',
            str_contains($patterns, 'onde-comer') || str_contains($patterns, 'onde_comer') => 'fork',
            str_contains($patterns, 'onde-ficar') || str_contains($patterns, 'onde_ficar') => 'bed',
            str_contains($patterns, 'videos') => 'video',
            str_contains($patterns, 'eventos') => 'calendar',
            str_contains($patterns, 'jogos-indigenas') || str_contains($patterns, 'jogos_indigenas') || str_contains($label, 'jogos ind') => 'calendar',
            str_contains($patterns, 'rota-do-cacau') || str_contains($patterns, 'rota_do_cacau') || str_contains($label, 'rota do cacau') => 'route',
            str_contains($patterns, 'secretaria') => 'landmark',
            str_contains($patterns, 'equipe') => 'team',
            str_contains($patterns, 'perfil') || str_contains($patterns, 'profile') => 'user',
            default => 'circle',
        };
    };

    $visibleSections = collect($menuSections)->map(function ($section) use ($resolveRoute, $routeExists, $canAccess, $iconFor) {
        $items = collect($section['items'])->map(function ($item) use ($resolveRoute, $routeExists, $canAccess, $iconFor) {
            $hasRoute = $routeExists($item['routes']);
            $hasFallback = ! empty($item['url_fallback']);

            if (! $hasRoute && ! $hasFallback) {
                return null;
            }

            if (! $canAccess($item['permission'] ?? null, $item['roles'] ?? null)) {
                return null;
            }

            $item['url'] = $resolveRoute($item['routes']) ?: ($item['url_fallback'] ?? null);
            $item['icon'] = $iconFor($item);

            return $item;
        })->filter()->values();

        if ($items->isEmpty()) {
            return null;
        }

        return [
            'title' => $section['title'],
            'items' => $items,
        ];
    })->filter()->values();

    $pageTitle = trim($__env->yieldContent('title')) ?: 'Dashboard';
    $pageKicker = $roleBase;
    $topbarDescription = trim($__env->yieldContent('topbar.description')) ?: 'Console institucional refinado, delicado e pronto para evolução temática.';
    $topbarNav = trim($__env->yieldContent('topbar.nav'));
    $topbarMeta = trim($__env->yieldContent('topbar.meta'));
    $topbarActions = trim($__env->yieldContent('topbar.actions'));
@endphp

<div class="ui-console-stage">
    <div class="ui-console-frame">
        <aside class="ui-console-sidebar" :class="consoleNavOpen ? 'is-open' : ''">
            <div class="ui-console-sidebar-rail">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ $dashboardRoute ?: '#' }}" class="ui-console-sidebar-brand">
                        <span class="ui-console-logo-wrap">
                            <img
                                src="{{ theme_asset('logo') }}"
                                alt="Logo SEMTUR"
                                class="ui-console-logo"
                            >
                        </span>

                        <span class="min-w-0">
                            <span class="block truncate text-[15px] font-semibold tracking-[-0.02em]">SEMTUR</span>
                            <span class="mt-0.5 block truncate text-xs text-[var(--ui-sidebar-text-soft)]">Painel institucional</span>
                        </span>
                    </a>

                    <button type="button" class="ui-console-sidebar-close lg:hidden" @click="consoleNavOpen = false" aria-label="Fechar navegação">
                        <svg viewBox="0 0 20 20" fill="none">
                            <path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>


                <div class="ui-console-sidebar-scroll">
                    @foreach($visibleSections as $section)
                        <div class="mb-6">
                            <div class="ui-console-sidebar-section">
                                {{ $section['title'] }}
                            </div>

                            <div class="space-y-1.5">
                                @foreach($section['items'] as $item)
                                    @php
                                        $active = $isRouteActive($item['patterns'] ?? []);
                                    @endphp

                                    <a href="{{ $item['url'] }}" class="ui-console-nav-link {{ $active ? 'is-active' : '' }}">
                                        <span class="ui-console-nav-icon" aria-hidden="true">
                                            @switch($item['icon'])
                                                @case('dashboard')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M3 4.75C3 3.78 3.78 3 4.75 3h3.5c.97 0 1.75.78 1.75 1.75v3.5C10 9.22 9.22 10 8.25 10h-3.5A1.75 1.75 0 0 1 3 8.25v-3.5ZM10 11.75c0-.97.78-1.75 1.75-1.75h3.5c.97 0 1.75.78 1.75 1.75v3.5A1.75 1.75 0 0 1 15.25 17h-3.5A1.75 1.75 0 0 1 10 15.25v-3.5ZM10 4.75C10 3.78 10.78 3 11.75 3h3.5C16.22 3 17 3.78 17 4.75v1.5C17 7.22 16.22 8 15.25 8h-3.5A1.75 1.75 0 0 1 10 6.25v-1.5ZM3 13.75C3 12.78 3.78 12 4.75 12h3.5c.97 0 1.75.78 1.75 1.75v1.5C10 16.22 9.22 17 8.25 17h-3.5A1.75 1.75 0 0 1 3 15.25v-1.5Z" stroke="currentColor" stroke-width="1.4"/></svg>
                                                    @break
                                                @case('users')
                                                @case('team')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M7 8.25a2.75 2.75 0 1 0 0-5.5 2.75 2.75 0 0 0 0 5.5ZM13.75 9.5a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5ZM2.75 15.75A3.75 3.75 0 0 1 6.5 12h1A3.75 3.75 0 0 1 11.25 15.75V17h-8.5v-1.25ZM11 17v-.75a3.25 3.25 0 0 1 3.25-3.25h.5A3.25 3.25 0 0 1 18 16.25V17H11Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('reports')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M5.75 3.5h6.69c.4 0 .78.16 1.06.44l2.56 2.56c.28.28.44.66.44 1.06v8.69A1.75 1.75 0 0 1 14.75 18h-9A1.75 1.75 0 0 1 4 16.25v-11A1.75 1.75 0 0 1 5.75 3.5Z" stroke="currentColor" stroke-width="1.4"/><path d="M7 8.25h6M7 11h6M7 13.75h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('sparkles')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="m10 2 1.68 4.32L16 8l-4.32 1.68L10 14l-1.68-4.32L4 8l4.32-1.68L10 2ZM15.5 13l.84 2.16L18.5 16l-2.16.84L15.5 19l-.84-2.16L12.5 16l2.16-.84L15.5 13ZM4.5 12l.6 1.54 1.54.6-1.54.6L4.5 16.3l-.6-1.56-1.56-.6 1.56-.6.6-1.54Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('image')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M4.75 4h10.5C16.22 4 17 4.78 17 5.75v8.5A1.75 1.75 0 0 1 15.25 16H4.75A1.75 1.75 0 0 1 3 14.25v-8.5C3 4.78 3.78 4 4.75 4Z" stroke="currentColor" stroke-width="1.4"/><path d="m5.5 13 2.5-2.5 2 2 2.75-3.25L14.5 11 16 13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7" cy="7" r="1" fill="currentColor"/></svg>
                                                    @break
                                                @case('bell')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M10 3.25a3.5 3.5 0 0 0-3.5 3.5v1.03c0 .73-.2 1.45-.58 2.08L4.75 11.8a1.25 1.25 0 0 0 1.07 1.95h8.36a1.25 1.25 0 0 0 1.07-1.95l-1.17-1.94a4 4 0 0 1-.58-2.08V6.75A3.5 3.5 0 0 0 10 3.25Z" stroke="currentColor" stroke-width="1.4"/><path d="M8.25 15.25a1.75 1.75 0 0 0 3.5 0" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('grid')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M4.75 4h3.5v3.5h-3.5V4ZM11.75 4h3.5v3.5h-3.5V4ZM4.75 11h3.5v5h-3.5v-5ZM11.75 11h3.5v5h-3.5v-5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('briefcase')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M7.25 5.25V4.5A1.5 1.5 0 0 1 8.75 3h2.5a1.5 1.5 0 0 1 1.5 1.5v.75M3.5 8h13v6.25A1.75 1.75 0 0 1 14.75 16h-9A1.75 1.75 0 0 1 4 14.25V8h-.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M3.5 8V6.75C3.5 5.78 4.28 5 5.25 5h9.5c.97 0 1.75.78 1.75 1.75V8" stroke="currentColor" stroke-width="1.4"/><path d="M8 10.5h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('pin')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M10 17s4.5-4.2 4.5-8A4.5 4.5 0 1 0 5.5 9c0 3.8 4.5 8 4.5 8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><circle cx="10" cy="9" r="1.75" stroke="currentColor" stroke-width="1.4"/></svg>
                                                    @break
                                                @case('route')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M5 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM18 15.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM3.5 6c0 5 6 1.5 6 6.5S15.5 10 15.5 14" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('book')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M5.25 4h8.5A1.75 1.75 0 0 1 15.5 5.75V16H6.75A1.75 1.75 0 0 0 5 17.75V5.25C5 4.56 5.56 4 6.25 4Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M5 16.5A1.5 1.5 0 0 1 6.5 15h9" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('building')
                                                @case('landmark')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M4.75 17V6.25L10 3l5.25 3.25V17M7 8.25h.01M10 8.25h.01M13 8.25h.01M7 11.5h.01M10 11.5h.01M13 11.5h.01M8.5 17v-2.75h3V17" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('fork')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M6.5 3v6.25M4.5 3v3.25a2 2 0 0 0 4 0V3M11.5 3v4.25a2.25 2.25 0 0 0 2.25 2.25h1V17" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('bed')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M3 14.5V8.75C3 7.78 3.78 7 4.75 7h10.5C16.22 7 17 7.78 17 8.75v5.75M3 12h14M5.5 10.25h2.25a1.25 1.25 0 1 0 0-2.5H5.5v2.5ZM3 17v-2.5M17 17v-2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('video')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M4.75 5h7.5C13.22 5 14 5.78 14 6.75v6.5A1.75 1.75 0 0 1 12.25 15h-7.5A1.75 1.75 0 0 1 3 13.25v-6.5C3 5.78 3.78 5 4.75 5Z" stroke="currentColor" stroke-width="1.4"/><path d="m14 8.25 3-1.75v7l-3-1.75V8.25Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                                                    @break
                                                @case('calendar')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M6.5 3v2.5M13.5 3v2.5M4.75 5h10.5C16.22 5 17 5.78 17 6.75v8.5A1.75 1.75 0 0 1 15.25 17H4.75A1.75 1.75 0 0 1 3 15.25v-8.5C3 5.78 3.78 5 4.75 5Z" stroke="currentColor" stroke-width="1.4"/><path d="M3 8h14" stroke="currentColor" stroke-width="1.4"/><path d="M7 11h.01M10 11h.01M13 11h.01M7 14h.01M10 14h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    @break
                                                @case('user')
                                                    <svg viewBox="0 0 20 20" fill="none"><path d="M10 9a3.25 3.25 0 1 0 0-6.5A3.25 3.25 0 0 0 10 9ZM4 16.25A4.75 4.75 0 0 1 8.75 11.5h2.5A4.75 4.75 0 0 1 16 16.25V17H4v-.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                                                    @break
                                                @default
                                                    <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="3.25" stroke="currentColor" stroke-width="1.4"/></svg>
                                            @endswitch
                                        </span>
                                        <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                        <span class="ui-console-nav-chevron" aria-hidden="true">
                                            <svg viewBox="0 0 20 20" fill="none"><path d="m8 6 4 4-4 4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="ui-console-sidebar-footer">
                    @if(false && $profileUrl)
                        <a href="{{ $profileUrl }}" class="ui-console-footer-link">
                            <span class="ui-console-footer-link-label">Perfil</span>
                            <span class="ui-console-footer-link-meta">Preferências e conta</span>
                        </a>
                    @endif

                    @if (\Illuminate\Support\Facades\Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="ui-console-logout-button">
                                <span class="ui-console-logout-icon" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none">
                                        <path d="M7.5 4.75H6.25A2.25 2.25 0 0 0 4 7v6a2.25 2.25 0 0 0 2.25 2.25H7.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 6.5 15.5 10 12 13.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M8 10h7.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>
                                    <span class="ui-console-logout-title">Sair</span>
                                    <span class="ui-console-logout-copy">Encerrar sessão atual</span>
                                </span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </aside>

        <div class="ui-console-sidebar-backdrop lg:hidden" x-show="consoleNavOpen" x-transition.opacity @click="consoleNavOpen = false" x-cloak aria-hidden="true"></div>

        <main class="ui-console-main">
            <div class="ui-console-main-inner">
                <div class="ui-console-shell-container">
                    <button type="button" class="ui-console-mobile-toggle lg:hidden" @click="consoleNavOpen = true">
                        <span class="ui-console-mobile-toggle-icon" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="none">
                                <path d="M3.5 5.5h13M3.5 10h13M3.5 14.5h13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Navegação</span>
                    </button>

                    <x-console.topbar
                        :title="$pageTitle"
                        :kicker="$pageKicker"
                        :description="$topbarDescription"
                        :nav="$topbarNav"
                        :meta="$topbarMeta"
                        :actions="$topbarActions"
                        :profile-url="$profileUrl"
                    />

                    @if(session('ok'))
                        <div class="ui-alert ui-alert-success mt-4">
                            {{ session('ok') }}
                        </div>
                    @endif

                    @if(session('erro'))
                        <div class="ui-alert ui-alert-danger mt-4">
                            {{ session('erro') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="ui-alert ui-alert-warning mt-4">
                            <div class="font-semibold">Existem ajustes pendentes.</div>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="ui-console-content">
                        @yield('content')
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

@stack('scripts')
<script>
    (function () {
        var key = 'console-mode';
        var root = document.documentElement;
        var locksMode = @json((bool) ($resolvedThemeHasCustomConsoleTheme ?? false));

        function getMode() {
            return root.dataset.consoleMode === 'dark' ? 'dark' : 'light';
        }

        function setMode(mode) {
            root.dataset.consoleMode = mode;

            try {
                window.localStorage.setItem(key, locksMode ? 'light' : mode);
            } catch (error) {}

            syncButtons();
        }

        function syncButtons() {
            var mode = getMode();
            var isDark = mode === 'dark';

            document.querySelectorAll('[data-console-mode-toggle]').forEach(function (button) {
                button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                button.setAttribute('title', isDark ? 'Modo escuro ativo. Trocar para claro.' : 'Modo claro ativo. Trocar para escuro.');
                button.setAttribute('aria-label', isDark ? 'Modo escuro ativo. Alternar para claro.' : 'Modo claro ativo. Alternar para escuro.');
            });
        }

        if (!locksMode) {
            document.querySelectorAll('[data-console-mode-toggle]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var next = getMode() === 'dark' ? 'light' : 'dark';
                    setMode(next);
                });
            });
        } else {
            root.dataset.consoleMode = 'light';
        }

        window.addEventListener('storage', function (event) {
            if (locksMode) {
                return;
            }

            if (event.key === key && (event.newValue === 'light' || event.newValue === 'dark')) {
                root.dataset.consoleMode = event.newValue;
                syncButtons();
            }
        });

        syncButtons();
    })();
</script>
</body>
</html>
