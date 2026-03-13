@php
  // tokens visuais
  $titleClass = 'text-[22px] leading-6 font-semibold';
  $chipSizeMb = 'w-14 h-14';   // 56px
  $iconSizeMb = 'w-7 h-7';     // 28px
  $labelClass = 'text-[12px] leading-[14px] text-white/95';

  // containers responsivos (alinha com a Home)
  $container = 'mx-auto w-full max-w-[420px] md:max-w-[1024px] lg:max-w-[1200px] px-4 md:px-6';
@endphp

@php
  $titleClass = 'text-[22px] leading-6 font-semibold';
  $chipSizeMb = 'w-14 h-14';
  $iconSizeMb = 'w-7 h-7';

  $container = 'mx-auto w-full max-w-[420px] md:max-w-[1024px] lg:max-w-[1200px] px-4 md:px-6';

  $activeSlug = request('categoria') ?? request('cat') ?? ($currentCat->slug ?? null);
  $linkFor = $href ?? fn($cat) => route('site.explorar', ['categoria' => $cat->slug]);
@endphp

{{-- estilo local p/ esconder scrollbar dos chips no mobile --}}
<style>
  .hide-scrollbar::-webkit-scrollbar { display: none; }
  .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<section class="text-white pt-3 pb-4">
  {{-- Título (sem "ver todas", conforme UX) --}}
  <div class="{{ $container }}">
    <h1 class="{{ $titleClass }}">Categorias</h1>
  </div>

  {{-- MOBILE/TABLET: chips roláveis --}}
  <div class="{{ $container }} mt-3 md:hidden">
  <div class="flex gap-4 overflow-x-auto pb-1 snap-x snap-mandatory hide-scrollbar">
    @forelse($categorias as $c)
      @php $isActive = $activeSlug === $c->slug; @endphp

      <a href="{{ $linkFor($c) }}"
         class="shrink-0 flex flex-col items-center gap-2 snap-start w-[82px]"
         aria-label="Categoria {{ $c->nome }}">
        <div class="{{ $chipSizeMb }} rounded-full grid place-items-center transition-all duration-200
                    {{ $isActive
                        ? 'bg-[#FFF4D9] ring-4 ring-white/25 shadow-[0_10px_24px_rgba(0,0,0,0.22)] scale-105'
                        : 'bg-white shadow-[0_3px_10px_rgba(0,0,0,0.12)]' }}">
          @if($c->icone_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($c->icone_path) }}"
                 alt="{{ $c->nome }}"
                 class="{{ $iconSizeMb }} object-contain"
                 loading="lazy" decoding="async">
          @else
            <span class="text-lg">🏷️</span>
          @endif
        </div>

        <span class="text-center text-[12px] leading-[14px] w-full min-h-[28px]
                    {{ $isActive ? 'font-semibold text-white' : 'text-white/95' }}">
          {{ $c->nome }}
        </span>
      </a>
    @empty
      <div class="text-white/85 text-sm">Sem categorias publicadas</div>
    @endforelse
  </div>
</div>

  {{-- DESKTOP: carrossel com setas (mobile/tablet continuam iguais) --}}
<div class="{{ $container }} hidden md:block mt-4">
  <div
    x-data="{
      el: null,
      by(px){ this.el && this.el.scrollBy({ left: px, behavior: 'smooth' }) },
      next(){ this.by(this.el?.clientWidth ? this.el.clientWidth * 0.8 : 640) },
      prev(){ this.by(-(this.el?.clientWidth ? this.el.clientWidth * 0.8 : 640)) },
    }"
    class="relative"
    role="region" aria-roledescription="carousel" aria-label="Categorias"
  >
    {{-- Seta esquerda --}}
    <button type="button"
            @click="prev()"
            class="absolute -left-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-white text-[#2B3536]
                   shadow ring-1 ring-black/5 grid place-content-center hover:bg-slate-50"
            aria-label="Anterior">
      <svg viewBox="0 0 24 24" class="h-5 w-5"><path fill="currentColor" d="M15.41 7.41 14 6 8 12l6 6 1.41-1.41L10.83 12z"/></svg>
    </button>

    {{-- Trilho --}}
    <div x-ref="track" x-init="el=$refs.track"
         class="overflow-hidden">
      <div class="flex gap-6">
        @forelse($categorias as $c)
          <a href="{{ route('site.explorar',['categoria'=>$c->slug]) }}"
             class="snap-start shrink-0 group w-[112px] flex flex-col items-center gap-3"
             aria-label="Categoria {{ $c->nome }}">
            <div class="w-16 h-16 lg:w-18 lg:h-18 rounded-full bg-white
                        shadow-[0_6px_20px_rgba(0,0,0,0.12)]
                        grid place-items-center transition-transform
                        group-hover:scale-105">
              @if($c->icone_path)
                <img src="{{ Storage::url($c->icone_path) }}"
                     alt="{{ $c->nome }}"
                     class="w-8 h-8 object-contain"
                     loading="lazy" decoding="async">
              @else
                <span class="text-xl">🏷️</span>
              @endif
            </div>
            <span class="text-sm leading-5 text-white/95 text-center line-clamp-1">
              {{ $c->nome }}
            </span>
          </a>
        @empty
          <div class="text-white/85">Sem categorias publicadas</div>
        @endforelse
      </div>
    </div>

    {{-- Seta direita --}}
    <button type="button"
            @click="next()"
            class="absolute -right-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-white text-[#2B3536]
                   shadow ring-1 ring-black/5 grid place-content-center hover:bg-slate-50"
            aria-label="Próximo">
      <svg viewBox="0 0 24 24" class="h-5 w-5"><path fill="currentColor" d="m10 6 1.41 1.41L8.83 10H20v2H8.83l2.58 2.59L10 16l-6-6 6-6z"/></svg>
    </button>
  </div>
</div>

</section>
