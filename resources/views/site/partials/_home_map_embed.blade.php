@php
    use Illuminate\Support\Facades\Route as R;

    $mapCategories = collect($mapCategories ?? []);
    $homeMapCategories = $mapCategories->take(4)->values();
    $apiFeed = R::has('api.mapa.feed') ? route('api.mapa.feed') : url('/api/mapa/feed');
    $mapHref = R::has('site.mapa') ? route('site.mapa') : '#';
    $TOK = '__TOKEN__';

    $safeUrl = function (string $name, array $params = [], $fallback = null) {
        try {
            return route($name, $params);
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
                <p class="site-badge">Mapa da cidade</p>
                <h2 class="site-section-head-title">Explore Altamira no mapa</h2>

            </div>

            <a href="{{ $mapHref }}" class="site-button-secondary">Abrir mapa completo</a>
        </div>

        @if($homeMapCategories->isNotEmpty())
            <div class="site-home-map-filters site-home-map-filters--compact">
                <div class="site-chips-shell">
                    <div class="site-chips-scroll" role="group" aria-label="Categorias do mapa na home">
                        <button type="button" class="site-chip site-chip-active" data-home-map-filter data-category="" data-label="Tudo" aria-pressed="true">
                            Tudo
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
        @endif

        <div class="site-home-map-stage site-home-map-stage--compact">
            <div class="site-home-map-viewport site-home-map-viewport--compact">
                <div
                    class="site-home-map-canvas site-home-map-canvas--compact"
                    id="home-map"
                    role="img"
                    aria-label="Mapa interativo de Altamira com pontos turísticos e empresas publicadas"
                ></div>
            </div>

            <div class="site-home-map-summary site-home-map-summary--compact">
                <p id="home-map-status" class="site-home-map-status">Carregando locais publicados...</p>
                <a href="{{ $mapHref }}" class="site-link">Ver mapa completo</a>
            </div>

            <div id="home-map-cards" class="site-home-map-results site-home-map-results--compact" aria-label="Resultados do mapa na home"></div>
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
            'resultLimit' => 3,
            'requestLimit' => 60,
            'fitToResultsOnFirstLoad' => false,
            'useBoundsAfterFirstLoad' => true,
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
            'emptyTitle' => 'Nada apareceu nesta area',
            'emptyCopy' => 'Troque a categoria ou abra o mapa completo para explorar mais.',
        ],
    ])
@endpush
