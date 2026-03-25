@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    $pub = fn($path) => $path ? Storage::disk('public')->url($path) : null;

    $nome = $empresa->nome ?? 'Empresa';
    $cidade = $empresa->cidade ?? 'Altamira';
    $descricao = $empresa->descricao ?? null;
    $categorias = collect($empresa->categorias ?? []);
    $categoriaPrincipal = $categorias->first();
    $capaUrl = $empresa->capa_url ?? $empresa->foto_capa_url ?? $pub($empresa->capa_path ?? null) ?? theme_asset('hero_image');
    $perfilUrl = $empresa->perfil_url ?? $empresa->foto_perfil_url ?? $pub($empresa->perfil_path ?? null) ?? theme_asset('logo');
    $empresaCanonical = Route::has('site.empresa')
        ? route('site.empresa', $empresa->slug ?? $empresa->id)
        : url()->current();
    $empresaTitle = $nome.' em Altamira';
    $empresaDescription = \Illuminate\Support\Str::limit(strip_tags($descricao ?: 'Conheca esta empresa em Altamira.'), 160);
    $lat = $empresa->lat ?? $empresa->latitude ?? null;
    $lng = $empresa->lng ?? $empresa->longitude ?? null;
    $socialLinks = array_filter((array) ($empresa->social_links ?? []));
    $sameAs = collect([
        $socialLinks['instagram'] ?? null,
        $socialLinks['facebook'] ?? null,
        $socialLinks['youtube'] ?? null,
        $socialLinks['site'] ?? null,
        $socialLinks['whatsapp'] ?? null,
        $socialLinks['maps'] ?? null,
    ])->filter()->values()->all();
    $empresaSchema = [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $empresaCanonical.'#breadcrumbs',
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
                    'item' => $empresaCanonical,
                ],
            ])),
        ],
        array_filter([
            '@type' => 'LocalBusiness',
            '@id' => $empresaCanonical.'#business',
            'name' => $nome,
            'description' => $empresaDescription,
            'url' => $empresaCanonical,
            'image' => [$capaUrl, $perfilUrl],
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $empresa->endereco ?? null,
                'addressLocality' => $cidade,
                'addressRegion' => 'PA',
                'addressCountry' => 'BR',
            ], fn ($value) => $value !== null),
            'geo' => (is_numeric($lat) && is_numeric($lng)) ? [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
            ] : null,
            'sameAs' => $sameAs ?: null,
            'keywords' => $categorias->pluck('nome')->filter()->implode(', '),
        ], fn ($value) => $value !== null),
    ];
@endphp

@section('title', $empresaTitle)
@section('meta.description', $empresaDescription)
@section('meta.image', $capaUrl)
@section('meta.canonical', $empresaCanonical)
@section('meta.type', 'business.business')

