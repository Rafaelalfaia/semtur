@extends('site.layouts.app')

@php
  $seoTitle = trim(($evento->nome ?? 'Evento').($edicao->ano ? ' '.$edicao->ano : '').' em Altamira');
  $seoDescription = \Illuminate\Support\Str::limit(strip_tags($edicao->resumo ?: ($evento->descricao ?? 'Detalhes do evento em Altamira.')), 160);
  $seoImage = $evento->capa_url
      ?? ($evento->capa_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($evento->capa_path) : null)
      ?? $evento->perfil_url
      ?? ($evento->perfil_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($evento->perfil_path) : null)
      ?? theme_asset('hero_image');
  $seoCanonical = isset($edicao->ano) && $edicao->ano
      ? route('eventos.show', [$evento->slug ?? $evento->id, $edicao->ano])
      : route('eventos.show', [$evento->slug ?? $evento->id]);
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
              [
                  '@type' => 'ListItem',
                  'position' => 1,
                  'name' => 'Inicio',
                  'item' => \Illuminate\Support\Facades\Route::has('site.home') ? route('site.home') : url('/'),
              ],
              \Illuminate\Support\Facades\Route::has('eventos.index') ? [
                  '@type' => 'ListItem',
                  'position' => 2,
                  'name' => 'Eventos',
                  'item' => route('eventos.index'),
              ] : null,
              [
                  '@type' => 'ListItem',
                  'position' => 3,
                  'name' => $seoTitle,
                  'item' => $seoCanonical,
              ],
          ])),
      ],
      array_filter([
          '@type' => 'Event',
          '@id' => $seoCanonical.'#event',
          'name' => $evento->nome ?? 'Evento',
          'description' => $seoDescription,
          'url' => $seoCanonical,
          'image' => [$seoImage],
          'startDate' => !empty($edicao->data_inicio) ? \Illuminate\Support\Carbon::parse($edicao->data_inicio)->toAtomString() : null,
          'endDate' => !empty($edicao->data_fim) ? \Illuminate\Support\Carbon::parse($edicao->data_fim)->toAtomString() : null,
          'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
          'eventStatus' => 'https://schema.org/EventScheduled',
          'location' => array_filter([
              '@type' => 'Place',
              'name' => trim((string) ($edicao->local ?? $evento->cidade ?? 'Altamira')),
              'address' => [
                  '@type' => 'PostalAddress',
                  'addressLocality' => $evento->cidade ?? 'Altamira',
                  'addressRegion' => 'PA',
                  'addressCountry' => 'BR',
              ],
              'geo' => (is_numeric($edicao->lat ?? null) && is_numeric($edicao->lng ?? null)) ? [
                  '@type' => 'GeoCoordinates',
                  'latitude' => (float) $edicao->lat,
                  'longitude' => (float) $edicao->lng,
              ] : null,
          ], fn ($value) => $value !== null),
          'organizer' => [
              '@type' => 'Organization',
              'name' => 'VisitAltamira',
              'url' => config('app.url') ?: url('/'),
          ],
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

  $nome = $evento->nome ?? 'Evento';
  $cidade = $evento->cidade ?? 'Altamira';
  $descricao = $edicao->resumo ?: ($evento->descricao ?? null);

  $capaUrl = $evento->capa_url
      ?? $pub($evento->capa_path ?? null)
      ?? $evento->perfil_url
      ?? $pub($evento->perfil_path ?? null)
      ?? theme_asset('hero_image');

  $nota = property_exists($evento, 'rating') ? (float) $evento->rating : null;
  $quando = $edicao->periodo
      ?: (($edicao->data_inicio ? \Carbon\Carbon::parse($edicao->data_inicio)->format('d/m/Y') : null)
      . ($edicao->data_fim ? ' - '.\Carbon\Carbon::parse($edicao->data_fim)->format('d/m/Y') : ''))
      ?: ($edicao->ano ?? null);

  $onde = trim($edicao->local ?? '') ?: $cidade;
  $lat = is_numeric($edicao->lat ?? null) ? (float) $edicao->lat : null;
  $lng = is_numeric($edicao->lng ?? null) ? (float) $edicao->lng : null;
  $mapBase = Route::has('site.mapa') ? route('site.mapa') : url('/mapa');
  $slugOrId = $evento->slug ?? $evento->id;
  $mapQuery = array_filter([
      'focus' => 'evento:'.$slugOrId,
      'lat' => $lat,
      'lng' => $lng,
      'open' => 1,
  ], fn($v) => $v !== null && $v !== '');
  $mapHref = $mapBase.(count($mapQuery) ? ('?'.http_build_query($mapQuery)) : '');

  $galeria = collect($edicao->midias ?? [])->map(function ($midia) {
      $src = \Illuminate\Support\Str::startsWith($midia->path, ['http://', 'https://', '/'])
          ? $midia->path
          : Storage::disk('public')->url($midia->path);
      return ['src' => $src, 'alt' => $midia->alt ?? ''];
  })->values();

  $atrativos = collect($edicao->atrativos ?? [])->map(function ($atrativo) use ($pub) {
      return [
          'title' => $atrativo->nome,
          'subtitle' => 'Atrativo do evento',
          'summary' => \Illuminate\Support\Str::limit(strip_tags((string) $atrativo->descricao), 120),
          'image' => $atrativo->thumb_url ?? $pub($atrativo->thumb_path ?? null) ?? theme_asset('hero_image'),
          'badge' => 'Atrativo',
      ];
  });

  $anos = collect($anos ?? []);
@endphp

<div class="site-page site-page-shell">
    @include('site.partials._page_hero', [
        'badge' => 'Evento',
        'title' => $nome,
        'subtitle' => 'Informacoes publicas, periodo e contexto para acompanhar a experiencia do evento com mais clareza.',
        'meta' => [
            $cidade,
            $quando,
            $nota ? number_format($nota, 1, ',', '.').' de avaliacao' : null,
        ],
        'primaryActionLabel' => 'Abrir no mapa',
        'primaryActionHref' => $mapHref,
        'secondaryActionLabel' => Route::has('eventos.index') ? 'Ver agenda' : null,
        'secondaryActionHref' => Route::has('eventos.index') ? route('eventos.index') : null,
        'image' => $capaUrl,
        'imageAlt' => 'Capa de '.$nome,
    ])

    @if($anos->count() > 1)
        <section class="site-section">
            <div class="site-surface-soft">
                <x-section-head eyebrow="Edicoes" title="Escolha o ano" subtitle="Troque entre as edicoes publicadas sem sair da pagina do evento." />
                <div class="site-filter-row">
                    @foreach($anos as $ano)
                        <a href="{{ route('eventos.show', [$evento->slug ?? $evento->id, $ano]) }}" class="{{ $ano == $edicao->ano ? 'site-year-chip is-active' : 'site-year-chip' }}">
                            {{ $ano }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="site-section">
        <div class="site-editorial-layout">
            <div class="site-editorial-main">
                <section class="site-surface site-content-block">
                    <x-section-head eyebrow="Sobre" title="Descricao do evento" subtitle="Resumo editorial e conteudo publico da edicao selecionada." />
                    <div class="site-prose">
                        {!! $descricao ?: '<p>Esta edicao ainda nao tem uma descricao editorial publicada.</p>' !!}
                    </div>
                </section>

                @if($atrativos->isNotEmpty())
                    <section class="site-section">
                        <x-section-head eyebrow="Atrativos" title="Destaques desta edicao" subtitle="Uma leitura visual consistente com o restante do portal." />
                        <div class="site-card-list-grid">
                            @foreach($atrativos as $item)
                                <div class="site-card-list">
                                    <div class="site-card-list-media">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="site-card-list-image" loading="lazy" decoding="async">
                                    </div>
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
                    <x-section-head eyebrow="Servico" title="Planeje sua presenca" />
                    <div class="site-stats-grid">
                        <div class="site-stat-card">
                            <span class="site-stat-label">Data</span>
                            <span class="site-stat-value">{{ $quando ?: 'A definir' }}</span>
                        </div>
                        <div class="site-stat-card">
                            <span class="site-stat-label">Local</span>
                            <span class="site-stat-value">{{ $onde }}</span>
                        </div>
                        <div class="site-stat-card">
                            <span class="site-stat-label">Fotos</span>
                            <span class="site-stat-value">{{ $galeria->count() }}</span>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    @if($galeria->isNotEmpty())
        <section class="site-section" x-data="{
            open:false,
            index:0,
            images:@js($galeria),
            show(i){ this.index=i; this.open=true; document.body.style.overflow='hidden'; },
            close(){ this.open=false; document.body.style.overflow=''; },
            next(){ this.index=(this.index+1)%this.images.length; },
            prev(){ this.index=(this.index-1+this.images.length)%this.images.length; }
        }">
            <x-section-head eyebrow="Galeria" title="Fotos da edicao" subtitle="Imagens publicas com o mesmo ritmo visual das paginas premium do portal." />

            <div class="site-gallery-grid">
                @foreach($galeria as $index => $img)
                    <button type="button" class="site-gallery-button" @click="show({{ $index }})">
                        <img src="{{ $img['src'] }}" alt="{{ $img['alt'] }}" class="site-gallery-image" loading="lazy" decoding="async">
                    </button>
                @endforeach
            </div>

            <div x-show="open" x-cloak class="site-lightbox" @click.self="close()" x-transition.opacity>
                <div class="site-lightbox-frame">
                    <button type="button" class="site-lightbox-close" @click="close()" aria-label="Fechar galeria">&times;</button>
                    <button type="button" class="site-lightbox-arrow is-prev" @click.stop="prev()" aria-label="Foto anterior">&#8249;</button>
                    <img :src="images[index]?.src" :alt="images[index]?.alt || ''" class="site-lightbox-image">
                    <button type="button" class="site-lightbox-arrow is-next" @click.stop="next()" aria-label="Proxima foto">&#8250;</button>
                </div>
            </div>
        </section>
    @endif

    <div class="site-bottom-safe-space md:hidden" aria-hidden="true"></div>
</div>
@endsection
