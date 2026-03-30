@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route as R;

    $categorias = collect($categorias ?? []);
    $pontos = $pontos ?? collect();
    $empresas = $empresas ?? collect();
    $currentCat = $currentCat ?? null;
    $buscaAtual = trim((string) request('busca', ''));
    $pointSource = collect(method_exists($pontos, 'items') ? $pontos->items() : $pontos);
    $companySource = collect(method_exists($empresas, 'items') ? $empresas->items() : $empresas);
    $categoriaSlugAtual = $currentCat?->slug ?? request('categoria') ?? request('cat');
    $explorarCanonical = R::has('site.explorar') ? localized_route('site.explorar') : url()->current();
    $explorarTitle = $currentCat?->nome ? ui_text('ui.explore.title_category', ['category' => $currentCat->nome]) : ui_text('ui.explore.title_default');
    $explorarDescription = $buscaAtual !== ''
        ? ui_text('ui.explore.description_search', ['search' => $buscaAtual])
        : ($currentCat?->nome
            ? ui_text('ui.explore.description_category', ['category' => $currentCat->nome])
            : ui_text('ui.explore.description_default'));

    $explorarItems = $pointSource
        ->take(3)
        ->map(fn ($item) => [
            '@type' => 'ListItem',
            'position' => null,
            'url' => R::has('site.ponto') ? localized_route('site.ponto', ['ponto' => $item->slug ?? $item->id]) : null,
            'name' => $item->nome ?? ui_text('ui.explore.point_name'),
        ])
        ->values()
        ->concat(
            $companySource->take(3)->map(fn ($item) => [
                '@type' => 'ListItem',
                'position' => null,
                'url' => R::has('site.empresa') ? localized_route('site.empresa', ['empresa' => $item->slug ?? $item->id]) : null,
                'name' => $item->nome ?? ui_text('ui.explore.company_name'),
            ])->values()
        )
        ->values()
        ->map(function ($item, $index) {
            $item['position'] = $index + 1;
            return $item;
        })
        ->filter(fn ($item) => filled($item['url']))
        ->values()
        ->all();

    $explorarSchema = [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $explorarCanonical.'#breadcrumbs',
            'itemListElement' => array_values(array_filter([
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => ui_text('ui.common.home'),
                    'item' => localized_route('site.home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $explorarTitle,
                    'item' => $explorarCanonical,
                ],
            ])),
        ],
        array_filter([
            '@type' => 'CollectionPage',
            '@id' => $explorarCanonical.'#collection',
            'url' => $explorarCanonical,
            'name' => $explorarTitle,
            'description' => $explorarDescription,
            'about' => [
                '@type' => 'TouristDestination',
                'name' => ui_text('ui.common.altamira'),
            ],
            'mainEntity' => $explorarItems ? [
                '@type' => 'ItemList',
                'itemListElement' => $explorarItems,
            ] : null,
        ], fn ($value) => $value !== null),
    ];
@endphp

@section('title', $explorarTitle)
@section('meta.description', $explorarDescription)
@section('meta.image', asset('imagens/altamira.jpg'))
@section('meta.canonical', $explorarCanonical)
@section('meta.type', 'website')

@push('structured-data')
<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $explorarSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@section('site.content')
@php
    $categoriaSlugAtual = $currentCat?->slug ?? request('categoria') ?? request('cat');
    $mapHref = R::has('site.mapa')
        ? localized_route('site.mapa', array_filter([
            'q' => $buscaAtual !== '' ? $buscaAtual : null,
            'categoria' => $categoriaSlugAtual ?: null,
        ]))
        : '#';

    $buildItem = function ($item, string $type) use ($categoriaSlugAtual, $buscaAtual) {
        $href = $type === 'empresa' && R::has('site.empresa')
            ? localized_route('site.empresa', ['empresa' => $item->slug ?? $item->id])
            : (R::has('site.ponto') ? localized_route('site.ponto', ['ponto' => $item->slug ?? $item->id]) : '#');

        $mapHref = R::has('site.mapa')
            ? localized_route('site.mapa', array_filter([
                'focus' => $type.':'.($item->slug ?? $item->id),
                'lat' => is_numeric($item->lat ?? null) ? (float) $item->lat : null,
                'lng' => is_numeric($item->lng ?? null) ? (float) $item->lng : null,
                'open' => 1,
                'q' => $buscaAtual !== '' ? $buscaAtual : null,
                'categoria' => $categoriaSlugAtual ?: null,
            ]))
            : '#';

        return [
            'title' => $item->nome,
            'subtitle' => $item->cidade ?? ui_text('ui.common.altamira'),
            'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 96),
            'image' => $item->capa_url ?? $item->foto_capa_url ?? $item->perfil_url ?? null,
            'href' => $href,
            'badge' => $type === 'empresa' ? ui_text('ui.explore.company_badge') : ui_text('ui.explore.point_badge'),
            'meta' => null,
            'cta' => $type === 'empresa' ? ui_text('ui.explore.view_company') : ui_text('ui.explore.view_point'),
            'map_href' => $mapHref,
        ];
    };

    $pointItems = $pointSource->map(fn ($item) => $buildItem($item, 'ponto'));
    $companyItems = $companySource->map(fn ($item) => $buildItem($item, 'empresa'));
