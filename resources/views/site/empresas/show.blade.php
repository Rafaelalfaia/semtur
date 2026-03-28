@extends('site.layouts.app')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    $pub = fn($path) => $path ? Storage::disk('public')->url($path) : null;

    $nome = $empresa->nome ?? __('ui.company.name');
    $cidade = $empresa->cidade ?? __('ui.common.altamira');
    $descricao = $empresa->descricao ?? null;
    $categorias = collect($empresa->categorias ?? []);
    $categoriaPrincipal = $categorias->first();
    $capaUrl = $empresa->capa_url ?? $empresa->foto_capa_url ?? $pub($empresa->capa_path ?? null) ?? theme_asset('hero_image');
    $perfilUrl = $empresa->perfil_url ?? $empresa->foto_perfil_url ?? $pub($empresa->perfil_path ?? null) ?? theme_asset('logo');
    $empresaCanonical = Route::has('site.empresa') ? route('site.empresa', $empresa->slug ?? $empresa->id) : url()->current();
    $empresaTitle = $nome.' '.__('ui.category.title_suffix');
    $empresaDescription = \Illuminate\Support\Str::limit(strip_tags($descricao ?: __('ui.company.meta_description')), 160);
    $lat = $empresa->lat ?? $empresa->latitude ?? null;
    $lng = $empresa->lng ?? $empresa->longitude ?? null;
    $socialLinks = array_filter((array) ($empresa->social_links ?? []));
    $sameAs = collect([$socialLinks['instagram'] ?? null,$socialLinks['facebook'] ?? null,$socialLinks['youtube'] ?? null,$socialLinks['site'] ?? null,$socialLinks['whatsapp'] ?? null,$socialLinks['maps'] ?? null])->filter()->values()->all();
    $empresaSchema = [[
        '@type' => 'BreadcrumbList','@id' => $empresaCanonical.'#breadcrumbs','itemListElement' => array_values(array_filter([
            ['@type' => 'ListItem','position' => 1,'name' => __('ui.nav.home'),'item' => Route::has('site.home') ? route('site.home') : url('/')],
            Route::has('site.explorar') ? ['@type' => 'ListItem','position' => 2,'name' => __('ui.nav.explore'),'item' => route('site.explorar')] : null,
            ['@type' => 'ListItem','position' => 3,'name' => $nome,'item' => $empresaCanonical],
        ])),
    ], array_filter([
        '@type' => 'LocalBusiness','@id' => $empresaCanonical.'#business','name' => $nome,'description' => $empresaDescription,'url' => $empresaCanonical,'image' => [$capaUrl, $perfilUrl],
        'address' => array_filter(['@type' => 'PostalAddress','streetAddress' => $empresa->endereco ?? null,'addressLocality' => $cidade,'addressRegion' => 'PA','addressCountry' => 'BR'], fn ($value) => $value !== null),
        'geo' => (is_numeric($lat) && is_numeric($lng)) ? ['@type' => 'GeoCoordinates','latitude' => (float) $lat,'longitude' => (float) $lng] : null,
        'sameAs' => $sameAs ?: null,
        'keywords' => $categorias->pluck('nome')->filter()->implode(', '),
    ], fn ($value) => $value !== null)];
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
    $galeriaItems = $galeria->map(fn($foto) => [
        'src' => site_image_url($foto->url ?? (!empty($foto->path) ? Storage::disk('public')->url($foto->path) : null), 'gallery'),
        'alt' => $foto->alt ?: __('ui.company.gallery_image_alt', ['name' => $nome]),
    ])->filter(fn ($item) => filled($item['src']))->values();
    $pacotes = collect($pontosRelacionados ?? []);
    $mapsUrl = $empresa->maps_url ?: ((is_numeric($lat) && is_numeric($lng)) ? 'https://www.google.com/maps?q='.(float) $lat.','.(float) $lng : null);
    $mapHref = Route::has('site.mapa') ? route('site.mapa', array_filter(['focus' => 'empresa:'.($empresa->slug ?: $empresa->id),'lat' => is_numeric($lat) ? (float) $lat : null,'lng' => is_numeric($lng) ? (float) $lng : null,'open' => 1,'categoria' => $categoriaPrincipal?->slug], fn($value) => !is_null($value) && $value !== '')) : '#';
    $explorarHref = Route::has('site.explorar') ? route('site.explorar', array_filter(['categoria' => $categoriaPrincipal?->slug])) : '#';

    $socialItems = collect([
        ['type' => 'whatsapp', 'label' => 'WhatsApp', 'href' => $socialLinks['whatsapp'] ?? null],
        ['type' => 'site', 'label' => 'Site', 'href' => $socialLinks['site'] ?? null],
        ['type' => 'instagram', 'label' => 'Instagram', 'href' => $socialLinks['instagram'] ?? null],
        ['type' => 'facebook', 'label' => 'Facebook', 'href' => $socialLinks['facebook'] ?? null],
        ['type' => 'youtube', 'label' => 'YouTube', 'href' => $socialLinks['youtube'] ?? null],
        ['type' => 'maps', 'label' => __('ui.common.map'), 'href' => $socialLinks['maps'] ?? $mapsUrl],
    ])->filter(fn($item) => filled($item['href']))->values();

    $packageItems = $pacotes->map(fn($item) => [
        'title' => $item->nome ?? __('ui.company.experience'),
        'subtitle' => $item->cidade ?? __('ui.common.altamira'),
        'summary' => \Illuminate\Support\Str::limit(strip_tags($item->descricao ?? ''), 72),
        'image' => $item->capa_url ?? $item->foto_capa_url ?? null,
        'href' => Route::has('site.ponto') ? route('site.ponto', $item->slug ?? $item->id) : '#',
    ]);

    $localizacao = collect([
        ['label' => __('ui.common.city'), 'value' => $cidade],
        ['label' => __('ui.common.address'), 'value' => $empresa->endereco ?? null],
        ['label' => __('ui.common.district'), 'value' => $empresa->bairro ?? null],
        ['label' => __('ui.common.category'), 'value' => $categoriaPrincipal?->nome],
    ])->filter(fn ($item) => filled($item['value']))->values();
