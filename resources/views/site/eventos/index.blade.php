@extends('site.layouts.app')
@section('title', __('ui.events.title'))
@section('meta.description', __('ui.events.meta_description'))
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
          'title' => $evento->nome ?? __('ui.events.event'),
          'subtitle' => $evento->cidade ?? __('ui.common.altamira'),
          'summary' => \Illuminate\Support\Str::limit(strip_tags((string) ($evento->descricao ?? '')), 130),
          'image' => $image,
          'href' => localized_route('eventos.show', ['slug' => $evento->slug ?? $evento->id, 'ano' => $ano ?: ($anoAtual ?? now()->year)]),
          'badge' => $periodo ?: ($ano ?: __('ui.events.event')),
          'cta' => __('ui.agenda.view_event'),
      ];
  });
@endphp

<div class="site-page site-page-shell site-agenda-page">
    @include('site.partials._page_hero', [
        'backHref' => localized_route('site.home'),
        'breadcrumbs' => [
            ['label' => __('ui.nav.home'), 'href' => localized_route('site.home')],
            ['label' => __('ui.agenda.title')],
        ],
        'badge' => __('ui.events.badge'),
        'title' => __('ui.events.hero_title'),
        'subtitle' => __('ui.events.hero_subtitle'),
        'meta' => [
            __('ui.events.events_count', ['count' => $eventCards->count()]),
            $anoAtual ? ('Ano '.$anoAtual) : __('ui.events.multiple_years'),
        ],
        'primaryActionLabel' => Route::has('site.explorar') ? __('ui.events.explore_city') : null,
        'primaryActionHref' => Route::has('site.explorar') ? localized_route('site.explorar') : null,
        'secondaryActionLabel' => Route::has('site.mapa') ? __('ui.common.tourist_map') : null,
        'secondaryActionHref' => Route::has('site.mapa') ? localized_route('site.mapa') : null,
        'image' => theme_asset('hero_image'),
        'imageAlt' => __('ui.events.title'),
        'compact' => true,
    ])

    @if($anosDisponiveis->isNotEmpty())
        <section class="site-section">
            <div class="site-surface-soft site-agenda-filter-shell">
                <x-section-head :eyebrow="__('ui.common.filters')" :title="__('ui.events.choose_year')" :subtitle="__('ui.events.choose_year_subtitle')" />
                <div class="site-filter-row site-agenda-filter-row">
                    @foreach($anosDisponiveis as $ano)
                        <a href="{{ localized_route('eventos.index', array_filter(['ano' => $ano])) }}" class="{{ (string) $ano === (string) $anoAtual ? 'site-year-chip is-active' : 'site-year-chip' }}">{{ $ano }}</a>
                    @endforeach
                    @if($anoAtual)
                        <a href="{{ localized_route('eventos.index') }}" class="site-link">{{ __('ui.events.clear_filter') }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section class="site-section">
        @if($eventCards->isEmpty())
            <div class="site-empty-state"><p class="site-empty-state-copy">{{ __('ui.events.empty_copy') }}</p></div>
        @else
            <div class="site-agenda-events-section">
                @include('site.partials._category_section', [
                    'eyebrow' => __('ui.events.programming'),
                    'title' => __('ui.events.published_events'),
                    'subtitle' => __('ui.events.published_events_subtitle'),
                    'items' => $eventCards,
                    'layout' => 'carousel',
                    'cardVariant' => 'compact',
                    'empty' => __('ui.events.empty_copy'),
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
