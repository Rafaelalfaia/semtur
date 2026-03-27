@extends('site.layouts.app')
@section('title','Eventos em Altamira')
@section('meta.description','Agenda publica de eventos de Altamira com edicoes publicadas para planejar a viagem com mais contexto.')
@section('meta.image', theme_asset('hero_image'))
@section('meta.canonical', url()->full())

@section('site.content')
@php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Facades\Storage;

  $isPaginator = $eventos instanceof \Illuminate\Contracts\Pagination\Paginator
              || $eventos instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
  $items = $isPaginator ? collect($eventos->items()) : collect($eventos);
  $pub = fn($p) => $p ? Storage::disk('public')->url($p) : null;
  $anosDisponiveis = collect($anosDisponiveis ?? []);
  $anoAtual = $anoAtual ?? request('ano');

  $eventCards = $items->map(function ($evento) use ($pub, $anoAtual) {
      $edicao = collect($evento->edicoes ?? [])->sortByDesc('ano')->first();
      $ano = $edicao->ano ?? null;
      $periodo = $edicao->periodo
          ?? (($edicao->data_inicio ? \Carbon\Carbon::parse($edicao->data_inicio)->format('d/m') : null)
          . ($edicao->data_fim ? ' - '.\Carbon\Carbon::parse($edicao->data_fim)->format('d/m') : ''));

      $image = $evento->capa_url
          ?? $pub($evento->capa_path ?? null)
          ?? $evento->perfil_url
          ?? $pub($evento->perfil_path ?? null)
          ?? theme_asset('hero_image');

      return [
          'title' => $evento->nome ?? 'Evento',
          'subtitle' => $evento->cidade ?? 'Altamira',
          'summary' => \Illuminate\Support\Str::limit(strip_tags((string) ($evento->descricao ?? '')), 130),
          'image' => $image,
          'href' => route('eventos.show', [$evento->slug ?? $evento->id, $ano ?: ($anoAtual ?? now()->year)]),
          'badge' => $periodo ?: ($ano ?: 'Evento'),
          'cta' => 'Ver evento',
      ];
  });
@endphp

<div class="site-page site-page-shell site-agenda-page">
    @include('site.partials._page_hero', [
        'backHref' => Route::has('site.home') ? route('site.home') : url('/'),
        'breadcrumbs' => [
            ['label' => 'Inicio', 'href' => Route::has('site.home') ? route('site.home') : url('/')],
            ['label' => 'Agenda'],
        ],
        'badge' => 'Agenda 2.0',
        'title' => 'Eventos de Altamira',
        'subtitle' => 'Descubra os eventos publicados com leitura rapida, foco em datas e clima de app.',
        'meta' => [
            $eventCards->count().' eventos',
            $anoAtual ? 'Ano '.$anoAtual : 'Multiplos anos',
        ],
        'primaryActionLabel' => Route::has('site.explorar') ? 'Explorar a cidade' : null,
        'primaryActionHref' => Route::has('site.explorar') ? route('site.explorar') : null,
        'secondaryActionLabel' => Route::has('site.mapa') ? 'Ver mapa turistico' : null,
        'secondaryActionHref' => Route::has('site.mapa') ? route('site.mapa') : null,
        'image' => theme_asset('hero_image'),
        'imageAlt' => 'Agenda de eventos de Altamira',
        'compact' => true,
    ])

    @if($anosDisponiveis->isNotEmpty())
        <section class="site-section">
            <div class="site-surface-soft site-agenda-filter-shell">
                <x-section-head eyebrow="Filtros" title="Escolha o ano" subtitle="Troque rapidamente o recorte da agenda." />
                <div class="site-filter-row site-agenda-filter-row">
                    @foreach($anosDisponiveis as $ano)
                        <a href="{{ route('eventos.index', array_filter(['ano' => $ano])) }}" class="{{ (string) $ano === (string) $anoAtual ? 'site-year-chip is-active' : 'site-year-chip' }}">
                            {{ $ano }}
                        </a>
                    @endforeach
                    @if($anoAtual)
                        <a href="{{ route('eventos.index') }}" class="site-link">Limpar filtro</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section class="site-section">
        @if($eventCards->isEmpty())
            <div class="site-empty-state">
                <p class="site-empty-state-copy">Nenhum evento apareceu neste recorte. Tente outro ano ou volte em breve.</p>
            </div>
        @else
            <div class="site-agenda-events-section">
                @include('site.partials._category_section', [
                    'eyebrow' => 'Programacao',
                    'title' => 'Eventos publicados',
                    'subtitle' => 'Passe pelos cards e abra os detalhes do evento que fizer sentido para a viagem.',
                    'items' => $eventCards,
                    'layout' => 'carousel',
                    'cardVariant' => 'compact',
                    'empty' => 'Nenhum evento apareceu neste recorte. Tente outro ano ou volte em breve.',
                ])
            </div>

            @if($isPaginator)
                <div class="site-surface-soft site-agenda-pagination-shell">
                    {{ $eventos->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </section>

    <div class="site-bottom-safe-space md:hidden" aria-hidden="true"></div>
</div>
@endsection
