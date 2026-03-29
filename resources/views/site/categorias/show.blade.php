@extends('site.layouts.app')
@section('title', $categoria->nome . ' ' . __('ui.category.title_suffix', ['default' => 'em Altamira']))
@section('meta.description', __('ui.category.meta_description', ['category' => $categoria->nome]))
@section('meta.image', theme_asset('hero_image'))
@section('meta.canonical', url()->full())

@section('site.content')
@php
    use Illuminate\Support\Facades\Route as R;

    $breadcrumbs = [
        ['label' => __('ui.nav.home'), 'href' => localized_route('site.home')],
        ['label' => __('ui.nav.explore'), 'href' => localized_route('site.explorar')],
        ['label' => $categoria->nome],
    ];

    $cardsFromPontos = $pontos->map(function ($ponto) {
        $image = $ponto->capa_url ?? $ponto->foto_capa_url ?? optional($ponto->midias->first())->url ?? null;
        return [
            'title' => $ponto->nome,
            'subtitle' => $ponto->cidade ?? __('ui.common.altamira'),
            'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $ponto->descricao), 125),
            'image' => $image,
            'href' => localized_route('site.ponto', ['ponto' => $ponto->id]),
            'badge' => __('ui.explore.point_badge'),
            'cta' => __('ui.explore.view_place'),
        ];
    });

    $cardsFromEmpresas = $empresas->map(function ($empresa) {
        $image = $empresa->capa_url ?? $empresa->foto_capa_url ?? null;
        return [
            'title' => $empresa->nome,
            'subtitle' => $empresa->cidade ?? __('ui.common.altamira'),
            'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $empresa->descricao), 125),
            'image' => $image,
            'href' => localized_route('site.empresa', ['empresa' => $empresa->slug ?? $empresa->id]),
            'badge' => __('ui.explore.company_badge'),
            'cta' => __('ui.explore.view_company'),
        ];
    });
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'backHref' => R::has('site.explorar') ? localized_route('site.explorar') : url()->previous(),
        'breadcrumbs' => $breadcrumbs,
        'badge' => __('ui.category.badge'),
        'title' => $categoria->nome,
        'subtitle' => __('ui.category.subtitle'),
        'meta' => [
            __('ui.category.points_count', ['count' => $pontos->total()]),
            __('ui.category.companies_count', ['count' => $empresas->total()]),
            filled($q) ? __('ui.category.search_meta', ['search' => $q]) : null,
        ],
        'primaryActionLabel' => __('ui.explore.explore_all'),
        'primaryActionHref' => R::has('site.explorar') ? localized_route('site.explorar', ['categoria' => $categoria->slug]) : '#',
        'secondaryActionLabel' => __('ui.common.tourist_map'),
        'secondaryActionHref' => R::has('site.mapa') ? localized_route('site.mapa') : '#',
        'image' => theme_asset('hero_image'),
        'imageAlt' => $categoria->nome,
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-surface">
            <x-section-head
                :eyebrow="__('ui.category.navigation')"
                :title="__('ui.explore.refine_title')"
                :subtitle="__('ui.explore.refine_subtitle')"
            />

            <form method="get" class="site-search-form">
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="{{ __('ui.explore.search_in_category') }}"
                    class="w-full rounded-[var(--ui-radius-control)] border border-[var(--ui-border)] bg-[var(--ui-surface-raised)] px-4 py-3 text-sm text-[var(--ui-text)] outline-none focus:border-[var(--ui-primary)] focus:ring-4 focus:ring-[var(--ui-border-focus)]"
                >
                <button type="submit" class="site-button-primary">{{ __('ui.explore.apply_search') }}</button>
            </form>
        </div>
    </section>

    <section class="site-section">
        <x-section-head
            :eyebrow="__('ui.explore.points_eyebrow')"
            :title="__('ui.explore.places_in_category')"
            :subtitle="__('ui.explore.places_in_category_subtitle')"
        />

        @if($cardsFromPontos->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-copy">{{ __('ui.explore.places_empty_in_category') }}</p>
            </div>
        @else
            <div class="site-card-list-grid">
                @foreach($cardsFromPontos as $item)
                    <x-card-list :title="$item['title']" :subtitle="$item['subtitle']" :summary="$item['summary']" :image="$item['image']" :href="$item['href']" :badge="$item['badge']" :cta="$item['cta']" />
                @endforeach
            </div>
        @endif

        @if ($pontos->hasPages())
            <div class="site-surface-soft">
                {{ $pontos->appends(['q' => $q, 'tab' => 'pontos'])->onEachSide(1)->links() }}
            </div>
        @endif
    </section>

    <section class="site-section">
        <x-section-head
            :eyebrow="__('ui.explore.companies_eyebrow')"
            :title="__('ui.explore.related_companies')"
            :subtitle="__('ui.explore.related_companies_subtitle')"
        />

        @if($cardsFromEmpresas->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-copy">{{ __('ui.explore.companies_empty_in_category') }}</p>
            </div>
        @else
            <div class="site-card-list-grid">
                @foreach($cardsFromEmpresas as $item)
                    <x-card-list :title="$item['title']" :subtitle="$item['subtitle']" :summary="$item['summary']" :image="$item['image']" :href="$item['href']" :badge="$item['badge']" :cta="$item['cta']" />
                @endforeach
            </div>
        @endif

        @if ($empresas->hasPages())
            <div class="site-surface-soft">
                {{ $empresas->appends(['q' => $q, 'tab' => 'empresas'])->onEachSide(1)->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
