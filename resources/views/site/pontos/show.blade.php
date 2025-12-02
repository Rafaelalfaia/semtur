{{-- resources/views/site/pontos/show.blade.php --}}
@extends('site.layouts.app')
@section('title','Descubra Altamira')
@section('meta.description','Guia turístico oficial de Altamira, Pará.')
@section('meta.image', $capaUrl ?? '/images/og-default.jpg')

@section('title', ($ponto->nome ?? 'Ponto'). ' — SEMTUR')

@section('site.content')
@php
  use Illuminate\Support\Facades\Storage;
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Str;

  $nome      = $ponto->nome ?? 'Ponto Turístico';
  $cidade    = $ponto->cidade ?? 'Altamira';
  $regiao    = $ponto->regiao ?? null;
  $descricao = $ponto->descricao ?? null;
  $nota      = property_exists($ponto,'avaliacao_media') ? $ponto->avaliacao_media : null;

  // Capa
  $capaUrl = $ponto->capa_url
           ?? ($ponto->foto_capa_url
              ?? (optional($ponto->midias)->firstWhere('tipo','image')?->path
                    ? Storage::url(optional($ponto->midias)->firstWhere('tipo','image')->path)
                    : (optional($ponto->midias)->firstWhere('tipo','video_file')?->path
                          ? Storage::url(optional($ponto->midias)->firstWhere('tipo','video_file')->path)
                          : asset('images/placeholders/ponto-cover.jpg'))));

  // Coordenadas
  $lat = $ponto->lat ?? $ponto->latitude ?? null;
  $lng = $ponto->lng ?? $ponto->longitude ?? null;

  // Deep link para o mapa do app (com foco neste ponto)
  $mapBase  = Route::has('site.mapa') ? route('site.mapa') : url('/mapa');
  $slugOrId = $ponto->slug ?: $ponto->id;
  $mapQuery = array_filter([
      'focus' => 'ponto:'.$slugOrId,
      // só envia lat/lng se existirem
      'lat'   => is_numeric($lat) ? (float)$lat : null,
      'lng'   => is_numeric($lng) ? (float)$lng : null,
      'open'  => 1,
  ], fn($v) => !is_null($v) && $v !== '');
  $mapHref = $mapBase.'?'.http_build_query($mapQuery);

  // Galeria (somente imagens)
  $galeria = collect($ponto->midias ?? [])
              ->filter(fn($m) => ($m->tipo ?? null) === 'image')
              ->sortBy('ordem')
              ->values();

  // URLs das imagens (compatível com diferentes modelos de Midia)
  $galeriaUrls = $galeria->map(function($m){
      if (!empty($m->url) && is_string($m->url)) return $m->url;
      if (!empty($m->path)) return Storage::url($m->path);
      return null;
  })->filter()->values();

  // Empresas relacionadas
$empresas = collect($empresasComPacotes ?? $empresasRelacionadas ?? []);
$temEmpresas = $empresas->isNotEmpty();

@endphp


<div class="relative mx-auto w-full max-w-[420px] md:max-w-[768px] lg:max-w-[960px]">

  {{-- HERO --}}
  <div class="relative h-[44vh] min-h-[320px] md:h-[360px] lg:h-[420px] -mt-6 md:mt-0 overflow-hidden rounded-b-3xl">
    <img src="{{ $capaUrl }}" alt="Capa de {{ $nome }}" class="absolute inset-0 w-full h-full object-cover" loading="eager" decoding="async">
    <div class="absolute inset-0" style="background: linear-gradient(179.92deg, #00837B 0.07%, rgba(255,255,255,0) 58.56%);"></div>

    {{-- Top bar com safe-area (app-like) --}}
    <div class="absolute left-0 right-0 top-[env(safe-area-inset-top)] transform translate-y-10 sm:translate-y-8 md:translate-y-10 px-4">
        <div class="flex items-center justify-between">

        <a href="{{ url()->previous() }}"
        class="h-11 w-11 md:h-12 md:w-12 rounded-full bg-[#0A8B83] text-white grid place-content-center
                shadow-md ring-1 ring-black/5 hover:bg-[#0C998F] active:bg-[#07766F]
                transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
        aria-label="Voltar">
        <svg viewBox="0 0 24 24" class="h-5 w-5 md:h-6 md:w-6" fill="currentColor">
            <path d="M15.41 7.41 14 6 8 12l6 6 1.41-1.41L10.83 12z"/>
        </svg>
        </a>

        <button type="button"
                x-data
                @click="
                if (navigator.share) { navigator.share({ title: '{{ addslashes($nome) }}', url: window.location.href }) }
                else { navigator.clipboard.writeText(window.location.href) }
                "
                class="h-11 w-11 md:h-12 md:w-12 rounded-full bg-[#0A8B83] text-white grid place-content-center
                    shadow-md ring-1 ring-black/5 hover:bg-[#0C998F] active:bg-[#07766F]
                    transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                aria-label="Compartilhar">
        <svg viewBox="0 0 24 24" class="h-5 w-5 md:h-6 md:w-6" fill="currentColor">
            <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11A2.99 2.99 0 0018 7.91a3 3 0 10-2.83-4A3 3 0 0012 6a3 3 0 00.09.7L5.04 10.8A3 3 0 003 10a3 3 0 100 6c.76 0 1.44-.3 1.96-.77l7.13 4.19c-.06.2-.09.41-.09.63a3 3 0 103-3z"/>
        </svg>
        </button>

      </div>
    </div>
  </div>

  {{-- CARTÃO --}}
  <div class="relative -mt-6 md:-mt-10"
       x-data="{
         tab: 'descricao',
         lightbox: false,
         lbIndex: 0,
         lbItems: @js($galeriaUrls),
         prev(){ if(this.lbItems.length){ this.lbIndex = (this.lbIndex + this.lbItems.length - 1) % this.lbItems.length } },
         next(){ if(this.lbItems.length){ this.lbIndex = (this.lbIndex + 1) % this.lbItems.length } },
       }">
    <section class="mx-auto w-full bg-white rounded-t-[30px] shadow-sm">
      <div class="w-full flex justify-center pt-4">
        <div class="w-[134px] h-[5px] bg-[#868B8B]/80 rounded-full"></div>
      </div>

      {{-- Título + Local + Nota --}}
      <div class="px-4 pt-3">
        <div class="flex items-center justify-between gap-3">
          <h1 class="text-[22px] md:text-[24px] leading-8 font-semibold text-[#2B3536] line-clamp-2">{{ $nome }}</h1>
        </div>

        <div class="mt-1 flex items-center justify-between">
          {{-- AJUSTE: linka para o MAPA do app com foco neste ponto --}}
          <a href="{{ $mapHref }}"
             class="inline-flex items-center gap-1 text-[#868B8B] hover:text-[#2B3536] transition group"
             aria-label="Ver no mapa">
            <svg class="h-[20px] w-[20px] group-hover:scale-105 transition-transform" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3-1.3 3-3 3z"/>
            </svg>
            <span class="text-[16px] leading-5 underline-offset-4 group-hover:underline">
              Ver no mapa {{-- (mantém discreto; sem poluir com “Google Maps”) --}}
            </span>
          </a>

          @if($nota)
            <div class="flex items-center gap-1">
              @for($i=1; $i<=5; $i++)
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $i <= round($nota) ? '#FCCF05' : '#E5E7EB' }}"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
              @endfor
              <span class="text-[#868B8B] text-[14px] leading-5 font-medium">{{ number_format($nota,1) }}</span>
            </div>
          @endif
        </div>
      </div>

      {{-- Abas (Descrição / Galeria) --}}
      <div class="mt-3">
        <div class="flex items-start gap-[49px] px-4">
          <button class="flex-1 flex flex-col items-center gap-2 pb-2"
                  :class="tab==='descricao' ? '' : 'text-[#2B3536]'"
                  @click="tab='descricao'">
            <span class="font-semibold text-[16px]" :class="tab==='descricao' ? 'text-[#00837B]' : 'text-[#2B3536]'">Descrição</span>
            <span class="w-full border-b-2" :class="tab==='descricao' ? 'border-[#00837B]' : 'border-transparent'"></span>
          </button>
          <button class="flex-1 flex flex-col items-center gap-2 pb-2"
                  :class="tab==='galeria' ? '' : 'text-[#2B3536]'"
                  @click="tab='galeria'">
            <span class="font-semibold text-[16px]" :class="tab==='galeria' ? 'text-[#00837B]' : 'text-[#2B3536]'">Galeria</span>
            <span class="w-full border-b-2" :class="tab==='galeria' ? 'border-[#00837B]' : 'border-transparent'"></span>
          </button>
        </div>

        {{-- Descrição --}}
        <section x-show="tab==='descricao'" x-cloak class="px-4 pt-2">
          <h3 class="text-[16px] font-semibold text-[#2B3536]">Sobre</h3>
          @if($descricao)
            <div x-data="{ open:false }" class="mt-1">
              <div :class="open ? '' : 'line-clamp-6'"
                   class="text-[14px] leading-5 text-[#868B8B] text-justify">{!! $descricao !!}</div>
              <button type="button" class="mt-1 text-[#00837B] text-[14px] font-medium"
                      @click="open=!open"
                      x-text="open ? 'Mostrar menos' : 'Ler mais'"></button>
            </div>
          @else
            <p class="text-[14px] leading-5 text-[#868B8B]">Sem descrição cadastrada.</p>
          @endif
        </section>

        {{-- Galeria --}}
        <section x-show="tab==='galeria'" x-cloak class="px-4 pt-2 pb-3">
          @if($galeriaUrls->isNotEmpty())
            <div class="grid grid-cols-3 md:grid-cols-4 gap-2">
              @foreach($galeriaUrls as $i => $url)
                <button type="button"
                        class="relative aspect-square rounded-xl overflow-hidden ring-1 ring-black/5 focus:outline-none focus:ring-2 focus:ring-[#00837B]"
                        @click="lbIndex={{ $i }}; lightbox=true">
                  <img src="{{ $url }}" alt="Foto da galeria {{ $i+1 }} de {{ $nome }}"
                       class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                  <div class="absolute inset-0 bg-black/0 hover:bg-black/10 transition"></div>
                </button>
              @endforeach
            </div>
          @else
            <div class="text-[14px] leading-5 text-[#868B8B]">Sem fotos na galeria ainda.</div>
          @endif
        </section>
      </div>

      {{-- Header "Empresas com pacotes" --}}
     @if($temEmpresas)
    <div class="px-4 mt-2 flex items-center justify-between">
        <h3 class="text-[16px] font-semibold text-[#2B3536]">Empresas com pacotes</h3>
        @if(Route::has('site.explorar'))
        <a href="{{ route('site.explorar', ['ponto' => $ponto->slug ?? $ponto->id]) }}"
            class="text-[14px] font-medium text-[#00837B] hover:underline underline-offset-4">Ver todos</a>
        @endif
    </div>
    @endif



      {{-- Lista de empresas --}}
      @if($temEmpresas)
    <div class="px-4 pb-6 pt-2 space-y-4">
        @foreach($empresas as $e)
        @php
            $empNome = $e->nome ?? 'Empresa';
            $empCid  = $e->cidade ?? 'Altamira';
            $empImg  = $e->perfil_url ?? $e->capa_url ?? asset('images/placeholders/perfil-empresa.jpg');
            $empHref = route('site.empresa', $e->slug ?? $e->id);
        @endphp

        <a href="{{ $empHref }}" class="block rounded-[10px] bg-white ring-1 ring-black/5 shadow-md hover:shadow-lg transition">
            <div class="relative h-[139px]">
            <div class="absolute left-2 top-2 h-[123px] w-[136px] rounded-[10px] overflow-hidden">
                <img src="{{ $empImg }}" alt="Imagem da empresa {{ $empNome }}" class="w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 rounded-b-[10px] bg-gradient-to-b from-transparent to-black/60"></div>
            </div>

            <div class="absolute left-[157px] top-[13px] right-3 flex flex-col gap-2">
                <div class="text-[16px] leading-[20px] font-semibold text-[#2B3536] line-clamp-2">{{ $empNome }}</div>
                <div class="flex items-center gap-1 text-[#868B8B]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3-1.3 3-3 3z"/></svg>
                <span class="text-[12px] leading-[18px]">{{ $empCid }}</span>
                </div>
                <div class="mt-1">
                <img src="{{ asset('imagens/visitpreto.png') }}" class="h-[17px]" alt="Visit Altamira">
                </div>
            </div>
            </div>
        </a>
        @endforeach
    </div>
