@props(['aviso'])

@if($aviso)
<div
  x-data="avisoPopup('{{ $aviso->dismiss_key }}')"
  x-show="open"
  x-cloak
  class="fixed inset-0 z-[9999] flex items-center justify-center"
  aria-labelledby="aviso-title"
  role="dialog" aria-modal="true"
>
  <!-- backdrop -->
  <div class="absolute inset-0 bg-black/60" @click="fechar()" aria-hidden="true"></div>

  <!-- modal -->
  <div class="relative mx-4 w-full max-w-md rounded-2xl bg-white text-slate-800 shadow-2xl">
    @if($aviso->imagem_url)
      <img src="{{ $aviso->imagem_url }}" alt="" class="w-full h-40 object-cover rounded-t-2xl">
    @endif

    <div class="p-5 space-y-3">
      <h2 id="aviso-title" class="text-lg font-semibold">{{ $aviso->titulo }}</h2>

      <p class="text-sm leading-relaxed text-slate-700">
        {!! nl2br(e($aviso->descricao)) !!}
      </p>

      <div class="flex gap-3 pt-2">
        @if($aviso->whatsapp_link)
          <a href="{{ $aviso->whatsapp_link }}" target="_blank" rel="noopener"
             class="inline-flex items-center justify-center rounded-lg border px-3 py-2 text-sm font-medium
                    border-emerald-600 hover:bg-emerald-50">
            Falar no WhatsApp
          </a>
        @endif
        <button type="button" @click="fechar()"
          class="ml-auto inline-flex items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
          Fechar
        </button>
      </div>
    </div>

    <!-- botão X -->
    <button @click="fechar()" class="absolute -right-2 -top-2 grid h-9 w-9 place-items-center rounded-full bg-white shadow">
      <span class="sr-only">Fechar</span>
      ✕
    </button>
  </div>
</div>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('avisoPopup', (key) => ({
      open: false,
      init(){ this.open = !localStorage.getItem(key); },
      fechar(){ localStorage.setItem(key, '1'); this.open = false; }
    }))
  })
</script>
@endif
