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
    <div class="site-shell" data-site-theme="{{ $resolvedThemeDataTheme ?? 'default' }}" data-desktop-frame="bleed">
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
            const desktopFrameToggle = document.querySelector('[data-desktop-frame-toggle]');

            if (!shell || !topbar) {
                return;
            }

            const mobileQuery = window.matchMedia('(max-width: 1023px)');
            const desktopQuery = window.matchMedia('(min-width: 1024px)');
            const storageKey = 'siteDesktopFrameMode';
            let lastScrollY = window.scrollY;

            const readDesktopFrame = () => {
                try {
                    const saved = window.localStorage.getItem(storageKey);
                    return saved === 'contained' ? 'contained' : 'bleed';
                } catch (error) {
                    return 'bleed';
                }
            };

            const writeDesktopFrame = (value) => {
                try {
                    window.localStorage.setItem(storageKey, value);
                } catch (error) {
                    // ignore storage failures for public desktop preference
                }
            };

            const syncDesktopFrameButton = (button = desktopFrameToggle) => {
                if (!button) {
                    return;
                }

                const mode = shell.dataset.desktopFrame === 'bleed' ? 'bleed' : 'contained';
                const nextLabel = mode === 'bleed'
                    ? button.dataset.labelBleed
                    : button.dataset.labelContained;
                const labelNode = button.querySelector('.site-desktop-frame-toggle-label');

                button.setAttribute('aria-pressed', mode === 'bleed' ? 'true' : 'false');
                if (labelNode) {
                    labelNode.textContent = nextLabel || '';
                }
            };

            const applyDesktopFrame = (value, persist = false, button = desktopFrameToggle) => {
                const mode = value === 'bleed' ? 'bleed' : 'contained';
                shell.setAttribute('data-desktop-frame', mode);
                document.documentElement.setAttribute('data-site-desktop-frame', mode);

                if (persist) {
                    writeDesktopFrame(mode);
                }

                syncDesktopFrameButton(button);
            };

            const toggleDesktopFrame = (button = desktopFrameToggle) => {
                if (!desktopQuery.matches) {
                    return;
                }

                const currentMode = shell.dataset.desktopFrame === 'bleed' ? 'bleed' : 'contained';
                applyDesktopFrame(currentMode === 'bleed' ? 'contained' : 'bleed', true, button);
            };

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

            window.toggleSiteDesktopFrame = toggleDesktopFrame;

            window.addEventListener('scroll', syncTopbarState, { passive: true });
            window.addEventListener('resize', syncTopbarState, { passive: true });
            mobileQuery.addEventListener?.('change', syncTopbarState);
            desktopQuery.addEventListener?.('change', () => syncDesktopFrameButton(desktopFrameToggle));

            applyDesktopFrame(readDesktopFrame());
            syncTopbarState();
        })();
    </script>

    @stack('scripts')
@endsection