@endphp

<div class="site-page site-page-shell site-empresa-page">
    @include('site.partials._page_hero', [
        'backHref' => $explorarHref !== '#' ? $explorarHref : (Route::has('site.home') ? route('site.home') : url('/')),
        'breadcrumbs' => [
            ['label' => __('ui.nav.home'), 'href' => Route::has('site.home') ? route('site.home') : url('/')],
            ['label' => __('ui.nav.explore'), 'href' => $explorarHref !== '#' ? $explorarHref : null],
            ['label' => $nome],
        ],
        'badge' => __('ui.company.name'),
        'title' => $nome,
        'subtitle' => null,
        'meta' => [$cidade, $categoriaPrincipal?->nome],
        'primaryActionLabel' => __('ui.common.open_map'),
        'primaryActionHref' => $mapHref,
        'secondaryActionLabel' => $mapsUrl ? __('ui.common.open_route') : (!empty($socialLinks['site']) ? __('ui.common.open_site') : null),
        'secondaryActionHref' => $mapsUrl ?: (!empty($socialLinks['site']) ? $socialLinks['site'] : null),
        'image' => $capaUrl,
        'imageAlt' => __('ui.company.cover_alt', ['name' => $nome]),
    ])

    <section class="site-section">
        <div class="site-detail-grid">
            <article class="site-surface site-detail-main">
                <div class="site-detail-profile">
                    <img src="{{ site_image_url($perfilUrl, 'avatar') }}" alt="{{ $nome }}" class="site-detail-avatar" loading="lazy" decoding="async">
                    <div>
                        <x-section-head :eyebrow="__('ui.common.about')" :title="__('ui.company.about_title')" :subtitle="__('ui.company.about_subtitle')" />
                    </div>
                </div>

                <div class="site-detail-copy site-prose">
                    {!! $descricao ?: '<p>'.__('ui.company.empty_description').'</p>' !!}
                </div>

                @if($categorias->isNotEmpty())
                    <div class="site-detail-chip-row">
                        @foreach($categorias as $categoria)
                            <a href="{{ Route::has('site.explorar') ? route('site.explorar', ['categoria' => $categoria->slug]) : '#' }}" class="site-filter-chip">{{ $categoria->nome }}</a>
                        @endforeach
                    </div>
                @endif
            </article>

            <aside class="site-detail-aside">
                <section class="site-surface-soft site-content-block">
                    @if($localizacao->isEmpty())
                        <div class="site-empty-state">
                            <p class="site-empty-state-title">{{ __('ui.company.location_updating') }}</p>
                            <p class="site-empty-state-copy">{{ __('ui.company.location_empty') }}</p>
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
                        <a href="{{ $mapHref }}" class="site-button-primary">{{ __('ui.common.open_map') }}</a>
                        @if($mapsUrl)
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="site-button-secondary">{{ __('ui.common.open_route') }}</a>
                        @endif
                        @if($explorarHref !== '#')
                            <a href="{{ $explorarHref }}" class="site-button-secondary">{{ __('ui.common.back_to_explore') }}</a>
                        @endif
                    </div>
                </section>

                <section class="site-surface-soft site-content-block">
                    <x-section-head :eyebrow="__('ui.common.contacts')" :title="__('ui.company.talk_to_company')" />

                    @if($socialItems->isEmpty())
                        <div class="site-empty-state">
                            <p class="site-empty-state-title">{{ __('ui.company.channels_updating') }}</p>
                            <p class="site-empty-state-copy">{{ __('ui.company.channels_empty') }}</p>
                        </div>
                    @else
                        <div class="site-company-contact-list">
                            @foreach($socialItems as $item)
                                <a href="{{ $item['href'] }}" target="_blank" rel="noopener noreferrer" class="site-company-contact-link">
                                    <span class="site-company-contact-icon site-company-contact-icon--{{ $item['type'] }}" aria-hidden="true">
                                        @switch($item['type'])
                                            @case('whatsapp')
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.05 4.94A9.9 9.9 0 0 0 12.03 2C6.57 2 2.13 6.43 2.13 11.89c0 1.75.46 3.47 1.34 4.98L2 22l5.27-1.38a9.9 9.9 0 0 0 4.76 1.21h.01c5.46 0 9.9-4.43 9.9-9.89 0-2.64-1.03-5.12-2.89-7zM12.04 20.1h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.13.82.84-3.05-.2-.31a8.14 8.14 0 0 1-1.26-4.35c0-4.5 3.67-8.17 8.19-8.17 2.18 0 4.23.84 5.77 2.38a8.1 8.1 0 0 1 2.39 5.79c0 4.5-3.68 8.16-8.11 8.16zm4.48-6.1c-.25-.12-1.47-.72-1.7-.8-.23-.09-.4-.12-.57.12-.17.25-.65.8-.8.96-.15.17-.3.19-.56.07-.25-.13-1.07-.39-2.03-1.24-.75-.67-1.26-1.48-1.41-1.73-.15-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.24.25-.4.08-.17.04-.31-.02-.43-.06-.12-.57-1.37-.78-1.88-.21-.5-.42-.43-.57-.44h-.49c-.17 0-.43.06-.66.31-.23.25-.87.85-.87 2.08s.89 2.42 1.02 2.58c.12.17 1.75 2.67 4.24 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.55.1.47-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.16-.48-.28z"/></svg>
                                                @break
                                            @case('instagram')
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 1.8A3.95 3.95 0 0 0 3.8 7.75v8.5A3.95 3.95 0 0 0 7.75 20.2h8.5a3.95 3.95 0 0 0 3.95-3.95v-8.5a3.95 3.95 0 0 0-3.95-3.95h-8.5zm8.95 1.35a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2zM12 6.6A5.4 5.4 0 1 1 6.6 12 5.4 5.4 0 0 1 12 6.6zm0 1.8A3.6 3.6 0 1 0 15.6 12 3.6 3.6 0 0 0 12 8.4z"/></svg>
                                                @break
                                            @case('facebook')
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8.1h2.73l.41-3.17H13.5V8.7c0-.92.26-1.54 1.58-1.54h1.69V4.32c-.29-.04-1.28-.12-2.43-.12-2.4 0-4.04 1.46-4.04 4.15v2.37H7.58v3.17h2.72V22h3.2z"/></svg>
                                                @break
                                            @case('youtube')
                                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.58 7.19a2.74 2.74 0 0 0-1.93-1.94C17.96 4.8 12 4.8 12 4.8s-5.96 0-7.65.45A2.74 2.74 0 0 0 2.42 7.2C2 8.9 2 12 2 12s0 3.1.42 4.8a2.74 2.74 0 0 0 1.93 1.94c1.69.45 7.65.45 7.65.45s5.96 0 7.65-.45a2.74 2.74 0 0 0 1.93-1.94C22 15.1 22 12 22 12s0-3.1-.42-4.81zM9.8 15.05V8.95L15.55 12 9.8 15.05z"/></svg>
                                                @break
                                            @case('site')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18"/><path d="M12 3a15 15 0 0 0 0 18"/></svg>
                                                @break
                                            @default
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l7-7 11 0 0 11-7 7-11 0z"/><circle cx="16" cy="8" r="1"/></svg>
                                        @endswitch
                                    </span>
                                    <span class="site-company-contact-label">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </aside>
        </div>
    </section>

    @if($galeriaItems->isNotEmpty())
        <section class="site-section" x-data="{ canPrev:false, canNext:true, open:false, index:0, images:@js($galeriaItems), update(){ const el=this.$refs.viewport; if(!el) return; this.canPrev=el.scrollLeft>12; this.canNext=(el.scrollWidth-el.clientWidth-el.scrollLeft)>12; }, move(direction){ const el=this.$refs.viewport; if(!el) return; const step=Math.max(el.clientWidth*0.76,240); el.scrollBy({ left: step * direction, behavior: 'smooth' }); window.setTimeout(() => this.update(), 220); }, show(i){ this.index=i; this.open=true; document.body.style.overflow='hidden'; }, close(){ this.open=false; document.body.style.overflow=''; }, next(){ this.index=(this.index+1)%this.images.length; }, prev(){ this.index=(this.index-1+this.images.length)%this.images.length; } }" x-init="$nextTick(() => update())">
            <x-section-head :eyebrow="__('ui.common.images')" :title="__('ui.common.gallery')" :subtitle="__('ui.company.gallery_subtitle')" />
            <div class="site-home-carousel-shell site-detail-gallery-shell">
                <div class="site-home-carousel-controls" aria-hidden="true">
                    <button type="button" class="site-home-carousel-control" @click="move(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">&larr;</button>
                    <button type="button" class="site-home-carousel-control" @click="move(1)" :disabled="!canNext" :aria-disabled="!canNext">&rarr;</button>
                </div>
                <div class="site-home-carousel-track site-detail-gallery-track" x-ref="viewport" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                    @foreach($galeriaItems as $index => $foto)
                        <div class="site-home-carousel-slide site-detail-gallery-slide"><button type="button" class="site-detail-gallery-card" @click="show({{ $index }})"><img src="{{ $foto['src'] }}" alt="{{ $foto['alt'] }}" loading="lazy" decoding="async" class="site-detail-gallery-image"></button></div>
                    @endforeach
                </div>
            </div>
            <div x-show="open" x-cloak class="site-lightbox" @click.self="close()" x-transition.opacity>
                <div class="site-lightbox-frame site-jogos-lightbox-frame">
                    <button type="button" class="site-lightbox-close" @click="close()" aria-label="{{ __('ui.common.gallery') }}">&times;</button>
                    <button type="button" class="site-lightbox-arrow is-prev" @click.stop="prev()" aria-label="{{ __('ui.common.photos') }}">&#8249;</button>
                    <img :src="images[index]?.src" :alt="images[index]?.alt || ''" class="site-lightbox-image">
                    <button type="button" class="site-lightbox-arrow is-next" @click.stop="next()" aria-label="{{ __('ui.common.photos') }}">&#8250;</button>
                </div>
            </div>
        </section>
    @endif

    @if($packageItems->isNotEmpty())
        <section class="site-section" x-data="{ canPrev:false, canNext:true, update(){ const el=this.$refs.viewport; if(!el) return; this.canPrev=el.scrollLeft>12; this.canNext=(el.scrollWidth-el.clientWidth-el.scrollLeft)>12; }, move(direction){ const el=this.$refs.viewport; if(!el) return; const step=Math.max(el.clientWidth*0.82,260); el.scrollBy({ left: step * direction, behavior: 'smooth' }); window.setTimeout(() => this.update(), 220); } }" x-init="$nextTick(() => update())">
            <x-section-head :eyebrow="__('ui.common.connections')" :title="__('ui.company.related_points')" :subtitle="__('ui.company.related_points_subtitle')" />
            <div class="site-home-carousel-shell site-detail-related-shell">
                <div class="site-home-carousel-controls" aria-hidden="true">
                    <button type="button" class="site-home-carousel-control" @click="move(-1)" :disabled="!canPrev" :aria-disabled="!canPrev">&larr;</button>
                    <button type="button" class="site-home-carousel-control" @click="move(1)" :disabled="!canNext" :aria-disabled="!canNext">&rarr;</button>
                </div>
                <div class="site-home-carousel-track site-detail-related-track" x-ref="viewport" @scroll.debounce.50ms="update()" x-on:resize.window.debounce.120ms="update()">
                    @foreach($packageItems as $item)
                        <div class="site-home-carousel-slide site-detail-related-slide">
                            <a href="{{ $item['href'] }}" class="site-detail-related-card">
                                <div class="site-detail-related-media">
                                    @if($item['image'])
                                        <img src="{{ site_image_url($item['image'], 'card') }}" alt="{{ $item['title'] }}" class="site-detail-related-image" loading="lazy" decoding="async">
                                    @else
                                        <div class="site-detail-related-placeholder">{{ __('ui.common.empty_image') }}</div>
                                    @endif
                                </div>
                                <div class="site-detail-related-copy">
                                    <span class="site-badge">{{ __('ui.point.badge') }}</span>
                                    <h3 class="site-detail-related-title">{{ $item['title'] }}</h3>
                                    <div class="site-detail-related-meta">{{ $item['subtitle'] }}</div>
                                    <p class="site-detail-related-summary">{{ $item['summary'] }}</p>
                                </div>
                                <span class="site-button-secondary site-detail-related-cta">{{ __('ui.explore.view_point') }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection