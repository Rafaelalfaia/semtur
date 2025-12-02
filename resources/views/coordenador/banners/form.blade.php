@extends('console.layout')

@section('title', ($banner->exists ? 'Editar' : 'Novo').' banner — Console')
@section('page.title', $banner->exists ? 'Editar banner' : 'Novo banner')

@section('content')
  <div class="max-w-4xl mx-auto space-y-6">

    @if($errors->any())
      <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2 text-red-300">
        <ul class="list-disc list-inside">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    {{-- CARD DO FORM --}}
    <div class="rounded-xl border border-white/5 bg-[#0F1412] p-5">
      <form method="post" enctype="multipart/form-data"
            action="{{ $banner->exists ? route('coordenador.banners.update',$banner) : route('coordenador.banners.store') }}"
            class="space-y-5">
        @csrf
        @if($banner->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm text-slate-300">Título</label>
            <input name="titulo" value="{{ old('titulo',$banner->titulo) }}"
                   class="mt-1 w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                   placeholder="Jogos Indígenas">
          </div>
          <div>
            <label class="block text-sm text-slate-300">Ordem</label>
            <input type="number" name="ordem" min="0" value="{{ old('ordem',$banner->ordem ?? 0) }}"
                   class="mt-1 w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600">
          </div>
        </div>

        <div>
          <label class="block text-sm text-slate-300">Subtítulo (opcional)</label>
          <input name="subtitulo" value="{{ old('subtitulo',$banner->subtitulo) }}"
                 class="mt-1 w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                 placeholder="Festival anual...">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-slate-300">Texto do botão (CTA)</label>
            <input name="cta_label" value="{{ old('cta_label',$banner->cta_label) }}"
                   class="mt-1 w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                   placeholder="Saiba mais">
          </div>
          <div>
            <label class="block text-sm text-slate-300">URL do botão</label>
            <input name="cta_url" value="{{ old('cta_url',$banner->cta_url) }}"
                   class="mt-1 w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                   placeholder="https://…">
          </div>
        </div>

        @php
          $bx = max(0, min(100, (float) old('pos_banner_x', data_get($banner, 'pos_banner_x', 50))));
          $by = max(0, min(100, (float) old('pos_banner_y', data_get($banner, 'pos_banner_y', 50))));
        @endphp

        {{-- IMAGEM + AJUSTE DE FOCO --}}
        <div class="grid grid-cols-1 md:grid-cols-[1fr_360px] gap-4 items-start">
          <div>
            <label class="block text-sm text-slate-300">Imagem (recomendado 3:1, ex. 1800×600)</label>
            <input id="imagem" type="file" name="imagem" accept=".jpg,.jpeg,.png,.webp"
                   class="mt-1 w-full rounded-md border border-white/10 bg-white/5 file:mr-4 file:rounded-md file:border-0 file:bg-white/10 file:px-3 file:py-2 file:text-slate-100 hover:file:bg-white/20">
            <p class="mt-1 text-xs text-slate-400">Arquivos até 4MB.</p>
          </div>

          <section class="rounded-xl border border-white/10 bg-white/5 p-3">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm text-slate-200">Prévia com foco</h3>
              <span class="text-[11px] text-slate-400">Arraste para ajustar • 2× clique para centralizar</span>
            </div>

            {{-- Posição (0–100) — enviados no submit --}}
            <input type="hidden" name="pos_banner_x" id="pos_banner_x" value="{{ $bx }}">
            <input type="hidden" name="pos_banner_y" id="pos_banner_y" value="{{ $by }}">
            {{-- Campo combinado no formato aceito pelo backend: "x,y" (sem %) --}}
            <input type="hidden" name="pos_banner" id="pos_banner" value="{{ $bx }},{{ $by }}">

            <div id="preview-banner" class="mt-2 w-full rounded-lg bg-white/10 ring-1 ring-white/10 overflow-hidden"
                 style="aspect-ratio: 3 / 1;">
              <img id="preview-banner-img"
                   src="{{ ($banner->exists && $banner->imagem_url && !old('imagem')) ? $banner->imagem_url : '' }}"
                   data-src-processed="{{ $banner->imagem_url ?? '' }}"
                   data-src-original="{{ $banner->imagem_original_url ?? ($banner->imagem_url ?? '') }}"
                   alt="Prévia do banner"
                   style="object-position: {{ $bx }}% {{ $by }}%;"
                   class="w-full h-full object-cover {{ ($banner->exists && $banner->imagem_url && !old('imagem')) ? '' : 'hidden' }}">
            </div>
          </section>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-slate-300">Status</label>
            <select name="status"
                    class="mt-1 w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600">
              @foreach(['rascunho'=>'Rascunho','publicado'=>'Publicado','arquivado'=>'Arquivado'] as $k=>$v)
                <option value="{{ $k }}" @selected(old('status',$banner->status)===$k)>{{ $v }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm text-slate-300">Publicado em</label>
            <input value="{{ optional($banner->published_at)->format('d/m/Y H:i') }}" disabled
                   class="mt-1 w-full rounded-md border border-white/10 bg-white/5 px-3 py-2 text-slate-400">
          </div>
        </div>

        <div class="flex items-center justify-between pt-2">
          <a href="{{ route('coordenador.banners.index') }}" class="text-slate-300 hover:text-slate-100 text-sm">← Voltar</a>
          <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">
            {{ $banner->exists ? 'Salvar alterações' : 'Criar banner' }}
          </button>
        </div>
      </form>
    </div>

    @if($banner->exists)
      {{-- Form de exclusão fora do form principal (evita nesting) --}}
      <form action="{{ route('coordenador.banners.destroy',$banner) }}" method="post"
            onsubmit="return confirm('Remover este banner?')" class="text-right">
        @csrf @method('DELETE')
        <button class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-sm text-red-300 hover:bg-red-500/20">
          Excluir
        </button>
      </form>
    @endif
  </div>
@endsection

@push('scripts')
  @vite('resources/js/simple-previews.js')
@endpush
