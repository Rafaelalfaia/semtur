@php
    use Illuminate\Support\Facades\Route as R;

    $mapCategories = collect($mapCategories ?? []);
    $homeMapCategories = $mapCategories->values();
    $apiFeed = R::has('api.mapa.feed') ? route('api.mapa.feed') : url('/api/mapa/feed');
    $mapHref = $ctaHref ?? (R::has('site.mapa') ? localized_route('site.mapa') : '#');
    $mapEyebrow = $eyebrow ?? ui_text('ui.home.map_badge');
    $mapTitle = $title ?? ui_text('ui.home.map_title');
    $mapCtaLabel = $ctaLabel ?? ui_text('ui.home.map_open_full');
    $mapEditor = $editor ?? null;
    $TOK = '__TOKEN__';

    $safeUrl = function (string $name, array $params = [], $fallback = null) {
        try {
            return localized_route($name, $params);
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    $EMP_PATTERNS = array_values(array_filter([
        $safeUrl('site.empresa', ['empresa' => $TOK], null),
        $safeUrl('site.empresa', ['slugOrId' => $TOK], null),
    ]));

    $PTO_PATTERNS = array_values(array_filter([
        $safeUrl('site.ponto', ['ponto' => $TOK], null),
    ]));
@endphp

@include('site.partials._map_leaflet_assets')

<section class="site-section site-home-map-section" id="mapa-home">
    <div id="home-map-root" class="site-home-map-shell site-home-map-shell--compact">
        <div class="site-home-map-heading site-home-map-heading--compact">
            <div class="site-home-map-heading-copy">
                @if($mapEditor)
                    @include('site.partials._content_editor', [
                        'editorTitle' => $mapEditor['title'] ?? $mapTitle,
                        'editorPage' => $mapEditor['page'] ?? 'site.home',
                        'editorKey' => $mapEditor['key'] ?? 'map_section',
                        'editorLabel' => $mapEditor['label'] ?? 'Mapa da home',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo', 'cta_label', 'cta_href'],
                        'editableTranslation' => $mapEditor['translation'] ?? null,
                        'editableStatus' => $mapEditor['status'] ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => $mapEyebrow,
                            'titulo' => $mapTitle,
                            'cta_label' => $mapCtaLabel,
                            'cta_href' => $mapHref,
                        ],
                    ])
                @endif
                <p class="site-badge">{{ $mapEyebrow }}</p>
                <h2 class="site-section-head-title">{{ $mapTitle }}</h2>
            </div>

            <a href="{{ $mapHref }}" class="site-button-secondary">{{ $mapCtaLabel }}</a>
        </div>

        @if($homeMapCategories->isNotEmpty())
            <div class="site-home-map-filters site-home-map-filters--compact">
                <div class="site-map-rail-shell site-map-rail-shell--categories">
                    <div class="site-map-rail-toolbar site-map-rail-toolbar--categories" aria-hidden="true">
                        <button type="button" class="site-map-rail-control is-prev" data-map-scroll-target="home-map-categories-track" data-map-scroll-direction="-1" aria-label="{{ ui_text('ui.map_page.categories_prev') }}">&larr;</button>
                        <button type="button" class="site-map-rail-control is-next" data-map-scroll-target="home-map-categories-track" data-map-scroll-direction="1" aria-label="{{ ui_text('ui.map_page.categories_next') }}">&rarr;</button>
                    </div>
                    <div class="site-map-category-row site-map-category-row--top">
                        <div class="site-chips-shell">
                            <div id="home-map-categories-track" class="site-chips-scroll" role="group" aria-label="{{ ui_text('ui.home.map_categories_aria') }}">
                                <button type="button" class="site-chip site-chip-active" data-home-map-filter data-category="" data-label="{{ ui_text('ui.home.map_all') }}" aria-pressed="true">
                                    {{ ui_text('ui.home.map_all') }}
                                </button>

                                @foreach($homeMapCategories as $category)
                                    <button
                                        type="button"
                                        class="site-chip"
                                        data-home-map-filter
                                        data-category="{{ $category->slug }}"
                                        data-label="{{ $category->nome }}"
                                        aria-pressed="false"
                                    >
                                        @if(! empty($category->icone_path))
                                            <img
                                                src="{{ \Illuminate\Support\Facades\Storage::url($category->icone_path) }}"
                                                alt="{{ $category->nome }}"
                                                loading="lazy"
                                                decoding="async"
                                                class="site-chip-icon"
                                            >
                                        @endif
                                        <span>{{ $category->nome }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="site-home-map-stage site-home-map-stage--compact">
            <div class="site-home-map-viewport site-home-map-viewport--compact">
                <div
                    class="site-home-map-canvas site-home-map-canvas--compact"
                    id="home-map"
                    role="img"
                    aria-label="{{ ui_text('ui.home.map_canvas_aria') }}"
                ></div>
            </div>

            <div class="site-home-map-summary site-home-map-summary--compact">
                <p id="home-map-status" class="site-home-map-status">{{ ui_text('ui.home.map_loading') }}</p>
                <a href="{{ $mapHref }}" class="site-link">{{ $mapCtaLabel }}</a>
            </div>

            <div class="site-map-rail-shell site-map-rail-shell--cards">
                <div class="site-map-rail-toolbar site-map-rail-toolbar--cards" aria-hidden="true">
                    <button type="button" class="site-map-rail-control is-prev" data-map-scroll-target="home-map-cards" data-map-scroll-direction="-1" aria-label="{{ ui_text('ui.map_page.items_prev') }}">&larr;</button>
                    <button type="button" class="site-map-rail-control is-next" data-map-scroll-target="home-map-cards" data-map-scroll-direction="1" aria-label="{{ ui_text('ui.map_page.items_next') }}">&rarr;</button>
                </div>
                <div id="home-map-cards" class="site-home-map-results site-home-map-results--compact" aria-label="{{ ui_text('ui.home.map_results_aria') }}" data-map-autoplay="true"></div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    @include('site.partials._map_embed_script', [
        'mapConfig' => [
            'rootId' => 'home-map-root',
            'mapId' => 'home-map',
            'cardsId' => 'home-map-cards',
            'searchId' => null,
            'statusId' => 'home-map-status',
            'apiFeed' => $apiFeed,
            'routeToken' => $TOK,
            'empresaPatterns' => $EMP_PATTERNS,
            'pontoPatterns' => $PTO_PATTERNS,
            'initialItems' => [],
            'initialCategory' => null,
            'initialQuery' => '',
            'defaultCenter' => [-3.2049, -52.2176],
            'defaultZoom' => 13,
            'focusedZoom' => 15,
            'resultLimit' => 12,
            'requestLimit' => 60,
            'fitToResultsOnFirstLoad' => false,
            'useBoundsAfterFirstLoad' => true,
            'readFocusFromUrl' => false,
            'markerSizes' => [
                'mobile' => ['width' => 16, 'height' => 16, 'anchorX' => 8, 'anchorY' => 8, 'dot' => 6],
                'tablet' => ['width' => 18, 'height' => 18, 'anchorX' => 9, 'anchorY' => 9, 'dot' => 7],
                'desktop' => ['width' => 20, 'height' => 20, 'anchorX' => 10, 'anchorY' => 10, 'dot' => 7],
            ],
            'pingRadii' => [
                'mobile' => 5,
                'tablet' => 6,
                'desktop' => 7,
            ],
            'filterButtonSelector' => '[data-home-map-filter]',
            'emptyTitle' => ui_text('ui.home.map_empty_title'),
            'emptyCopy' => ui_text('ui.home.map_empty_copy'),
            'i18n' => [
                'altamira' => ui_text('ui.common.altamira'),
                'company' => ui_text('ui.explore.company_badge'),
                'point' => ui_text('ui.explore.point_badge'),
                'detail' => ui_text('ui.common.detail'),
                'route' => ui_text('ui.common.route'),
                'focus' => ui_text('ui.common.focus'),
                'all' => ui_text('ui.common.all'),
                'itemName' => ui_text('ui.map_page.item_name'),
                'helperWithRoute' => ui_text('ui.map_page.helper_with_route'),
                'helperWithoutRoute' => ui_text('ui.map_page.helper_without_route'),
                'emptyTitle' => ui_text('ui.home.map_empty_title'),
                'emptyCopy' => ui_text('ui.home.map_empty_copy'),
                'emptyStatus' => ui_text('ui.map_page.empty_status'),
            ],
        ],
    ])
@endpush
