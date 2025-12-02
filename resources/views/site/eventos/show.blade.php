{{-- resources/views/site/eventos/show.blade.php --}}
@extends('site.layouts.app')
@section('title','Descubra Altamira')
@section('meta.description','Guia turístico oficial de Altamira, Pará.')
@section('meta.image', $capaUrl ?? '/images/og-default.jpg')

@section('title', ($evento->nome ?? 'Evento').' — '.($edicao->ano ?? ''))

@section('site.content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Storage;
  use Illuminate\Support\Facades\Route;

  $pub = fn($p) => $p ? Storage::disk('public')->url($p) : null;

  $nome      = $evento->nome ?? 'Evento';
  $cidade    = $evento->cidade ?? 'Altamira';
  $descricao = $edicao->resumo ?: ($evento->descricao ?? null);

  // imagens (preferir urls prontas > paths do storage > placeholder)
  $capaUrl = $evento->capa_url
          ?? $pub($evento->capa_path ?? null)
          ?? $evento->perfil_url
          ?? $pub($evento->perfil_path ?? null)
          ?? asset('images/placeholders/capa-evento.jpg');

  // rating (se existir)
  $nota = property_exists($evento,'rating') ? (float)$evento->rating : null;

  // Período/Quando
  $quando = $edicao->periodo
        ?: (($edicao->data_inicio ? \Carbon\Carbon::parse($edicao->data_inicio)->format('d/m/Y') : null)
          . ($edicao->data_fim ? ' – '.\Carbon\Carbon::parse($edicao->data_fim)->format('d/m/Y') : ''))
        ?: ($edicao->ano ?? null);

  // Onde + coordenadas
  $onde = trim($edicao->local ?? '') ?: $cidade;
  $lat  = is_numeric($edicao->lat ?? null) ? (float)$edicao->lat : null;
  $lng  = is_numeric($edicao->lng ?? null) ? (float)$edicao->lng : null;

  // deep-link do mapa (modelo de empresa)
  $mapBase    = Route::has('site.mapa') ? route('site.mapa') : url('/mapa');
  $slugOrId   = $evento->slug ?? $evento->id;
  $mapQuery   = array_filter([
      'focus' => 'evento:'.$slugOrId, // 👈 foco no evento
      'lat'   => $lat,
      'lng'   => $lng,
      'open'  => 1,
  ], fn($v) => $v !== null && $v !== '');
  $mapHref = $mapBase.(count($mapQuery) ? ('?'.http_build_query($mapQuery)) : '');

  // Galeria p/ lightbox
  $galeria = collect($edicao->midias ?? [])->map(function($m){
    $src = Str::startsWith($m->path, ['http://','https://','/']) ? $m->path : Storage::disk('public')->url($m->path);
    return ['src'=>$src, 'alt'=>$m->alt ?? ''];
  })->values();

  // Atrativos
  $atrativos = collect($edicao->atrativos ?? []);

  // Anos (para seletor)
  $anos = collect($anos ?? []);
@endphp

{{-- Preload da capa --}}
<link rel="preload" as="image" href="{{ $capaUrl }}"/>

@push('head')
<style>
  :root{
    --app-max: 420px;            /* largura app em qualquer tela */
    --sheet-r: 30px;
    --brand: #00837B;
    --muted: #868B8B;
    --shadow-1: 0 -6px 24px rgba(16,24,40,.08), 0 -2px 8px rgba(16,24,40,.05);
  }
  .wrap{ margin-inline:auto; max-width:var(--app-max); }
  .hero{ height:44dvh; min-height:320px; }
  @media (min-width:768px){ .hero{ height:360px; } }
  @media (min-width:1024px){ .hero{ height:420px; } }

  .card-top{
    background:#fff; border-top-left-radius:var(--sheet-r); border-top-right-radius:var(--sheet-r);
    box-shadow:var(--shadow-1);
  }
  .indicator{ width:134px; height:5px; background:#868B8B; border-radius:100px; }

  .tab-btn{ padding-bottom:.5rem; font-weight:600; font-size:16px; }
  .tab-on{ color:var(--brand); border-bottom:2px solid var(--brand); }
  .tab-off{ color:#2B3536; opacity:.9; border-bottom:2px solid transparent; }

  .lb-bg{ background:rgba(0,0,0,.85); }
  .lb-btn{ width:42px; height:42px; border-radius:999px; background:rgba(255,255,255,.15); color:#fff; }
  .lb-btn:hover{ background:rgba(255,255,255,.25); }
</style>
@endpush

<div class="relative mx-auto w-full max-w-[420px] md:max-w-[768px] lg:max-w-[960px]">

  {{-- HERO (igual ao da empresa) --}}
  <div class="relative hero -mt-6 md:mt-0 overflow-hidden rounded-b-3xl">
    <img src="{{ $capaUrl }}" alt="Capa de {{ $nome }}"
         class="absolute inset-0 w-full h-full object-cover"
         loading="eager" decoding="async" fetchpriority="high"
         onerror="this.onerror=null;this.src='{{ asset('images/placeholders/capa-evento.jpg') }}';">
    <div class="absolute inset-0" style="background:linear-gradient(180deg,#00837B 0%,rgba(255,255,255,0) 58.5%);"></div>

    {{-- Top bar --}}
    <div class="absolute left-0 right-0 top-0 pt-[env(safe-area-inset-top)] px-4">
      <div class="mt-3 flex items-center justify-between">
        <a href="{{ url()->previous() }}"
           class="h-12 w-12 rounded-full bg-white/20 backdrop-blur flex items-center justify-center shadow-md text-white hover:bg-white/30 transition"
           aria-label="Voltar">
          <svg viewBox="0 0 24 24" class="h-6 w-6"><path fill="currentColor" d="M15.41 7.41 14 6 8 12l6 6 1.41-1.41L10.83 12z"/></svg>
        </a>

        <button type="button"
                x-data
                @click="
                  if (navigator.share) {
                    navigator.share({ title: '{{ addslashes($nome) }}', url: window.location.href });
                  } else {
                    navigator.clipboard.writeText(window.location.href);
                  }"
                class="h-12 w-12 rounded-full bg-white/20 backdrop-blur flex items-center justify-center shadow-md text-white hover:bg-white/30 transition"
                aria-label="Compartilhar">
          <svg viewBox="0 0 24 24" class="h-6 w-6"><path fill="currentColor" d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11A2.99 2.99 0 0018 7.91a3 3 0 10-2.83-4A3 3 0 0012 6a3 3 0 00.09.7L5.04 10.8A3 3 0 003 10a3 3 0 100 6c.76 0 1.44-.3 1.96-.77l7.13 4.19c-.06.2-.09.41-.09.63a3 3 0 103-3z"/></svg>
        </button>
      </div>
    </div>
  </div>

  {{-- CARTÃO / CONTEÚDO (mesma lógica do show de empresa) --}}
  <div class="relative -mt-6 md:-mt-10">
    <section class="mx-auto w-full card-top">
      <div class="w-full flex justify-center pt-4"><div class="indicator"></div></div>

      {{-- Título + Ações rápidas --}}
      <div class="px-4 pt-3">
        <div class="flex items-center justify-between gap-3">
          <h1 class="text-[22px] md:text-[24px] leading-8 font-semibold text-[#2B3536] line-clamp-2">{{ $nome }}</h1>
          <div class="text-sm text-[#2B3536]/70">{{ $edicao->ano }}</div>
        </div>

        <div class="mt-1 flex items-center justify-between">
          <div class="flex items-center gap-2 text-[#868B8B]">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 110-6 3 3 0 010 6z"/></svg>
            <span class="text-[16px] leading-5">{{ $cidade }}</span>
          </div>

          @if(!is_null($nota))
            <div class="flex items-center gap-1">
              @for($i=1; $i<=5; $i++)
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $i <= round($nota) ? '#FCCF05' : '#E5E7EB' }}"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
              @endfor
              <span class="text-[#868B8B] text-[14px] leading-5 font-medium">{{ number_format($nota,1,',','.') }}</span>
            </div>
          @endif
        </div>
      </div>

      {{-- Seleção de anos --}}
      @if($anos->count() > 1)
        <div class="px-4 mt-2 overflow-x-auto">
          <div class="flex items-center gap-2">
            @foreach($anos as $a)
              <a href="{{ route('eventos.show', [$evento->slug ?? $evento->id, $a]) }}"
                 class="px-3 py-1.5 rounded-full border {{ $a==$edicao->ano ? 'bg-[var(--brand)] text-white border-[var(--brand)]' : 'bg-white text-[#2B3536] border-slate-300' }}">
                {{ $a }}
              </a>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Quando / Onde / Mapa --}}
      <div class="mt-3 px-4 grid gap-2">
        @if($quando)
          <div class="flex items-center gap-2 text-sm">
            <div>
              <div class="text-slate-900 font-medium">Data</div>
              <div class="text-slate-600">{{ $quando }}</div>
            </div>
          </div>
        @endif

        <div class="flex items-center gap-2 text-sm">
          <div class="min-w-0 flex-1">
            <div class="text-slate-900 font-medium">Local</div>
            <div class="text-slate-600 truncate">{{ $onde }}</div>
          </div>
          <a href="{{ $mapHref }}" class="ml-auto inline-flex items-center rounded-full bg-[var(--brand)] text-white px-3 py-1.5 text-xs">
            Ver no mapa
          </a>
        </div>
      </div>

      {{-- Tabs --}}
      <div x-data="{
            tab:'desc',
            lbOpen:false, i:0,
            imgs: @js($galeria),
            startX:0,
            openAt(k){ this.i=k; this.lbOpen=true; document.body.style.overflow='hidden'; },
            close(){ this.lbOpen=false; document.body.style.overflow=''; },
            next(){ if(!this.imgs.length)return; this.i=(this.i+1)%this.imgs.length; },
            prev(){ if(!this.imgs.length)return; this.i=(this.i-1+this.imgs.length)%this.imgs.length; },
            tstart(e){ this.startX=(e.touches?.[0]?.clientX)||0; },
            tend(e){ const dx=((e.changedTouches?.[0]?.clientX)||0)-this.startX; if(Math.abs(dx)>50){ dx<0?this.next():this.prev(); } }
          }"
          x-on:keydown.escape.window="lbOpen=false"
          x-on:keydown.arrow-right.window="lbOpen && next()"
          x-on:keydown.arrow-left.window="lbOpen && prev()"
          class="mt-3">

        <div class="px-4 flex items-start gap-12">
          <button class="tab-btn" :class="tab==='desc' ? 'tab-on' : 'tab-off'" @click="tab='desc'">Descrição</button>
          <button class="tab-btn" :class="tab==='gal'  ? 'tab-on' : 'tab-off'" @click="tab='gal'">Galeria de Fotos</button>
        </div>

        {{-- Descrição + Atrativos --}}
        <section x-show="tab==='desc'" x-cloak class="px-4 pt-3 space-y-4">
          <div>
            <h3 class="text-[16px] font-semibold text-[#2B3536]">Sobre</h3>
            @if($descricao)
              <p class="mt-1 text-[14px] leading-5 text-[#868B8B] text-justify">{!! $descricao !!}</p>
            @else
              <p class="mt-1 text-[14px] leading-5 text-[#868B8B]">Sem descrição cadastrada.</p>
            @endif
          </div>

          @if($atrativos->count())
            <div>
              <h3 class="text-[16px] font-semibold text-[#2B3536]">Atrativos</h3>
              <div class="mt-2 space-y-4">
                @foreach($atrativos as $a)
                  @php
                    $thumb = $a->thumb_url ?? $pub($a->thumb_path ?? null) ?? asset('images/placeholders/card.jpg');
                  @endphp
                  <div class="rounded-[10px] bg-white shadow-[0_4px_36px_rgba(0,0,0,0.09)] overflow-hidden">
                    <div class="p-2 flex gap-3">
                      <div class="w-[112px] sm:w-[136px] aspect-square rounded-[10px] overflow-hidden shrink-0">
                        <img src="{{ $thumb }}" class="w-full h-full object-cover" alt="{{ $a->nome }}">
                      </div>
                      <div class="min-w-0 flex-1 py-1">
                        <div class="text-[16px] font-semibold text-[#2B3536] truncate">{{ $a->nome }}</div>
                        @if($a->descricao)
                          <div class="mt-1 text-[12px] leading-[18px] text-[#868B8B] line-clamp-2">{{ $a->descricao }}</div>
                        @endif
                        <div class="mt-2">
                          <img src="{{ asset('imagens/visitpreto.png') }}" alt="Visit Altamira" class="h-5 opacity-90">
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif
        </section>

        {{-- Galeria (abre dentro com lightbox) --}}
        <section x-show="tab==='gal'" x-cloak class="px-4 pt-3">
          @if($galeria->count())
            <div class="grid grid-cols-2 gap-2">
              @foreach($galeria as $idx => $img)
                <button type="button" class="block rounded-lg overflow-hidden"
                        @click="openAt({{ $idx }})" aria-label="Abrir imagem {{ $idx+1 }}">
                  <img src="{{ $img['src'] }}" alt="{{ $img['alt'] }}" class="w-full h-28 object-cover">
                </button>
              @endforeach
            </div>

            {{-- Lightbox --}}
            <div x-show="lbOpen" x-cloak class="fixed inset-0 z-[60] lb-bg flex items-center justify-center"
                 x-transition.opacity @click.self="close()">
              <div class="relative w-full h-full wrap">
                <button class="absolute right-3 top-3 lb-btn" @click="close()" aria-label="Fechar">✕</button>
                <div class="h-full flex items-center justify-center px-3 select-none"
                     @touchstart.passive="tstart($event)" @touchend.passive="tend($event)">
                  <img :src="imgs[i]?.src" :alt="imgs[i]?.alt || ''"
                       class="max-h-[85vh] w-auto rounded-xl shadow-xl object-contain">
                </div>
                <div class="absolute inset-y-0 left-2 flex items-center">
                  <button class="lb-btn" @click.stop="prev()" aria-label="Anterior">‹</button>
                </div>
                <div class="absolute inset-y-0 right-2 flex items-center">
                  <button class="lb-btn" @click.stop="next()" aria-label="Próxima">›</button>
                </div>
              </div>
            </div>
          @else
            <div class="text-sm text-slate-500">Sem fotos nesta edição.</div>
          @endif
        </section>
      </div>

      {{-- Espaço final --}}
      <div class="h-4"></div>
    </section>
  </div>
</div>

{{-- Espaço p/ não cobrir conteúdo (mobile) + bottom nav --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@includeIf('site.partials._bottom_nav')
@endsection
