{{-- resources/views/site/eventos/index.blade.php --}}
@extends('site.layouts.app')
@section('title','Descubra Altamira')
@section('meta.description','Guia turístico oficial de Altamira, Pará.')
@section('meta.image', $capaUrl ?? '/images/og-default.jpg')

@section('title','Eventos — VisitAltamira')

@section('site.content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Storage;

  // pode vir Collection ou Paginator
  $isPaginator = $eventos instanceof \Illuminate\Contracts\Pagination\Paginator
              || $eventos instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
  $items = $isPaginator ? collect($eventos->items()) : collect($eventos);

  // helper para URL pública
  $pub = fn($p) => $p ? Storage::disk('public')->url($p) : null;

  // chips de ano (opcional, se o controller mandar)
  $anosDisponiveis = collect($anosDisponiveis ?? []);
  $anoAtual = $anoAtual ?? request('ano');
@endphp

<section class="border-t border-slate-200">
  <div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <h1 class="text-xl font-semibold">Eventos</h1>
      </div>
      {{-- Filtro simples por ano (opcional) --}}
      @if($anosDisponiveis->count())
        <div class="hidden md:flex items-center gap-2">
          @foreach($anosDisponiveis as $a)
            <a href="{{ route('eventos.index', array_filter(['ano'=>$a])) }}"
               class="px-3 py-1.5 rounded-full border text-sm
                      {{ (string)$a===(string)$anoAtual ? 'bg-emerald-700 border-emerald-700 text-white' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50' }}">
              {{ $a }}
            </a>
          @endforeach
          @if($anoAtual)
            <a href="{{ route('eventos.index') }}" class="text-emerald-700 text-sm hover:underline">Limpar</a>
          @endif
        </div>
      @endif
    </div>

    {{-- Chips de ano no mobile --}}
    @if($anosDisponiveis->count())
      <div class="md:hidden overflow-x-auto -mx-4 px-4 mb-4">
        <div class="flex items-center gap-2">
          @foreach($anosDisponiveis as $a)
            <a href="{{ route('eventos.index', array_filter(['ano'=>$a])) }}"
               class="px-3 py-1.5 rounded-full border text-sm whitespace-nowrap
                      {{ (string)$a===(string)$anoAtual ? 'bg-emerald-700 border-emerald-700 text-white' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50' }}">
              {{ $a }}
            </a>
          @endforeach
          @if($anoAtual)
            <a href="{{ route('eventos.index') }}" class="text-emerald-700 text-sm hover:underline">Limpar</a>
          @endif
        </div>
      </div>
    @endif

    @if($items->count())
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($items as $e)
          @php
            $nome   = $e->nome ?? 'Evento';
            $cidade = $e->cidade ?? 'Altamira';

            // capa: url pronta > storage path > perfil > placeholder
            $capa = $e->capa_url
                  ?? $pub($e->capa_path ?? null)
                  ?? $e->perfil_url
                  ?? $pub($e->perfil_path ?? null)
                  ?? asset('images/placeholders/capa-evento.jpg');

            // edição mais recente (espera vir eager-loaded desc; senão tenta ordenar)
            $ed = collect($e->edicoes ?? [])->sortByDesc('ano')->first();
            $ano = $ed->ano ?? null;
            $periodo = $ed->periodo
                     ?? (($ed->data_inicio ? \Carbon\Carbon::parse($ed->data_inicio)->format('d/m') : null)
                       . ($ed->data_fim ? '–'.\Carbon\Carbon::parse($ed->data_fim)->format('d/m') : ''));
            $badge = $periodo ?: $ano;

            // href seguro
            $slugOrId = $e->slug ?? $e->id;
            $href = route('eventos.show', [$slugOrId, $ano ?: ($anoAtual ?? now()->year)]);
          @endphp

          <article class="rounded-2xl overflow-hidden border border-slate-200 bg-white hover:shadow transition">
            <a href="{{ $href }}" class="block">
              <div class="aspect-[4/3] bg-slate-100 relative">
                <img src="{{ $capa }}" class="w-full h-full object-cover" alt="{{ $nome }}" loading="lazy">
                @if($badge)
                  <span class="absolute left-3 top-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-white/90 backdrop-blur border border-white/70 shadow">
                    {{ $badge }}
                  </span>
                @endif
              </div>
              <div class="p-4">
                <h2 class="font-semibold line-clamp-1">{{ $nome }}</h2>
                <div class="mt-1 flex items-center gap-1 text-sm text-slate-600">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 110-6 3 3 0 010 6z"/></svg>
                  <span class="line-clamp-1">{{ $cidade }}</span>
                </div>
              </div>
            </a>
          </article>
        @endforeach
      </div>

      {{-- paginação, se houver --}}
      @if($isPaginator)
        <div class="mt-8">
          {{ $eventos->withQueryString()->links() }}
        </div>
      @endif
    @else
      <div class="text-slate-600">Nenhum evento encontrado.</div>
    @endif
  </div>
</section>
{{-- Espaço p/ não cobrir conteúdo (mobile) + bottom nav --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@includeIf('site.partials._bottom_nav')
@endsection
