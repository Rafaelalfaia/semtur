{{-- resources/views/partials/_instagram_carousel.blade.php --}}
@php $items = collect($instagram ?? []); @endphp

@if($items->isNotEmpty())
  <section class="mt-6">
    <div class="flex items-baseline justify-between mb-2">
      <h2 class="text-lg font-semibold">No Instagram</h2>
      <a href="https://www.instagram.com/visitaltamira/"
         class="text-sm opacity-80 hover:opacity-100 underline"
         target="_blank" rel="noopener">
         Ver perfil
      </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
      @foreach($items as $post)
        <a href="{{ $post['url'] ?? 'https://www.instagram.com/visitaltamira/' }}"
           target="_blank" rel="noopener"
           class="block group overflow-hidden rounded-xl border border-white/10 bg-white/5">
          <div class="aspect-square overflow-hidden">
            @if(!empty($post['image']))
              <img
                src="{{ $post['image'] }}"
                alt="Post do Instagram"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
                decoding="async">
            @else
              <div class="h-full w-full bg-slate-800 animate-pulse"></div>
            @endif
          </div>
          @if(!empty($post['caption']))
            <p class="p-3 text-xs leading-snug line-clamp-2 opacity-80">
              {{ $post['caption'] }}
            </p>
          @endif
        </a>
      @endforeach
    </div>
  </section>
@endif
