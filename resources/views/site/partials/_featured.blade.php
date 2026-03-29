@if(($pontos ?? null) && $pontos->count())
<section class="bg-white dark:bg-slate-900">
  <div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold">Destaques</h2>
      <a href="{{ localized_route('site.explorar', ['tipo' => 'pontos']) }}" class="text-emerald-700 hover:underline">Ver mais</a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      @foreach($pontos as $p)
        @php
          $capa = optional($p->midias->first())->path ?? null;
        @endphp
        <article class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:shadow">
          <a href="{{ localized_route('site.explorar', ['ponto' => $p->id]) }}" class="block">
            <div class="aspect-[4/3] bg-slate-100 dark:bg-slate-800">
              @if($capa)
                <img src="{{ Storage::url($capa) }}" class="w-full h-full object-cover" alt="{{ $p->nome }}">
              @endif
            </div>
            <div class="p-4">
              <h3 class="font-semibold line-clamp-1">{{ $p->nome }}</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2 mt-1">{{ $p->descricao }}</p>
            </div>
          </a>
        </article>
      @endforeach
    </div>
  </div>
</section>
@endif
