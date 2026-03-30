@extends('site.layouts.app')

@php
  $seoTitle = trim(($evento->nome ?? ui_text('ui.events.event')).($edicao->ano ? ' '.$edicao->ano : '').' '.ui_text('ui.events.in_city_suffix'));
  $seoDescription = \Illuminate\Support\Str::limit(strip_tags($edicao->resumo ?: ($evento->descricao ?? ui_text('ui.events.details'))), 160);
  $seoImage = $evento->capa_url
      ?? ($evento->capa_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($evento->capa_path) : null)
      ?? $evento->perfil_url
      ?? ($evento->perfil_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($evento->perfil_path) : null)
      ?? theme_asset('hero_image');
  $seoCanonical = isset($edicao->ano) && $edicao->ano
      ? localized_route('eventos.show', ['slug' => $evento->slug ?? $evento->id, 'ano' => $edicao->ano])
      : localized_route('eventos.show', ['slug' => $evento->slug ?? $evento->id]);
@endphp

@section('title', $seoTitle)
@section('meta.description', $seoDescription)
@section('meta.image', $seoImage)
@section('meta.canonical', $seoCanonical)
@section('meta.type', 'article')

@php
  $eventSchema = [
      [
          '@type' => 'BreadcrumbList',
          '@id' => $seoCanonical.'#breadcrumbs',
          'itemListElement' => array_values(array_filter([
              ['@type' => 'ListItem','position' => 1,'name' => ui_text('ui.nav.home'),'item' => localized_route('site.home')],
              \Illuminate\Support\Facades\Route::has('eventos.index') ? ['@type' => 'ListItem','position' => 2,'name' => ui_text('ui.events.title'),'item' => localized_route('eventos.index')] : null,
              ['@type' => 'ListItem','position' => 3,'name' => $seoTitle,'item' => $seoCanonical],
          ])),
      ],
      array_filter([
          '@type' => 'Event','@id' => $seoCanonical.'#event','name' => $evento->nome ?? ui_text('ui.events.event'),'description' => $seoDescription,'url' => $seoCanonical,'image' => [$seoImage],
          'startDate' => !empty($edicao->data_inicio) ? \Illuminate\Support\Carbon::parse($edicao->data_inicio)->toAtomString() : null,
          'endDate' => !empty($edicao->data_fim) ? \Illuminate\Support\Carbon::parse($edicao->data_fim)->toAtomString() : null,
          'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode','eventStatus' => 'https://schema.org/EventScheduled',
          'location' => array_filter(['@type' => 'Place','name' => trim((string) ($edicao->local ?? $evento->cidade ?? ui_text('ui.common.altamira'))),'address' => ['@type' => 'PostalAddress','addressLocality' => $evento->cidade ?? ui_text('ui.common.altamira'),'addressRegion' => 'PA','addressCountry' => 'BR'],'geo' => (is_numeric($edicao->lat ?? null) && is_numeric($edicao->lng ?? null)) ? ['@type' => 'GeoCoordinates','latitude' => (float) $edicao->lat,'longitude' => (float) $edicao->lng] : null], fn ($value) => $value !== null),
          'organizer' => ['@type' => 'Organization','name' => 'VisitAltamira','url' => config('app.url') ?: url('/')],
      ], fn ($value) => $value !== null),
  ];
@endphp

