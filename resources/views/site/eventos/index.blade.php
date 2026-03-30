@extends('site.layouts.app')
@section('title', ui_text('ui.events.title'))
@section('meta.description', ui_text('ui.events.meta_description'))
@section('meta.image', theme_asset('hero_image'))
@section('meta.canonical', url()->full())

@section('site.content')
@php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Facades\Storage;

  $isPaginator = $eventos instanceof \Illuminate\Contracts\Pagination\Paginator || $eventos instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
  $items = $isPaginator ? collect($eventos->items()) : collect($eventos);
  $pub = fn($p) => $p ? Storage::disk('public')->url($p) : null;
  $anosDisponiveis = collect($anosDisponiveis ?? []);
  $anoAtual = $anoAtual ?? request('ano');

  $eventCards = $items->map(function ($evento) use ($pub, $anoAtual) {
      $edicao = collect($evento->edicoes ?? [])->sortByDesc('ano')->first();
      $ano = $edicao->ano ?? null;
      $periodo = $edicao->periodo ?? (($edicao->data_inicio ? \Carbon\Carbon::parse($edicao->data_inicio)->format('d/m') : null) . ($edicao->data_fim ? ' - '.\Carbon\Carbon::parse($edicao->data_fim)->format('d/m') : ''));
      $image = $evento->capa_url ?? $pub($evento->capa_path ?? null) ?? $evento->perfil_url ?? $pub($evento->perfil_path ?? null) ?? theme_asset('hero_image');

      return [
          'title' => $evento->nome ?? ui_text('ui.events.event'),
          'subtitle' => $evento->cidade ?? ui_text('ui.common.altamira'),
          'summary' => \Illuminate\Support\Str::limit(strip_tags((string) ($evento->descricao ?? '')), 130),
          'image' => $image,
          'href' => localized_route('eventos.show', ['slug' => $evento->slug ?? $evento->id, 'ano' => $ano ?: ($anoAtual ?? now()->year)]),
          'badge' => $periodo ?: ($ano ?: ui_text('ui.events.event')),
          'cta' => ui_text('ui.agenda.view_event'),
      ];
  });
@endphp

<div class="site-page site-page-shell site-agenda-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => ui_text('ui.nav.home'), 'href' => localized_route('site.home')],
            ['label' => ui_text('ui.agenda.title')],
        ],
        'badge' => ui_text('ui.events.badge'),
        'title' => ui_text('ui.events.hero_title'),
        'subtitle' => ui_text('ui.events.hero_subtitle'),
        'meta' => [
            ui_text('ui.events.events_count', ['count' => $eventCards->count()]),
            $anoAtual ? ('Ano '.$anoAtual) : ui_text('ui.events.multiple_years'),
        ],
        'primaryActionLabel' => Route::has('site.explorar') ? ui_text('ui.events.explore_city') : null,
        'primaryActionHref' => Route::has('site.explorar') ? localized_route('site.explorar') : null,
        'secondaryActionLabel' => Route::has('site.mapa') ? ui_text('ui.common.tourist_map') : null,
        'secondaryActionHref' => Route::has('site.mapa') ? localized_route('site.mapa') : null,
        'image' => theme_asset('hero_image'),
        'imageAlt' => ui_text('ui.events.title'),
        'compact' => true,
    ])

    @if($anosDisponiveis->isNotEmpty())
        <section class="site-section">
            <div class="site-surface-soft site-agenda-filter-shell">
                <x-section-head :eyebrow="ui_text('ui.common.filters')" :title="ui_text('ui.events.choose_year')" :subtitle="ui_text('ui.events.choose_year_subtitle')" />
                <div class="site-filter-row site-agenda-filter-row">
                    @foreach($anosDisponiveis as $ano)
                        <a href="{{ localized_route('eventos.index', array_filter(['ano' => $ano])) }}" class="{{ (string) $ano === (string) $anoAtual ? 'site-year-chip is-active' : 'site-year-chip' }}">{{ $ano }}</a>
                    @endforeach
                    @if($anoAtual)
                        <a href="{{ localized_route('eventos.index') }}" class="site-link">{{ ui_text('ui.events.clear_filter') }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section class="site-section">
        @if($eventCards->isEmpty())
            <div class="site-empty-state"><p class="site-empty-state-copy">{{ ui_text('ui.events.empty_copy') }}</p></div>
        @else
            <div class="site-agenda-events-section">
                @include('site.partials._category_section', [
                    'eyebrow' => ui_text('ui.events.programming'),
                    'title' => ui_text('ui.events.published_events'),
                    'subtitle' => ui_text('ui.events.published_events_subtitle'),
                    'items' => $eventCards,
                    'layout' => 'carousel',
                    'cardVariant' => 'compact',
                    'empty' => ui_text('ui.events.empty_copy'),
                ])
            </div>

            @if($isPaginator)
                <div class="site-surface-soft site-agenda-pagination-shell">{{ $eventos->withQueryString()->links() }}</div>
            @endif
        @endif
    </section>

    <div class="site-bottom-safe-space md:hidden" aria-hidden="true"></div>
</div>
@endsection
