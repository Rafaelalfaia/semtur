@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route as R;

    $homeCanonical = localized_route('site.home');
    $homeTitle = __('ui.home.title');
    $homeDescription = __('ui.home.description');
    $homeImage = theme_asset('hero_image');
    $homeSchema = [
        [
            '@type' => 'TouristDestination',
            '@id' => $homeCanonical.'#destination',
            'name' => 'Altamira',
            'url' => $homeCanonical,
            'description' => $homeDescription,
            'image' => $homeImage,
            'touristType' => [
                __('ui.home.tourist_nature'),
                __('ui.home.tourist_cultural'),
                __('ui.home.tourist_xingu'),
            ],
            'containedInPlace' => [
                '@type' => 'State',
                'name' => 'Para',
            ],
        ],
    ];
@endphp

@section('title', $homeTitle)
@section('meta.description', $homeDescription)
@section('meta.image', $homeImage)
@section('meta.canonical', $homeCanonical)
@section('meta.type', 'website')

@push('structured-data')
<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $homeSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@section('site.content')
@php
    $pontosDestaque = collect($pontosDestaque ?? []);
    $recomendacoes = collect($recomendacoes ?? []);
    $videosHome = collect($videosHome ?? []);
    $instagram = collect($instagram ?? []);
    $bannersDestaque = collect($bannersDestaque ?? []);
    $experienciasEntrada = collect($experienciasEntrada ?? []);
    $atalhosPremium = collect($atalhosPremium ?? []);
    $mapCategories = collect($mapCategories ?? []);
    $bannerTopo = $bannerTopo ?? $bannersDestaque->first() ?? null;
    $bannerIntermediario = $bannerIntermediario ?? $banner ?? null;
    $conhecaImage = asset('imagens/conheça.png');
    $grafismoIndigena = asset('imagens/fundo.svg');
    $grafismoExperiencia = asset('imagens/grafismo.svg');
    $pointCards = $pontosDestaque
        ->take(8)
        ->map(fn ($item) => [
            'title' => $item->card_title ?? $item->nome ?? '',
            'subtitle' => $item->cidade ?? __('ui.common.altamira'),
            'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 92),
            'image' => $item->card_image_url ?? $item->capa_url ?? $item->foto_capa_url ?? null,
            'href' => R::has('site.ponto') ? localized_route('site.ponto', ['ponto' => ($item->slug ?? $item->id)]) : '#',
            'badge' => __('ui.home.point_badge'),
            'cta' => __('ui.common.discover'),
        ]);

    $recommendationCards = $recomendacoes->map(fn ($item) => [
        'title' => $item['title'] ?? '',
        'subtitle' => $item['subtitle'] ?? __('ui.common.altamira'),
        'image' => $item['image'] ?? null,
        'href' => $item['href'] ?? '#',
        'badge' => $item['badge'] ?? __('ui.home.recommended_badge'),
        'meta' => ($item['type'] ?? null) === 'empresa' ? __('ui.home.recommended_company_meta') : __('ui.home.recommended_point_meta'),
    ]);

    $videoCards = $videosHome->map(function ($video) {
        return [
            'title' => $video->titulo,
            'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $video->descricao), 132),
            'image' => $video->capa_url ?: theme_asset('hero_image'),
            'href' => R::has('site.videos.show') ? localized_route('site.videos.show', ['slug' => $video->slug]) : '#',
            'embed' => $video->embed_url,
            'meta' => optional($video->published_at)->format('d.m.Y'),
        ];
    });

    $videosIndexHref = R::has('site.videos') ? localized_route('site.videos') : '#';
@endphp

<div
    class="site-page site-home-page"
    x-data="{
        videoModalOpen: false,
        videoModalSrc: '',
        videoModalTitle: '',
        openVideo(src, title) {
            if (!src) return;
            this.videoModalSrc = src;
            this.videoModalTitle = title || '';
            this.videoModalOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeVideo() {
            this.videoModalOpen = false;
            this.videoModalSrc = '';
            this.videoModalTitle = '';
            document.body.style.overflow = '';
        }
    }"
