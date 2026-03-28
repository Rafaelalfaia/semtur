@extends('layouts.app')

@php
    $criticalSiteImage = trim($__env->yieldContent('meta.image', theme_asset('hero_image')));
    $criticalSiteImagePreload = site_image_url($criticalSiteImage, 'hero');
@endphp

@push('head')
    <x-seo
        :title="trim($__env->yieldContent('title', 'VisitAltamira'))"
        :description="trim($__env->yieldContent('meta.description', 'Portal turístico de Altamira'))"
        :image="trim($__env->yieldContent('meta.image', theme_asset('hero_image')))"
        :canonical="trim($__env->yieldContent('meta.canonical', url()->current()))"
        :type="trim($__env->yieldContent('meta.type', 'website'))"
        :noindex="in_array(strtolower(trim($__env->yieldContent('meta.noindex', 'false'))), ['1', 'true', 'yes', 'on'], true)"
    >
        @stack('structured-data')
    </x-seo>

    @if(filled($criticalSiteImagePreload))
        <link rel="preload" as="image" href="{{ $criticalSiteImagePreload }}">
    @endif

    <style>
        @if(! empty($resolvedThemeCssVariables))
        :root {
            @foreach($resolvedThemeCssVariables as $variable => $value)
            {{ $variable }}: {{ $value }};
            @endforeach
        }
        @endif
    </style>
@endpush

@section('content')
    <div class="site-shell" data-site-theme="{{ $resolvedThemeDataTheme ?? 'default' }}">
        @include('site.partials._top_nav')

        <main class="site-main">
            @yield('site.content')
        </main>

        @include('site.partials._footer')
        @include('site.partials._bottom_nav')
    </div>

    <x-aviso-popup :aviso="$aviso ?? null" />

    <script>
        (() => {
            const shell = document.querySelector('.site-shell');
            const topbar = document.querySelector('.site-topbar');

            if (!shell || !topbar) {
                return;
            }

            const mobileQuery = window.matchMedia('(max-width: 1023px)');
            let lastScrollY = window.scrollY;

            const syncTopbarState = () => {
                if (!mobileQuery.matches) {
                    shell.classList.remove('is-mobile-scrolling-down');
                    lastScrollY = window.scrollY;
                    return;
                }

                const currentY = window.scrollY;
                const delta = currentY - lastScrollY;

                if (currentY <= 24 || delta < -8) {
                    shell.classList.remove('is-mobile-scrolling-down');
                } else if (delta > 8 && currentY > 72) {
                    shell.classList.add('is-mobile-scrolling-down');
                }

                lastScrollY = currentY;
            };

            window.addEventListener('scroll', syncTopbarState, { passive: true });
            window.addEventListener('resize', syncTopbarState, { passive: true });
            mobileQuery.addEventListener?.('change', syncTopbarState);
            syncTopbarState();
        })();
    </script>

    @stack('scripts')
@endsection
