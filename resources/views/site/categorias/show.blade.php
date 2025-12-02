@extends('site.layouts.app')
@section('title','Descubra Altamira')
@section('meta.description','Guia turístico oficial de Altamira, Pará.')
@section('meta.image', $capaUrl ?? '/images/og-default.jpg')

@section('title', $categoria->nome . ' | Categorias')

@section('content')
<div class="relative min-h-screen bg-white">

  {{-- gradiente do topo (aproxima o Figma) --}}
  <div class="pointer-events-none absolute inset-x-0 top-0 h-[1080px] -z-10
              bg-gradient-to-b from-[#00837B] via-white/70 to-transparent"></div>

  {{-- status bar/safe-area --}}
  <div class="h-4"></div>

  {{-- header simples com back + título + share --}}
  <div class="flex items-center justify-between px-4 pt-3 pb-2 text-white">
    <button onclick="history.back()" class="rounded-full bg-black/20 p-2">
      <svg class="w-6 h-6" viewBox="0 0 24 24"><path fill="currentColor" d="m15 18l-6-6l6-6"/></svg>
    </button>
    <h1 class="text-lg font-semibold truncate">{{ $categoria->nome }}</h1>
    <button onclick="navigator.share ? navigator.share({title:'{{ addslashes($categoria->nome) }}', url: location.href}) : (navigator.clipboard?.writeText(location.href), alert('Link copiado'))"
            class="rounded-full bg-black/20 p-2">
      <svg class="w-6 h-6" viewBox="0 0 24 24"><path fill="currentColor" d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7a3.27 3.27 0 0 0 0-1.39l7.02-4.11A2.99 2.99 0 1 0 14 5a3 3 0 0 0 .06.58L7.03 9.69a3 3 0 1 0 0 4.62l7.03 4.1c-.04.19-.06.39-.06.59a3 3 0 1 0 3-3Z"/></svg>
    </button>
  </div>

  {{-- busca opcional --}}
  <form method="get" class="px-4 pb-2">
    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar nesta categoria..."
           class="w-full rounded-full border border-teal-600/30 bg-white/80 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-teal-600/30" />
  </form>

  {{-- conteúdo --}}
  <div class="px-4 pb-28 space-y-8">

    {{-- Pontos --}}
    <section id="pontos" class="space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-[#FDE5D4]">Pontos Turísticos</h2>
        <a href="#pontos" class="text-sm font-medium text-[#00837B]">Ver todos</a>
      </div>

      @forelse ($pontos as $p)
        <a href="{{ route('site.ponto', $p->id) }}"
           class="block bg-white rounded-xl shadow-[0_4px_36px_rgba(0,0,0,.09)]">
          <div class="flex gap-3 p-2">
            <div class="relative shrink-0 w-[136px] h-[123px]">
              <img src="{{ $p->capa_url ?? $p->foto_capa_url ?? optional($p->midias->first())->url ?? '' }}"
                   alt="{{ $p->nome }}" class="w-full h-full object-cover rounded-[10px]">
              <div class="absolute inset-x-0 bottom-0 h-14 bg-gradient-to-t from-black/60 to-transparent rounded-b-[10px]"></div>
            </div>
            <div class="flex flex-col gap-2 pt-1">
              <div class="text-[16px] leading-5 font-semibold text-[#2B3536] line-clamp-2">{{ $p->nome }}</div>
              <div class="flex items-center gap-1 text-[12px] text-[#868B8B]">
                <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg>
                <span>{{ $p->cidade ?? 'Altamira' }}</span>
              </div>
              {{-- espaço para badges/mini-logos se quiser --}}
            </div>
          </div>
        </a>
      @empty
        <div class="text-sm text-gray-500">Nenhum ponto nesta categoria.</div>
      @endforelse

      @if ($pontos->hasPages())
        <div class="pt-1">{{ $pontos->onEachSide(1)->links() }}</div>
      @endif
    </section>

    {{-- Empresas --}}
    <section id="empresas" class="space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-[#2B3536]">Empresas de Turismo</h2>
        <a href="#empresas" class="text-sm font-medium text-[#00837B]">Ver todos</a>
      </div>

      @forelse ($empresas as $e)
        <a href="{{ route('site.empresa', $e->slug ?? $e->id) }}"
           class="block bg-white rounded-xl shadow-[0_4px_36px_rgba(0,0,0,.09)]">
          <div class="flex gap-3 p-2">
            <div class="relative shrink-0 w-[136px] h-[123px]">
              <img src="{{ $e->capa_url ?? $e->foto_capa_url ?? '' }}"
                   alt="{{ $e->nome }}" class="w-full h-full object-cover rounded-[10px]">
              <div class="absolute inset-x-0 bottom-0 h-14 bg-gradient-to-t from-black/60 to-transparent rounded-b-[10px]"></div>
            </div>
            <div class="flex flex-col gap-2 pt-1">
              <div class="text-[16px] leading-5 font-semibold text-[#2B3536] line-clamp-2">{{ $e->nome }}</div>
              <div class="flex items-center gap-1 text-[12px] text-[#868B8B]">
                <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg>
                <span>{{ $e->cidade ?? 'Altamira' }}</span>
              </div>
            </div>
          </div>
        </a>
      @empty
        <div class="text-sm text-gray-500">Nenhuma empresa nesta categoria.</div>
      @endforelse

      @if ($empresas->hasPages())
        <div class="pt-1">{{ $empresas->onEachSide(1)->links() }}</div>
      @endif
    </section>

  </div>

  {{-- espaçador para não colidir com a bottom-nav do app --}}
  <div class="h-24"></div>
</div>

{{-- rolar direto para a aba se vier com ?tab= --}}
<script>
  (function(){
    const tab = new URLSearchParams(location.search).get('tab');
    if (tab && document.getElementById(tab)) {
      document.getElementById(tab).scrollIntoView({behavior:'smooth', block:'start'});
    }
  })();
</script>
@endsection
