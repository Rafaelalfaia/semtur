@extends('site.layouts.app')

@section('title', ui_text('ui.guides_index.title').' - Visit Altamira')
@section('meta.description', ui_text('ui.guides_index.meta_description'))
@section('meta.image', asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $tipoAtual = (string) ($tipo ?? '');
    $qAtual = (string) ($q ?? '');
    $totalMateriais = method_exists($materiais, 'total') ? $materiais->total() : $materiais->count();
    $agrupados = collect(method_exists($materiais, 'items') ? $materiais->items() : $materiais)->groupBy('tipo');
    $explorarUrl = localized_route('site.explorar');
    $homeUrl = localized_route('site.home');
    $heroMeta = array_filter([
    ]);
@endphp

<div class="site-page site-page-shell site-guides-page">
    @include('site.partials._page_hero', [
        'backHref' => $homeUrl,
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => $homeUrl],
            ['label' => ui_text('ui.guides_index.title')],
        ],
        'badge' => ui_text('ui.guides_index.badge'),
        'title' => ui_text('ui.guides_index.title'),
        'subtitle' => ui_text('ui.guides_index.subtitle'),
        'meta' => $heroMeta,
        'primaryActionLabel' => ui_text('ui.guides_index.primary_action'),
        'primaryActionHref' => '#lista-materiais',
        'secondaryActionLabel' => ui_text('ui.guides_index.secondary_action'),
        'secondaryActionHref' => $explorarUrl,
        'image' => asset('imagens/altamira.jpg'),
        'imageAlt' => ui_text('ui.guides_index.image_alt'),
        'compact' => true,
    ])

    <section class="site-section">
        <div class="site-surface-soft">
            <div class="site-app-toolbar site-app-toolbar--stacked">
                <div>
                    <p class="site-app-eyebrow">{{ ui_text('ui.common.filters') }}</p>
                    <h2 class="site-app-title">{{ ui_text('ui.guides_index.find_title') }}</h2>
                    <p class="site-app-copy">{{ ui_text('ui.guides_index.find_subtitle') }}</p>
                </div>
            </div>

            @if(!empty($tipos))
                <div class="site-guides-filter-chips">
                    <a href="{{ localized_route('site.guias') }}" class="{{ $tipoAtual === '' ? 'site-year-chip is-active' : 'site-year-chip' }}">
                        {{ ui_text('ui.common.all') }}
                    </a>

                    @foreach(($tipos ?? []) as $tipoKey => $tipoLabel)
                        <a
                            href="{{ localized_route('site.guias', array_filter(['tipo' => $tipoKey, 'q' => $qAtual ?: null])) }}"
                            class="{{ $tipoAtual === $tipoKey ? 'site-year-chip is-active' : 'site-year-chip' }}"
                        >
                            {{ $tipoLabel }}
                        </a>
                    @endforeach
                </div>
            @endif

            <form method="GET" class="site-guides-filter-form">
                <div class="site-guides-filter-field">
                    <label class="site-guides-filter-label" for="guias-busca">{{ ui_text('ui.common.search') }}</label>
                    <input
                        id="guias-busca"
                        type="text"
                        name="q"
                        value="{{ $qAtual }}"
                        placeholder="{{ ui_text('ui.guides_index.search_placeholder') }}"
                        class="site-search-input"
                    >
                </div>

                <div class="site-guides-filter-field">
                    <label class="site-guides-filter-label" for="guias-tipo">{{ ui_text('ui.common.category') }}</label>
                    <select id="guias-tipo" name="tipo" class="site-search-input site-search-select">
                        <option value="">{{ ui_text('ui.common.all') }}</option>
                        @foreach(($tipos ?? []) as $tipoKey => $tipoLabel)
                            <option value="{{ $tipoKey }}" @selected($tipoAtual === $tipoKey)>{{ $tipoLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="site-guides-filter-actions">
                    <button type="submit" class="site-button-primary">{{ ui_text('ui.guides_index.apply_filters') }}</button>

                    @if($qAtual !== '' || $tipoAtual !== '')
                        <a href="{{ localized_route('site.guias') }}" class="site-button-secondary">{{ ui_text('ui.common.clear') }}</a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <section id="lista-materiais" class="site-section">
        @if($totalMateriais === 0)
            <div class="site-empty-state">
                <p class="site-empty-state-title">{{ ui_text('ui.guides_index.empty_title') }}</p>
                <p class="site-empty-state-copy">{{ ui_text('ui.guides_index.empty_copy') }}</p>
            </div>
        @else
            <div class="site-guides-app-shell">
                @forelse($agrupados as $grupoTipo => $grupoItems)
                    <section class="site-guides-app-group">
                        <div class="site-guides-app-group-head">
                            <div>
                                <p class="site-app-eyebrow">{{ ui_text('ui.guides_index.library_eyebrow') }}</p>
                                <h2 class="site-guides-app-group-title">{{ ($tipos[$grupoTipo] ?? ucfirst($grupoTipo)) }}</h2>
                            </div>
                            <span class="site-guides-app-group-count">{{ count($grupoItems) }}</span>
                        </div>

                        <div class="site-directory-grid">
                            @foreach($grupoItems as $material)
                                @php
                                    $cover = $material->capa_url ?: asset('imagens/altamira.jpg');
                                @endphp

                                <article class="site-directory-card">
                                    <div class="site-directory-card-media">
                                        <img
                                            src="{{ $cover }}"
                                            alt="{{ $material->nome }}"
                                            class="site-directory-card-image"
                                            loading="lazy"
                                            decoding="async"
                                        >

                                        <div class="site-directory-card-overlay">
                                            <span class="site-badge">{{ $material->tipo_label }}</span>
                                        </div>
                                    </div>

                                    <div class="site-directory-card-body">
                                        <div>
                                            <h3 class="site-directory-card-title">{{ $material->nome }}</h3>
                                            <p class="site-inline-meta">{{ ui_text('ui.guides_index.card_meta') }}</p>
                                        </div>

                                        <p class="site-directory-card-summary">
                                            {{ Str::limit(strip_tags((string) $material->descricao), 140) }}
                                        </p>

                                        <div class="site-directory-card-actions">
                                            <a href="{{ localized_route('site.guias.show', ['slug' => $material->slug]) }}" class="site-button-primary">{{ ui_text('ui.guides_index.open_material') }}</a>

                                            @if(filled($material->link_acesso))
                                                <a
                                                    href="{{ $material->link_acesso }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="site-button-secondary"
                                                >
                                                    {{ ui_text('ui.guides_index.drive_label') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="site-empty-state">
                        <p class="site-empty-state-title">{{ ui_text('ui.guides_index.empty_title') }}</p>
                        <p class="site-empty-state-copy">{{ ui_text('ui.guides_index.empty_copy') }}</p>
                    </div>
                @endforelse
            </div>

            @if(method_exists($materiais, 'links'))
                <div class="site-surface-soft">
                    {{ $materiais->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
