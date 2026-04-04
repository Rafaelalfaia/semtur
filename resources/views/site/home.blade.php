@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route as R;

    $homeCanonical = localized_route('site.home');
    $homeTitle = $heroTranslation?->seo_title ?: ($heroTranslation?->titulo ?: ui_text('ui.home.title'));
    $homeDescription = $heroTranslation?->seo_description ?: ($heroTranslation?->lead ?: ui_text('ui.home.description'));
    $homeImage = $heroMedia?->url ?: theme_asset('hero_image');
    $homeSchema = [
        [
            '@type' => 'TouristDestination',
            '@id' => $homeCanonical.'#destination',
            'name' => 'Altamira',
            'url' => $homeCanonical,
            'description' => $homeDescription,
            'image' => $homeImage,
            'touristType' => [
                ui_text('ui.home.tourist_nature'),
                ui_text('ui.home.tourist_cultural'),
                ui_text('ui.home.tourist_xingu'),
            ],
            'containedInPlace' => [
                '@type' => 'State',
                'name' => 'Pará',
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
    $pageBlocks = $pageBlocks ?? collect();
    $homeContentBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'conheca' => $pageBlocks->get('conheca'),
        'discovery_band' => $pageBlocks->get('discovery_band'),
        'recommended_section' => $pageBlocks->get('recommended_section'),
        'points_section' => $pageBlocks->get('points_section'),
        'planning_section' => $pageBlocks->get('planning_section'),
        'videos_section' => $pageBlocks->get('videos_section'),
        'whatsapp_section' => $pageBlocks->get('whatsapp_section'),
        'map_section' => $pageBlocks->get('map_section'),
        'instagram_section' => $pageBlocks->get('instagram_section'),
        'entry_section' => $pageBlocks->get('entry_section'),
        'editorial_banner_section' => $pageBlocks->get('editorial_banner_section'),
        'entry_rota_do_cacau' => $pageBlocks->get('entry_rota_do_cacau'),
        'entry_jogos_indigenas' => $pageBlocks->get('entry_jogos_indigenas'),
        'entry_museus' => $pageBlocks->get('entry_museus'),
        'utility_onde_comer' => $pageBlocks->get('utility_onde_comer'),
        'utility_onde_ficar' => $pageBlocks->get('utility_onde_ficar'),
        'utility_guias' => $pageBlocks->get('utility_guias'),
    ];
    $homeBlockTranslation = function (string $key) use ($homeContentBlocks) {
        return $homeContentBlocks[$key]?->getAttribute('traducao_resolvida');
    };
    $homeBlockMedia = function (string $key, string $slot = 'hero') use ($homeContentBlocks) {
        return $homeContentBlocks[$key]?->getAttribute('media_by_slot')?->get($slot);
    };
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
    $conhecaTranslation = $homeBlockTranslation('conheca');
    $conhecaMedia = $homeBlockMedia('conheca', 'brand') ?: $homeBlockMedia('conheca');
    $discoveryBandTranslation = $homeBlockTranslation('discovery_band');
    $discoveryBandMedia = $homeBlockMedia('discovery_band', 'background') ?: $homeBlockMedia('discovery_band');
    $recommendedSectionTranslation = $homeBlockTranslation('recommended_section');
    $pointsSectionTranslation = $homeBlockTranslation('points_section');
    $planningSectionTranslation = $homeBlockTranslation('planning_section');
    $videosSectionTranslation = $homeBlockTranslation('videos_section');
    $whatsAppSectionTranslation = $homeBlockTranslation('whatsapp_section');
    $mapSectionTranslation = $homeBlockTranslation('map_section');
    $instagramSectionTranslation = $homeBlockTranslation('instagram_section');
    $entrySectionTranslation = $homeBlockTranslation('entry_section');
    $editorialBannerSectionTranslation = $homeBlockTranslation('editorial_banner_section');
    $planningBackgroundMedia = $homeBlockMedia('planning_section', 'background') ?: $homeBlockMedia('planning_section');
    $entryImageKeys = [
        'rota_do_cacau' => 'entry_rota_do_cacau',
        'jogos_indigenas' => 'entry_jogos_indigenas',
        'museus' => 'entry_museus',
    ];
    $utilityImageKeys = [
        'onde_comer' => 'utility_onde_comer',
        'onde_ficar' => 'utility_onde_ficar',
        'guias' => 'utility_guias',
    ];
    $conhecaImage = $conhecaMedia?->url ?: asset('imagens/conheça.png');
    $grafismoIndigena = $discoveryBandMedia?->url ?: asset('imagens/fundo.svg');
    $grafismoExperiencia = $planningBackgroundMedia?->url ?: asset('imagens/grafismo.svg');
    $homeHeroBadge = $heroTranslation?->eyebrow ?: ui_text('ui.common.official_destination');
    $homeHeroTitle = $heroTranslation?->titulo ?: 'Visit Altamira';
    $homeHeroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.common.discover');
    $homeHeroPrimaryHref = $heroTranslation?->cta_href ?: localized_route('site.explorar');
    $homeHeroImage = $heroMedia?->url ?: asset('imagens/visitcapa.png');
    $pointCards = $pontosDestaque
        ->take(8)
        ->map(fn ($item) => [
            'title' => $item->card_title ?? $item->nome ?? '',
            'subtitle' => $item->cidade ?? ui_text('ui.common.altamira'),
            'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 92),
            'image' => $item->card_image_url ?? $item->capa_url ?? $item->foto_capa_url ?? null,
            'href' => R::has('site.ponto') ? localized_route('site.ponto', ['ponto' => ($item->slug ?? $item->id)]) : '#',
            'badge' => ui_text('ui.home.point_badge'),
            'cta' => ui_text('ui.common.discover'),
        ]);

    $recommendationCards = $recomendacoes->map(fn ($item) => [
        'title' => $item['title'] ?? '',
        'subtitle' => $item['subtitle'] ?? ui_text('ui.common.altamira'),
        'image' => $item['image'] ?? null,
        'href' => $item['href'] ?? '#',
        'badge' => $item['badge'] ?? ui_text('ui.home.recommended_badge'),
        'meta' => ($item['type'] ?? null) === 'empresa' ? ui_text('ui.home.recommended_company_meta') : ui_text('ui.home.recommended_point_meta'),
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
    $recommendedTitle = $recommendedSectionTranslation?->titulo ?: ui_text('ui.home.recommended_title');
    $recommendedEyebrow = $recommendedSectionTranslation?->eyebrow;
    $recommendedSubtitle = $recommendedSectionTranslation?->lead;
    $pointsEyebrow = $pointsSectionTranslation?->eyebrow ?: ui_text('ui.home.points_eyebrow');
    $pointsTitle = $pointsSectionTranslation?->titulo ?: ui_text('ui.home.points_title');
    $pointsSubtitle = $pointsSectionTranslation?->lead ?: ui_text('ui.home.points_subtitle');
    $pointsHref = $pointsSectionTranslation?->cta_href ?: localized_route('site.explorar');
    $planningEyebrow = $planningSectionTranslation?->eyebrow ?: ui_text('ui.home.planning_eyebrow');
    $planningTitle = $planningSectionTranslation?->titulo ?: ui_text('ui.home.planning_title');
    $planningSubtitle = $planningSectionTranslation?->lead ?: ui_text('ui.home.planning_subtitle');
    $videosEyebrow = $videosSectionTranslation?->eyebrow;
    $videosTitle = $videosSectionTranslation?->titulo ?: ui_text('ui.home.videos_title');
    $videosSubtitle = $videosSectionTranslation?->lead;
    $videosAllLabel = $videosSectionTranslation?->cta_label ?: ui_text('ui.home.videos_all');
    $videosAllHref = $videosSectionTranslation?->cta_href ?: $videosIndexHref;
    $whatsAppTitle = $whatsAppSectionTranslation?->titulo ?: 'WhatsApp';
    $whatsAppSubtitle = $whatsAppSectionTranslation?->lead ?: 'Fale com a SEMTUR';
    $whatsAppHref = $whatsAppSectionTranslation?->cta_href ?: 'https://wa.me/559391727547?text='.rawurlencode('Olá! Quero tirar dúvidas e planejar minha visita a Altamira.');
    $mapEyebrow = $mapSectionTranslation?->eyebrow ?: ui_text('ui.home.map_badge');
    $mapTitle = $mapSectionTranslation?->titulo ?: ui_text('ui.home.map_title');
    $mapCtaLabel = $mapSectionTranslation?->cta_label ?: ui_text('ui.home.map_open_full');
    $mapCtaHref = $mapSectionTranslation?->cta_href ?: (R::has('site.mapa') ? localized_route('site.mapa') : '#');
    $instagramEyebrow = $instagramSectionTranslation?->eyebrow ?: ui_text('ui.instagram.eyebrow');
    $instagramTitle = $instagramSectionTranslation?->titulo ?: '@visitaltamira';
    $instagramCtaHref = $instagramSectionTranslation?->cta_href ?: 'https://www.instagram.com/visitaltamira/';
    $entrySectionTitle = $entrySectionTranslation?->titulo ?: ui_text('ui.home.entry_title');
    $editorialBannerTitle = $editorialBannerSectionTranslation?->titulo ?: ($bannerIntermediario->titulo ?? 'VisitAltamira');
    $editorialBannerSubtitle = $editorialBannerSectionTranslation?->subtitulo ?: ($bannerIntermediario->subtitulo ?? null);
    $editorialBannerCtaLabel = $editorialBannerSectionTranslation?->cta_label ?: ($bannerIntermediario->cta_label ?? ui_text('ui.home.banner_cta'));
    $editorialBannerCtaHref = $editorialBannerSectionTranslation?->cta_href ?: ($bannerIntermediario->cta_url ?? $bannerIntermediario->href ?? localized_route('site.explorar'));
    $canViewFeaturedBanners = auth()->check() && auth()->user()->can('banners_destaque.view');
    $canManageFeaturedBanners = auth()->check() && auth()->user()->can('banners_destaque.manage');
    $canViewBanners = auth()->check() && auth()->user()->can('banners.view');
    $canManageBanners = auth()->check() && auth()->user()->can('banners.manage');
    $experienciasEntrada = $experienciasEntrada->map(function (array $item) use ($entryImageKeys, $homeContentBlocks, $homeBlockTranslation) {
        $blockKey = $entryImageKeys[$item['key']] ?? null;
        $block = $blockKey ? ($homeContentBlocks[$blockKey] ?? null) : null;
        $translation = $blockKey ? $homeBlockTranslation($blockKey) : null;
        $media = $block?->getAttribute('media_by_slot')?->get('card') ?: $block?->getAttribute('media_by_slot')?->get('hero');

        return array_merge($item, [
            'image' => $media?->url ?: ($item['image'] ?? null),
            'editor' => $blockKey ? [
                'title' => $translation?->titulo ?: ($item['title'] ?? 'Imagem'),
                'page' => 'site.home',
                'key' => $blockKey,
                'label' => 'Imagem '.($item['title'] ?? 'do bloco'),
                'translation' => $translation,
                'media' => $media,
                'status' => $block?->status ?? 'publicado',
            ] : null,
        ]);
    });
    $atalhosPremium = $atalhosPremium->map(function (array $item) use ($utilityImageKeys, $homeContentBlocks, $homeBlockTranslation) {
        $blockKey = $utilityImageKeys[$item['key']] ?? null;
        $block = $blockKey ? ($homeContentBlocks[$blockKey] ?? null) : null;
        $translation = $blockKey ? $homeBlockTranslation($blockKey) : null;
        $media = $block?->getAttribute('media_by_slot')?->get('card') ?: $block?->getAttribute('media_by_slot')?->get('hero');

        return array_merge($item, [
            'image' => $media?->url ?: ($item['image'] ?? null),
            'editor' => $blockKey ? [
                'title' => $translation?->titulo ?: ($item['title'] ?? 'Imagem'),
                'page' => 'site.home',
                'key' => $blockKey,
                'label' => 'Imagem '.($item['title'] ?? 'do bloco'),
                'translation' => $translation,
                'media' => $media,
                'status' => $block?->status ?? 'publicado',
            ] : null,
        ]);
    });
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
            'eyebrow' => $homeHeroBadge,
            'title' => $homeHeroTitle,
            'subtitle' => null,
            'ctaLabel' => $homeHeroPrimaryLabel,
            'href' => $homeHeroPrimaryHref,
            'secondaryCtaLabel' => null,
            'secondaryHref' => null,
            'overlayOnly' => false,
            'overlayImage' => $homeHeroImage,
            'overlayImageAlt' => ui_text('ui.home.hero_overlay_alt'),
            'heroClass' => 'site-hero-home-immersive',
            'contentVisible' => false,
            'sectionActions' => array_values(array_filter([
                ($bannerTopo && $canManageFeaturedBanners && R::has('coordenador.banners-destaque.edit'))
                    ? ['label' => 'Editar banner principal', 'href' => route('coordenador.banners-destaque.edit', $bannerTopo), 'class' => 'site-button-secondary']
                    : null,
                ($canViewFeaturedBanners && R::has('coordenador.banners-destaque.index'))
                    ? ['label' => 'Banners principais', 'href' => route('coordenador.banners-destaque.index'), 'class' => 'site-button-secondary']
                    : null,
            ])),
        ])
    </div>

    <section class="site-section site-home-conheca-section" aria-label="{{ $conhecaTranslation?->titulo ?: ui_text('ui.home.know_altamira') }}">
        <div class="site-home-conheca-shell">
            @include('site.partials._content_editor', [
                'editorTitle' => $homeHeroBadge,
                'editorPage' => 'site.home',
                'editorKey' => 'conheca',
                'editorLabel' => 'Logo da home',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline-compact',
                'editorTriggerLabel' => 'Editar logo',
                'editorFields' => ['eyebrow', 'media'],
                'editableTranslation' => $conhecaTranslation,
                'editableMedia' => $conhecaMedia,
                'editableStatus' => $homeContentBlocks['conheca']?->status ?? 'publicado',
                'editorMediaSlot' => 'brand',
                'editorMediaLabel' => 'Logo',
                'editorMediaPreviewLabel' => 'logo atual',
                'editableFallback' => [
                    'eyebrow' => $homeHeroBadge,
                ],
            ])
            <span class="site-badge site-home-conheca-badge">{{ $conhecaTranslation?->eyebrow ?: $homeHeroBadge }}</span>
            <img
                src="{{ $conhecaImage }}"
                alt="{{ $conhecaTranslation?->titulo ?: ui_text('ui.home.know_altamira') }}"
                class="site-home-conheca-image"
                loading="lazy"
                decoding="async"
            >
        </div>
    </section>

    <div class="site-home-discovery-band" style="--site-home-band-art: url('{{ $grafismoIndigena }}');">
        @include('site.partials._content_editor', [
            'editorTitle' => $discoveryBandTranslation?->titulo ?: 'Faixa de descoberta',
            'editorPage' => 'site.home',
            'editorKey' => 'discovery_band',
            'editorLabel' => 'Grafismo de descoberta',
            'editorLocale' => route_locale(),
            'editorTriggerVariant' => 'inline-compact',
            'editorTriggerLabel' => 'Editar fundo',
            'editorFields' => ['media'],
            'editableTranslation' => $discoveryBandTranslation,
            'editableMedia' => $discoveryBandMedia,
            'editableStatus' => $homeContentBlocks['discovery_band']?->status ?? 'publicado',
            'editorMediaSlot' => 'background',
            'editorMediaLabel' => 'Imagem de fundo',
            'editorMediaPreviewLabel' => 'Imagem de fundo atual',
            'editableFallback' => [
                'titulo' => 'Faixa de descoberta',
            ],
        ])
        @include('site.partials._portal_shortcuts', [
            'experienciasEntrada' => $experienciasEntrada,
            'title' => $entrySectionTitle,
            'editor' => [
                'title' => $entrySectionTitle,
                'page' => 'site.home',
                'key' => 'entry_section',
                'label' => 'Título da seção de experiências',
                'translation' => $entrySectionTranslation,
                'status' => $homeContentBlocks['entry_section']?->status ?? 'publicado',
            ],
        ])

        @if($recommendationCards->isNotEmpty())
            <section class="site-section site-home-recommendations-section">
                @include('site.partials._content_editor', [
                    'editorTitle' => $recommendedTitle,
                    'editorPage' => 'site.home',
                    'editorKey' => 'recommended_section',
                    'editorLabel' => 'Seção recomendados',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline',
                    'editableTranslation' => $recommendedSectionTranslation,
                    'editableStatus' => $homeContentBlocks['recommended_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => null,
                        'titulo' => ui_text('ui.home.recommended_title'),
                        'lead' => null,
                    ],
                ])
                <x-section-head :eyebrow="$recommendedEyebrow" :title="$recommendedTitle" :subtitle="$recommendedSubtitle" />

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

    @include('site.partials._instagram_carousel', [
        'instagram' => $instagram,
        'eyebrow' => $instagramEyebrow,
        'title' => $instagramTitle,
        'href' => $instagramCtaHref,
        'editor' => [
            'title' => $instagramTitle,
            'page' => 'site.home',
            'key' => 'instagram_section',
            'label' => 'Seção Instagram',
            'translation' => $instagramSectionTranslation,
            'status' => $homeContentBlocks['instagram_section']?->status ?? 'publicado',
        ],
    ])

    @if($bannerIntermediario)
        <section class="site-section site-home-editorial-banner-section">
            @include('site.partials._banner', [
                'banner' => $bannerIntermediario,
                'title' => $editorialBannerTitle,
                'subtitle' => $editorialBannerSubtitle,
                'ctaLabel' => $editorialBannerCtaLabel,
                'href' => $editorialBannerCtaHref,
                'heroClass' => 'site-hero-home-editorial-banner',
                'textEditor' => [
                    'title' => $editorialBannerTitle,
                    'page' => 'site.home',
                    'key' => 'editorial_banner_section',
                    'label' => 'Texto do banner intermediário',
                'translation' => $editorialBannerSectionTranslation,
                'status' => $homeContentBlocks['editorial_banner_section']?->status ?? 'publicado',
                'trigger_label' => 'Editar texto',
                'fields' => ['titulo', 'subtitulo', 'cta_label', 'cta_href'],
            ],
            'sectionActions' => array_values(array_filter([
                ($bannerIntermediario && $canManageBanners && R::has('coordenador.banners.edit'))
                    ? ['label' => 'Editar banner', 'href' => route('coordenador.banners.edit', $bannerIntermediario), 'class' => 'site-button-secondary']
                    : null,
                ($canViewBanners && R::has('coordenador.banners.index'))
                    ? ['label' => 'Banners', 'href' => route('coordenador.banners.index'), 'class' => 'site-button-secondary']
                    : null,
            ])),
        ])
        </section>
    @endif

    @include('site.partials._content_editor', [
        'editorTitle' => $pointsTitle,
        'editorPage' => 'site.home',
        'editorKey' => 'points_section',
        'editorLabel' => 'Seção pontos',
        'editorLocale' => route_locale(),
        'editorTriggerVariant' => 'inline',
        'editableTranslation' => $pointsSectionTranslation,
        'editableStatus' => $homeContentBlocks['points_section']?->status ?? 'publicado',
        'editableFallback' => [
            'eyebrow' => ui_text('ui.home.points_eyebrow'),
            'titulo' => ui_text('ui.home.points_title'),
            'lead' => ui_text('ui.home.points_subtitle'),
            'cta_href' => localized_route('site.explorar'),
        ],
    ])

    @include('site.partials._category_section', [
        'eyebrow' => $pointsEyebrow,
        'title' => $pointsTitle,
        'subtitle' => $pointsSubtitle,
        'href' => $pointsHref,
        'items' => $pointCards,
        'layout' => 'carousel',
        'cardVariant' => 'compact',
        'empty' => ui_text('ui.home.points_empty'),
        'emptyTitle' => ui_text('ui.home.points_empty_title'),
    ])

    @if($atalhosPremium->isNotEmpty() || $videoCards->isNotEmpty())
        @include('site.partials._content_editor', [
            'editorTitle' => $planningTitle,
            'editorPage' => 'site.home',
            'editorKey' => 'planning_section',
            'editorLabel' => 'Seção planeje sua viagem',
            'editorLocale' => route_locale(),
            'editorTriggerVariant' => 'inline',
            'editableTranslation' => $planningSectionTranslation,
            'editableMedia' => $planningBackgroundMedia,
            'editableStatus' => $homeContentBlocks['planning_section']?->status ?? 'publicado',
            'editorMediaSlot' => 'background',
            'editorMediaLabel' => 'Grafismo de fundo',
            'editorMediaPreviewLabel' => 'Grafismo atual',
            'editableFallback' => [
                'eyebrow' => ui_text('ui.home.planning_eyebrow'),
                'titulo' => ui_text('ui.home.planning_title'),
                'lead' => ui_text('ui.home.planning_subtitle'),
            ],
        ])
        <div class="site-home-experience-band" style="--site-home-experience-art: url('{{ $grafismoExperiencia }}');">
    @endif

    @if($atalhosPremium->isNotEmpty())
        <section class="site-section site-home-utility-section">
            <x-section-head
                :eyebrow="$planningEyebrow"
                :title="$planningTitle"
                :subtitle="$planningSubtitle"
            />

            <div class="site-home-utility-grid">
                @foreach($atalhosPremium as $item)
                    <div>
                        @if(!empty($item['editor']))
                            @include('site.partials._content_editor', [
                                'editorTitle' => $item['editor']['title'],
                                'editorPage' => $item['editor']['page'],
                                'editorKey' => $item['editor']['key'],
                                'editorLabel' => $item['editor']['label'],
                                'editorLocale' => route_locale(),
                                'editorTriggerVariant' => 'inline-compact',
                                'editorTriggerLabel' => 'Editar imagem',
                                'editableTranslation' => $item['editor']['translation'],
                                'editableMedia' => $item['editor']['media'],
                                'editableStatus' => $item['editor']['status'],
                                'editorMediaSlot' => 'card',
                                'editorMediaLabel' => 'Imagem do card',
                                'editorMediaPreviewLabel' => 'imagem do card atual',
                                'editorFields' => ['media'],
                                'editableFallback' => [
                                    'titulo' => $item['title'] ?? 'Imagem do card',
                                ],
                            ])
                        @endif
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
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if($videoCards->isNotEmpty())
        <section class="site-section site-home-videos-section">
            @include('site.partials._content_editor', [
                'editorTitle' => $videosTitle,
                'editorPage' => 'site.home',
                'editorKey' => 'videos_section',
                'editorLabel' => 'Seção vídeos',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline',
                'editableTranslation' => $videosSectionTranslation,
                'editableStatus' => $homeContentBlocks['videos_section']?->status ?? 'publicado',
                'editableFallback' => [
                    'titulo' => ui_text('ui.home.videos_title'),
                    'lead' => null,
                    'cta_label' => ui_text('ui.home.videos_all'),
                    'cta_href' => $videosIndexHref,
                ],
            ])
            <x-section-head :eyebrow="$videosEyebrow" :title="$videosTitle" :subtitle="$videosSubtitle" />

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
                                        aria-label="{{ ui_text('ui.home.play_video', ['title' => $item['title']]) }}"
                                    >
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="site-home-video-play-icon">
                                            <path d="M8 6.8v10.4c0 .7.8 1.1 1.4.7l8.1-5.2a.85.85 0 0 0 0-1.4L9.4 6.1A.85.85 0 0 0 8 6.8Z"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            <div class="site-home-video-rail-body">
                                <div class="site-home-video-rail-top">
                                    <span class="site-badge">{{ ui_text('ui.home.video_badge') }}</span>
                                    @if($item['meta'])
                                        <span class="site-home-video-meta">{{ $item['meta'] }}</span>
                                    @endif
                                </div>

                                <h3 class="site-home-video-title">
                                    <a href="{{ $item['href'] }}" class="site-home-video-title-link">{{ $item['title'] }}</a>
                                </h3>
                                <a href="{{ $item['href'] }}" class="site-home-video-cta">{{ ui_text('ui.home.watch_now') }}</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="site-home-video-actions">
                    <a href="{{ $videosAllHref }}" class="site-button-primary">{{ $videosAllLabel }}</a>
                </div>
            </div>
        </section>
    @endif

    @if($atalhosPremium->isNotEmpty() || $videoCards->isNotEmpty())
        </div>
    @endif

    @include('site.partials._home_map_embed', [
        'mapCategories' => $mapCategories,
        'eyebrow' => $mapEyebrow,
        'title' => $mapTitle,
        'ctaLabel' => $mapCtaLabel,
        'ctaHref' => $mapCtaHref,
        'editor' => [
            'title' => $mapTitle,
            'page' => 'site.home',
            'key' => 'map_section',
            'label' => 'Mapa da home',
            'translation' => $mapSectionTranslation,
            'status' => $homeContentBlocks['map_section']?->status ?? 'publicado',
        ],
    ])

    @include('site.partials._content_editor', [
        'editorTitle' => $whatsAppTitle,
        'editorPage' => 'site.home',
        'editorKey' => 'whatsapp_section',
        'editorLabel' => 'Atalho WhatsApp',
        'editorLocale' => route_locale(),
        'editorTriggerVariant' => 'inline',
        'editableTranslation' => $whatsAppSectionTranslation,
        'editableStatus' => $homeContentBlocks['whatsapp_section']?->status ?? 'publicado',
        'editableFallback' => [
            'titulo' => 'WhatsApp',
            'lead' => 'Fale com a SEMTUR',
            'cta_href' => 'https://wa.me/559391727547?text='.rawurlencode('Olá! Quero tirar dúvidas e planejar minha visita a Altamira.'),
        ],
    ])
    <a
        href="{{ $whatsAppHref }}"
        target="_blank"
        rel="noopener noreferrer"
        class="site-home-whatsapp"
        aria-label="{{ trim($whatsAppTitle.' '.$whatsAppSubtitle) }}"
        title="{{ trim($whatsAppTitle.' '.$whatsAppSubtitle) }}"
    >
        <span class="site-home-whatsapp-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19.05 4.91A9.82 9.82 0 0 0 12.03 2a9.94 9.94 0 0 0-8.47 15.15L2 22l4.99-1.55A10 10 0 0 0 12.03 22c5.5 0 9.97-4.46 9.97-9.97a9.9 9.9 0 0 0-2.95-7.12Zm-7.02 15.4a8.3 8.3 0 0 1-4.23-1.16l-.3-.18-2.96.92.97-2.88-.2-.31a8.3 8.3 0 1 1 6.72 3.61Zm4.56-6.18c-.25-.13-1.47-.73-1.7-.81-.23-.09-.4-.13-.57.12-.16.25-.65.81-.79.98-.15.16-.29.19-.54.06-.25-.12-1.04-.38-1.98-1.21-.73-.65-1.22-1.45-1.37-1.69-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.43.13-.15.17-.25.25-.41.08-.16.04-.31-.02-.43-.07-.13-.57-1.38-.78-1.89-.2-.49-.41-.42-.57-.43h-.49c-.16 0-.43.06-.65.31-.22.25-.86.84-.86 2.06s.88 2.39 1 2.56c.12.16 1.72 2.62 4.17 3.68.58.25 1.04.4 1.39.51.58.18 1.1.15 1.52.09.47-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.09-.22-.15-.47-.27Z"/>
            </svg>
        </span>
        <span class="site-home-whatsapp-copy">
            <strong>{{ $whatsAppTitle }}</strong>
            <span>{{ $whatsAppSubtitle }}</span>
        </span>
    </a>

    <div x-show="videoModalOpen" x-cloak class="site-lightbox site-home-video-modal" @click.self="closeVideo()" x-transition.opacity>
        <div class="site-lightbox-frame site-home-video-modal-frame">
            <button type="button" class="site-lightbox-close" @click="closeVideo()" aria-label="{{ ui_text('ui.home.close_video') }}">&times;</button>

            <div class="site-home-video-modal-shell">
                <div class="site-home-video-modal-head">
                    <span class="site-badge">{{ ui_text('ui.home.video_badge') }}</span>
                    <h2 class="site-home-video-modal-title" x-text="videoModalTitle"></h2>
                </div>

                <div class="site-home-video-modal-media">
                    <iframe
                        x-bind:src="videoModalOpen ? videoModalSrc : ''"
                        x-bind:title="videoModalTitle || @js(ui_text('ui.home.video_badge'))"
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


