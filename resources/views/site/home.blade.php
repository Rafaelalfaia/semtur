@extends('site.layouts.app')
@section('title','Descubra Altamira')
@section('meta.description','Guia turístico oficial de Altamira, Pará.')
@section('meta.image', $capaUrl ?? '/images/og-default.jpg')

@section('title','Descubra Altamira — VisitAltamira')

@push('head')
  <style>[x-cloak]{display:none;}</style>
@endpush

@section('site.content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Route as R;
  use Illuminate\Support\Facades\Storage as FS;

  /* =================== 🛠 TAMANHOS AQUI =================== */
  // Destaque — celular (quadrado)
  $HERO_MOBILE_H  = '100vw';                 // altura = largura
  // Destaque — tablet/desktop (3:1 com +10% de altura) => 100vw / (3/1.1) = 100vw/2.727
  $HERO_DESKTOP_H = 'calc(100vw / 2.727)';
  // Banner normal — desktop -10% (≈ 3.333:1)
  $NORMAL_DESKTOP_ASPECT = '10/3';
  /* ======================================================== */

  /* Container padrão */
  $container = $container
    ?? 'mx-auto w-full max-w-[420px] md:max-w-[1024px] lg:max-w-[1200px] px-4 md:px-6';

  /* Dados */
  $categoriasConteudo = collect($categoriasConteudo ?? []);
  $recomendacoes      = collect($recomendacoes ?? []);
  $empresasTurismo    = collect($empresasTurismo ?? []);
  $banner             = $banner ?? null;                 // banner normal (single)
  $bannersNormais     = collect($bannersNormais ?? []);  // opcional: vários
  $bannerTopo         = $bannerTopo ?? null;             // destaque (single)
  $bannersDestaque    = collect($bannersDestaque ?? []); // destaque (coleção)
  $eventosHome        = collect($eventosHome ?? []); // [+] eventos que vêm do HomeController

  /* Rotas */
  $hasCategoria = R::has('site.categoria');
  $hasExplorar  = R::has('site.explorar');
  $hasPonto     = R::has('site.ponto');
  $hasEmpresa   = R::has('site.empresa');
  $hasEvtIndex  = R::has('eventos.index');  // [+]
  $hasEvtShow   = R::has('eventos.show');
  /* Helpers */
  $safeUrl = function (string $name, array $params = [], $fallback = '#') {
      try { return route($name, $params); } catch (\Throwable $e) { return $fallback; }
  };

  $toUrl = function ($path) {
      if (!$path) return null;
      return Str::startsWith($path, ['http://','https://','/'])
          ? $path : FS::disk('public')->url($path);
  };

  $mkHref = function ($item) use ($safeUrl, $hasExplorar, $hasPonto, $hasEmpresa) {
      $isArray = is_array($item); $isObj = is_object($item);
      $id    = $isArray ? ($item['id']   ?? null) : ($item->id   ?? null);
      $slug  = $isArray ? ($item['slug'] ?? null) : ($item->slug ?? null);
      $param = $slug ?: $id;
      $class   = $isObj ? class_basename($item) : null;
      $type    = $isArray ? ($item['type'] ?? null) : null;
      $hrefApi = $isArray ? ($item['href'] ?? null) : null;
      $isEmpresa = ($class === 'Empresa') || ($type === 'empresa') ||
                   ($isArray && isset($item['empresa_slug'])) ||
                   ($isObj && (isset($item->cnpj) || isset($item->perfil_path)));
      $isPonto = ($class === 'PontoTuristico') || ($type === 'ponto') ||
                 ($isObj && (isset($item->lat) || isset($item->lng) || method_exists($item, 'midias')));
      if ($param) {
            if ($isEmpresa && $hasEmpresa) return $safeUrl('site.empresa', ['empresa' => $param]);
          if ($isPonto   && $hasPonto)   return $safeUrl('site.ponto',   ['ponto'    => $param]);
          if ($hasPonto)   { $u = $safeUrl('site.ponto',   ['ponto'=>$param], null);   if ($u) return $u; }
          if ($hasEmpresa) { $u = $safeUrl('site.empresa', ['slugOrId'=>$param], null); if ($u) return $u; }
      }
      if ($hrefApi && Str::startsWith($hrefApi, ['http://','https://'])) return $hrefApi;
      if ($param && $hasExplorar) {
          return $isEmpresa ? $safeUrl('site.explorar', ['empresa'=>$param])
                            : $safeUrl('site.explorar', ['ponto'=>$param]);
      }
      return $hrefApi ?: '#';
  };

  /* Link do banner normal (single) */
  $bannerHref = '#';
  if ($banner) {
      $bArr = is_array($banner) ? $banner : null;
      $bObj = is_object($banner) ? $banner : null;
      $link = $bArr['link_url'] ?? ($bObj->link_url ?? null);
      $rel  = ($bArr['ponto'] ?? ($bObj->ponto ?? null)) ?: ($bArr['empresa'] ?? ($bObj->empresa ?? null));
      if ($link && Str::startsWith($link, ['http://','https://','/'])) {
          $bannerHref = $link;
      } elseif ($rel) {
          $bannerHref = $mkHref($rel);
      } elseif (!empty($bObj?->ponto_id) || !empty($bArr['ponto_id'] ?? null)) {
          $pid = $bArr['ponto_id'] ?? $bObj->ponto_id ?? null;
          $bannerHref = $hasPonto ? $safeUrl('site.ponto', ['ponto'=>$pid]) : '#';
      } elseif (!empty($bObj?->empresa_id) || !empty($bArr['empresa_id'] ?? null)) {
          $eid = $bArr['empresa_id'] ?? $bObj->empresa_id ?? null;
          $bannerHref = $hasEmpresa ? $safeUrl('site.empresa', ['slugOrId'=>$eid]) : '#';
      } else {
          $bannerHref = $mkHref($banner);
      }
  }

  /* Destaques (normaliza) */
  $rawHeroes = $bannersDestaque->count() ? $bannersDestaque
              : ($bannerTopo ? collect([$bannerTopo]) : collect());

  $heroes = $rawHeroes->map(function($b){
      $href  = $b->cta_link ?? $b->link_url ?? '#';
      if (!Str::startsWith((string)$href, ['http://','https://','/'])) $href = '#';
      $blank = (bool)($b->target_blank ?? false);
      $desktop = $b->imagem_desktop_url ?? $b->desktop_url ?? $b->imagem_url ?? $b->image_url ?? $b->imagem ?? null;
      $mobile  = $b->imagem_mobile_url  ?? $b->mobile_url  ?? $desktop;
      $cropD = $b->crop_desktop ?? [];
      $cropM = $b->crop_mobile  ?? [];
      // (mantemos sem aplicar no retorno — não é essencial ao carrossel)
      $bg      = $b->cor_fundo ?? '#00837B';
      return (object)['href'=>$href,'blank'=>$blank,'desktop'=>$desktop,'mobile'=>$mobile,'bg'=>$bg];
  })->filter(fn($h)=>$h->desktop || $h->mobile)->values();

  $hasHero = $heroes->isNotEmpty();
@endphp


{{-- =================== TOPO: BANNER DESTAQUE (sem título/overlay, encostado no topo) =================== --}}
<section class="relative overflow-hidden pb-6 md:pb-10"
         style="background: linear-gradient(180deg, #00837B 0%, #FFFFFF 100%);">

  @if($hasHero)
    {{-- FULL-BLEED + SEM MARGEM NO TOPO --}}
    <div class="relative pt-0"
         style="width:100vw;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw);margin-top:0;">
      <div
        x-data="{ i:0, n:{{ $heroes->count() }}, next(){ this.i=(this.i+1)%this.n }, go(k){ this.i=k } }"
        x-init="if(n>1){ setInterval(()=>next(), 5000) }"
        class="relative"
        role="region" aria-roledescription="carousel"
        data-hero-carousel
        >

        <!-- alturas do container -->
        <div class="relative">
          <!-- celular: quadrado -->
          <div class="block md:hidden" style="height: {{ $HERO_MOBILE_H }};"></div>
          <!-- tablet+desktop: mesmo tamanho e +10% de altura -->
          <div class="hidden md:block" style="height: {{ $HERO_DESKTOP_H }};"></div>

          @foreach($heroes as $k => $h)
            @php
              $srcM = $h->mobile ?: $h->desktop;
              $srcD = $h->desktop ?: $h->mobile;
              $isPngM = Str::endsWith(Str::lower(parse_url($srcM, PHP_URL_PATH) ?? ''), '.png');
              $isPngD = Str::endsWith(Str::lower(parse_url($srcD, PHP_URL_PATH) ?? ''), '.png');
              $fitM   = $isPngM ? 'object-contain' : 'object-cover';
              $fitD   = $isPngD ? 'object-contain' : 'object-cover';

              // 🔹 estilos de crop (opcionais; não impedem o carrossel)
              $cropM = $h->crop_mobile ?? ($h->cropMobile ?? null);
              $cropD = $h->crop_desktop ?? ($h->cropDesktop ?? null);
              $styleM = '';
              $styleD = '';
              if (is_array($cropM) && isset($cropM['x'])) {
                  $scaleM = $cropM['scaleX'] ?? 1;
                  $styleM = "object-position: {$cropM['x']}px {$cropM['y']}px; transform: scale({$scaleM});";
              }
              if (is_array($cropD) && isset($cropD['x'])) {
                  $scaleD = $cropD['scaleX'] ?? 1;
                  $styleD = "object-position: {$cropD['x']}px {$cropD['y']}px; transform: scale({$scaleD});";
              }
            @endphp

            <a href="{{ $h->href ?: '#' }}"
            @if($h->blank) target="_blank" rel="noopener" @endif
            class="absolute inset-0 transition-opacity duration-700"
            :style="i === {{ $k }} ? 'opacity:1;pointer-events:auto' : 'opacity:0;pointer-events:none'"
            style="background-color: {{ $h->bg }}"
            data-slide>



              <picture class="absolute inset-0 w-full h-full">
                <source media="(min-width: 768px)" srcset="{{ $srcD }}">
                <img src="{{ $srcM }}" alt="" class="w-full h-full md:hidden {{ $fitM }}" style="{{ $styleM }}">
                <img src="{{ $srcD }}" alt="" class="w-full h-full hidden md:block {{ $fitD }}" style="{{ $styleD }}">
              </picture>
            </a>
          @endforeach

        </div>

        @if($heroes->count() > 1)
          <div class="absolute bottom-3 inset-x-0 flex justify-center gap-2">
            @foreach($heroes as $k => $h)
             <button type="button" class="w-2.5 h-2.5 rounded-full bg-white/70"
                :class="{ 'bg-white': i==={{ $k }} }"
                @click="go({{ $k }})"
                data-dot
                aria-label="Ir ao banner {{ $k+1 }}"></button>

            @endforeach
          </div>
        @endif
      </div>
    </div>
  @endif

  {{-- ===================== Categorias ===================== --}}
  <div class="{{ $container }} mt-3 md:mt-6">
    @includeIf('site.partials._categories_top', [
      'categorias' => ($categorias ?? null) ?: $categoriasConteudo
    ])
  </div>

  {{-- ===================== Banner NORMAL (desktop -10%) ===================== --}}
  <div class="{{ $container }} mt-3 md:mt-6">
    <div
      class="relative overflow-hidden rounded-[20px] md:rounded-2xl w-full
             aspect-[21/7] md:aspect-[21/7] lg:aspect-[{{ $NORMAL_DESKTOP_ASPECT }}]
             md:bg-white/5 md:ring-1 md:ring-black/5 md:shadow-xl
             [&_img]:absolute [&_img]:inset-0 [&_img]:w-full [&_img]:h-full [&_img]:object-cover
             [&_picture>img]:absolute [&_picture>img]:inset-0 [&_picture>img]:w-full [&_picture>img]:h-full [&_picture>img]:object-cover
             [&_video]:absolute [&_video]:inset-0 [&_video]:w-full [&_video]:h-full [&_video]:object-cover
             [&_a]:block [&_a]:h-full [&_a]:w-full [&_figure]:h-full [&_figure]:w-full">

      @php $hasCarouselNormal = $bannersNormais->count() > 1; @endphp

      @if($hasCarouselNormal)
        {{-- vários banners normais -> gira para a ESQUERDA --}}
        <div x-data="{ i:0, n:{{ $bannersNormais->count() }}, prev(){ this.i=(this.i-1+this.n)%this.n }, go(k){ this.i=k } }"
             x-init="setInterval(()=>prev(), 5000)" class="relative h-full" role="region" aria-roledescription="carousel">
          @foreach($bannersNormais as $k => $bn)
            <div class="absolute inset-0 transition-opacity duration-700"
                 :class="{ 'opacity-100': i === {{ $k }}, 'opacity-0 pointer-events-none': i !== {{ $k }} }">
              @includeIf('site.partials._banner', ['banner' => $bn, 'href' => '#'])
            </div>
          @endforeach
        </div>
      @else
        @includeIf('site.partials._banner', ['banner' => $banner, 'href' => $bannerHref])
      @endif
    </div>
  </div>

  {{-- ===================== Recomendações ===================== --}}
  <div class="{{ $container }} pt-5">
    <div class="flex items-center justify-between">
      <h2 class="text-white font-semibold text-[16px] md:text-lg leading-5">Recomendações</h2>
      <a href="{{ Route::has('site.explorar') ? route('site.explorar') : '#' }}"
         class="text-white text-[14px] leading-5 hover:underline">Ver todos</a>
    </div>

    <div class="mt-3">
      @if($recomendacoes->isNotEmpty())
        @include('site.partials._recomendacoes_carousel', [
          'itens'       => $recomendacoes,
          'mkHref'      => $mkHref,
          'mobile_full' => true,
        ])
      @else
        <div class="text-sm text-white/90">Sem recomendações ainda.</div>
      @endif
    </div>
  </div>
</section>

{{-- ===================== Publicações Instagram ===================== --}}
<section class="bg-white py-4 md:py-6">
  <div class="{{ $container }}">
    @include('partials._instagram_carousel', ['instagram' => $instagram])
  </div>
</section>

{{-- ===================== BLOCOS POR CATEGORIA ===================== --}}
@foreach($categoriasConteudo as $cat)
  @php
    $catSlug = $cat->slug ?? null;
    $catId   = $cat->id   ?? null;

    if (\Illuminate\Support\Facades\Route::has('site.explorar')) {
        $catHref = route('site.explorar', array_filter([
            'categoria'    => $catSlug,
            'categoria_id' => $catId,
        ], fn($v) => !is_null($v) && $v !== ''));
    }
    elseif (\Illuminate\Support\Facades\Route::has('site.categoria') && $catSlug) {
        $catHref = route('site.categoria', ['categoria' => $catSlug]);
    } else {
        $catHref = '#';
    }
  @endphp

  <section class="bg-gradient-to-b from-slate-50 to-[#EAF4F2] py-2 md:py-4">
    <div class="{{ $container }}">
      <div class="flex items-center justify-between mt-1 mb-3">
        <h2 class="text-[16px] md:text-lg font-semibold text-[#2B3536]">{{ $cat->nome }}</h2>
        <a href="{{ $catHref }}" class="text-[14px] text-[#00837B] hover:underline">Ver todos</a>
      </div>

      <div class="space-y-3">
        @php
            $pontos    = collect($cat->pontos ?? []);
            $empresas  = collect($cat->empresas ?? []);
            $itens     = $pontos->concat($empresas)
                                ->sortBy(fn($i) => [ $i->ordem ?? 999999, \Illuminate\Support\Str::lower($i->nome ?? '') ])
                                ->values();
            @endphp


        @foreach($itens as $item)
          @php
            $cardTitle = $item->card_title ?? $item->nome ?? '';
            $cardSub   = $item->card_city  ?? ($item->cidade ?? 'Altamira');
            $cardImg   = $item->card_image_url ?? ($item->capa_url ?? ($item->foto_capa_url ?? null));
            $cardHref  = $item->card_href ?? $mkHref($item);
          @endphp
          <x-card-list :title="$cardTitle" :subtitle="$cardSub" :image="$cardImg" :href="$cardHref" logo="/imagens/visitpreto.png" />
        @endforeach
      </div>
    </div>
  </section>
@endforeach



{{-- ===================== EVENTOS (substitui Parceiros Turísticos) ===================== --}}
@php
  $eventosHref = $hasEvtIndex ? $safeUrl('eventos.index') : '#';
@endphp
<section class="bg-gradient-to-b from-white to-[#F5F7F7] py-2 md:py-4">
  <div class="{{ $container }}">
    <div class="flex items-center justify-between mt-1 mb-3">
      <h2 class="text-[16px] md:text-lg font-semibold text-[#2B3536]">Eventos</h2>
      <a href="{{ $eventosHref }}" class="text-[14px] text-[#00837B] hover:underline">Ver todos</a>
    </div>

    <div class="space-y-3">
      @forelse($eventosHome as $ev)
        @php
          $ed    = collect($ev->edicoes ?? [])->first();
          $ano   = $ed->ano ?? ($ev->ano_max ?? null);
          $slug  = $ev->slug ?? null;
          $href  = ($hasEvtShow && $slug) ? route('eventos.show', [$slug, $ano]) : '#';

          $imgPath = $ev->perfil_path ?? $ev->capa_path ?? null;
          $imgUrl  = $toUrl($imgPath);

          $cidade  = $ev->cidade ?? 'Altamira';
        @endphp

        <x-card-list
          :title="$ev->nome"
          :subtitle="$cidade"
          :image="$imgUrl"
          :href="$href"
          logo="/imagens/visitpreto.png"
        />
      @empty
        <div class="text-sm text-slate-500">Sem eventos publicados.</div>
      @endforelse
    </div>
  </div>
</section>

{{-- Espaço p/ não cobrir conteúdo (mobile) + bottom nav --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@includeIf('site.partials._bottom_nav')

{{-- Fallback JS: garante o carrossel do destaque em qualquer device, mesmo sem Alpine --}}
@push('scripts')
<script type="module">
(() => {
  const root = document.querySelector('[data-hero-carousel]');
  if (!root) return;
  const slides = Array.from(root.querySelectorAll('[data-slide]'));
  const dots   = Array.from(root.querySelectorAll('[data-dot]'));
  const n = slides.length;
  if (n <= 1) return;

  let i = 0;
  const show = (k) => {
    slides.forEach((el, idx) => {
      const active = idx === k;
      el.style.opacity = active ? '1' : '0';
      el.style.pointerEvents = active ? 'auto' : 'none';
      el.setAttribute('aria-hidden', active ? 'false' : 'true');
    });
    dots.forEach((d, idx) => {
      if (!d) return;
      d.classList.toggle('bg-white', idx === k);
      d.classList.toggle('bg-white/70', idx !== k);
    });
    i = k;
  };

  dots.forEach((d, idx) => d && d.addEventListener('click', () => show(idx)));

  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!reduce) setInterval(() => show((i + 1) % n), 5000);

  show(0);
})();
</script>
@endpush
@endsection
