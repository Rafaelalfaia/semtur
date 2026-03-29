@php
  /** @var \App\Models\Catalogo\Categoria $categoria */
  /** @var \Illuminate\Support\Collection $pontos */
  /** @var \Illuminate\Support\Collection $empresas */
@endphp

<section class="border-t border-slate-200 dark:border-slate-800">
  <div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <h2 class="text-xl font-semibold">{{ $categoria->nome }}</h2>
      </div>
      <a href="{{ localized_route('site.explorar', ['categoria' => $categoria->slug]) }}" class="text-emerald-700 hover:underline">Ver tudo</a>
    </div>

    {{-- Pontos Turísticos --}}
    @if($pontos->count())
      <h3 class="text-sm font-medium text-slate-500 mb-2">Pontos Turísticos</h3>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($pontos as $p)
          @php $capa = optional($p->midias->first())->path ?? null; @endphp
          <article class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:shadow">
            <a href="{{ localized_route('site.explorar', ['ponto' => $p->id]) }}" class="block">
              <div class="aspect-[4/3] bg-slate-100 dark:bg-slate-800">
                @if($capa)
                  <img src="{{ Storage::url($capa) }}" class="w-full h-full object-cover" alt="{{ $p->nome }}" loading="lazy">
                @endif
              </div>
              <div class="p-4">
                <h4 class="font-semibold line-clamp-1">{{ $p->nome }}</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2 mt-1">{{ $p->descricao }}</p>
              </div>
            </a>
          </article>
        @endforeach
      </div>
    @endif

    {{-- Empresas --}}
    @if($empresas->count())
      <h3 class="text-sm font-medium text-slate-500 mb-2">Empresas</h3>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($empresas as $e)
          <article class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:shadow">
            <a href="{{ localized_route('site.explorar', ['empresa' => $e->slug]) }}" class="block">
              <div class="aspect-[4/3] bg-slate-100 dark:bg-slate-800 relative">
                @if($e->foto_capa_url)
                  <img src="{{ $e->foto_capa_url }}" class="w-full h-full object-cover" alt="{{ $e->nome }}" loading="lazy">
                @endif
                <div class="absolute -bottom-6 left-4">
                  <div class="w-12 h-12 rounded-xl overflow-hidden border-2 border-white shadow">
                    @if($e->foto_perfil_url)
                      <img src="{{ $e->foto_perfil_url }}" class="w-full h-full object-cover" alt="{{ $e->nome }}" loading="lazy">
                    @else
                      <div class="w-full h-full bg-slate-200"></div>
                    @endif
                  </div>
                </div>
              </div>
              <div class="p-4 pt-8">
                <h4 class="font-semibold line-clamp-1">{{ $e->nome }}</h4>
                <p class="text-xs text-slate-500">Empresa</p>
              </div>
            </a>
          </article>
        @endforeach
      </div>
    @endif
  </div>
</section>