>
    <div class="site-section site-home-hero-section site-home-hero-section--premium">
        @include('site.partials._banner', [
            'banner' => $bannerTopo,
            'title' => '',
            'subtitle' => null,
            'ctaLabel' => null,
            'href' => null,
            'secondaryCtaLabel' => null,
            'secondaryHref' => null,
            'overlayOnly' => true,
            'overlayImage' => asset('imagens/visitcapa.png'),
            'overlayImageAlt' => __('ui.home.hero_overlay_alt'),
            'heroClass' => 'site-hero-home-immersive',
        ])

        <div class="site-home-hero-panel">
            <div class="site-home-hero-panel-copy">
                <span class="site-badge">{{ __('ui.common.official_destination') }}</span>
                <img
                    src="{{ $conhecaImage }}"
                    alt="{{ __('ui.home.know_altamira') }}"
                    class="site-home-hero-panel-brand"
                    loading="lazy"
                    decoding="async"
                >
            </div>
        </div>
    </div>

    <section class="site-section site-home-conheca-section" aria-label="{{ __('ui.home.know_altamira') }}">
        <div class="site-home-conheca-shell">
            <img
                src="{{ $conhecaImage }}"
                alt="{{ __('ui.home.know_altamira') }}"
                class="site-home-conheca-image"
                loading="lazy"
                decoding="async"
            >
        </div>
    </section>

    <div class="site-home-discovery-band" style="--site-home-band-art: url('{{ $grafismoIndigena }}');">
        @include('site.partials._portal_shortcuts', ['experienciasEntrada' => $experienciasEntrada])

        @if($recommendationCards->isNotEmpty())
            <section class="site-section site-home-recommendations-section">
                <x-section-head :title="__('ui.home.recommended_title')" />

                <div class="site-home-carousel-shell site-home-recommendations-shell" x-data="{
                    canPrev: false,
                    canNext: true,
                    update() {
                        const el = this.$refs.viewport;
                        if (!el) return;
                        this.canPrev = el.scrollLeft > 12;
                        this.canNext = (el.scrollWidth - el.clientWidth - el.scrollLeft) > 12;
                    },
                    move(direction) {
                        const el = this.$refs.viewport;
                        if (!el) return;
                        const step = Math.max(el.clientWidth * 0.76, 280);
                        el.scrollBy({ left: step * direction, behavior: 'smooth' });
                        window.setTimeout(() => this.update(), 220);
                    }
                }" x-init="$nextTick(() => update())">
                    <div class="site-home-carousel-controls" aria-hidden="true">
                        <button type="button" class="site-home-carousel-control" @click="move(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">&larr;</button>
                        <button type="button" class="site-home-carousel-control" @click="move(1)" :disabled="!canNext" :aria-disabled="!canNext">&rarr;</button>
                    </div>

                    <div class="site-home-carousel-track site-home-recommendations-track" x-ref="viewport" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                        @foreach($recommendationCards as $item)
                            <div class="site-home-carousel-slide site-home-recommendations-slide">
                                <x-card-mini
                                    :title="$item['title']"
                                    :subtitle="$item['subtitle']"
                                    :image="$item['image']"
                                    :href="$item['href']"
                                    :badge="$item['badge']"
                                    :meta="$item['meta']"
                                    variant="editorial"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>

    @include('site.partials._instagram_carousel', ['instagram' => $instagram])

    @if($bannerIntermediario)
        <section class="site-section site-home-editorial-banner-section">
            @include('site.partials._banner', [
                'banner' => $bannerIntermediario,
                'title' => $bannerIntermediario->titulo ?? 'VisitAltamira',
                'ctaLabel' => $bannerIntermediario->cta_label ?? __('ui.home.banner_cta'),
                'href' => $bannerIntermediario->cta_url ?? $bannerIntermediario->href ?? (localized_route('site.explorar')),
                'heroClass' => 'site-hero-home-editorial-banner',
            ])
        </section>
    @endif

    @include('site.partials._category_section', [
        'eyebrow' => __('ui.home.points_eyebrow'),
        'title' => __('ui.home.points_title'),
        'subtitle' => __('ui.home.points_subtitle'),
        'href' => localized_route('site.explorar'),
        'items' => $pointCards,
        'layout' => 'carousel',
        'cardVariant' => 'compact',
        'empty' => __('ui.home.points_empty'),
        'emptyTitle' => __('ui.home.points_empty_title'),
    ])

    @if($atalhosPremium->isNotEmpty() || $videoCards->isNotEmpty())
        <div class="site-home-experience-band" style="--site-home-experience-art: url('{{ $grafismoExperiencia }}');">
    @endif

    @if($atalhosPremium->isNotEmpty())
        <section class="site-section site-home-utility-section">
            <x-section-head
                :eyebrow="__('ui.home.planning_eyebrow')"
                :title="__('ui.home.planning_title')"
                :subtitle="__('ui.home.planning_subtitle')"
            />

            <div class="site-home-utility-grid">
                @foreach($atalhosPremium as $item)
                    <a
                        href="{{ $item['href'] ?? '#' }}"
                        class="site-home-utility-card site-home-utility-card--{{ $item['key'] ?? 'entry' }}"
                        aria-label="{{ $item['title'] }}"
                        title="{{ $item['title'] }}"
                    >
                        <div class="site-home-utility-media">
                            @php $utilityImageSources = site_image_sources($item['image'] ?? null, 'card'); @endphp
                            <x-picture
                                :jpg="$utilityImageSources['jpg'] ?? ($item['image'] ?? null)"
                                :webp="$utilityImageSources['webp'] ?? null"
                                :alt="$item['title']"
                                class="site-home-utility-image"
                                sizes="(max-width: 768px) 86vw, 33vw"
                                :width="$utilityImageSources['width'] ?? null"
                                :height="$utilityImageSources['height'] ?? null"
                            />
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($videoCards->isNotEmpty())
        <section class="site-section site-home-videos-section">
            <x-section-head :title="__('ui.home.videos_title')" />

            <div class="site-home-carousel-shell site-home-video-shell" x-data="{
                canPrev: false,
                canNext: true,
                update() {
                    const el = this.$refs.viewport;
                    if (!el) return;
                    this.canPrev = el.scrollLeft > 12;
                    this.canNext = (el.scrollWidth - el.clientWidth - el.scrollLeft) > 12;
                },
                move(direction) {
                    const el = this.$refs.viewport;
                    if (!el) return;
                    const step = Math.max(el.clientWidth * 0.78, 260);
                    el.scrollBy({ left: step * direction, behavior: 'smooth' });
                    window.setTimeout(() => this.update(), 220);
                }
            }" x-init="$nextTick(() => update())">
                <div class="site-home-carousel-controls" aria-hidden="true">
                    <button type="button" class="site-home-carousel-control" @click="move(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">&larr;</button>
                    <button type="button" class="site-home-carousel-control" @click="move(1)" :disabled="!canNext" :aria-disabled="!canNext">&rarr;</button>
                </div>

                <div class="site-home-video-track" x-ref="viewport" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                    @foreach($videoCards as $item)
                        <article class="site-home-video-rail-card">
                            <div class="site-home-video-media">
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy" decoding="async" class="site-home-video-image">
                                @if($item['embed'])
                                    <button
                                        type="button"
                                        class="site-home-video-play"
                                        @click="openVideo(@js($item['embed']), @js($item['title']))"
                                        aria-label="{{ __('ui.home.play_video', ['title' => $item['title']]) }}"
                                    >
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="site-home-video-play-icon">
                                            <path d="M8 6.8v10.4c0 .7.8 1.1 1.4.7l8.1-5.2a.85.85 0 0 0 0-1.4L9.4 6.1A.85.85 0 0 0 8 6.8Z"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            <div class="site-home-video-rail-body">
                                <div class="site-home-video-rail-top">
                                    <span class="site-badge">{{ __('ui.home.video_badge') }}</span>
                                    @if($item['meta'])
                                        <span class="site-home-video-meta">{{ $item['meta'] }}</span>
                                    @endif
                                </div>

                                <h3 class="site-home-video-title">
                                    <a href="{{ $item['href'] }}" class="site-home-video-title-link">{{ $item['title'] }}</a>
                                </h3>
                                <a href="{{ $item['href'] }}" class="site-home-video-cta">{{ __('ui.home.watch_now') }}</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="site-home-video-actions">
                    <a href="{{ $videosIndexHref }}" class="site-button-primary">{{ __('ui.home.videos_all') }}</a>
                </div>
            </div>
        </section>
    @endif

    @if($atalhosPremium->isNotEmpty() || $videoCards->isNotEmpty())
        </div>
    @endif

    @include('site.partials._home_map_embed', ['mapCategories' => $mapCategories])

    <a
        href="https://wa.me/559391727547?text={{ rawurlencode('Olá! Quero tirar dúvidas e planejar minha visita a Altamira.') }}"
        target="_blank"
        rel="noopener noreferrer"
        class="site-home-whatsapp"
        aria-label="Falar com a SEMTUR no WhatsApp"
        title="Falar com a SEMTUR no WhatsApp"
    >
        <span class="site-home-whatsapp-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19.05 4.91A9.82 9.82 0 0 0 12.03 2a9.94 9.94 0 0 0-8.47 15.15L2 22l4.99-1.55A10 10 0 0 0 12.03 22c5.5 0 9.97-4.46 9.97-9.97a9.9 9.9 0 0 0-2.95-7.12Zm-7.02 15.4a8.3 8.3 0 0 1-4.23-1.16l-.3-.18-2.96.92.97-2.88-.2-.31a8.3 8.3 0 1 1 6.72 3.61Zm4.56-6.18c-.25-.13-1.47-.73-1.7-.81-.23-.09-.4-.13-.57.12-.16.25-.65.81-.79.98-.15.16-.29.19-.54.06-.25-.12-1.04-.38-1.98-1.21-.73-.65-1.22-1.45-1.37-1.69-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.43.13-.15.17-.25.25-.41.08-.16.04-.31-.02-.43-.07-.13-.57-1.38-.78-1.89-.2-.49-.41-.42-.57-.43h-.49c-.16 0-.43.06-.65.31-.22.25-.86.84-.86 2.06s.88 2.39 1 2.56c.12.16 1.72 2.62 4.17 3.68.58.25 1.04.4 1.39.51.58.18 1.1.15 1.52.09.47-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.09-.22-.15-.47-.27Z"/>
            </svg>
        </span>
        <span class="site-home-whatsapp-copy">
            <strong>WhatsApp</strong>
            <span>Fale com a SEMTUR</span>
        </span>
    </a>

    <div x-show="videoModalOpen" x-cloak class="site-lightbox site-home-video-modal" @click.self="closeVideo()" x-transition.opacity>
        <div class="site-lightbox-frame site-home-video-modal-frame">
            <button type="button" class="site-lightbox-close" @click="closeVideo()" aria-label="{{ __('ui.home.close_video') }}">&times;</button>

            <div class="site-home-video-modal-shell">
                <div class="site-home-video-modal-head">
                    <span class="site-badge">{{ __('ui.home.video_badge') }}</span>
                    <h2 class="site-home-video-modal-title" x-text="videoModalTitle"></h2>
                </div>

                <div class="site-home-video-modal-media">
                    <iframe
                        x-bind:src="videoModalOpen ? videoModalSrc : ''"
                        x-bind:title="videoModalTitle || @js(__('ui.home.video_badge'))"
                        class="site-home-video-modal-embed"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="autoplay; encrypted-media; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
