@extends('site.layouts.app')

@section('title', ($heroTranslation?->seo_title ?: ($heroTranslation?->titulo ?: ui_text('ui.guides_index.title'))).' - Visit Altamira')
@section('meta.description', $heroTranslation?->seo_description ?: ($heroTranslation?->lead ?: ui_text('ui.guides_index.meta_description')))
@section('meta.image', $heroMedia?->url ?: asset('imagens/altamira.jpg'))

@section('site.content')
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $pageBlocks = $pageBlocks ?? collect();
    $guideBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'filters_section' => $pageBlocks->get('filters_section'),
        'listing_section' => $pageBlocks->get('listing_section'),
        'empty_state' => $pageBlocks->get('empty_state'),
    ];
    $guideTranslation = fn (string $key) => $guideBlocks[$key]?->getAttribute('traducao_resolvida');
    $filtersTranslation = $guideTranslation('filters_section');
    $listingTranslation = $guideTranslation('listing_section');
    $emptyTranslation = $guideTranslation('empty_state');

    $tipoAtual = (string) ($tipo ?? '');
    $qAtual = (string) ($q ?? '');
    $totalMateriais = method_exists($materiais, 'total') ? $materiais->total() : $materiais->count();
    $agrupados = collect(method_exists($materiais, 'items') ? $materiais->items() : $materiais)->groupBy('tipo');
    $explorarUrl = localized_route('site.explorar');
    $homeUrl = localized_route('site.home');
    $canCreateGuide = auth()->check() && auth()->user()->can('guias.create');
    $canManageGuide = auth()->check() && auth()->user()->can('guias.update');
    $createGuideHref = $canCreateGuide && Route::has('coordenador.guias.create')
        ? route('coordenador.guias.create')
        : null;
    $heroBadge = $heroTranslation?->eyebrow ?: ui_text('ui.guides_index.badge');
    $heroTitle = $heroTranslation?->titulo ?: ui_text('ui.guides_index.title');
    $heroSubtitle = $heroTranslation?->lead ?: ui_text('ui.guides_index.subtitle');
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.guides_index.primary_action');
    $heroPrimaryHref = $heroTranslation?->cta_href ?: '#lista-materiais';
    $heroMeta = array_filter([
    ]);

    $filtersEyebrow = $filtersTranslation?->eyebrow ?: ui_text('ui.common.filters');
    $filtersTitle = $filtersTranslation?->titulo ?: ui_text('ui.guides_index.find_title');
    $filtersSubtitle = $filtersTranslation?->lead ?: ui_text('ui.guides_index.find_subtitle');

    $listingEyebrow = $listingTranslation?->eyebrow ?: ui_text('ui.guides_index.library_eyebrow');
    $listingTitle = $listingTranslation?->titulo ?: ui_text('ui.guides_index.title');
    $listingSubtitle = $listingTranslation?->lead ?: ui_text('ui.guides_index.subtitle');

    $emptyTitle = $emptyTranslation?->titulo ?: ui_text('ui.guides_index.empty_title');
    $emptyCopy = $emptyTranslation?->lead ?: ui_text('ui.guides_index.empty_copy');
@endphp

