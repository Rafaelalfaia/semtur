<section class="border-t border-slate-200 dark:border-slate-800">
  <div class="mx-auto max-w-7xl px-4 py-6">
    <form action="{{ route('site.home') }}" method="get" class="relative">
      <input type="search" name="q" value="{{ $busca }}"
             placeholder="Buscar pontos, hotéis, empresas…"
             class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 backdrop-blur px-4 py-3 pr-12">
      <button class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
        Buscar
      </button>
    </form>
  </div>
</section>