@endif


    {{-- LIGHTBOX --}}
    <div x-show="lightbox" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80"
         @keydown.escape.window="lightbox=false">
      <button class="absolute top-5 right-5 h-11 w-11 rounded-full bg-white/20 text-white backdrop-blur flex items-center justify-center hover:bg-white/30 transition"
              @click="lightbox=false" aria-label="Fechar">
        <svg viewBox="0 0 24 24" class="h-6 w-6"><path fill="currentColor" d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
      </button>

      <button class="absolute left-3 md:left-6 h-11 w-11 rounded-full bg-white/20 text-white backdrop-blur flex items-center justify-center hover:bg-white/30 transition"
              @click.stop="prev()" aria-label="Anterior">
        <svg viewBox="0 0 24 24" class="h-6 w-6"><path fill="currentColor" d="M15.41 7.41 14 6 8 12l6 6 1.41-1.41L10.83 12z"/></svg>
      </button>

      <img :src="lbItems[lbIndex]" class="max-h-[88vh] max-w-[92vw] object-contain rounded-xl shadow-2xl" alt="Foto da galeria">

      <button class="absolute right-3 md:right-6 h-11 w-11 rounded-full bg-white/20 text-white backdrop-blur flex items-center justify-center hover:bg-white/30 transition"
              @click.stop="next()" aria-label="Próxima">
        <svg viewBox="0 0 24 24" class="h-6 w-6"><path fill="currentColor" d="m10 6 1.41 1.41L8.83 10H20v2H8.83l2.58 2.59L10 16l-6-6 6-6z"/></svg>
      </button>
    </div>
  </div>
</div>

{{-- SEO básico --}}
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'TouristAttraction',
  'name' => $nome,
  'image' => $capaUrl,
  'address' => [
    '@type' => 'PostalAddress',
    'addressLocality' => $cidade,
    'addressRegion' => $regiao,
    'addressCountry' => 'BR',
  ],
  'url' => url()->current(),
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
</script>

{{-- Espaço p/ não cobrir conteúdo (mobile) + bottom nav --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@includeIf('site.partials._bottom_nav')
@endsection
