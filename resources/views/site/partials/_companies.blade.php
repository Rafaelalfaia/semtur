@if(($empresas ?? null) && $empresas->count())
<section class="bg-white dark:bg-slate-900">
  <div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold">Empresas</h2>
      <a href="{{ route('site.explorar', ['tipo' => 'empresas']) }}" class="text-emerald-700 hover:underline">Ver mais</a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      @foreach($empresas as $e)
        <a href="{{ route('site.explorar', ['empresa' => $e->slug]) }}"
           class="rounded-2xl p-4 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 hover:shadow flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-200">
            @if($e->foto_perfil_path)
              <img src="{{ Storage::url($e->foto_perfil_path) }}" class="w-full h-full object-cover" alt="{{ $e->nome }}">
            @endif
          </div>
          <div>
            <div class="font-medium line-clamp-1">{{ $e->nome }}</div>
            <div class="text-xs text-slate-500">Empresa</div>
          </div>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif
