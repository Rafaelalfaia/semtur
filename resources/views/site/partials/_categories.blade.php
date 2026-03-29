@if(($categorias ?? null) && $categorias->count())
<section class="mx-auto max-w-7xl px-4 py-10">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Categorias</h2>
    <a href="{{ localized_route('site.explorar') }}" class="text-emerald-700 hover:underline">Ver todas</a>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @foreach($categorias as $c)
      <a href="{{ localized_route('site.explorar', ['categoria' => $c->slug]) }}"
         class="group p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:shadow">
        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
          @if($c->icone_path)
            <img src="{{ Storage::url($c->icone_path) }}" alt="{{ $c->nome }}" class="w-8 h-8 object-contain">
          @else
            <span class="text-slate-400">🏷️</span>
          @endif
        </div>
        <div class="text-sm font-medium line-clamp-1">{{ $c->nome }}</div>
      </a>
    @endforeach
  </div>
</section>
@endif
