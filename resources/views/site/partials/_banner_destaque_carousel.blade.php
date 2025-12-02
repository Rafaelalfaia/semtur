@props(['banners' => collect()])

@if($banners->count())
  <section class="relative mx-auto w-full rounded-2xl overflow-hidden my-4"
           style="--h: 220px;">
    <div x-data="{
            i: 0,
            n: {{ $banners->count() }},
            next(){ this.i = (this.i+1)%this.n },
            prev(){ this.i = (this.i-1+this.n)%this.n },
            go(k){ this.i = k }
         }"
         x-init="setInterval(()=>next(), 7000)"
         class="relative">

      <div class="relative" style="height: var(--h);">
        @foreach($banners as $k => $b)
          <a @if($b->cta_link) href="{{ $b->cta_link }}" @endif
             class="absolute inset-0 transition-opacity duration-700"
             :class="{ 'opacity-100': i === {{ $k }}, 'opacity-0 pointer-events-none': i !== {{ $k }} }"
             aria-label="{{ $b->titulo }}">
            <picture>
              <img src="{{ $b->imagem_url }}"
                   alt="{{ $b->titulo }}"
                   class="w-full h-full object-cover" loading="lazy" decoding="async">
            </picture>
            @if($b->titulo || $b->subtitulo || $b->cta_texto)
              <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                @if($b->titulo)
                  <h3 class="text-lg font-semibold leading-tight line-clamp-2">{{ $b->titulo }}</h3>
                @endif
                @if($b->subtitulo)
                  <p class="text-sm opacity-90 line-clamp-2 mt-1">{{ $b->subtitulo }}</p>
                @endif
                @if($b->cta_texto && $b->cta_link)
                  <span class="inline-block mt-3 text-xs font-medium bg-emerald-600/90 backdrop-blur px-3 py-1 rounded-full">
                    {{ $b->cta_texto }}
                  </span>
                @endif
              </div>
            @endif
          </a>
        @endforeach
      </div>

      <!-- bullets -->
      <div class="absolute bottom-2 inset-x-0 flex justify-center gap-2">
        @foreach($banners as $k => $b)
          <button type="button" class="w-2.5 h-2.5 rounded-full bg-white/70"
                  :class="{ 'bg-white': i==={{ $k }} }"
                  @click="go({{ $k }})" aria-label="Ir ao banner {{ $k+1 }}"></button>
        @endforeach
      </div>
    </div>
  </section>
@endif