<div class="site-page site-page-shell site-guides-page">
    @include('site.partials._page_hero', [
        'backHref' => $homeUrl,
        'breadcrumbs' => [
            ['label' => ui_text('ui.common.home'), 'href' => $homeUrl],
            ['label' => $heroTitle],
        ],
        'badge' => $heroBadge,
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'meta' => $heroMeta,
        'primaryActionLabel' => $heroPrimaryLabel,
        'primaryActionHref' => $heroPrimaryHref,
        'secondaryActionLabel' => ui_text('ui.guides_index.secondary_action'),
        'secondaryActionHref' => $explorarUrl,
        'image' => $heroMedia?->url ?: asset('imagens/altamira.jpg'),
        'imageAlt' => ui_text('ui.guides_index.image_alt'),
        'compact' => true,
        'textEditor' => [
            'title' => $heroTitle,
            'page' => 'site.guias',
            'key' => 'hero',
            'label' => 'Texto da capa de Guias',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href', 'seo_title', 'seo_description'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.guias',
            'key' => 'hero',
            'label' => 'Imagem da capa de Guias',
            'locale' => route_locale(),
            'trigger_label' => 'Editar imagem',
            'translation' => $heroTranslation ?? null,
            'media' => $heroMedia ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
            'media_slot' => 'hero',
            'media_label' => 'Imagem da capa',
            'preview_label' => 'imagem atual da capa',
        ],
    ])

    <section class="site-section">
        <div class="site-surface-soft">
            @include('site.partials._content_editor', [
                'editorTitle' => $filtersTitle,
                'editorPage' => 'site.guias',
                'editorKey' => 'filters_section',
                'editorLabel' => 'Seção de filtros de Guias',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline-compact',
                'editorTriggerLabel' => 'Editar texto',
                'editorFields' => ['eyebrow', 'titulo', 'lead'],
                'editableTranslation' => $filtersTranslation,
                'editableStatus' => $guideBlocks['filters_section']?->status ?? 'publicado',
                'editableFallback' => [
                    'eyebrow' => ui_text('ui.common.filters'),
                    'titulo' => ui_text('ui.guides_index.find_title'),
                    'lead' => ui_text('ui.guides_index.find_subtitle'),
                ],
            ])
            <div class="site-app-toolbar site-app-toolbar--stacked">
                <div>
                    <p class="site-app-eyebrow">{{ $filtersEyebrow }}</p>
                    <h2 class="site-app-title">{{ $filtersTitle }}</h2>
                    <p class="site-app-copy">{{ $filtersSubtitle }}</p>
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
        @if($createGuideHref)
            <div class="site-inline-actions">
                <a href="{{ $createGuideHref }}" class="site-button-primary">Novo material</a>
            </div>
        @endif

        @include('site.partials._content_editor', [
            'editorTitle' => $listingTitle,
            'editorPage' => 'site.guias',
            'editorKey' => 'listing_section',
            'editorLabel' => 'Seção de materiais de Guias',
            'editorLocale' => route_locale(),
            'editorTriggerVariant' => 'inline-compact',
            'editorTriggerLabel' => 'Editar texto',
            'editorFields' => ['eyebrow', 'titulo', 'lead'],
            'editableTranslation' => $listingTranslation,
            'editableStatus' => $guideBlocks['listing_section']?->status ?? 'publicado',
            'editableFallback' => [
                'eyebrow' => ui_text('ui.guides_index.library_eyebrow'),
                'titulo' => ui_text('ui.guides_index.title'),
                'lead' => ui_text('ui.guides_index.subtitle'),
            ],
        ])
        <x-section-head
            :eyebrow="$listingEyebrow"
            :title="$listingTitle"
            :subtitle="$listingSubtitle"
        />

        @if($totalMateriais === 0)
            <div class="site-empty-state">
                @include('site.partials._content_editor', [
                    'editorTitle' => $emptyTitle,
                    'editorPage' => 'site.guias',
                    'editorKey' => 'empty_state',
                    'editorLabel' => 'Estado vazio de Guias',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['titulo', 'lead'],
                    'editableTranslation' => $emptyTranslation,
                    'editableStatus' => $guideBlocks['empty_state']?->status ?? 'publicado',
                    'editableFallback' => [
                        'titulo' => ui_text('ui.guides_index.empty_title'),
                        'lead' => ui_text('ui.guides_index.empty_copy'),
                    ],
                ])
                <p class="site-empty-state-title">{{ $emptyTitle }}</p>
                <p class="site-empty-state-copy">{{ $emptyCopy }}</p>
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
                                    @if($canManageGuide && Route::has('coordenador.guias.edit'))
                                        <div class="site-inline-actions">
                                            <a href="{{ route('coordenador.guias.edit', $material) }}" class="site-button-secondary">Editar material</a>
                                        </div>
                                    @endif

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
                        @include('site.partials._content_editor', [
                            'editorTitle' => $emptyTitle,
                            'editorPage' => 'site.guias',
                            'editorKey' => 'empty_state',
                            'editorLabel' => 'Estado vazio de Guias',
                            'editorLocale' => route_locale(),
                            'editorTriggerVariant' => 'inline-compact',
                            'editorTriggerLabel' => 'Editar texto',
                            'editorFields' => ['titulo', 'lead'],
                            'editableTranslation' => $emptyTranslation,
                            'editableStatus' => $guideBlocks['empty_state']?->status ?? 'publicado',
                            'editableFallback' => [
                                'titulo' => ui_text('ui.guides_index.empty_title'),
                                'lead' => ui_text('ui.guides_index.empty_copy'),
                            ],
                        ])
                        <p class="site-empty-state-title">{{ $emptyTitle }}</p>
                        <p class="site-empty-state-copy">{{ $emptyCopy }}</p>
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
