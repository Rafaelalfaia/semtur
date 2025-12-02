@props(['action' => url('/explorar'), 'q' => ''])

<div class="pointer-events-none absolute left-0 right-0 top-4 md:top-6 z-30 flex justify-center px-4">
  <form x-ref="form" method="GET" action="{{ $action }}"
        class="pointer-events-auto w-full max-w-md glass rounded-full px-3 py-2 flex items-center gap-2">
    <svg aria-hidden="true" class="w-5 h-5 text-slate-500" viewBox="0 0 24 24" fill="none">
      <path d="M21 21l-3.8-3.8m.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <input name="q" value="{{ $q }}" autocomplete="off" placeholder="Pesquise..."
           class="flex-1 bg-transparent outline-none text-slate-800 placeholder:text-slate-400"
           aria-label="Pesquisar no mapa">
    @if($q !== '')
      <button type="button" onclick="this.form.querySelector('[name=q]').value=''; this.form.submit();"
              class="shrink-0 rounded-full px-2 py-1 text-xs text-slate-600 bg-slate-200/80 hover:bg-slate-300">
        limpar
      </button>
    @endif
    <button type="submit" class="shrink-0 rounded-full px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm">
      Buscar
    </button>
  </form>
</div>
