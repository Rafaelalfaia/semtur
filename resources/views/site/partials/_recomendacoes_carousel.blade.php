@props([
  'itens'  => collect(),
  'mkHref' => null,
  'cid'    => 'rec-'.\Illuminate\Support\Str::random(6),
  // controla a largura dos slides p/ ficar como no design antigo
  'mobile_full' => true, // true => card quase full no mobile
])

<div id="{{ $cid }}" class="relative">
  <div class="overflow-x-auto no-scrollbar scroll-smooth snap-x snap-mandatory" data-autoscroll="1">
    <div class="flex gap-3 sm:gap-4 lg:gap-6">
      @foreach($itens as $r)
        @php
          $recTitle = is_array($r) ? ($r['title'] ?? $r['nome'] ?? '') : ($r->title ?? $r->nome ?? '');
          $recSub   = is_array($r) ? ($r['subtitle'] ?? ($r['cidade'] ?? 'Altamira')) : ($r->subtitle ?? ($r->cidade ?? 'Altamira'));
          $recImg   = is_array($r) ? ($r['image'] ?? $r['capa_url'] ?? $r['foto_capa_url'] ?? null)
                                   : ($r->image ?? $r->capa_url ?? $r->foto_capa_url ?? null);
          $recHref  = $mkHref ? $mkHref($r) : '#';
        @endphp

        {{-- ATT: largura calibrada para lembrar o layout antigo --}}
        <div class="snap-start shrink-0 {{ $mobile_full ? 'w-[56%]' : 'w-[52%]' }} sm:w-[48%] md:w-[44%] lg:w-[31%]">
          <x-card-recomendacao
            :title="$recTitle"
            :subtitle="$recSub"
            :image="$recImg"
            :href="$recHref" />
        </div>
      @endforeach
    </div>
  </div>

  {{-- setas (md+) --}}
  <button type="button"
          class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 bg-black/35 text-white rounded-full p-2"
          data-prev aria-label="Anterior">
    <svg class="w-6 h-6" viewBox="0 0 24 24"><path fill="currentColor" d="m15 18l-6-6l6-6"/></svg>
  </button>
  <button type="button"
          class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 bg-black/35 text-white rounded-full p-2"
          data-next aria-label="Próximo">
    <svg class="w-6 h-6" viewBox="0 0 24 24"><path fill="currentColor" d="m9 6l6 6l-6 6"/></svg>
  </button>
</div>

<style>
  .no-scrollbar::-webkit-scrollbar{display:none}
  .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
</style>

<script>
(() => {
  const root = document.getElementById(@json($cid));
  if (!root) return;
  const scroller = root.querySelector('[data-autoscroll]');
  const slides = Array.from(root.querySelectorAll('.snap-start'));
  const prevBtn = root.querySelector('[data-prev]');
  const nextBtn = root.querySelector('[data-next]');
  if (slides.length < 2) return;

  let timer = null;
  const period = 3000; // 3s

  const visibleIndex = () => {
    const center = scroller.scrollLeft + scroller.clientWidth * 0.5;
    // pega o slide cujo centro está mais perto do centro do viewport
    let best = 0, bestDist = Infinity;
    slides.forEach((el, i) => {
      const mid = el.offsetLeft + el.offsetWidth * 0.5;
      const d = Math.abs(mid - center);
      if (d < bestDist) { bestDist = d; best = i; }
    });
    return best;
  };

  const goTo = (i) => {
    const n = slides.length;
    const t = slides[(i % n + n) % n];
    scroller.scrollTo({ left: t.offsetLeft, behavior: 'smooth' });
  };
  const next = () => goTo(visibleIndex()+1);
  const prev = () => goTo(visibleIndex()-1);

  const start = () => { stop(); timer = setInterval(next, period); };
  const stop  = () => { if (timer) clearInterval(timer); timer = null; };

  start();

  // pausa em interação
  ['mouseenter','touchstart','pointerdown','focusin'].forEach(ev => scroller.addEventListener(ev, stop, {passive:true}));
  ['mouseleave','touchend','pointerup','focusout'].forEach(ev => scroller.addEventListener(ev, () => setTimeout(start, 500), {passive:true}));

  prevBtn?.addEventListener('click', () => { stop(); prev(); });
  nextBtn?.addEventListener('click', () => { stop(); next(); });
})();
</script>
