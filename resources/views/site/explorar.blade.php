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
    $explorarCanonical = R::has('site.explorar') ? route('site.explorar') : url()->current();
    $explorarTitle = $currentCat?->nome ? 'Explorar '.$currentCat->nome : 'Explorar Altamira';
    $explorarDescription = $buscaAtual !== ''
        ? 'Resultados publicos em Altamira para a busca "'.$buscaAtual.'", com continuidade entre lista, mapa e paginas de detalhe.'
        : ($currentCat?->nome
            ? 'Explore '.$currentCat->nome.' em Altamira com conteudos publicados, leitura geografica e acesso direto ao mapa turistico.'
            : 'Explore pontos e empresas publicadas de Altamira com filtros editoriais, busca e conexao direta com o mapa turistico.');

    $explorarItems = $pointSource
        ->take(3)
        ->map(fn ($item) => [
            '@type' => 'ListItem',
            'position' => null,
            'url' => R::has('site.ponto') ? route('site.ponto', $item->slug ?? $item->id) : null,
            'name' => $item->nome ?? 'Ponto turistico',
        ])
        ->values()
        ->concat(
            $companySource->take(3)->map(fn ($item) => [
                '@type' => 'ListItem',
                'position' => null,
                'url' => R::has('site.empresa') ? route('site.empresa', $item->slug ?? $item->id) : null,
                'name' => $item->nome ?? 'Empresa',
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
                    'name' => 'Inicio',
                    'item' => R::has('site.home') ? route('site.home') : url('/'),
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
                'name' => 'Altamira',
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
        ? route('site.mapa', array_filter([
            'q' => $buscaAtual !== '' ? $buscaAtual : null,
            'categoria' => $categoriaSlugAtual ?: null,
        ]))
        : '#';

    $totalPontos = method_exists($pontos, 'total') ? $pontos->total() : collect($pontos)->count();
    $totalEmpresas = method_exists($empresas, 'total') ? $empresas->total() : collect($empresas)->count();

    $buildItem = function ($item, string $type) use ($categoriaSlugAtual, $buscaAtual) {
        $href = $type === 'empresa' && R::has('site.empresa')
            ? route('site.empresa', $item->slug ?? $item->id)
            : (R::has('site.ponto') ? route('site.ponto', $item->slug ?? $item->id) : '#');

        $mapHref = R::has('site.mapa')
            ? route('site.mapa', array_filter([
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
            'subtitle' => $item->cidade ?? 'Altamira',
            'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 96),
            'image' => $item->capa_url ?? $item->foto_capa_url ?? $item->perfil_url ?? null,
            'href' => $href,
            'badge' => $type === 'empresa' ? 'Empresa' : 'Ponto turistico',
            'meta' => null,
            'cta' => $type === 'empresa' ? 'Ver empresa' : 'Ver ponto',
            'map_href' => $mapHref,
        ];
    };

    $pointItems = $pointSource->map(fn ($item) => $buildItem($item, 'ponto'));
    $companyItems = $companySource->map(fn ($item) => $buildItem($item, 'empresa'));
@endphp

<div class="site-page site-page-shell site-explore-page">
    @include('site.partials._page_hero', [
        'backHref' => R::has('site.home') ? route('site.home') : url('/'),
        'breadcrumbs' => [
            ['label' => 'Inicio', 'href' => R::has('site.home') ? route('site.home') : url('/')],
            ['label' => 'Explorar'],
        ],
        'badge' => 'Explorar',
        'title' => $currentCat?->nome ? 'Explorar '.$currentCat->nome : 'Explorar Altamira',
        'subtitle' => $buscaAtual !== ''
            ? 'Resultados diretos para seguir entre lista, mapa e detalhe.'
            : 'Descubra lugares, rotas e servicos com leitura rapida e visual.',
        'meta' => [
            $currentCat?->nome,
            $buscaAtual !== '' ? 'Busca: '.$buscaAtual : null,
        ],
        'primaryActionLabel' => R::has('site.mapa') ? 'Ver no mapa' : null,
        'primaryActionHref' => R::has('site.mapa') ? $mapHref : null,
        'secondaryActionLabel' => $buscaAtual !== '' || $categoriaSlugAtual ? 'Limpar' : (R::has('site.home') ? 'Inicio' : null),
        'secondaryActionHref' => ($buscaAtual !== '' || $categoriaSlugAtual)
            ? route('site.explorar')
            : (R::has('site.home') ? route('site.home') : null),
        'image' => asset('imagens/altamira.jpg'),
        'imageAlt' => 'Explorar Altamira',
        'compact' => true,
    ])

    <section class="site-section site-explore-discovery-section">
        <div class="site-surface site-search-shell site-explore-discovery-shell">
            <x-section-head
                eyebrow="Descoberta"
                title="Comece pelas categorias"
                subtitle="Deslize, toque e refine a busca sem sair do ritmo da exploracao."
            />

            <form method="get" class="site-search-form site-explore-search-form">
                <input
                    type="search"
                    name="busca"
                    value="{{ $buscaAtual }}"
                    placeholder="Buscar no explorar"
                    class="ui-input"
                >

                @if(request('categoria_id'))
                    <input type="hidden" name="categoria_id" value="{{ request('categoria_id') }}">
                @elseif($categoriaSlugAtual)
                    <input type="hidden" name="categoria" value="{{ $categoriaSlugAtual }}">
                @endif

                <button class="site-button-primary" type="submit">Buscar</button>
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

                <div class="site-explore-categories-rail site-home-carousel-track" x-ref="viewport" role="list" aria-label="Categorias para explorar" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                    <div class="site-home-carousel-slide">
                        <a
                            href="{{ route('site.explorar') }}"
                            class="{{ empty($categoriaSlugAtual) ? 'site-explore-category-card is-active' : 'site-explore-category-card' }}"
                            @if(empty($categoriaSlugAtual)) aria-current="page" @endif
                        >
                            <span class="site-explore-category-icon site-explore-category-icon--all" aria-hidden="true">A</span>
                            <span class="site-explore-category-copy">
                                <span class="site-explore-category-title">Tudo</span>
                                <span class="site-explore-category-meta">Visao geral</span>
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
                                href="{{ route('site.explorar', ['categoria' => $categoria->slug]) }}"
                                class="{{ $isActive ? 'site-explore-category-card is-active' : 'site-explore-category-card' }}"
                                aria-label="Categoria {{ $categoria->nome }}"
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
                                    <span class="site-explore-category-meta">{{ $isActive ? 'Selecionada' : 'Explorar' }}</span>
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
                <span class="site-badge">Lista + mapa</span>
                <h2 class="site-section-head-title">Troque de contexto sem perder a descoberta</h2>
                <p class="site-section-head-subtitle">Veja a lista, abra no mapa e siga para o detalhe com a mesma leitura.</p>
            </div>
            <div class="site-context-strip-actions">
                <a href="{{ $mapHref }}" class="site-button-primary">Abrir no mapa</a>
                @if($currentCat && R::has('site.categoria'))
                    <a href="{{ route('site.categoria', $currentCat->slug) }}" class="site-button-secondary">Ver categoria</a>
                @endif
            </div>
        </div>
    </section>

    @if($pointItems->isNotEmpty())
        @include('site.partials._category_section', [
            'eyebrow' => 'Pontos',
            'title' => 'Atrativos',
            'subtitle' => 'Pontos encontrados agora.',
            'items' => $pointItems,
            'layout' => 'carousel',
            'cardVariant' => 'compact',
            'empty' => 'Nenhum atrativo apareceu com os filtros atuais. Tente ampliar a busca ou limpar os filtros.',
        ])
    @endif

    @if($companyItems->isNotEmpty())
        @include('site.partials._category_section', [
            'eyebrow' => 'Empresas',
            'title' => 'Empresas',
            'subtitle' => 'Servicos e operacoes do contexto atual.',
            'items' => $companyItems,
            'layout' => 'carousel',
            'cardVariant' => 'compact',
            'empty' => 'Nenhuma empresa apareceu com os filtros atuais. Vale testar outra categoria ou uma busca mais ampla.',
        ])
    @endif

</div>
@endsection
