@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    $nome = $ponto->nome ?? 'Ponto turistico';
    $cidade = $ponto->cidade ?? 'Altamira';
    $descricao = $ponto->descricao ?? null;
    $categorias = collect($ponto->categorias ?? []);
    $categoriaPrincipal = $categorias->first();
    $capaUrl = $ponto->capa_url
        ?? $ponto->foto_capa_url
        ?? (optional(collect($ponto->midias ?? [])->firstWhere('tipo', 'image'))->path ? Storage::url(collect($ponto->midias ?? [])->firstWhere('tipo', 'image')->path) : null)
        ?? theme_asset('hero_image');
    $pontoCanonical = Route::has('site.ponto')
        ? route('site.ponto', $ponto->slug ?? $ponto->id)
        : url()->current();
    $pontoTitle = $nome.' em Altamira';
    $pontoDescription = \Illuminate\Support\Str::limit(strip_tags($descricao ?: 'Conheca este ponto turistico em Altamira.'), 160);
    $lat = $ponto->lat ?? $ponto->latitude ?? null;
    $lng = $ponto->lng ?? $ponto->longitude ?? null;
    $pontoSchema = [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $pontoCanonical.'#breadcrumbs',
            'itemListElement' => array_values(array_filter([
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Inicio',
                    'item' => Route::has('site.home') ? route('site.home') : url('/'),
                ],
                Route::has('site.explorar') ? [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Explorar',
                    'item' => route('site.explorar'),
                ] : null,
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $nome,
                    'item' => $pontoCanonical,
                ],
            ])),
        ],
        array_filter([
            '@type' => 'TouristAttraction',
            '@id' => $pontoCanonical.'#place',
            'name' => $nome,
            'description' => $pontoDescription,
            'url' => $pontoCanonical,
            'image' => [$capaUrl],
            'touristType' => $categorias->pluck('nome')->filter()->values()->all(),
            'containedInPlace' => [
                '@type' => 'City',
                'name' => $cidade,
            ],
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $ponto->endereco ?? null,
                'addressLocality' => $cidade,
                'addressRegion' => 'PA',
                'addressCountry' => 'BR',
            ], fn ($value) => $value !== null),
            'geo' => (is_numeric($lat) && is_numeric($lng)) ? [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
            ] : null,
        ], fn ($value) => $value !== null),
    ];
@endphp

@section('title', $pontoTitle)
@section('meta.description', $pontoDescription)
@section('meta.image', $capaUrl)
@section('meta.canonical', $pontoCanonical)
@section('meta.type', 'article')