@push('structured-data')
<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $empresaSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@section('site.content')
@php
    $galeria = collect($empresa->galeriaFotos ?? [])->sortBy('ordem')->values();
    $pacotes = collect($pontosRelacionados ?? []);
    $mapsUrl = $empresa->maps_url ?: ((is_numeric($lat) && is_numeric($lng)) ? 'https://www.google.com/maps?q='.(float) $lat.','.(float) $lng : null);
    $mapHref = Route::has('site.mapa')
        ? route('site.mapa', array_filter([
            'focus' => 'empresa:'.($empresa->slug ?: $empresa->id),
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

    $socialItems = collect([
        ['label' => 'WhatsApp', 'href' => $socialLinks['whatsapp'] ?? null],
        ['label' => 'Site', 'href' => $socialLinks['site'] ?? null],
        ['label' => 'Instagram', 'href' => $socialLinks['instagram'] ?? null],
        ['label' => 'Facebook', 'href' => $socialLinks['facebook'] ?? null],
        ['label' => 'YouTube', 'href' => $socialLinks['youtube'] ?? null],
        ['label' => 'Mapa', 'href' => $socialLinks['maps'] ?? $mapsUrl],
    ])->filter(fn($item) => filled($item['href']));

    $packageItems = $pacotes->map(fn($item) => [
        'title' => $item->nome ?? 'Experiencia',
        'subtitle' => $item->cidade ?? 'Altamira',
        'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 110),
        'image' => $item->capa_url ?? $item->foto_capa_url ?? null,
        'href' => Route::has('site.ponto') ? route('site.ponto', $item->slug ?? $item->id) : '#',
        'badge' => 'Ponto turistico',
        'meta' => 'Conectado a esta empresa',
        'cta' => 'Ver ponto',
    ]);

    $localizacao = collect([
        ['label' => 'Cidade', 'value' => $cidade],
        ['label' => 'Endereco', 'value' => $empresa->endereco ?? null],
        ['label' => 'Bairro', 'value' => $empresa->bairro ?? null],
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
        'badge' => 'Empresa',
        'title' => $nome,
        'subtitle' => 'Informacoes publicas, canais e conexoes para transformar a viagem em uma experiencia mais completa.',
        'meta' => [
            $cidade,
            $categoriaPrincipal?->nome,
            $galeria->count().' fotos',
            $socialItems->count() ? $socialItems->count().' canais' : null,
        ],
        'primaryActionLabel' => 'Abrir no mapa',
        'primaryActionHref' => $mapHref,
        'secondaryActionLabel' => $mapsUrl ? 'Abrir rota' : (!empty($socialLinks['site']) ? 'Acessar site' : null),
        'secondaryActionHref' => $mapsUrl ?: (!empty($socialLinks['site']) ? $socialLinks['site'] : null),
        'image' => $capaUrl,
        'imageAlt' => 'Capa de '.$nome,
    ])

    <section class="site-section">
        <div class="site-detail-grid">
            <article class="site-surface site-detail-main">
                <div class="site-detail-profile">
                    <img src="{{ $perfilUrl }}" alt="{{ $nome }}" class="site-detail-avatar" loading="lazy" decoding="async">
                    <div>
                        <x-section-head
                            eyebrow="Sobre"
                            title="Conheca a empresa"
                            subtitle="Apresentacao publica com informacoes para planejar contato, visita e servicos."
                        />
                    </div>
                </div>

                <div class="site-detail-copy site-prose">
                    {!! $descricao ?: '<p>Esta empresa ainda nao tem uma apresentacao editorial publicada.</p>' !!}
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
                    <x-section-head eyebrow="Localizacao" title="Use mapa e rota" subtitle="Leitura geografica para entender onde a empresa esta e o que faz sentido combinar na visita." />

                    @if($localizacao->isEmpty())
                        <div class="site-empty-state">
                            <p class="site-empty-state-title">Localizacao em atualizacao</p>
                            <p class="site-empty-state-copy">Os dados de localizacao ainda nao foram publicados para esta empresa.</p>
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
                    <x-section-head eyebrow="Contatos" title="Canais publicos" subtitle="Links para continuar a conversa fora do portal quando fizer sentido." />

                    @if($socialItems->isEmpty())
                        <div class="site-empty-state">
                            <p class="site-empty-state-title">Canais em atualizacao</p>
                            <p class="site-empty-state-copy">Ainda nao ha canais publicos cadastrados para esta empresa.</p>
                        </div>
                    @else
                        <div class="site-detail-links">
                            @foreach($socialItems as $item)
                                <a href="{{ $item['href'] }}" target="_blank" rel="noopener noreferrer" class="site-link">{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </aside>
        </div>
    </section>

    @if($galeria->isNotEmpty())
        <section class="site-section">
            <x-section-head eyebrow="Imagens" title="Galeria" subtitle="Imagens publicas da empresa para reforcar contexto e atmosfera." />
            <div class="site-gallery-grid">
                @foreach($galeria as $foto)
                    <div class="site-card">
                        <img src="{{ $foto->url }}" alt="{{ $foto->alt ?: 'Foto da galeria de '.$nome }}" loading="lazy" decoding="async" class="site-gallery-image">
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @include('site.partials._category_section', [
        'eyebrow' => 'Conexoes',
        'title' => 'Pontos relacionados',
        'subtitle' => 'Lugares e experiencias publicadas conectadas a esta empresa.',
        'items' => $packageItems,
        'emptyTitle' => 'Sem conexoes por enquanto',
        'empty' => 'Ainda nao ha pontos turisticos relacionados publicados para esta empresa.',
    ])
</div>
@endsection