@push('structured-data')
<script type="application/ld+json">@json(['@context' => 'https://schema.org', '@graph' => $eventSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
@endpush

@section('site.content')
@php
  use Illuminate\Support\Facades\Storage;
  use Illuminate\Support\Facades\Route;

  $pub = fn($p) => $p ? Storage::disk('public')->url($p) : null;
  $nome = $evento->nome ?? ui_text('ui.events.event');
  $cidade = $evento->cidade ?? ui_text('ui.common.altamira');
  $descricao = $edicao->resumo ?: ($evento->descricao ?? null);
  $capaUrl = $evento->capa_url ?? $pub($evento->capa_path ?? null) ?? $evento->perfil_url ?? $pub($evento->perfil_path ?? null) ?? theme_asset('hero_image');
  $nota = property_exists($evento, 'rating') ? (float) $evento->rating : null;
  $quando = $edicao->periodo ?: (($edicao->data_inicio ? \Carbon\Carbon::parse($edicao->data_inicio)->format('d/m/Y') : null) . ($edicao->data_fim ? ' - '.\Carbon\Carbon::parse($edicao->data_fim)->format('d/m/Y') : '')) ?: ($edicao->ano ?? null);
  $onde = trim($edicao->local ?? '') ?: $cidade;
  $lat = is_numeric($edicao->lat ?? null) ? (float) $edicao->lat : null;
  $lng = is_numeric($edicao->lng ?? null) ? (float) $edicao->lng : null;
  $mapBase = Route::has('site.mapa') ? localized_route('site.mapa') : localized_route('site.mapa');
  $slugOrId = $evento->slug ?? $evento->id;
  $mapQuery = array_filter(['focus' => 'evento:'.$slugOrId,'lat' => $lat,'lng' => $lng,'open' => 1], fn($v) => $v !== null && $v !== '');
  $mapHref = $mapBase.(count($mapQuery) ? ('?'.http_build_query($mapQuery)) : '');

  $galeria = collect($edicao->midias ?? [])->map(function ($midia) {
      $src = \Illuminate\Support\Str::startsWith($midia->path, ['http://', 'https://', '/']) ? $midia->path : Storage::disk('public')->url($midia->path);
      return ['src' => $src, 'alt' => $midia->alt ?? ''];
  })->values();

  $atrativos = collect($edicao->atrativos ?? [])->map(function ($atrativo) use ($pub) {
      return ['title' => $atrativo->nome,'subtitle' => ui_text('ui.events.highlights'),'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $atrativo->descricao), 120),'image' => $atrativo->thumb_url ?? $pub($atrativo->thumb_path ?? null) ?? theme_asset('hero_image'),'badge' => ui_text('ui.events.highlight_badge')];
  });

  $anos = collect($anos ?? []);
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'badge' => ui_text('ui.events.event'),
        'title' => $nome,
        'subtitle' => ui_text('ui.events.description_subtitle'),
        'meta' => [$cidade, $quando, $nota ? number_format($nota, 1, ',', '.').' '.ui_text('ui.events.rating_suffix') : null],
        'primaryActionLabel' => ui_text('ui.common.open_map'),
        'primaryActionHref' => $mapHref,
        'secondaryActionLabel' => Route::has('eventos.index') ? ui_text('ui.agenda.view_full_agenda') : null,
        'secondaryActionHref' => Route::has('eventos.index') ? localized_route('eventos.index') : null,
        'image' => $capaUrl,
        'imageAlt' => $nome,
    ])

    @if($anos->count() > 1)
        <section class="site-section">
            <div class="site-surface-soft">
                <x-section-head :eyebrow="ui_text('ui.events.editions')" :title="ui_text('ui.events.choose_year')" :subtitle="ui_text('ui.events.choose_year_subtitle')" />
                <div class="site-filter-row">
                    @foreach($anos as $ano)
                        <a href="{{ localized_route('eventos.show', ['slug' => $evento->slug ?? $evento->id, 'ano' => $ano]) }}" class="{{ $ano == $edicao->ano ? 'site-year-chip is-active' : 'site-year-chip' }}">{{ $ano }}</a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    <x-section-head :eyebrow="ui_text('ui.common.about')" :title="ui_text('ui.events.description_title')" :subtitle="ui_text('ui.events.description_subtitle')" />
                    <div class="site-prose">{!! $descricao ?: '<p>'.ui_text('ui.events.edition_empty_description').'</p>' !!}</div>
                </section>

                @if($atrativos->isNotEmpty())
                    <section class="site-section">
                        <x-section-head :eyebrow="ui_text('ui.events.highlights')" :title="ui_text('ui.events.highlights_title')" :subtitle="ui_text('ui.events.highlights_subtitle')" />
                        <div class="site-card-list-grid">
                            @foreach($atrativos as $item)
                                <div class="site-card-list">
                                    <div class="site-card-list-media"><img src="{{ site_image_url($item['image'], 'card') }}" alt="{{ $item['title'] }}" class="site-card-list-image" loading="lazy" decoding="async"></div>
                                    <div class="site-card-list-body">
                                        <span class="site-badge">{{ $item['badge'] }}</span>
                                        <h3 class="site-card-list-title">{{ $item['title'] }}</h3>
                                        <p class="site-card-list-summary">{{ $item['summary'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="site-editorial-aside">
                <section class="site-surface-soft site-content-block">
                    <x-section-head :eyebrow="ui_text('ui.common.service')" :title="ui_text('ui.events.service_title')" />
                    <div class="site-stats-grid">
                        <div class="site-stat-card"><span class="site-stat-label">{{ ui_text('ui.common.date') }}</span><span class="site-stat-value">{{ $quando ?: ui_text('ui.events.to_define') }}</span></div>
                        <div class="site-stat-card"><span class="site-stat-label">{{ ui_text('ui.common.place') }}</span><span class="site-stat-value">{{ $onde }}</span></div>
                        <div class="site-stat-card"><span class="site-stat-label">{{ ui_text('ui.common.photos') }}</span><span class="site-stat-value">{{ $galeria->count() }}</span></div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    @if($galeria->isNotEmpty())
        <section class="site-section" x-data="{ open:false,index:0,images:@js($galeria), show(i){ this.index=i; this.open=true; document.body.style.overflow='hidden'; }, close(){ this.open=false; document.body.style.overflow=''; }, next(){ this.index=(this.index+1)%this.images.length; }, prev(){ this.index=(this.index-1+this.images.length)%this.images.length; } }">
            <x-section-head :eyebrow="ui_text('ui.common.gallery')" :title="ui_text('ui.events.edition_gallery_title')" :subtitle="ui_text('ui.events.edition_gallery_subtitle')" />
            <div class="site-gallery-grid">
                @foreach($galeria as $index => $img)
                    <button type="button" class="site-gallery-button" @click="show({{ $index }})"><img src="{{ site_image_url($img['src'], 'gallery') }}" alt="{{ $img['alt'] }}" class="site-gallery-image" loading="lazy" decoding="async"></button>
                @endforeach
            </div>

            <div x-show="open" x-cloak class="site-lightbox" @click.self="close()" x-transition.opacity>
                <div class="site-lightbox-frame">
                    <button type="button" class="site-lightbox-close" @click="close()" aria-label="{{ ui_text('ui.common.gallery') }}">&times;</button>
                    <button type="button" class="site-lightbox-arrow is-prev" @click.stop="prev()" aria-label="{{ ui_text('ui.common.photos') }}">&#8249;</button>
                    <img :src="images[index]?.src" :alt="images[index]?.alt || ''" class="site-lightbox-image">
                    <button type="button" class="site-lightbox-arrow is-next" @click.stop="next()" aria-label="{{ ui_text('ui.common.photos') }}">&#8250;</button>
                </div>
            </div>
        </section>
    @endif

    <div class="site-bottom-safe-space md:hidden" aria-hidden="true"></div>
</div>
@endsection
