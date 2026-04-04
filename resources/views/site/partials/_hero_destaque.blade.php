@props([
    'banner' => null,
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
    'ctaLabel' => null,
    'href' => null,
    'secondaryCtaLabel' => null,
    'secondaryHref' => null,
    'overlayImage' => null,
    'overlayImageAlt' => null,
    'overlayOnly' => false,
    'heroClass' => null,
    'contentVisible' => true,
    'textEditor' => null,
    'imageEditor' => null,
    'sectionActions' => [],
])

@php
    $fallbackHero = theme_asset('hero_image');
    $hasPosterDesktopResolver = is_object($banner) && method_exists($banner, 'resolvedPosterDesktopUrl');
    $hasPosterMobileResolver = is_object($banner) && method_exists($banner, 'resolvedPosterMobileUrl');
    $hasValidVideoResolver = is_object($banner) && method_exists($banner, 'hasValidVideo');

    $desktopFallbackImage = $banner?->fallback_image_desktop_url
        ?? $banner?->imagem_desktop_url
        ?? $banner?->imagem_url
        ?? $banner?->fallback_image_mobile_url
        ?? $banner?->imagem_mobile_url
        ?? $banner?->imagem_url
        ?? $fallbackHero;

    $mobileFallbackImage = $banner?->fallback_image_mobile_url
        ?? $banner?->imagem_mobile_url
        ?? $banner?->imagem_url
        ?? $banner?->fallback_image_desktop_url
        ?? $banner?->imagem_desktop_url
        ?? $banner?->imagem_url
        ?? $desktopFallbackImage;

    $desktopPoster = $hasPosterDesktopResolver
        ? ($banner->resolvedPosterDesktopUrl($desktopFallbackImage) ?? $desktopFallbackImage)
        : $desktopFallbackImage;
    $mobilePoster = $hasPosterMobileResolver
        ? ($banner->resolvedPosterMobileUrl($mobileFallbackImage) ?? $mobileFallbackImage)
        : $mobileFallbackImage;

    $desktopFallbackImage = site_image_url($desktopFallbackImage, 'hero');
    $mobileFallbackImage = site_image_url($mobileFallbackImage, 'hero-mobile');
    $desktopPoster = site_image_url($desktopPoster, 'hero');
    $mobilePoster = site_image_url($mobilePoster, 'hero-mobile');

    $desktopVideo = $banner?->video_desktop_url;
    $mobileVideo = $banner?->video_mobile_url ?: $desktopVideo;

    $isVideoHero = $hasValidVideoResolver
        ? (bool) ($banner?->media_type === 'video' && $banner->hasValidVideo())
        : (bool) (($banner?->media_type === 'video') && ($desktopVideo || $mobileVideo));
    $resolvedEyebrow = $eyebrow ?? 'Destino oficial';
    $resolvedTitle = $title ?? $banner?->titulo ?? 'VisitAltamira';
    $resolvedSubtitle = $subtitle
        ?? $banner?->subtitulo
        ?? null;

    $resolvedHref = $href
        ?? $banner?->href
        ?? $banner?->cta_url
        ?? $banner?->link_url
        ?? null;

    $resolvedCta = $ctaLabel ?? $banner?->cta_label ?? null;
    $resolvedSecondaryHref = $secondaryHref ?? null;
    $resolvedSecondaryCta = $secondaryCtaLabel ?? null;

    $resolvedOverlayImage = $overlayImage ?: asset('imagens/visitcapa.png');
    $resolvedOverlayAlt = $overlayImageAlt ?: 'VisitAltamira';
    $alt = $banner?->alt_text ?: $resolvedTitle;
    $preload = $banner?->preload_mode ?: 'metadata';

    $variantClass = trim(implode(' ', array_filter([
        $banner?->hero_variant === 'hero_short' ? 'site-hero-short' : null,
        $isVideoHero ? 'site-hero-has-video' : 'site-hero-has-image',
        $overlayOnly ? 'site-hero-cover' : null,
        $heroClass,
    ])));

    $isImmersiveHero = str_contains((string) $variantClass, 'site-hero-home-immersive')
        || str_contains((string) $variantClass, 'site-hero-cover');
@endphp

<section
    class="site-hero {{ $variantClass }} {{ $isVideoHero ? 'is-video-loading' : '' }}"
    x-data="{
        reduceMotion: false,
        frame: null,
        heroMediaState: {{ $isVideoHero ? '\'loading\'' : '\'image\'' }},
        isImmersive: {{ $isImmersiveHero ? 'true' : 'false' }},
        syncHeroMediaState() {
            const layers = Array.from(this.$el.querySelectorAll('[data-hero-video-layer]'));
            const activeLayer = layers.find((layer) => window.getComputedStyle(layer).display !== 'none') || layers[0] || null;

            this.$el.classList.remove('is-video-loading', 'is-video-ready', 'is-video-error');

            if (!activeLayer) {
                return;
            }

            if (activeLayer.classList.contains('is-video-ready')) {
                this.heroMediaState = 'ready';
                this.$el.classList.add('is-video-ready');
                return;
            }

            if (activeLayer.classList.contains('is-video-error')) {
                this.heroMediaState = 'error';
                this.$el.classList.add('is-video-error');
                return;
            }

            this.heroMediaState = 'loading';
            this.$el.classList.add('is-video-loading');
        },
        markVideoReady(video) {
            const layer = video.closest('[data-hero-video-layer]');
            if (!layer || layer.classList.contains('is-video-ready')) {
                return;
            }

            layer.classList.remove('is-video-error', 'is-video-loading');
            layer.classList.add('is-video-ready');
            this.syncHeroMediaState();
        },
        markVideoError(video) {
            const layer = video.closest('[data-hero-video-layer]');
            if (!layer) {
                return;
            }

            layer.classList.remove('is-video-ready', 'is-video-loading');
            layer.classList.add('is-video-error');
            this.syncHeroMediaState();
        },
        applyScrollState() {
            if (!this.isImmersive || this.reduceMotion) {
                this.$el.style.setProperty('--site-hero-progress', '0');
                this.$el.style.setProperty('--site-hero-media-scale', '1.015');
                this.$el.style.setProperty('--site-hero-media-shift', '0px');
                this.$el.style.setProperty('--site-hero-overlay-shift', '0px');
                this.$el.style.setProperty('--site-hero-fade', '1');
                return;
            }

            const rect = this.$el.getBoundingClientRect();
            const distance = Math.min(Math.max(-rect.top / Math.max(rect.height, 1), 0), 1);
            const progress = Math.min(Math.max(distance * 0.82, 0), 1);

            this.$el.style.setProperty('--site-hero-progress', progress.toFixed(3));
            this.$el.style.setProperty('--site-hero-media-scale', (1.02 + (progress * 0.04)).toFixed(3));
            this.$el.style.setProperty('--site-hero-media-shift', `${Math.round(progress * -34)}px`);
            this.$el.style.setProperty('--site-hero-overlay-shift', `${Math.round(progress * -14)}px`);
            this.$el.style.setProperty('--site-hero-fade', Math.max(0.46, 1 - (progress * 0.54)).toFixed(3));
        },
        handleScroll() {
            if (this.frame) return;
            this.frame = window.requestAnimationFrame(() => {
                this.frame = null;
                this.applyScrollState();
            });
        },
        init() {
            this.reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const videos = Array.from(this.$el.querySelectorAll('[data-hero-video]')).filter((video) => video.currentSrc || video.querySelector('source'));
            videos.forEach((video) => {
                const layer = video.closest('[data-hero-video-layer]');
                if (layer) {
                    layer.classList.add('is-video-loading');
                }

                const handleReady = () => this.markVideoReady(video);
                const handleError = () => this.markVideoError(video);

                ['loadeddata', 'canplay', 'playing'].forEach((eventName) => {
                    video.addEventListener(eventName, handleReady, { passive: true });
                });

                video.addEventListener('error', handleError, { passive: true });

                if (video.readyState >= 2) {
                    this.markVideoReady(video);
                }
            });

            if (videos.length && 'IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        const video = entry.target;

                        if (entry.isIntersecting) {
                            const promise = video.play?.();
                            if (promise && typeof promise.catch === 'function') {
                                promise.catch(() => {});
                            }
                            return;
                        }

                        video.pause?.();
                    });
                }, { threshold: 0.2 });

                videos.forEach((video) => observer.observe(video));
            }

            this.syncHeroMediaState();
            this.applyScrollState();

            if (this.isImmersive && !this.reduceMotion) {
                window.addEventListener('scroll', () => this.handleScroll(), { passive: true });
                window.addEventListener('resize', () => this.handleScroll(), { passive: true });
            }

            if (videos.length) {
                window.addEventListener('resize', () => this.syncHeroMediaState(), { passive: true });
            }
        }
    }"
>
    @php
        $renderVideoLayer = function (
            string $wrapperClass,
            ?string $videoUrl,
            ?string $posterUrl,
            string $altText,
            bool $hidden = false
        ) use ($preload) {
            if (! $videoUrl) {
                return '';
            }

            $hiddenClass = $hidden ? ' hidden' : '';

            return <<<HTML
                <div class="site-hero-video-layer is-video-loading {$wrapperClass}{$hiddenClass}" data-hero-video-layer>
                    <video
                        data-hero-video
                        class="site-hero-video"
                        poster="{$posterUrl}"
                        preload="{$preload}"
                        autoplay
                        loop
                        muted
                        playsinline
                    >
                        <source src="{$videoUrl}" type="video/mp4">
                    </video>
                    <img src="{$posterUrl}" alt="{$altText}" loading="eager" decoding="async" class="site-hero-poster" aria-hidden="true">
                </div>
            HTML;
        };
    @endphp

    <div class="site-hero-media" aria-hidden="true">
        @if($isVideoHero)
            {!! $renderVideoLayer('site-hero-desktop hidden md:block', $desktopVideo, $desktopPoster, $alt) !!}

            {!! $renderVideoLayer('site-hero-mobile md:hidden', $mobileVideo, $mobilePoster, $alt) !!}

            @if(! $desktopVideo && ! $mobileVideo)
                <picture class="site-hero-picture">
                    <source media="(max-width: 767px)" srcset="{{ $mobileFallbackImage }}">
                    <img src="{{ $desktopFallbackImage }}" alt="{{ $alt }}" loading="eager" decoding="async" class="site-hero-image">
                </picture>
            @endif
        @else
            <picture class="site-hero-picture">
                <source media="(max-width: 767px)" srcset="{{ $mobileFallbackImage }}">
                <img src="{{ $desktopFallbackImage }}" alt="{{ $alt }}" loading="eager" decoding="async" class="site-hero-image">
            </picture>
        @endif
    </div>

    <div class="site-hero-overlay">
    <div class="site-hero-veil" aria-hidden="true"></div>

    @if(!empty($imageEditor))
        <div class="site-hero-editor site-hero-editor--image">
            @include('site.partials._content_editor', [
                'editorTitle' => $imageEditor['title'] ?? $resolvedTitle,
                'editorPage' => $imageEditor['page'] ?? 'site.home',
                'editorKey' => $imageEditor['key'] ?? 'hero',
                'editorLabel' => $imageEditor['label'] ?? 'Imagem do banner',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline-compact',
                'editorTriggerLabel' => $imageEditor['trigger_label'] ?? 'Editar imagem',
                'editorFields' => ['media'],
                'editableTranslation' => $imageEditor['translation'] ?? null,
                'editableMedia' => $imageEditor['media'] ?? null,
                'editableStatus' => $imageEditor['status'] ?? 'publicado',
                'editorMediaSlot' => $imageEditor['media_slot'] ?? 'hero',
                'editorMediaLabel' => $imageEditor['media_label'] ?? 'Imagem do banner',
                'editorMediaPreviewLabel' => $imageEditor['preview_label'] ?? 'imagem atual do banner',
                'editableFallback' => [
                    'titulo' => $resolvedTitle,
                ],
            ])
        </div>
    @endif

    @if(collect($sectionActions)->isNotEmpty())
        <div class="site-hero-editor">
            <div class="site-inline-actions">
                @foreach($sectionActions as $action)
                    @if(!empty($action['href']) && !empty($action['label']))
                        <a href="{{ $action['href'] }}" class="{{ $action['class'] ?? 'site-button-secondary' }}">{{ $action['label'] }}</a>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @unless($overlayOnly)
        <div class="site-hero-reading-gradient" aria-hidden="true"></div>
        <div class="site-hero-glow" aria-hidden="true"></div>
    @endunless

    @if($overlayOnly)
        <h1 class="sr-only">{{ $resolvedTitle }}</h1>

        <div class="site-hero-brand-mark">
            <span class="site-hero-brand-mark-glow" aria-hidden="true"></span>
            <img
                src="{{ $resolvedOverlayImage }}"
                alt="{{ $resolvedOverlayAlt }}"
                loading="eager"
                fetchpriority="high"
                decoding="async"
                draggable="false"
            >
        </div>
    @elseif($contentVisible)
        <div class="site-hero-copy site-hero-content site-hero-content-signature">
            @if(!empty($textEditor))
                <div class="site-hero-editor site-hero-editor--text">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $textEditor['title'] ?? $resolvedTitle,
                        'editorPage' => $textEditor['page'] ?? 'site.home',
                        'editorKey' => $textEditor['key'] ?? 'hero',
                        'editorLabel' => $textEditor['label'] ?? 'Texto do banner',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => $textEditor['trigger_label'] ?? 'Editar texto',
                        'editorFields' => $textEditor['fields'] ?? ['eyebrow', 'titulo', 'cta_label', 'cta_href'],
                        'editableTranslation' => $textEditor['translation'] ?? null,
                        'editableStatus' => $textEditor['status'] ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => $resolvedEyebrow,
                            'titulo' => $resolvedTitle,
                            'subtitulo' => $resolvedSubtitle,
                            'cta_label' => $resolvedCta,
                            'cta_href' => $resolvedHref,
                        ],
                    ])
                </div>
            @endif

            <p class="site-badge site-hero-badge">{{ $resolvedEyebrow }}</p>

            <div class="site-hero-copy-stack">
                <h1 class="site-hero-title">{{ $resolvedTitle }}</h1>

                @if($resolvedSubtitle)
                    <p class="site-hero-subtitle">{{ $resolvedSubtitle }}</p>
                @endif
            </div>

            @if($resolvedHref || $resolvedSecondaryHref)
                <div class="site-hero-cta">
                    @if($resolvedHref && $resolvedCta)
                        <a href="{{ $resolvedHref }}" class="site-button-primary">
                            {{ $resolvedCta }}
                        </a>
                    @endif

                    @if($resolvedSecondaryHref && $resolvedSecondaryCta)
                        <a href="{{ $resolvedSecondaryHref }}" class="site-button-secondary">
                            {{ $resolvedSecondaryCta }}
                        </a>
                    @endif
                </div>
            @endif


        </div>
    @endif
</div>
</section>
