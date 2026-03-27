@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route as R;

    $homeCanonical = R::has('site.home') ? route('site.home') : url('/');
    $homeTitle = 'Descubra Altamira';
    $homeDescription = 'Guia turístico oficial de Altamira com experiências publicadas, curadoria, mapa e vídeos para planejar a viagem com mais clareza.';
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
                'Turismo de natureza',
                'Turismo cultural',
                'Experiências no Rio Xingu',
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

    $pointCards = $pontosDestaque
        ->take(8)
        ->map(fn ($item) => [
            'title' => $item->card_title ?? $item->nome ?? '',
            'subtitle' => $item->cidade ?? 'Altamira',
            'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 92),
            'image' => $item->card_image_url ?? $item->capa_url ?? $item->foto_capa_url ?? null,
            'href' => R::has('site.ponto') ? route('site.ponto', ['ponto' => ($item->slug ?? $item->id)]) : '#',
            'badge' => 'Ponto turístico',
            'cta' => 'Descobrir',
        ]);

    $recommendationCards = $recomendacoes->map(fn ($item) => [
        'title' => $item['title'] ?? '',
        'subtitle' => $item['subtitle'] ?? 'Altamira',
        'image' => $item['image'] ?? null,
        'href' => $item['href'] ?? '#',
        'badge' => $item['badge'] ?? 'Recomendado',
        'meta' => ($item['type'] ?? null) === 'empresa' ? 'Curadoria ativa' : 'Ponto recomendado',
    ]);

    $videoCards = $videosHome->map(function ($video) {
        return [
            'title' => $video->titulo,
            'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $video->descricao), 132),
            'image' => $video->capa_url ?: theme_asset('hero_image'),
            'href' => R::has('site.videos.show') ? route('site.videos.show', $video->slug) : '#',
            'meta' => optional($video->published_at)->format('d.m.Y'),
        ];
    });

    $videosIndexHref = R::has('site.videos') ? route('site.videos') : '#';
@endphp

<div class="site-page site-home-page">
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
            'overlayImageAlt' => 'VisitAltamira - Experimente, vivencie, apaixone-se',
            'heroClass' => 'site-hero-home-immersive',
        ])

        <div class="site-home-hero-panel">
            <div class="site-home-hero-panel-copy">
                <span class="site-badge">Destino oficial</span>
                <h1 class="site-home-hero-panel-title">Altamira para sentir.</h1>
            </div>

            <div class="site-home-hero-panel-actions">
                <a href="{{ R::has('site.explorar') ? route('site.explorar') : '#' }}" class="site-button-primary">Descobrir</a>
                <a href="{{ R::has('site.mapa') ? route('site.mapa') : '#' }}" class="site-button-secondary">Mapa</a>
            </div>
        </div>
    </div>

    @include('site.partials._portal_shortcuts', ['experienciasEntrada' => $experienciasEntrada])

    @if($recommendationCards->isNotEmpty())
        <section class="site-section site-home-recommendations-section">
            <x-section-head
                title="Comece pelos recomendados"
            />

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

    @include('site.partials._instagram_carousel', ['instagram' => $instagram])

    @if($bannerIntermediario)
        <section class="site-section site-home-editorial-banner-section">
            @include('site.partials._banner', [
                'banner' => $bannerIntermediario,
                'title' => $bannerIntermediario->titulo ?? 'VisitAltamira',
                'ctaLabel' => $bannerIntermediario->cta_label ?? 'Explorar',
                'href' => $bannerIntermediario->cta_url ?? $bannerIntermediario->href ?? (R::has('site.explorar') ? route('site.explorar') : '#'),
                'heroClass' => 'site-hero-home-editorial-banner',
            ])
        </section>
    @endif

    @include('site.partials._category_section', [
        'eyebrow' => 'Coleção publicada',
        'title' => 'Pontos turísticos',
        'subtitle' => 'Uma coleção publicada para descobrir Altamira com leitura mais leve.',
        'href' => R::has('site.explorar') ? route('site.explorar') : '#',
        'items' => $pointCards,
        'layout' => 'carousel',
        'cardVariant' => 'compact',
        'empty' => 'Sem pontos turísticos publicados no momento.',
        'emptyTitle' => 'Nenhum ponto em destaque agora',
    ])

    @if($atalhosPremium->isNotEmpty())
        <section class="site-section site-home-utility-section">
            <x-section-head
                eyebrow="Planeje a experiência"
                title="Três atalhos para seguir"
                subtitle="Comer, ficar e acessar guias oficiais."
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
                            <img
                                src="{{ $item['image'] }}"
                                alt="{{ $item['title'] }}"
                                loading="lazy"
                                decoding="async"
                                class="site-home-utility-image"
                            >
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($videoCards->isNotEmpty())
        <section class="site-section site-home-videos-section">
            <x-section-head title="Vídeos" />

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
                        <a href="{{ $item['href'] }}" class="site-home-video-rail-card">
                            <div class="site-home-video-media">
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy" decoding="async" class="site-home-video-image">
                                <span class="site-home-video-play" aria-hidden="true">Assistir</span>
                            </div>

                            <div class="site-home-video-rail-body">
                                <div class="site-home-video-rail-top">
                                    <span class="site-badge">Vídeo</span>
                                    @if($item['meta'])
                                        <span class="site-home-video-meta">{{ $item['meta'] }}</span>
                                    @endif
                                </div>

                                <h3 class="site-home-video-title">{{ $item['title'] }}</h3>
                                <span class="site-home-video-cta">Assistir agora</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="site-home-video-actions">
                    <a href="{{ $videosIndexHref }}" class="site-button-primary">Ver todos os vídeos</a>
                </div>
            </div>
        </section>
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
</div>
@endsection
