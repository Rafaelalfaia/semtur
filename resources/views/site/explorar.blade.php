@extends('site.layouts.app')
@section('title','Descubra Altamira')
@section('title','Explorar — VisitAltamira')

@section('site.content')
@php
  use Illuminate\Support\Facades\Route as R;

  $container = $container
    ?? 'mx-auto w-full max-w-[420px] md:max-w-[1024px] lg:max-w-[1200px] px-4 md:px-6';

  $hasPonto     = R::has('site.ponto');
  $hasEmpresa   = R::has('site.empresa');

  // Coleções seguras
  $categorias = collect($categorias ?? []);
  $pontos     = $pontos ?? collect();
  $empresas   = $empresas ?? collect();

  // Filtra categorias: mantém só as que têm conteúdo publicado (se contagens existirem)
  $categorias = $categorias->filter(function($c){
      $pc = $c->pontos_publicados_count ?? null;
      $ec = $c->empresas_publicadas_count ?? null;
      if ($pc !== null || $ec !== null) return (int)($pc ?? 0) + (int)($ec ?? 0) > 0;
      if (isset($c->pontos) || isset($c->empresas)) return (count($c->pontos ?? []) + count($c->empresas ?? [])) > 0;
      return true;
  })->values();

  // nome da categoria selecionada (se vier do controller)
  $currentCat = $currentCat ?? null;
@endphp

{{-- topo com gradiente estendido para contraste --}}
<section class="relative overflow-hidden pb-4 md:pb-6"
         style="background: linear-gradient(180deg,
                  #00837B 0%,
                  #00837B 40%,
                  rgba(255,255,255,0.92) 72%,
                  #FFFFFF 100%);">

  {{-- título + voltar/compartilhar --}}
  <div class="{{ $container }} pt-3 text-white">
    <div class="flex items-start justify-between gap-3">
        <button onclick="history.back()" class="rounded-full bg-black/20 p-2 shrink-0">
            <svg class="w-6 h-6" viewBox="0 0 24 24"><path fill="currentColor" d="m15 18l-6-6l6-6"/></svg>
        </button>

        <div class="flex-1 min-w-0 text-center">
            <p class="text-[11px] uppercase tracking-[0.18em] text-white/75">
            Explorar
            </p>

            @if($currentCat)
            <div class="mt-1 inline-flex max-w-full items-center gap-2 rounded-2xl bg-white px-4 py-2 text-[#00837B] shadow-[0_10px_30px_rgba(0,0,0,0.18)]">
                @if(!empty($currentCat->icone_path))
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::url($currentCat->icone_path) }}"
                    alt="{{ $currentCat->nome }}"
                    class="w-5 h-5 object-contain shrink-0"
                >
                @endif

                <span class="text-lg md:text-xl font-bold truncate">
                {{ $currentCat->nome }}
                </span>
            </div>
            @else
            <h1 class="text-lg font-semibold truncate">
                Explorar
            </h1>
            @endif
        </div>

  <button
    onclick="navigator.share ? navigator.share({title:document.title, url: location.href}) : (navigator.clipboard?.writeText(location.href), alert('Link copiado'))"
    class="rounded-full bg-black/20 p-2 shrink-0"
  >
    <svg class="w-6 h-6" viewBox="0 0 24 24"><path fill="currentColor" d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7a3.27 3.27 0 0 0 0-1.39l7.02-4.11A2.99 2.99 0 1 0 14 5a3 3 0 0 0 .06.58L7.03 9.69a3 3 0 1 0 0 4.62l7.03 4.1c-.04.19-.06.39-.06.59a3 3 0 1 0 3-3Z"/></svg>
  </button>
</div>
  </div>

  {{-- busca --}}
  <div class="{{ $container }} pt-3">
    <form method="get" class="mb-1">
      <div class="flex gap-2">
        <input type="text" name="busca" value="{{ request('busca','') }}"
               placeholder="Buscar pontos ou empresas..."
               class="w-full rounded-full border border-teal-600/30 bg-white/90 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-teal-600/30" />
        @php
          $catId = request('categoria_id');
          $catSlug = request('categoria') ?? request('cat');
        @endphp
        @if($catId)
          <input type="hidden" name="categoria_id" value="{{ $catId }}">
        @elseif($catSlug)
          <input type="hidden" name="categoria" value="{{ $catSlug }}">
        @endif
      </div>
    </form>

    @if(request('categoria_id') || request('categoria') || request('busca'))
      <div class="text-xs text-white/90 pb-2 flex items-center gap-2">
        @if(request('busca'))
          <span>Buscando por “{{ request('busca') }}”</span>
        @endif
        @if($currentCat)
          <span>| Categoria: {{ $currentCat->nome }}</span>
        @elseif(request('categoria_id'))
          <span>| Categoria #{{ request('categoria_id') }}</span>
        @elseif(request('categoria') || request('cat'))
          <span>| Categoria: {{ request('categoria') ?? request('cat') }}</span>
        @endif
        <a href="{{ url()->current() }}" class="underline">Limpar</a>
      </div>
    @endif
  </div>

  {{-- chips de categorias (só mostram se houver) --}}
  @if($categorias->isNotEmpty())
    <div class="{{ $container }}">
      @includeIf('site.partials._categories_top', [
    'categorias' => $categorias,
    'currentCat' => $currentCat,
    'href' => function($cat) {
        return route('site.explorar', ['categoria' => $cat->slug]);
    }
    ])
    </div>
  @endif
</section>

{{-- PONTOS --}}
@if(($pontos instanceof \Illuminate\Support\Collection && $pontos->isNotEmpty())
   || ($pontos instanceof \Illuminate\Contracts\Pagination\Paginator && $pontos->count()))
<section class="bg-gradient-to-b from-white to-[#EAF4F2] py-3 md:py-5">
  <div class="{{ $container }}">
    <h2 class="text-[16px] md:text-lg font-semibold text-[#2B3536] mb-3">Atrativos Turísticos</h2>

    <div class="space-y-3">
      @foreach($pontos as $p)
        @php
          $img  = $p->capa_url ?? $p->foto_capa_url ?? optional($p->midias->first())->url ?? null;
          $href = $hasPonto ? route('site.ponto', $p->id) : '#';
        @endphp
        <x-card-list
          :title="$p->nome"
          :subtitle="$p->cidade ?? 'Altamira'"
          :image="$img"
          :href="$href"
          logo="/imagens/visitpreto.png" />
      @endforeach
    </div>

    @if($pontos instanceof \Illuminate\Contracts\Pagination\Paginator && $pontos->hasPages())
      <div class="mt-4">
        {{ $pontos->onEachSide(1)->links() }}
      </div>
    @endif
  </div>
</section>
@endif

{{-- EMPRESAS --}}
@if(($empresas instanceof \Illuminate\Support\Collection && $empresas->isNotEmpty())
   || ($empresas instanceof \Illuminate\Contracts\Pagination\Paginator && $empresas->count()))
<section class="bg-gradient-to-b from-white to-[#F5F7F7] py-3 md:py-5">
  <div class="{{ $container }}">
    <h2 class="text-[16px] md:text-lg font-semibold text-[#2B3536] mb-3">Empresas</h2>

    <div class="space-y-3">
      @foreach($empresas as $e)
        @php
          $img  = $e->capa_url ?? $e->perfil_url ?? $e->foto_capa_url ?? null;
          $href = $hasEmpresa ? route('site.empresa', $e->slug ?? $e->id) : '#';
        @endphp
        <x-card-list
          :title="$e->nome"
          :subtitle="$e->cidade ?? 'Altamira'"
          :image="$img"
          :href="$href"
          logo="/imagens/visitpreto.png" />
      @endforeach
    </div>

    @if($empresas instanceof \Illuminate\Contracts\Pagination\Paginator && $empresas->hasPages())
      <div class="mt-4">
        {{ $empresas->onEachSide(1)->links() }}
      </div>
    @endif
  </div>
</section>
@endif

{{-- espaçador para não colidir com a bottom-nav --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@includeIf('site.partials._bottom_nav')
@endsection
