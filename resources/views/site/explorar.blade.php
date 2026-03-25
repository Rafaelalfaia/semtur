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
        ? 'Resultados públicos em Altamira para a busca "'.$buscaAtual.'", com continuidade entre lista, mapa e páginas de detalhe.'
        : ($currentCat?->nome
            ? 'Explore '.$currentCat->nome.' em Altamira com conteúdos publicados, leitura geográfica e acesso direto ao mapa turístico.'
            : 'Explore pontos e empresas publicadas de Altamira com filtros editoriais, busca e conexão direta com o mapa turístico.');

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
@section('meta.image', theme_asset('hero_image'))
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
            'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 120),
            'image' => $item->capa_url ?? $item->foto_capa_url ?? $item->perfil_url ?? null,
            'href' => $href,
            'badge' => $type === 'empresa' ? 'Empresa' : 'Ponto turistico',
            'meta' => R::has('site.mapa') ? 'Abrir no mapa' : null,
            'cta' => $type === 'empresa' ? 'Ver empresa' : 'Ver ponto',
            'map_href' => $mapHref,
        ];
    };

    $pointItems = $pointSource->map(fn ($item) => $buildItem($item, 'ponto'));
    $companyItems = $companySource->map(fn ($item) => $buildItem($item, 'empresa'));
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'backHref' => R::has('site.home') ? route('site.home') : url('/'),
        'breadcrumbs' => [
            ['label' => 'Inicio', 'href' => R::has('site.home') ? route('site.home') : url('/')],
            ['label' => 'Explorar'],
        ],
        'badge' => 'Descoberta',
        'title' => $currentCat?->nome ? 'Explorar '.$currentCat->nome : 'Explorar Altamira',
        'subtitle' => $buscaAtual !== ''
            ? 'Resultados públicos conectados ao mapa e aos detalhes para continuar a descoberta sem perder contexto.'
            : 'Encontre atrativos, empresas e caminhos para montar a viagem com continuidade entre lista, mapa e detalhe.',
        'meta' => [
            $totalPontos.' pontos',
            $totalEmpresas.' empresas',
            $currentCat?->nome,
            $buscaAtual !== '' ? 'Busca: '.$buscaAtual : null,
        ],
        'primaryActionLabel' => R::has('site.mapa') ? 'Ver esta busca no mapa' : null,
        'primaryActionHref' => R::has('site.mapa') ? $mapHref : null,
        'secondaryActionLabel' => $buscaAtual !== '' || $categoriaSlugAtual ? 'Limpar filtros' : (R::has('site.home') ? 'Voltar ao inicio' : null),
        'secondaryActionHref' => ($buscaAtual !== '' || $categoriaSlugAtual)
            ? route('site.explorar')
            : (R::has('site.home') ? route('site.home') : null),
        'image' => theme_asset('hero_image'),
        'imageAlt' => 'Explorar Altamira',
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-surface site-search-shell">
            <x-section-head
                eyebrow="Busca e filtros"
                title="Refine sua descoberta"
                subtitle="Use os filtros para navegar pelas publicações e levar essa mesma leitura geográfica direto para o mapa."
            />

            <form method="get" class="site-search-form">
                <input
                    type="search"
                    name="busca"
                    value="{{ $buscaAtual }}"
                    placeholder="Buscar pontos ou empresas..."
                    class="ui-input"
                >

                @if(request('categoria_id'))
                    <input type="hidden" name="categoria_id" value="{{ request('categoria_id') }}">
                @elseif($categoriaSlugAtual)
                    <input type="hidden" name="categoria" value="{{ $categoriaSlugAtual }}">
                @endif

                <button class="site-button-primary" type="submit">Buscar</button>
            </form>

            @include('site.partials._categories_chips', [
                'categorias' => $categorias,
                'currentCat' => $currentCat,
            ])
        </div>
    </section>

    <section class="site-section">
        <div class="site-surface-soft site-context-strip">
            <div class="site-context-strip-copy">
                <span class="site-badge">Mapa e lista integrados</span>
                <h2 class="site-section-head-title">Continue a leitura no formato que fizer mais sentido agora</h2>
                <p class="site-section-head-subtitle">Os mesmos conteúdos públicos podem ser vistos em lista para comparar ou no mapa para entender proximidade, rota e contexto geográfico.</p>
            </div>
            <div class="site-context-strip-actions">
                <a href="{{ $mapHref }}" class="site-button-primary">Abrir no mapa</a>
                @if($currentCat && R::has('site.categoria'))
                    <a href="{{ route('site.categoria', $currentCat->slug) }}" class="site-button-secondary">Ver categoria</a>
                @endif
            </div>
        </div>
    </section>

    @include('site.partials._category_section', [
        'eyebrow' => 'Pontos',
        'title' => 'Atrativos turisticos',
        'subtitle' => 'Pontos publicados encontrados para a navegação atual.',
        'items' => $pointItems,
        'empty' => 'Nenhum atrativo apareceu com os filtros atuais. Tente ampliar a busca ou limpar os filtros.',
    ])

    @include('site.partials._category_section', [
        'eyebrow' => 'Empresas',
        'title' => 'Empresas',
        'subtitle' => 'Empresas públicas relacionadas ao contexto atual.',
        'items' => $companyItems,
        'empty' => 'Nenhuma empresa apareceu com os filtros atuais. Vale testar outra categoria ou uma busca mais ampla.',
    ])

    @if($pontos instanceof \Illuminate\Contracts\Pagination\Paginator && $pontos->hasPages())
        <div class="site-section">{{ $pontos->onEachSide(1)->links() }}</div>
    @endif

    @if($empresas instanceof \Illuminate\Contracts\Pagination\Paginator && $empresas->hasPages())
        <div class="site-section">{{ $empresas->onEachSide(1)->links() }}</div>
    @endif
</div>
@endsection
