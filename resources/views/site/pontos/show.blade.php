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
    $mapsUrl = $ponto->maps_url ?: ((is_numeric($lat) && is_numeric($lng)) ? 'https://www.google.com/maps?q='.(float) $lat.','.(float) $lng : null);
    $mapHref = Route::has('site.mapa') ? localized_route('site.mapa', array_filter(['focus' => 'ponto:'.($ponto->slug ?: $ponto->id),'lat' => is_numeric($lat) ? (float) $lat : null,'lng' => is_numeric($lng) ? (float) $lng : null,'open' => 1,'categoria' => $categoriaPrincipal?->slug], fn($value) => !is_null($value) && $value !== '')) : '#';
    $explorarHref = Route::has('site.explorar') ? localized_route('site.explorar', array_filter(['categoria' => $categoriaPrincipal?->slug])) : '#';

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
@endphp

<div class="site-page site-page-shell site-ponto-page">
    @include('site.partials._page_hero', [
        'backHref' => $explorarHref !== '#' ? $explorarHref : (localized_route('site.home')),
        'breadcrumbs' => [
            ['label' => ui_text('ui.nav.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.nav.explore'), 'href' => $explorarHref !== '#' ? $explorarHref : null],
            ['label' => $nome],
        ],
        'badge' => ui_text('ui.point.badge'),
        'title' => $nome,
        'subtitle' => null,
        'meta' => [$cidade, $categoriaPrincipal?->nome],
        'primaryActionLabel' => ui_text('ui.common.open_map'),
        'primaryActionHref' => $mapHref,
        'secondaryActionLabel' => $mapsUrl ? ui_text('ui.common.open_route') : (Route::has('site.explorar') ? ui_text('ui.common.explore') : null),
        'secondaryActionHref' => $mapsUrl ?: (Route::has('site.explorar') ? $explorarHref : null),
        'image' => $capaUrl,
        'imageAlt' => ui_text('ui.point.cover_alt', ['name' => $nome]),
    ])

    <section class="site-section">
        <div class="site-detail-grid">
            <article class="site-surface site-detail-main">
                <x-section-head :eyebrow="ui_text('ui.common.about')" :title="ui_text('ui.point.about_title')" :subtitle="ui_text('ui.point.about_subtitle')" />
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
                    @if($localizacao->isEmpty())
                        <div class="site-empty-state"><p class="site-empty-state-copy">{{ ui_text('ui.point.location_empty') }}</p></div>
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
        <section class="site-section" x-data="{ canPrev:false, canNext:true, open:false, index:0, images:@js($galeria), update(){ const el=this.$refs.viewport; if(!el) return; this.canPrev=el.scrollLeft>12; this.canNext=(el.scrollWidth-el.clientWidth-el.scrollLeft)>12; }, move(direction){ const el=this.$refs.viewport; if(!el) return; const step=Math.max(el.clientWidth*0.76,240); el.scrollBy({ left: step * direction, behavior: 'smooth' }); window.setTimeout(() => this.update(), 220); }, show(i){ this.index=i; this.open=true; document.body.style.overflow='hidden'; }, close(){ this.open=false; document.body.style.overflow=''; }, next(){ this.index=(this.index+1)%this.images.length; }, prev(){ this.index=(this.index-1+this.images.length)%this.images.length; } }" x-init="$nextTick(() => update())">
            <x-section-head :eyebrow="ui_text('ui.common.images')" :title="ui_text('ui.common.gallery')" :subtitle="ui_text('ui.point.gallery_subtitle')" />
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
            <x-section-head :eyebrow="ui_text('ui.common.videos')" :title="ui_text('ui.point.videos_title')" :subtitle="ui_text('ui.point.videos_subtitle')" />
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
        <section class="site-section" x-data="{ canPrev:false, canNext:true, update(){ const el=this.$refs.viewport; if(!el) return; this.canPrev=el.scrollLeft>12; this.canNext=(el.scrollWidth-el.clientWidth-el.scrollLeft)>12; }, move(direction){ const el=this.$refs.viewport; if(!el) return; const step=Math.max(el.clientWidth*0.82,260); el.scrollBy({ left: step * direction, behavior: 'smooth' }); window.setTimeout(() => this.update(), 220); } }" x-init="$nextTick(() => update())">
            <x-section-head :eyebrow="ui_text('ui.common.connections')" :title="ui_text('ui.point.related_companies')" :subtitle="ui_text('ui.point.related_companies_subtitle')" />
            <div class="site-home-carousel-shell site-detail-related-shell">
                <div class="site-home-carousel-controls" aria-hidden="true">
                    <button type="button" class="site-home-carousel-control" @click="move(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">&larr;</button>
                    <button type="button" class="site-home-carousel-control" @click="move(1)" :disabled="!canNext" :aria-disabled="!canNext">&rarr;</button>
                </div>
                <div class="site-home-carousel-track site-detail-related-track" x-ref="viewport" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                    @foreach($relatedCompanies as $item)
                        <div class="site-home-carousel-slide site-detail-related-slide">
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
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection
