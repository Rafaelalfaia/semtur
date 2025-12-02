<section class="relative overflow-hidden">
  <div class="mx-auto max-w-7xl px-4 py-12 md:py-20">
    <div class="grid md:grid-cols-12 items-center gap-8">
      <div class="md:col-span-7 space-y-4">
        <h1 class="text-3xl md:text-5xl font-bold leading-tight">
          Descubra o melhor de <span class="text-emerald-600">Altamira</span>
        </h1>
        <p class="text-slate-600 dark:text-slate-300 max-w-2xl">
          Pontos turísticos, hotéis e experiências únicas — tudo em um só lugar.
        </p>
        <div class="flex items-center gap-3">
          <a href="{{ route('site.explorar') }}"
             class="inline-flex items-center rounded-xl px-5 py-3 bg-emerald-600 text-white hover:bg-emerald-700 shadow">
            Explorar agora
          </a>
          <a href="{{ route('site.mapa') }}"
             class="inline-flex items-center rounded-xl px-5 py-3 border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
            Ver no mapa
          </a>
        </div>
      </div>
      <div class="md:col-span-5">
        <div class="aspect-[4/3] rounded-2xl bg-cover bg-center shadow-lg"
             style="background-image:url('/images/hero-semtur.jpg')"></div>
      </div>
    </div>
  </div>
</section>
