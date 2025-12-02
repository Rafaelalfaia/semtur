{{-- resources/views/partials/_instagram_carousel.blade.php --}}
@php
  $items = collect($instagram ?? []);
@endphp

@if($items->isNotEmpty())
  <div class="w-full">
    {{-- Header alinhado com “Eventos” --}}
    <div class="flex items-center justify-between mt-1 mb-3">
      <h2 class="text-[16px] md:text-lg font-semibold text-[#2B3536]">
        Publicações Instagram
      </h2>
      <a href="https://www.instagram.com/visitaltamira/" target="_blank" rel="noopener"
         class="text-[14px] text-[#00837B] hover:underline">Ver perfil</a>
    </div>

    {{-- trilho horizontal (gap de 9px como no UX) --}}
    <div class="ig-rail flex overflow-x-auto no-scrollbar snap-x snap-mandatory pb-3 gap-[9px]"
         style="scroll-behavior:smooth;-webkit-overflow-scrolling:touch;">
      @foreach($items as $post)
        @php
          $img     = $post['image'] ?? null;
          $proxied = $img ? route('proxy.ig', ['u' => $img]) : null;
          $href    = $post['url'] ?? 'https://www.instagram.com/visitaltamira/';
        @endphp

        <a href="{{ $href }}" target="_blank" rel="noopener"
           class="ig-card snap-start flex-none rounded-[16px] overflow-hidden
                  bg-white border border-[#E6EAEA]
                  shadow-[0_1px_2px_rgba(16,24,40,0.06)]
                  hover:shadow-[0_4px_12px_rgba(16,24,40,0.10)]
                  transition-all duration-300 flex flex-col group">
          {{-- mídia --}}
          <div class="ig-media relative w-full bg-[#F2F5F5] flex-shrink-0 overflow-hidden">
            @if($proxied)
              <img src="{{ $proxied }}" alt="Post do Instagram"
                   class="absolute inset-0 w-full h-full object-cover
                          transition-transform duration-500 group-hover:scale-[1.03]"
                   loading="lazy" decoding="async"
                   referrerpolicy="no-referrer" crossorigin="anonymous">
            @else
              <div class="h-full w-full bg-gray-100 animate-pulse"></div>
            @endif
          </div>

          {{-- legenda (2 linhas) --}}
          @if(!empty($post['caption']))
            <div class="flex-1 px-3 pt-2 pb-3 text-[14px] leading-5 text-[#2B3536] font-medium line-clamp-2">
              {{ $post['caption'] }}
            </div>
          @else
            <div class="flex-1"></div>
          @endif
        </a>
      @endforeach
    </div>
  </div>
@endif

<style>
  .no-scrollbar::-webkit-scrollbar{display:none}
  .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}

  /* Alturas “de app” */
  .ig-card  { height: 320px; }         /* mais delicado que 334px */
  .ig-media { height: 190px; }

  /* Larguras responsivas (fino no desktop) */
  @media (max-width: 660px){            /* mobile ~2 cards por tela */
    .ig-card { width: 45vw; }
  }
  @media (min-width: 661px) and (max-width: 1023px){ /* tablet */
    .ig-card { width: 280px; }
  }
  @media (min-width: 1024px){           /* desktop menor e elegante */
    .ig-card { width: 300px; }
  }
  @media (min-width: 1280px){           /* telas grandes */
    .ig-card { width: 320px; }
  }
</style>
