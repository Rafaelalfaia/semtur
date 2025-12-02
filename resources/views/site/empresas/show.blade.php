{{-- resources/views/site/empresas/show.blade.php --}}
@extends('site.layouts.app')
@section('title','Descubra Altamira')
@section('meta.description','Guia turístico oficial de Altamira, Pará.')
@section('meta.image', $capaUrl ?? '/images/og-default.jpg')


@section('site.content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Storage;

  use Illuminate\Support\Facades\Route;

  // helper p/ URL pública do storage (sempre no disk 'public')
  $pub = function($path){
    return $path ? Storage::disk('public')->url($path) : null;
  };

  $nome      = $empresa->nome ?? 'Empresa';
  $cidade    = $empresa->cidade ?? 'Altamira';
  $regiao    = $empresa->regiao ?? null;
  $descricao = $empresa->descricao ?? null;

  // IMAGENS (sempre tentar caminhos do disk 'public' antes do placeholder)
  $capaUrl = $empresa->capa_url
            ?? $empresa->foto_capa_url
            ?? $pub($empresa->capa_path ?? null)
            ?? asset('images/placeholders/capa-empresa.jpg');

  $perfilUrl = $empresa->perfil_url
            ?? $empresa->foto_perfil_url
            ?? $pub($empresa->perfil_path ?? null)
            ?? asset('images/placeholders/perfil-empresa.jpg');

  // nota (opcional)
  $nota = property_exists($empresa,'avaliacao_media') ? $empresa->avaliacao_media : null;

  // pacotes (pontos relacionados) – vindo do controller; fallback vazio
  $pacotes = collect($pontosRelacionados ?? []);

  // coordenadas
  $lat = $empresa->lat ?? $empresa->latitude ?? null;
  $lng = $empresa->lng ?? $empresa->longitude ?? null;

  // deep-link para o mapa do app (foco nesta empresa)
  $mapBase  = Route::has('site.mapa') ? route('site.mapa') : url('/mapa');
  $slugOrId = $empresa->slug ?: $empresa->id;
  $mapQuery = array_filter([
      'focus' => 'empresa:'.$slugOrId,
      'lat'   => is_numeric($lat) ? (float)$lat : null,
      'lng'   => is_numeric($lng) ? (float)$lng : null,
      'open'  => 1,
  ], fn($v) => !is_null($v) && $v !== '');
  $mapHref = $mapBase.'?'.http_build_query($mapQuery);
@endphp

@push('head')
<script type="application/ld+json">
{
 "@context": "https://schema.org",
 "@type": "LocalBusiness",
 "name": "{{ $empresa->nome }}",
 "image": ["{{ $capaUrl ?? $logoUrl ?? url('/images/og-default.jpg') }}"],
 "url": "{{ url()->current() }}",
 "address": {"@type":"PostalAddress","addressLocality":"Altamira","addressRegion":"PA","addressCountry":"BR"},
 "geo": {"@type":"GeoCoordinates","latitude": {{ $empresa->lat ?? 'null' }}, "longitude": {{ $empresa->lng ?? 'null' }}},
 "telephone": "{{ $empresa->telefone ?? '' }}",
 "sameAs": [
   @if($empresa->instagram) "{{ $empresa->instagram }}", @endif
   @if($empresa->facebook) "{{ $empresa->facebook }}", @endif
   @if($empresa->site) "{{ $empresa->site }}" @endif
 ]
}
</script>
@endpush


{{-- Pré-carregar a capa para evitar flash/sumiço em mobile --}}
<link rel="preload" as="image" href="{{ $capaUrl }}"/>

<div class="relative mx-auto w-full max-w-[420px] md:max-w-[768px] lg:max-w-[960px]">

  {{-- HERO: usar dvh no mobile para altura estável --}}
  <div class="relative h-[44dvh] min-h-[320px] md:h-[360px] lg:h-[420px] -mt-6 md:mt-0 overflow-hidden rounded-b-3xl">
    <img
      src="{{ $capaUrl }}"
      alt="Capa de {{ $nome }}"
      class="absolute inset-0 w-full h-full object-cover"
      loading="eager"
      decoding="async"
      fetchpriority="high"
      onerror="this.onerror=null;this.src='{{ asset('images/placeholders/capa-empresa.jpg') }}';"
    >
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

  {{-- CARTÃO de conteúdo (app look) --}}
  <div class="relative -mt-6 md:-mt-10">
    <section class="mx-auto w-full bg-white rounded-t-[30px] shadow-sm">
      {{-- home indicator (pílula) --}}
      <div class="w-full flex justify-center pt-4">
        <div class="w-[134px] h-[5px] bg-[#868B8B]/80 rounded-full"></div>
      </div>

      {{-- Título + Local + Nota --}}
      <div class="px-4 pt-3">
        <div class="flex items-center justify-between gap-3">
          <h1 class="text-[22px] md:text-[24px] leading-8 font-semibold text-[#2B3536] line-clamp-2">{{ $nome }}</h1>
        </div>

        <div class="mt-1 flex items-center justify-between">
          <a href="{{ $mapHref }}"
             class="inline-flex items-center gap-1 text-[#868B8B] hover:text-[#2B3536] transition group"
             aria-label="Ver no mapa">
            <svg class="h-[20px] w-[20px] group-hover:scale-105 transition-transform" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3-1.3 3-3 3z"/>
            </svg>
            <span class="text-[16px] leading-5 underline-offset-4 group-hover:underline">
              Ver no mapa
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

      {{-- Abas --}}
      <div x-data="{ tab:'descricao' }" class="mt-3">
        <div class="flex items-start gap-[49px] px-4">
          <button class="flex-1 flex flex-col items-center gap-2 pb-2"
                  :class="tab==='descricao' ? '' : 'text-[#2B3536]'"
                  @click="tab='descricao'">
            <span class="font-semibold text-[16px]" :class="tab==='descricao' ? 'text-[#00837B]' : 'text-[#2B3536]'">Descrição</span>
            <span class="w-full border-b-2" :class="tab==='descricao' ? 'border-[#00837B]' : 'border-transparent'"></span>
          </button>
          <button class="flex-1 flex flex-col items-center gap-2 pb-2"
                  :class="tab==='redes' ? '' : 'text-[#2B3536]'"
                  @click="tab='redes'">
            <span class="font-semibold text-[16px]" :class="tab==='redes' ? 'text-[#00837B]' : 'text-[#2B3536]'">Redes Sociais</span>
            <span class="w-full border-b-2" :class="tab==='redes' ? 'border-[#00837B]' : 'border-transparent'"></span>
          </button>
        </div>

        {{-- Aba: Descrição --}}
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

        {{-- Aba: Redes sociais / contato (ícones redondos teal) --}}
    <section x-show="tab==='redes'" x-cloak class="px-4 pt-6 sm:pt-7 md:pt-8 lg:pt-4">
    @php
        $links = is_array($empresa->social_links ?? null) ? $empresa->social_links : [];
        $socialItems = collect([
        ['k'=>'whatsapp','label'=>'WhatsApp','href'=>$links['whatsapp']??null,'rel'=>'noopener nofollow',
            'svg'=>'<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] md:w-[20px] md:h-[20px]" fill="currentColor"><path d="M20.52 3.48A11.8 11.8 0 0 0 12.06 0C5.48 0 .16 5.32.16 11.9c0 2.1.55 4.17 1.6 5.99L0 24l6.29-1.72a11.85 11.85 0 0 0 5.78 1.5h.01c6.57 0 11.9-5.32 11.9-11.9a11.8 11.8 0 0 0-3.46-8.4ZM12.08 21.4h-.01a9.5 9.5 0 0 1-4.84-1.32l-.35-.2-3.73 1.02 1-3.63-.23-.37a9.5 9.5 0 0 1-1.47-5.02c0-5.25 4.27-9.52 9.53-9.52 2.54 0 4.92.99 6.71 2.78a9.45 9.45 0 0 1 2.78 6.72c0 5.25-4.27 9.52-9.53 9.52Zm5.33-7.12c-.29-.14-1.67-.82-1.93-.91-.26-.1-.45-.14-.64.14-.19.29-.74.91-.9 1.1-.16.2-.34.22-.63.08-.29-.14-1.23-.45-2.36-1.43a8.81 8.81 0 0 1-1.62-1.99c-.17-.29 0-.45.13-.59.13-.13.29-.34.42-.51.14-.17.19-.29.29-.48.1-.2.05-.37-.02-.51-.07-.14-.64-1.54-.88-2.12-.23-.55-.47-.48-.64-.49l-.55-.01c-.2 0-.51.07-.78.37-.27.29-1.03 1-1.03 2.43s1.06 2.82 1.21 3.02c.14.2 2.09 3.2 5.06 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.88.12.57-.08 1.67-.69 1.91-1.36.24-.66.24-1.22.17-1.36-.07-.14-.26-.22-.55-.36Z"/></svg>'],
        ['k'=>'site','label'=>'Site','href'=>$links['site']??null,'rel'=>'noopener nofollow',
            'svg'=>'<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] md:w-[20px] md:h-[20px]" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 3 2.5 15 0 18M6 6c3 2 9 2 12 0"/></svg>'],
        ['k'=>'instagram','label'=>'Instagram','href'=>$links['instagram']??null,'rel'=>'noopener nofollow',
            'svg'=>'<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] md:w-[20px] md:h-[20px]" fill="currentColor"><path d="M12 2.2c3.27 0 3.67.01 4.96.07 1.2.06 1.86.25 2.3.42.58.22.99.49 1.43.92.43.44.7.85.92 1.43.17.44.36 1.1.42 2.3.06 1.29.07 1.69.07 4.96s-.01 3.67-.07 4.96c-.06 1.2-.25 1.86-.42 2.3a3.86 3.86 0 0 1-.92 1.43c-.44.43-.85.7-1.43.92-.44.17-1.1.36-2.3.42-1.29.06-1.69.07-4.96.07s-3.67-.01-4.96-.07c-1.2-.06-1.86-.25-2.3-.42a3.86 3.86 0 0 1-1.43-.92 3.86 3.86 0 0 1-.92-1.43c-.17-.44-.36-1.1-.42-2.3C2.21 15.67 2.2 15.27 2.2 12s.01-3.67.07-4.96c.06-1.2.25-1.86.42-2.3.22-.58.49-.99.92-1.43.44-.43.85-.7 1.43-.92.44-.17 1.1-.36 2.3-.42C8.33 2.21 8.73 2.2 12 2.2Zm0 4.8a4.2 4.2 0 1 0 0 8.4 4.2 4.2 0 0 0 0-8.4Z"/></svg>'],
        ['k'=>'facebook','label'=>'Facebook','href'=>$links['facebook']??null,'rel'=>'noopener nofollow',
            'svg'=>'<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] md:w-[20px] md:h-[20px]" fill="currentColor"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.19 8.44 9.94v-7.03H7.9v-2.9h2.54V9.9c0-2.51 1.5-3.9 3.8-3.9 1.1 0 2.25.2 2.25.2v2.47h-1.27c-1.25 0-1.64.78-1.64 1.58v1.9h2.79l-.45 2.9h-2.34V22c4.78-.75 8.44-4.92 8.44-9.94Z"/></svg>'],
        ['k'=>'youtube','label'=>'YouTube','href'=>$links['youtube']??null,'rel'=>'noopener nofollow',
            'svg'=>'<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] md:w-[20px] md:h-[20px]" fill="currentColor"><path d="M23.5 7.1s-.23-1.62-.93-2.33c-.9-.93-1.9-.94-2.36-.99C16.84 3.5 12 3.5 12 3.5h-.01s-4.84 0-8.2.28c-.46.05-1.47.06-2.36.99C.73 5.48.5 7.1.5 7.1S.25 9.06.25 11v1.98c0 1.94.25 3.9.25 3.9s.23 1.62.93 2.33c.9.93 2.09.9 2.62 1 1.9.18 8 .28 8 .28s4.84 0 8.2-.28c.46-.05 1.47-.06 2.36-.99.7-.71.93-2.33.93-2.33s.25-1.96.25-3.9V11c0-1.94-.25-3.9-.25-3.9ZM9.75 14.75V8.75l6 3-6 3Z"/></svg>'],
        ['k'=>'tiktok','label'=>'TikTok','href'=>$links['tiktok']??null,'rel'=>'noopener nofollow',
            'svg'=>'<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] md:w-[20px] md:h-[20px]" fill="currentColor"><path d="M17.66 6.9a6.3 6.3 0 0 1-2.64-3.2V3h-3.1v11.33a2.37 2.37 0 1 1-2.37-2.37c.23 0 .45.03.66.1V8.8a5.47 5.47 0 1 0 3.91 5.23V8.76c1 .86 2.27 1.44 3.66 1.54V8.1c-.71-.05-1.43-.28-2.12-.63Z"/></svg>'],
        ['k'=>'maps','label'=>'Como chegar','href'=>$links['maps']??null,'rel'=>'noopener',
            'svg'=>'<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] md:w-[20px] md:h-[20px]" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-5.33-7-11a7 7 0 1 1 14 0c0 5.67-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>'],
        ['k'=>'email','label'=>'E-mail','href'=>(!empty($links['email']) ? 'mailto:'.$links['email'] : null),'rel'=>'noopener',
            'svg'=>'<svg viewBox="0 0 24 24" class="w-[18px] h-[18px] md:w-[20px] md:h-[20px]" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>'],
        ])->filter(fn($i) => filled($i['href']))->values();
    @endphp

    @if($socialItems->isNotEmpty())
        <div class="mt-2 sm:mt-3 md:mt-4 pb-3 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3 sm:gap-4 md:gap-5">
        @foreach($socialItems as $i)
            <a href="{{ $i['href'] }}" target="_blank" rel="{{ $i['rel'] }}" title="{{ $i['label'] }}"
            class="group w-14 h-14 md:w-[60px] md:h-[60px] rounded-full bg-[#0A8B83] text-white
                    grid place-content-center shadow-md ring-1 ring-black/5
                    hover:bg-[#0C998F] active:bg-[#07766F]
                    focus:outline-none focus-visible:ring-2 focus-visible:ring-[#9CE5DF]"
            aria-label="{{ $i['label'] }}">
            <span class="opacity-95 group-hover:opacity-100">{!! $i['svg'] !!}</span>
            <span class="sr-only">{{ $i['label'] }}</span>
            </a>
        @endforeach
        </div>
    @else
        <p class="text-[14px] leading-5 text-[#868B8B]">Sem redes sociais cadastradas.</p>
    @endif

    @php unset($socialItems); @endphp
    </section>




      {{-- Header "Pacotes Turísticos" --}}
      @if($pacotes instanceof \Illuminate\Support\Collection ? $pacotes->isNotEmpty() : (!empty($pacotes)))
    <div class="px-4 mt-2 flex items-center justify-between">
        <h3 class="text-[16px] font-semibold text-[#2B3536]">Pacotes Turísticos</h3>
        @if(Route::has('site.explorar'))
        <a href="{{ route('site.explorar', ['empresa' => $empresa->slug ?? $empresa->id]) }}"
            class="text-[14px] font-medium text-[#00837B] hover:underline underline-offset-4">Ver todos</a>
        @endif
    </div>

    @endif




    </section>
  </div>
</div>

{{-- JSON-LD básico para SEO --}}
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'LocalBusiness',
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
