@extends('site.layouts.app')

@php
  use Illuminate\Support\Facades\Route as R;

  $initItems = collect($initItems ?? [])->values();
  $currentQuery = trim((string) ($queryAtual ?? request('q', '')));
  $currentCategory = $categoriaAtual ?? null;
  $mapCanonical = R::has('site.mapa') ? route('site.mapa') : url()->current();
  $mapTitle = $currentCategory?->nome ? 'Mapa de '.$currentCategory->nome.' em Altamira' : 'Mapa turistico de Altamira';
  $mapDescription = $currentQuery !== ''
      ? 'Mapa turistico de Altamira com resultados publicados para "'.$currentQuery.'", conectando busca, proximidade e paginas de detalhe.'
      : ($currentCategory?->nome
          ? 'Mapa turistico de Altamira para explorar '.$currentCategory->nome.' com leitura geografica, rota e contexto.'
          : 'Mapa turistico oficial de Altamira com pontos e empresas publicados para planejar a visita com mais clareza.');
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
              'name' => $item['nome'] ?? 'Item turistico',
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
                  'name' => 'Inicio',
                  'item' => R::has('site.home') ? route('site.home') : url('/'),
              ],
              [
                  '@type' => 'ListItem',
                  'position' => 2,
                  'name' => 'Mapa turistico',
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
              'name' => 'Altamira',
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
    $explorarHref = R::has('site.explorar')
        ? route('site.explorar', array_filter([
            'busca' => $currentQuery !== '' ? $currentQuery : null,
            'categoria' => $currentCategory?->slug,
        ]))
        : '#';

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
    <div id="map" class="site-map-canvas" role="img" aria-label="Mapa de pontos turisticos e empresas"></div>
    <div class="site-map-gradient" aria-hidden="true"></div>

    <div class="site-map-searchwrap site-map-searchwrap-intro">
      <div class="site-surface site-map-heading">
        <div class="site-map-heading-copy">
          <x-section-head
            eyebrow="Mapa turistico"
            title="{{ $currentCategory?->nome ? 'Mapa de '.$currentCategory->nome : 'Mapa turistico de Altamira' }}"
            subtitle="{{ $currentQuery !== '' ? 'Resultados publicos filtrados pela sua busca atual, com continuidade entre mapa, explorar e detalhes.' : 'Explore pontos e empresas publicados e use o mapa para ler proximidade, rota e contexto geografico.' }}"
          />

          <p class="site-map-heading-note">
            {{ $currentQuery !== '' ? 'A busca atual continua ativa no mapa para ajudar a comparar proximidade, detalhe e rota no mesmo fluxo.' : 'Arraste o mapa, toque nos cards e use as categorias para descobrir o que combina melhor com o seu roteiro.' }}
          </p>

          <div class="site-map-heading-actions">
            @if($explorarHref !== '#')
              <a href="{{ $explorarHref }}" class="site-button-secondary">Voltar para a lista</a>
            @endif
            @if(R::has('site.home'))
              <a href="{{ route('site.home') }}" class="site-button-secondary">Inicio</a>
            @endif
          </div>
        </div>

        @if(collect($categorias ?? [])->isNotEmpty())
          <div class="site-map-category-row">
            @include('site.partials._categories_chips', [
                'categorias' => $categorias,
                'currentCat' => $currentCategory,
                'href' => fn ($cat) => route('site.mapa', array_filter([
                    'categoria' => $cat->slug,
                    'q' => $currentQuery !== '' ? $currentQuery : null,
                ])),
            ])
          </div>
        @endif
      </div>
    </div>

    <div class="site-map-searchwrap">
      <div class="site-map-searchpill">
        <svg class="site-map-searchicon" viewBox="0 0 24 24" fill="none" role="img" aria-hidden="true">
          <path d="M21 21l-4.2-4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.6"/>
        </svg>
        <input id="q" type="search" class="site-map-searchinput" placeholder="Buscar ponto ou empresa" autocomplete="off" value="{{ $currentQuery }}" />
      </div>
    </div>

    <section class="site-map-nearby" id="nearby" aria-label="Itens proximos">
      <div class="site-map-handle" aria-hidden="true"></div>
      <div class="site-map-sheet-head">
        <div>
          <p class="site-badge">Descoberta guiada</p>
          <h2 class="site-map-sheet-title">O que esta por perto agora</h2>
          <p class="site-map-sheet-subtitle">Toque em um card para focar no mapa, abrir o detalhe ou seguir a rota.</p>
        </div>
        @if($explorarHref !== '#')
          <a href="{{ $explorarHref }}" class="site-link">Ver em lista</a>
        @endif
      </div>
      <div class="site-map-cards" id="cards" aria-label="Itens proximos"></div>
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
          'defaultCenter' => [-3.2049, -52.2176],
          'defaultZoom' => 13,
          'focusedZoom' => 15,
          'resultLimit' => 12,
          'requestLimit' => 200,
          'fitPadding' => [40, 40],
          'fitToResultsOnFirstLoad' => true,
          'useBoundsAfterFirstLoad' => true,
          'readFocusFromUrl' => true,
          'statusId' => null,
          'filterButtonSelector' => null,
          'emptyTitle' => 'Nada apareceu nesta area',
          'emptyCopy' => 'Mova o mapa, limpe a busca ou troque de categoria para continuar explorando.',
      ],
  ])
@endpush
