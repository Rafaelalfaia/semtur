@if(($hoteis ?? null) && $hoteis->count())
<section class="border-t border-slate-200 dark:border-slate-800">
  <div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold">Hotéis</h2>
      <a href="{{ route('site.explorar', ['tipo' => 'hoteis']) }}" class="text-emerald-700 hover:underline">Ver todos</a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      @foreach($hoteis as $h)
        <article class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:shadow">
          <a href="{{ route('site.explorar', ['empresa' => $h->slug]) }}" class="block">
            <div class="aspect-[4/3] bg-slate-100 dark:bg-slate-800 relative">
              @if($h->foto_capa_path)
                <img src="{{ Storage::url($h->foto_capa_path) }}" class="w-full h-full object-cover" alt="{{ $h->nome }}">
              @endif
              <div class="absolute -bottom-6 left-4">
                <div class="w-12 h-12 rounded-xl overflow-hidden border-2 border-white shadow">
                  @if($h->foto_perfil_path)
                    <img src="{{ Storage::url($h->foto_perfil_path) }}" class="w-full h-full object-cover" alt="{{ $h->nome }}">
                  @else
                    <div class="w-full h-full bg-slate-200"></div>
                  @endif
                </div>
              </div>
            </div>
            <div class="p-4 pt-8">
              <h3 class="font-semibold line-clamp-1">{{ $h->nome }}</h3>
              <p class="text-xs text-slate-500">Hotel</p>
            </div>
          </a>
        </article>
      @endforeach
    </div>
  </div>
</section>
@endif
