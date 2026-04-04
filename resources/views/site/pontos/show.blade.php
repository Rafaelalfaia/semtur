@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    $nome = $ponto->nome ?? ui_text('ui.point.name');
    $cidade = $ponto->cidade ?? ui_text('ui.common.altamira');
    $descricao = $ponto->descricao ?? null;
    $categorias = collect($ponto->categorias ?? []);
    $categoriaPrincipal = $categorias->first();
    $capaUrl = $ponto->capa_url ?? $ponto->foto_capa_url ?? (optional(collect($ponto->midias ?? [])->firstWhere('tipo', 'image'))->path ? Storage::url(collect($ponto->midias ?? [])->firstWhere('tipo', 'image')->path) : null) ?? theme_asset('hero_image');
    $pontoCanonical = Route::has('site.ponto') ? localized_route('site.ponto', ['ponto' => $ponto->slug ?? $ponto->id]) : url()->current();
    $pontoTitle = $nome.' '.ui_text('ui.category.title_suffix');
    $pontoDescription = \Illuminate\Support\Str::limit(strip_tags($descricao ?: ui_text('ui.point.meta_description')), 160);
    $lat = $ponto->lat ?? $ponto->latitude ?? null;
    $lng = $ponto->lng ?? $ponto->longitude ?? null;
    $pontoSchema = [[
        '@type' => 'BreadcrumbList','@id' => $pontoCanonical.'#breadcrumbs','itemListElement' => array_values(array_filter([
            ['@type' => 'ListItem','position' => 1,'name' => ui_text('ui.nav.home'),'item' => localized_route('site.home')],
            Route::has('site.explorar') ? ['@type' => 'ListItem','position' => 2,'name' => ui_text('ui.nav.explore'),'item' => localized_route('site.explorar')] : null,
            ['@type' => 'ListItem','position' => 3,'name' => $nome,'item' => $pontoCanonical],
        ])),
    ], array_filter([
        '@type' => 'TouristAttraction','@id' => $pontoCanonical.'#place','name' => $nome,'description' => $pontoDescription,'url' => $pontoCanonical,'image' => [$capaUrl],
        'touristType' => $categorias->pluck('nome')->filter()->values()->all(),
        'containedInPlace' => ['@type' => 'City','name' => $cidade],
        'address' => array_filter(['@type' => 'PostalAddress','streetAddress' => $ponto->endereco ?? null,'addressLocality' => $cidade,'addressRegion' => 'PA','addressCountry' => 'BR'], fn ($value) => $value !== null),
        'geo' => (is_numeric($lat) && is_numeric($lng)) ? ['@type' => 'GeoCoordinates','latitude' => (float) $lat,'longitude' => (float) $lng] : null,
    ], fn ($value) => $value !== null)];
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
    $pageBlocks = $pageBlocks ?? collect();
    $pointBlocks = [
        'hero' => $pageBlocks->get('hero'),
        'about_section' => $pageBlocks->get('about_section'),
        'location_section' => $pageBlocks->get('location_section'),
        'location_empty_state' => $pageBlocks->get('location_empty_state'),
        'gallery_section' => $pageBlocks->get('gallery_section'),
        'videos_section' => $pageBlocks->get('videos_section'),
        'related_companies_section' => $pageBlocks->get('related_companies_section'),
    ];
    $pointTranslation = fn (string $key) => $pointBlocks[$key]?->getAttribute('traducao_resolvida');
    $aboutTranslation = $pointTranslation('about_section');
    $locationTranslation = $pointTranslation('location_section');
    $locationEmptyTranslation = $pointTranslation('location_empty_state');
    $galleryTranslation = $pointTranslation('gallery_section');
    $videosTranslation = $pointTranslation('videos_section');
    $relatedCompaniesTranslation = $pointTranslation('related_companies_section');

    $mapsUrl = $ponto->maps_url ?: ((is_numeric($lat) && is_numeric($lng)) ? 'https://www.google.com/maps?q='.(float) $lat.','.(float) $lng : null);
    $mapHref = Route::has('site.mapa') ? localized_route('site.mapa', array_filter(['focus' => 'ponto:'.($ponto->slug ?: $ponto->id),'lat' => is_numeric($lat) ? (float) $lat : null,'lng' => is_numeric($lng) ? (float) $lng : null,'open' => 1,'categoria' => $categoriaPrincipal?->slug], fn($value) => !is_null($value) && $value !== '')) : '#';
    $explorarHref = Route::has('site.explorar') ? localized_route('site.explorar', array_filter(['categoria' => $categoriaPrincipal?->slug])) : '#';
    $canManagePoint = auth()->check() && auth()->user()->can('pontos.update');
    $canManageCompany = auth()->check() && auth()->user()->can('empresas.update');
    $editPointHref = $canManagePoint && Route::has('coordenador.pontos.edit')
        ? route('coordenador.pontos.edit', $ponto)
        : null;

    $galeria = collect($ponto->midias ?? [])->filter(fn($midia) => ($midia->tipo ?? null) === 'image')->sortBy('ordem')->map(function ($midia) use ($nome) {
        $src = $midia->url ?? (!empty($midia->path) ? Storage::url($midia->path) : null);
        return ['src' => site_image_url($src, 'gallery'),'alt' => $midia->alt ?: ui_text('ui.point.photo_alt', ['name' => $nome])];
    })->filter(fn ($item) => filled($item['src']))->values();

    $videos = collect($ponto->midias ?? [])->filter(fn($midia) => in_array($midia->tipo ?? null, ['video', 'video_file', 'video_link'], true))->values();
    $empresas = collect($empresasRelacionadas ?? []);
    $relatedCompanies = $empresas->map(fn($item) => ['title' => $item->nome ?? ui_text('ui.company.name'),'subtitle' => $item->cidade ?? ui_text('ui.common.altamira'),'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 72),'image' => $item->perfil_url ?? $item->capa_url ?? null,'href' => Route::has('site.empresa') ? localized_route('site.empresa', ['empresa' => $item->slug ?? $item->id]) : '#']);

    $localizacao = collect([
        ['label' => ui_text('ui.common.city'), 'value' => $cidade],
        ['label' => ui_text('ui.common.address'), 'value' => $ponto->endereco ?? null],
        ['label' => ui_text('ui.common.district'), 'value' => $ponto->bairro ?? null],
        ['label' => ui_text('ui.common.category'), 'value' => $categoriaPrincipal?->nome],
    ])->filter(fn ($item) => filled($item['value']))->values();

    $heroBadge = $heroTranslation?->eyebrow ?: ui_text('ui.point.badge');
    $heroTitle = $heroTranslation?->titulo ?: $nome;
    $heroSubtitle = $heroTranslation?->lead ?: null;
    $heroPrimaryLabel = $heroTranslation?->cta_label ?: ui_text('ui.common.open_map');
    $heroPrimaryHref = $heroTranslation?->cta_href ?: $mapHref;

    $aboutEyebrow = $aboutTranslation?->eyebrow ?: ui_text('ui.common.about');
    $aboutTitle = $aboutTranslation?->titulo ?: ui_text('ui.point.about_title');
    $aboutSubtitle = $aboutTranslation?->lead ?: ui_text('ui.point.about_subtitle');

    $locationTitle = $locationTranslation?->titulo ?: ui_text('ui.common.general_overview');
    $locationEyebrow = $locationTranslation?->eyebrow ?: ui_text('ui.common.location');
    $locationEmptyTitle = $locationEmptyTranslation?->titulo ?: ui_text('ui.common.location');
    $locationEmptyCopy = $locationEmptyTranslation?->lead ?: ui_text('ui.point.location_empty');

    $galleryEyebrow = $galleryTranslation?->eyebrow ?: ui_text('ui.common.images');
    $galleryTitle = $galleryTranslation?->titulo ?: ui_text('ui.common.gallery');
    $gallerySubtitle = $galleryTranslation?->lead ?: ui_text('ui.point.gallery_subtitle');

    $videosEyebrow = $videosTranslation?->eyebrow ?: ui_text('ui.common.videos');
    $videosTitle = $videosTranslation?->titulo ?: ui_text('ui.point.videos_title');
    $videosSubtitle = $videosTranslation?->lead ?: ui_text('ui.point.videos_subtitle');

    $relatedCompaniesEyebrow = $relatedCompaniesTranslation?->eyebrow ?: ui_text('ui.common.connections');
    $relatedCompaniesTitle = $relatedCompaniesTranslation?->titulo ?: ui_text('ui.point.related_companies');
    $relatedCompaniesSubtitle = $relatedCompaniesTranslation?->lead ?: ui_text('ui.point.related_companies_subtitle');
@endphp

<div class="site-page site-page-shell site-ponto-page">
    @include('site.partials._page_hero', [
        'backHref' => $explorarHref !== '#' ? $explorarHref : (localized_route('site.home')),
        'breadcrumbs' => [
            ['label' => ui_text('ui.nav.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.nav.explore'), 'href' => $explorarHref !== '#' ? $explorarHref : null],
            ['label' => $nome],
        ],
        'badge' => $heroBadge,
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'meta' => [$cidade, $categoriaPrincipal?->nome],
        'primaryActionLabel' => $heroPrimaryLabel,
        'primaryActionHref' => $heroPrimaryHref,
        'secondaryActionLabel' => $mapsUrl ? ui_text('ui.common.open_route') : (Route::has('site.explorar') ? ui_text('ui.common.explore') : null),
        'secondaryActionHref' => $mapsUrl ?: (Route::has('site.explorar') ? $explorarHref : null),
        'image' => $capaUrl,
        'imageAlt' => ui_text('ui.point.cover_alt', ['name' => $nome]),
        'compact' => true,
        'textEditor' => [
            'title' => $heroTitle,
            'page' => 'site.pontos.show',
            'key' => 'hero',
            'label' => 'Texto da capa do ponto',
            'locale' => route_locale(),
            'trigger_label' => 'Editar texto',
            'fields' => ['eyebrow', 'titulo', 'lead', 'cta_label', 'cta_href', 'seo_title', 'seo_description'],
            'translation' => $heroTranslation ?? null,
            'status' => $heroBlock?->status ?? 'publicado',
        ],
        'imageEditor' => [
            'title' => $heroTitle,
            'page' => 'site.pontos.show',
            'key' => 'hero',
            'label' => 'Imagem da capa do ponto',
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
        <div class="site-detail-grid">
            <article class="site-surface site-detail-main">
                @if($editPointHref)
                    <div class="site-inline-actions">
                        <a href="{{ $editPointHref }}" class="site-button-secondary">Editar ponto</a>
                    </div>
                @endif

                @include('site.partials._content_editor', [
                    'editorTitle' => $aboutTitle,
                    'editorPage' => 'site.pontos.show',
                    'editorKey' => 'about_section',
                    'editorLabel' => 'Seção sobre o ponto',
                    'editorLocale' => route_locale(),
                    'editorTriggerVariant' => 'inline-compact',
                    'editorTriggerLabel' => 'Editar texto',
                    'editorFields' => ['eyebrow', 'titulo', 'lead'],
                    'editableTranslation' => $aboutTranslation,
                    'editableStatus' => $pointBlocks['about_section']?->status ?? 'publicado',
                    'editableFallback' => [
                        'eyebrow' => ui_text('ui.common.about'),
                        'titulo' => ui_text('ui.point.about_title'),
                        'lead' => ui_text('ui.point.about_subtitle'),
                    ],
                ])
                <x-section-head :eyebrow="$aboutEyebrow" :title="$aboutTitle" :subtitle="$aboutSubtitle" />
                <div class="site-detail-copy site-prose">{!! $descricao ?: '<p>'.ui_text('ui.point.empty_description').'</p>' !!}</div>
                @if($categorias->isNotEmpty())
                    <div class="site-detail-chip-row">
                        @foreach($categorias as $categoria)
                            <a href="{{ Route::has('site.explorar') ? localized_route('site.explorar', ['categoria' => $categoria->slug]) : '#' }}" class="site-filter-chip">{{ $categoria->nome }}</a>
                        @endforeach
                    </div>
                @endif
            </article>

            <aside class="site-detail-aside">
                <section class="site-surface-soft site-content-block">
                    @include('site.partials._content_editor', [
                        'editorTitle' => $locationTitle,
                        'editorPage' => 'site.pontos.show',
                        'editorKey' => 'location_section',
                        'editorLabel' => 'Seção de localização do ponto',
                        'editorLocale' => route_locale(),
                        'editorTriggerVariant' => 'inline-compact',
                        'editorTriggerLabel' => 'Editar texto',
                        'editorFields' => ['eyebrow', 'titulo'],
                        'editableTranslation' => $locationTranslation,
                        'editableStatus' => $pointBlocks['location_section']?->status ?? 'publicado',
                        'editableFallback' => [
                            'eyebrow' => ui_text('ui.common.location'),
                            'titulo' => ui_text('ui.common.general_overview'),
                        ],
                    ])
                    @if($localizacao->isEmpty())
                        <div class="site-empty-state">
                            @include('site.partials._content_editor', [
                                'editorTitle' => $locationEmptyTitle,
                                'editorPage' => 'site.pontos.show',
                                'editorKey' => 'location_empty_state',
                                'editorLabel' => 'Estado vazio da localização do ponto',
                                'editorLocale' => route_locale(),
                                'editorTriggerVariant' => 'inline-compact',
                                'editorTriggerLabel' => 'Editar texto',
                                'editorFields' => ['titulo', 'lead'],
                                'editableTranslation' => $locationEmptyTranslation,
                                'editableStatus' => $pointBlocks['location_empty_state']?->status ?? 'publicado',
                                'editableFallback' => [
                                    'titulo' => ui_text('ui.common.location'),
                                    'lead' => ui_text('ui.point.location_empty'),
                                ],
                            ])
                            <p class="site-empty-state-title">{{ $locationEmptyTitle }}</p>
                            <p class="site-empty-state-copy">{{ $locationEmptyCopy }}</p>
                        </div>
                    @else
                        <div class="site-location-card-list">
                            @foreach($localizacao as $item)
                                <div class="site-location-card"><span class="site-location-card-label">{{ $item['label'] }}</span><strong class="site-location-card-value">{{ $item['value'] }}</strong></div>
                            @endforeach
                        </div>
                    @endif

                    <div class="site-inline-actions">
                        <a href="{{ $mapHref }}" class="site-button-primary">{{ ui_text('ui.common.open_map') }}</a>
                        @if($mapsUrl)
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">{{ ui_text('ui.common.open_route') }}</a>
                        @endif
                        @if($explorarHref !== '#')
                            <a href="{{ $explorarHref }}" class="site-button-secondary">{{ ui_text('ui.common.back_to_explore') }}</a>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </section>

    @if($galeria->isNotEmpty())
        <section class="site-section site-point-gallery-section" x-data="{ canPrev:false, canNext:true, open:false, index:0, images:@js($galeria), update(){ const el=this.$refs.viewport; if(!el) return; this.canPrev=el.scrollLeft>12; this.canNext=(el.scrollWidth-el.clientWidth-el.scrollLeft)>12; }, move(direction){ const el=this.$refs.viewport; if(!el) return; const step=Math.max(el.clientWidth*0.76,240); el.scrollBy({ left: step * direction, behavior: 'smooth' }); window.setTimeout(() => this.update(), 220); }, show(i){ this.index=i; this.open=true; document.body.style.overflow='hidden'; }, close(){ this.open=false; document.body.style.overflow=''; }, next(){ this.index=(this.index+1)%this.images.length; }, prev(){ this.index=(this.index-1+this.images.length)%this.images.length; } }" x-init="$nextTick(() => update())">
            @include('site.partials._content_editor', [
                'editorTitle' => $galleryTitle,
                'editorPage' => 'site.pontos.show',
                'editorKey' => 'gallery_section',
                'editorLabel' => 'Seção de galeria do ponto',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline-compact',
                'editorTriggerLabel' => 'Editar texto',
                'editorFields' => ['eyebrow', 'titulo', 'lead'],
                'editableTranslation' => $galleryTranslation,
                'editableStatus' => $pointBlocks['gallery_section']?->status ?? 'publicado',
                'editableFallback' => [
                    'eyebrow' => ui_text('ui.common.images'),
                    'titulo' => ui_text('ui.common.gallery'),
                    'lead' => ui_text('ui.point.gallery_subtitle'),
                ],
            ])
            <x-section-head :eyebrow="$galleryEyebrow" :title="$galleryTitle" :subtitle="$gallerySubtitle" />
            <div class="site-home-carousel-shell site-detail-gallery-shell">
                <div class="site-home-carousel-controls" aria-hidden="true">
                    <button type="button" class="site-home-carousel-control" @click="move(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">&larr;</button>
                    <button type="button" class="site-home-carousel-control" @click="move(1)" :disabled="!canNext" :aria-disabled="!canNext">&rarr;</button>
                </div>
                <div class="site-home-carousel-track site-detail-gallery-track" x-ref="viewport" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                    @foreach($galeria as $index => $foto)
                        <div class="site-home-carousel-slide site-detail-gallery-slide"><button type="button" class="site-detail-gallery-card" @click="show({{ $index }})"><img src="{{ $foto['src'] }}" alt="{{ $foto['alt'] }}" loading="lazy" decoding="async" class="site-detail-gallery-image"></button></div>
                    @endforeach
                </div>
            </div>
            <div x-show="open" x-cloak class="site-lightbox" @click.self="close()" x-transition.opacity>
                <div class="site-lightbox-frame site-jogos-lightbox-frame">
                    <button type="button" class="site-lightbox-close" @click="close()" aria-label="{{ ui_text('ui.common.gallery') }}">&times;</button>
                    <button type="button" class="site-lightbox-arrow is-prev" @click.stop="prev()" aria-label="{{ ui_text('ui.common.photos') }}">&#8249;</button>
                    <img :src="images[index]?.src" :alt="images[index]?.alt || ''" class="site-lightbox-image">
                    <button type="button" class="site-lightbox-arrow is-next" @click.stop="next()" aria-label="{{ ui_text('ui.common.photos') }}">&#8250;</button>
                </div>
            </div>
        </section>
    @endif

    @if($videos->isNotEmpty())
        <section class="site-section">
            @include('site.partials._content_editor', [
                'editorTitle' => $videosTitle,
                'editorPage' => 'site.pontos.show',
                'editorKey' => 'videos_section',
                'editorLabel' => 'Seção de vídeos do ponto',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline-compact',
                'editorTriggerLabel' => 'Editar texto',
                'editorFields' => ['eyebrow', 'titulo', 'lead'],
                'editableTranslation' => $videosTranslation,
                'editableStatus' => $pointBlocks['videos_section']?->status ?? 'publicado',
                'editableFallback' => [
                    'eyebrow' => ui_text('ui.common.videos'),
                    'titulo' => ui_text('ui.point.videos_title'),
                    'lead' => ui_text('ui.point.videos_subtitle'),
                ],
            ])
            <x-section-head :eyebrow="$videosEyebrow" :title="$videosTitle" :subtitle="$videosSubtitle" />
            <div class="site-card-list-grid">
                @foreach($videos as $video)
                    <div class="site-card">
                        <div class="site-card-list-body">
                            <span class="site-badge">{{ ui_text('ui.common.video') }}</span>
                            <h3 class="site-card-list-title">{{ $video->titulo ?? ui_text('ui.point.point_video') }}</h3>
                            <p class="site-card-list-summary">{{ \Illuminate\Support\Str::limit(strip_tags($video->descricao ?? ''), 120) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if($relatedCompanies->isNotEmpty())
        <section class="site-section site-point-related-section" x-data="{ canPrev:false, canNext:true, update(){ const el=this.$refs.viewport; if(!el) return; this.canPrev=el.scrollLeft>12; this.canNext=(el.scrollWidth-el.clientWidth-el.scrollLeft)>12; }, move(direction){ const el=this.$refs.viewport; if(!el) return; const step=Math.max(el.clientWidth*0.82,260); el.scrollBy({ left: step * direction, behavior: 'smooth' }); window.setTimeout(() => this.update(), 220); } }" x-init="$nextTick(() => update())">
            @include('site.partials._content_editor', [
                'editorTitle' => $relatedCompaniesTitle,
                'editorPage' => 'site.pontos.show',
                'editorKey' => 'related_companies_section',
                'editorLabel' => 'Seção de empresas relacionadas do ponto',
                'editorLocale' => route_locale(),
                'editorTriggerVariant' => 'inline-compact',
                'editorTriggerLabel' => 'Editar texto',
                'editorFields' => ['eyebrow', 'titulo', 'lead'],
                'editableTranslation' => $relatedCompaniesTranslation,
                'editableStatus' => $pointBlocks['related_companies_section']?->status ?? 'publicado',
                'editableFallback' => [
                    'eyebrow' => ui_text('ui.common.connections'),
                    'titulo' => ui_text('ui.point.related_companies'),
                    'lead' => ui_text('ui.point.related_companies_subtitle'),
                ],
            ])
            <x-section-head :eyebrow="$relatedCompaniesEyebrow" :title="$relatedCompaniesTitle" :subtitle="$relatedCompaniesSubtitle" />
            <div class="site-home-carousel-shell site-detail-related-shell">
                <div class="site-home-carousel-controls" aria-hidden="true">
                    <button type="button" class="site-home-carousel-control" @click="move(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">&larr;</button>
                    <button type="button" class="site-home-carousel-control" @click="move(1)" :disabled="!canNext" :aria-disabled="!canNext">&rarr;</button>
                </div>
                <div class="site-home-carousel-track site-detail-related-track" x-ref="viewport" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                    @foreach($relatedCompanies as $item)
                        <div class="site-home-carousel-slide site-detail-related-slide">
                            <div>
                                @if($canManageCompany && Route::has('coordenador.empresas.edit') && isset($empresas[$loop->index]))
                                    <div class="site-inline-actions">
                                        <a href="{{ route('coordenador.empresas.edit', $empresas[$loop->index]) }}" class="site-button-secondary">Editar empresa</a>
                                    </div>
                                @endif

                                <a href="{{ $item['href'] }}" class="site-detail-related-card">
                                    <div class="site-detail-related-media">
                                        @if($item['image'])
                                            <img src="{{ site_image_url($item['image'], 'card') }}" alt="{{ $item['title'] }}" class="site-detail-related-image" loading="lazy" decoding="async">
                                        @else
                                            <div class="site-detail-related-placeholder">{{ ui_text('ui.common.empty_image') }}</div>
                                        @endif
                                    </div>
                                    <div class="site-detail-related-copy">
                                        <span class="site-badge">{{ ui_text('ui.company.name') }}</span>
                                        <h3 class="site-detail-related-title">{{ $item['title'] }}</h3>
                                        <div class="site-detail-related-meta">{{ $item['subtitle'] }}</div>
                                        <p class="site-detail-related-summary">{{ $item['summary'] }}</p>
                                    </div>
                                    <span class="site-button-secondary site-detail-related-cta">{{ ui_text('ui.explore.view_company') }}</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection
