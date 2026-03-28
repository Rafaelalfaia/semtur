@extends('site.layouts.app')

@php
  use Illuminate\Support\Facades\Route as R;

  $initItems = collect($initItems ?? [])->values();
  $currentQuery = trim((string) ($queryAtual ?? request('q', '')));
  $currentCategory = $categoriaAtual ?? null;
  $mapCanonical = R::has('site.mapa') ? route('site.mapa') : url()->current();
  $mapTitle = $currentCategory?->nome ? __('ui.map_page.title_category', ['category' => $currentCategory->nome]) : __('ui.map_page.title_default');
  $mapDescription = $currentQuery !== ''
      ? __('ui.map_page.description_search', ['search' => $currentQuery])
      : ($currentCategory?->nome
          ? __('ui.map_page.description_category', ['category' => $currentCategory->nome])
          : __('ui.map_page.description_default'));
  $mapSchemaItems = $initItems
      ->take(8)
      ->map(function ($item, $index) {
          $href = '#';

          if (($item['type'] ?? null) === 'empresa' && R::has('site.empresa')) {
              $href = route('site.empresa', $item['slug'] ?? $item['id'] ?? null);
          } elseif (R::has('site.ponto')) {
              $href = route('site.ponto', $item['slug'] ?? $item['id'] ?? null);
          }

          return [
              '@type' => 'ListItem',
              'position' => $index + 1,
              'name' => $item['nome'] ?? __('ui.map_page.item_name'),
              'url' => $href !== '#' ? $href : null,
          ];
      })
      ->filter(fn ($item) => filled($item['url']))
      ->values()
      ->all();
  $mapSchema = [
      [
          '@type' => 'BreadcrumbList',
          '@id' => $mapCanonical.'#breadcrumbs',
          'itemListElement' => [
              [
                  '@type' => 'ListItem',
                  'position' => 1,
                  'name' => __('ui.common.home'),
                  'item' => R::has('site.home') ? route('site.home') : url('/'),
              ],
              [
                  '@type' => 'ListItem',
                  'position' => 2,
                  'name' => __('ui.map_page.title_default'),
                  'item' => $mapCanonical,
              ],
          ],
      ],
      array_filter([
          '@type' => 'CollectionPage',
          '@id' => $mapCanonical.'#map',
          'url' => $mapCanonical,
          'name' => $mapTitle,
          'description' => $mapDescription,
          'about' => [
              '@type' => 'TouristDestination',
              'name' => __('ui.common.altamira'),
          ],
          'mainEntity' => $mapSchemaItems ? [
              '@type' => 'ItemList',
              'itemListElement' => $mapSchemaItems,
          ] : null,
      ], fn ($value) => $value !== null),
  ];
@endphp

@section('meta.image', theme_asset('hero_image'))
@section('title', $mapTitle)
@section('meta.description', $mapDescription)
@section('meta.canonical', $mapCanonical)
@section('meta.type', 'website')

@push('structured-data')
<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $mapSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@include('site.partials._map_leaflet_assets')