@endphp

<div class="site-page site-page-shell site-explore-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.common.explore')],
        ],
        'badge' => ui_text('ui.common.explore'),
        'title' => $currentCat?->nome ? ui_text('ui.explore.title_category', ['category' => $currentCat->nome]) : ui_text('ui.explore.title_default'),
        'subtitle' => $buscaAtual !== ''
            ? ui_text('ui.explore.hero_subtitle_results')
            : ui_text('ui.explore.hero_subtitle_default'),
        'meta' => [
            $currentCat?->nome,
            $buscaAtual !== '' ? ui_text('ui.explore.search_label', ['search' => $buscaAtual]) : null,
        ],
        'primaryActionLabel' => R::has('site.mapa') ? ui_text('ui.common.see_on_map') : null,
        'primaryActionHref' => R::has('site.mapa') ? $mapHref : null,
        'secondaryActionLabel' => $buscaAtual !== '' || $categoriaSlugAtual ? ui_text('ui.common.clear') : (R::has('site.home') ? ui_text('ui.common.home') : null),
        'secondaryActionHref' => ($buscaAtual !== '' || $categoriaSlugAtual)
            ? localized_route('site.explorar')
            : (R::has('site.home') ? localized_route('site.home') : null),
        'image' => asset('imagens/altamira.jpg'),
        'imageAlt' => ui_text('ui.explore.image_alt'),
        'compact' => true,
    ])

    <section class="site-section site-explore-discovery-section">
        <div class="site-surface site-search-shell site-explore-discovery-shell">
            <x-section-head
                :eyebrow="ui_text('ui.explore.discovery_eyebrow')"
                :title="ui_text('ui.explore.discovery_title')"
                :subtitle="ui_text('ui.explore.discovery_subtitle')"
            />

            <form method="get" class="site-search-form site-explore-search-form">
                <input
                    type="search"
                    name="busca"
                    value="{{ $buscaAtual }}"
                    placeholder="{{ ui_text('ui.explore.search_placeholder') }}"
                    class="ui-input"
                >

                @if(request('categoria_id'))
                    <input type="hidden" name="categoria_id" value="{{ request('categoria_id') }}">
                @elseif($categoriaSlugAtual)
                    <input type="hidden" name="categoria" value="{{ $categoriaSlugAtual }}">
                @endif

                <button class="site-button-primary" type="submit">{{ ui_text('ui.common.search') }}</button>
            </form>

            <div class="site-explore-categories-shell site-home-carousel-shell" x-data="{
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
                    const step = Math.max(el.clientWidth * 0.72, 220);
                    el.scrollBy({ left: step * direction, behavior: 'smooth' });
                    window.setTimeout(() => this.update(), 220);
                }
            }" x-init="$nextTick(() => update())">
                <div class="site-home-carousel-controls site-explore-carousel-controls" aria-hidden="true">
                    <button type="button" class="site-home-carousel-control" @click="move(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">&larr;</button>
                    <button type="button" class="site-home-carousel-control" @click="move(1)" :disabled="!canNext" :aria-disabled="!canNext">&rarr;</button>
                </div>

                <div class="site-explore-categories-rail site-home-carousel-track" x-ref="viewport" role="list" aria-label="{{ ui_text('ui.explore.categories_aria') }}" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                    <div class="site-home-carousel-slide">
                        <a
                            href="{{ localized_route('site.explorar') }}"
                            class="{{ empty($categoriaSlugAtual) ? 'site-explore-category-card is-active' : 'site-explore-category-card' }}"
                            @if(empty($categoriaSlugAtual)) aria-current="page" @endif
                        >
                            <span class="site-explore-category-icon site-explore-category-icon--all" aria-hidden="true">A</span>
                            <span class="site-explore-category-copy">
                                <span class="site-explore-category-title">{{ ui_text('ui.explore.all_categories') }}</span>
                                <span class="site-explore-category-meta">{{ ui_text('ui.explore.all_categories_meta') }}</span>
                            </span>
                        </a>
                    </div>

                    @foreach($categorias as $categoria)
                        @php
                            $isActive = $categoriaSlugAtual === ($categoria->slug ?? null);
                            $categoriaIcon = ! empty($categoria->icone_path)
                                ? \Illuminate\Support\Facades\Storage::url($categoria->icone_path)
                                : null;
                            $categoriaLabel = trim((string) ($categoria->nome ?? 'Categoria'));
                            $categoriaInitial = function_exists('mb_substr')
                                ? mb_strtoupper(mb_substr($categoriaLabel, 0, 1))
                                : strtoupper(substr($categoriaLabel, 0, 1));
                        @endphp

                        <div class="site-home-carousel-slide">
                            <a
                                href="{{ localized_route('site.explorar', ['categoria' => $categoria->slug]) }}"
                                class="{{ $isActive ? 'site-explore-category-card is-active' : 'site-explore-category-card' }}"
                                aria-label="{{ ui_text('ui.explore.category_aria', ['name' => $categoria->nome]) }}"
                                @if($isActive) aria-current="page" @endif
                            >
                                @if($categoriaIcon)
                                    <span class="site-explore-category-icon" aria-hidden="true">
                                        <img
                                            src="{{ $categoriaIcon }}"
                                            alt=""
                                            loading="lazy"
                                            decoding="async"
                                            class="site-explore-category-icon-image"
                                        >
                                    </span>
                                @else
                                    <span class="site-explore-category-icon site-explore-category-icon--fallback" aria-hidden="true">{{ $categoriaInitial }}</span>
                                @endif

                                <span class="site-explore-category-copy">
                                    <span class="site-explore-category-title">{{ $categoria->nome }}</span>
                                    <span class="site-explore-category-meta">{{ $isActive ? ui_text('ui.explore.category_meta_selected') : ui_text('ui.explore.category_meta_default') }}</span>
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="site-section">
        <div class="site-surface-soft site-context-strip site-explore-context-strip">
            <div class="site-context-strip-copy">
                <span class="site-badge">{{ ui_text('ui.common.list_plus_map') }}</span>
                <h2 class="site-section-head-title">{{ ui_text('ui.explore.context_title') }}</h2>
                <p class="site-section-head-subtitle">{{ ui_text('ui.explore.context_subtitle') }}</p>
            </div>
            <div class="site-context-strip-actions">
                <a href="{{ $mapHref }}" class="site-button-primary">{{ ui_text('ui.common.open_map') }}</a>
                @if($currentCat && R::has('site.categoria'))
                    <a href="{{ localized_route('site.categoria', ['slug' => $currentCat->slug]) }}" class="site-button-secondary">{{ ui_text('ui.common.view_category') }}</a>
                @endif
            </div>
        </div>
    </section>

    @if($pointItems->isNotEmpty())
        @include('site.partials._category_section', [
            'eyebrow' => ui_text('ui.explore.points_eyebrow'),
            'title' => ui_text('ui.explore.points_title'),
            'subtitle' => ui_text('ui.explore.points_subtitle'),
            'items' => $pointItems,
            'layout' => 'carousel',
            'cardVariant' => 'compact',
            'empty' => ui_text('ui.explore.points_empty'),
        ])
    @endif

    @if($companyItems->isNotEmpty())
        @include('site.partials._category_section', [
            'eyebrow' => ui_text('ui.explore.companies_eyebrow'),
            'title' => ui_text('ui.explore.companies_title'),
            'subtitle' => ui_text('ui.explore.companies_subtitle'),
            'items' => $companyItems,
            'layout' => 'carousel',
            'cardVariant' => 'compact',
            'empty' => ui_text('ui.explore.companies_empty'),
        ])
    @endif

</div>
@endsection