@push('structured-data')
<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $pontoSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@section('site.content')
@php
    $mapsUrl = $ponto->maps_url ?: ((is_numeric($lat) && is_numeric($lng)) ? 'https://www.google.com/maps?q='.(float) $lat.','.(float) $lng : null);
    $mapHref = Route::has('site.mapa')
        ? route('site.mapa', array_filter([
            'focus' => 'ponto:'.($ponto->slug ?: $ponto->id),
            'lat' => is_numeric($lat) ? (float) $lat : null,
            'lng' => is_numeric($lng) ? (float) $lng : null,
            'open' => 1,
            'categoria' => $categoriaPrincipal?->slug,
        ], fn($value) => !is_null($value) && $value !== ''))
        : '#';

    $explorarHref = Route::has('site.explorar')
        ? route('site.explorar', array_filter([
            'categoria' => $categoriaPrincipal?->slug,
        ]))
        : '#';

    $galeria = collect($ponto->midias ?? [])
        ->filter(fn($midia) => ($midia->tipo ?? null) === 'image')
        ->sortBy('ordem')
        ->values();

    $videos = collect($ponto->midias ?? [])
        ->filter(fn($midia) => in_array($midia->tipo ?? null, ['video', 'video_file', 'video_link'], true))
        ->values();

    $empresas = collect($empresasRelacionadas ?? []);
    $relatedCompanies = $empresas->map(fn($item) => [
        'title' => $item->nome ?? 'Empresa',
        'subtitle' => $item->cidade ?? 'Altamira',
        'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 110),
        'image' => $item->perfil_url ?? $item->capa_url ?? null,
        'href' => Route::has('site.empresa') ? route('site.empresa', $item->slug ?? $item->id) : '#',
        'badge' => 'Empresa',
        'meta' => 'Ligada a este ponto',
        'cta' => 'Ver empresa',
    ]);

    $localizacao = collect([
        ['label' => 'Cidade', 'value' => $cidade],
        ['label' => 'Endereco', 'value' => $ponto->endereco ?? null],
        ['label' => 'Bairro', 'value' => $ponto->bairro ?? null],
        ['label' => 'Categoria', 'value' => $categoriaPrincipal?->nome],
    ])->filter(fn ($item) => filled($item['value']))->values();
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'backHref' => $explorarHref !== '#' ? $explorarHref : (Route::has('site.home') ? route('site.home') : url('/')),
        'breadcrumbs' => [
            ['label' => 'Inicio', 'href' => Route::has('site.home') ? route('site.home') : url('/')],
            ['label' => 'Explorar', 'href' => $explorarHref !== '#' ? $explorarHref : null],
            ['label' => $nome],
        ],
        'badge' => 'Ponto turistico',
        'title' => $nome,
        'subtitle' => 'Detalhes públicos, leitura geográfica e caminhos para encaixar este lugar no roteiro com mais segurança.',
        'meta' => [
            $cidade,
            $categoriaPrincipal?->nome,
            $galeria->count().' fotos',
            $videos->count().' videos',
        ],
        'primaryActionLabel' => 'Abrir no mapa',
        'primaryActionHref' => $mapHref,
        'secondaryActionLabel' => $mapsUrl ? 'Abrir rota' : (Route::has('site.explorar') ? 'Explorar mais' : null),
        'secondaryActionHref' => $mapsUrl ?: (Route::has('site.explorar') ? $explorarHref : null),
        'image' => $capaUrl,
        'imageAlt' => 'Capa de '.$nome,
    ])

    <section class="site-section">
        <div class="site-detail-grid">
            <article class="site-surface site-detail-main">
                <x-section-head eyebrow="Sobre" title="Conheça este ponto" subtitle="Informações públicas, acesso e contexto do local." />
                <div class="site-detail-copy site-prose">
                    {!! $descricao ?: '<p>Este ponto ainda não tem uma descrição editorial publicada.</p>' !!}
                </div>

                @if($categorias->isNotEmpty())
                    <div class="site-detail-chip-row">
                        @foreach($categorias as $categoria)
                            <a href="{{ Route::has('site.explorar') ? route('site.explorar', ['categoria' => $categoria->slug]) : '#' }}" class="site-filter-chip">
                                {{ $categoria->nome }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </article>

            <aside class="site-detail-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head eyebrow="Localização" title="Leitura geográfica" subtitle="Use o mapa e a rota para entender melhor o entorno deste ponto." />

                    @if($localizacao->isEmpty())
                        <div class="site-empty-state">
                            <p class="site-empty-state-copy">Os dados de localização ainda não foram publicados para este ponto.</p>
                        </div>
                    @else
                        <div class="site-location-card-list">
                            @foreach($localizacao as $item)
                                <div class="site-location-card">
                                    <span class="site-location-card-label">{{ $item['label'] }}</span>
                                    <strong class="site-location-card-value">{{ $item['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="site-inline-actions">
                        <a href="{{ $mapHref }}" class="site-button-primary">Abrir no mapa</a>
                        @if($mapsUrl)
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">Abrir rota</a>
                        @endif
                        @if($explorarHref !== '#')
                            <a href="{{ $explorarHref }}" class="site-button-secondary">Voltar para explorar</a>
                        @endif
                    </div>
                </section>

                <section class="site-surface-soft site-content-block">
                    <x-section-head eyebrow="Resumo" title="Planeje sua visita" subtitle="Um resumo rápido para seguir na descoberta sem sair do fluxo." />
                    <div class="site-stats-grid">
                        <div class="site-stat-card">
                            <span class="site-stat-label">Galeria</span>
                            <span class="site-stat-value">{{ $galeria->count() }}</span>
                        </div>
                        <div class="site-stat-card">
                            <span class="site-stat-label">Videos</span>
                            <span class="site-stat-value">{{ $videos->count() }}</span>
                        </div>
                        <div class="site-stat-card">
                            <span class="site-stat-label">Empresas</span>
                            <span class="site-stat-value">{{ $relatedCompanies->count() }}</span>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    @if($galeria->isNotEmpty())
        <section class="site-section">
            <x-section-head eyebrow="Imagens" title="Galeria" subtitle="Imagens públicas deste ponto turístico." />
            <div class="site-gallery-grid">
                @foreach($galeria as $foto)
                    @php
                        $imageUrl = $foto->url ?? (!empty($foto->path) ? Storage::url($foto->path) : null);
                    @endphp
                    <div class="site-card">
                        <img src="{{ $imageUrl }}" alt="Foto de {{ $nome }}" loading="lazy" decoding="async" class="site-gallery-image">
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if($videos->isNotEmpty())
        <section class="site-section">
            <x-section-head eyebrow="Vídeos" title="Conteúdos audiovisuais" subtitle="Materiais públicos que ajudam a sentir melhor o lugar antes da visita." />
            <div class="site-card-list-grid">
                @foreach($videos as $video)
                    <div class="site-card">
                        <div class="site-card-list-body">
                            <span class="site-badge">Video</span>
                            <h3 class="site-card-list-title">{{ $video->titulo ?? 'Video do ponto' }}</h3>
                            <p class="site-card-list-summary">{{ \Illuminate\Support\Str::limit(strip_tags($video->descricao ?? ''), 120) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @include('site.partials._category_section', [
        'eyebrow' => 'Conexoes',
        'title' => 'Empresas relacionadas',
        'subtitle' => 'Serviços e operações publicadas conectadas a este ponto turístico.',
        'items' => $relatedCompanies,
        'empty' => 'Ainda não há empresas relacionadas publicadas para este ponto.',
    ])
</div>
@endsection
