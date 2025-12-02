<x-section-head title="Categorias" :href="route('site.explorar')" />
<div class="px-4">
  <div class="flex gap-3 overflow-x-auto pb-2">
    @forelse($categorias as $c)
      <a href="{{ route('site.explorar',['categoria'=>$c->slug]) }}" class="shrink-0 flex flex-col items-center gap-2">
        <div class="w-16 h-16 rounded-full bg-white shadow-sm grid place-items-center">
          @if($c->icone_path)
            <img src="{{ Storage::url($c->icone_path) }}" alt="{{ $c->nome }}" class="w-8 h-8 object-contain">
          @else
            <span class="text-xl">🏷️</span>
          @endif
        </div>
        <span class="text-[12px] text-white/95">{{ $c->nome }}</span>
      </a>
    @empty
      <div class="text-white/80 text-sm">Sem categorias publicadas</div>
    @endforelse
  </div>
</div>