@section('site.content')
  @php
    $apiFeed = R::has('api.mapa.feed') ? route('api.mapa.feed') : url('/api/mapa/feed');

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

  <div id="mapa-root" class="site-map-page">
    <div id="map" class="site-map-canvas" role="img" aria-label="{{ __('ui.map_page.map_aria') }}"></div>
    <div class="site-map-gradient" aria-hidden="true"></div>

    <div class="site-map-searchwrap">
      <div class="site-map-searchpanel">
        <div class="site-map-searchpill">
          <svg class="site-map-searchicon" viewBox="0 0 24 24" fill="none" role="img" aria-hidden="true">
            <path d="M21 21l-4.2-4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.6"/>
          </svg>
          <input id="q" type="search" class="site-map-searchinput" placeholder="{{ __('ui.map_page.search_placeholder') }}" autocomplete="off" value="{{ $currentQuery }}" />
          <button type="button" class="site-map-searchclear" id="map-search-clear" @if($currentQuery === '') hidden @endif aria-label="{{ __('ui.map_page.clear_search') }}">{{ __('ui.map_page.clear_search') }}</button>
        </div>
        @if(collect($categorias ?? [])->isNotEmpty())
          <div class="site-map-category-row site-map-category-row--top">
            <button type="button" class="site-map-rail-control is-prev" data-map-scroll-target="map-categories-track" data-map-scroll-direction="-1" aria-label="{{ __('ui.map_page.categories_prev') }}">&larr;</button>
            @include('site.partials._categories_chips', [
                'categorias' => $categorias,
                'currentCat' => $currentCategory,
                'scrollId' => 'map-categories-track',
                'href' => fn ($cat) => route('site.mapa', array_filter([
                    'categoria' => $cat->slug,
                    'q' => $currentQuery !== '' ? $currentQuery : null,
                ])),
            ])
            <button type="button" class="site-map-rail-control is-next" data-map-scroll-target="map-categories-track" data-map-scroll-direction="1" aria-label="{{ __('ui.map_page.categories_next') }}">&rarr;</button>
          </div>
        @endif
      </div>
    </div>

    <section class="site-map-nearby" id="nearby" aria-label="{{ __('ui.map_page.nearby_items') }}">
      <div class="site-map-handle" aria-hidden="true"></div>
      <div class="site-map-sheet-head">
        <div class="site-map-sheet-actions">
          <button type="button" class="site-map-rail-control is-prev" data-map-scroll-target="cards" data-map-scroll-direction="-1" aria-label="{{ __('ui.map_page.items_prev') }}">&larr;</button>
          <button type="button" class="site-map-rail-control is-next" data-map-scroll-target="cards" data-map-scroll-direction="1" aria-label="{{ __('ui.map_page.items_next') }}">&rarr;</button>
        </div>
      </div>
      <div class="site-map-cards" id="cards" aria-label="{{ __('ui.map_page.nearby_items') }}"></div>
    </section>
  </div>
@endsection

@push('scripts')
  @include('site.partials._map_embed_script', [
      'mapConfig' => [
          'rootId' => 'mapa-root',
          'mapId' => 'map',
          'cardsId' => 'cards',
          'searchId' => 'q',
          'apiFeed' => $apiFeed,
          'routeToken' => $TOK,
          'empresaPatterns' => $EMP_PATTERNS,
          'pontoPatterns' => $PTO_PATTERNS,
          'initialItems' => $initItems->values()->all(),
          'initialCategory' => $currentCategory?->slug ?? null,
          'initialQuery' => $currentQuery,
          'defaultCenter' => [-3.2041, -52.2063],
          'defaultZoom' => 14,
          'focusedZoom' => 15,
          'resultLimit' => 12,
          'requestLimit' => 200,
          'fitPadding' => [40, 40],
          'fitToResultsOnFirstLoad' => false,
          'useBoundsAfterFirstLoad' => true,
          'readFocusFromUrl' => true,
          'statusId' => 'map-status',
          'currentCategoryLabel' => $currentCategory?->nome ?? null,
          'filterButtonSelector' => null,
          'markerSizes' => [
              'mobile' => ['width' => 14, 'height' => 14, 'anchorX' => 7, 'anchorY' => 7, 'dot' => 4],
              'tablet' => ['width' => 16, 'height' => 16, 'anchorX' => 8, 'anchorY' => 8, 'dot' => 5],
              'desktop' => ['width' => 18, 'height' => 18, 'anchorX' => 9, 'anchorY' => 9, 'dot' => 5],
          ],
          'pingRadii' => [
              'mobile' => 4,
              'tablet' => 5,
              'desktop' => 6,
          ],
          'emptyTitle' => __('ui.map_page.empty_title'),
          'emptyCopy' => __('ui.map_page.empty_copy'),
      ],
  ])
@endpush
