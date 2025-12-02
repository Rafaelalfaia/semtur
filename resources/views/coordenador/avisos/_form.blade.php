@php
  // Garante um objeto "vazio" quando $aviso não foi passado
  $aviso = $aviso ?? new \App\Models\Conteudo\Aviso();
  $statuses = ['publicado'=>'Publicado','rascunho'=>'Rascunho','arquivado'=>'Arquivado'];
@endphp

{{-- Card principal --}}
<div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 sm:p-5 space-y-5">

  {{-- Título --}}
  <div class="space-y-1">
    <label class="text-sm font-medium text-slate-200">Título *</label>
    <input type="text" name="titulo"
           value="{{ old('titulo', data_get($aviso,'titulo','')) }}"
           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-400
                  focus:outline-none focus:ring-2 focus:ring-emerald-600" required>
    @error('titulo')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
  </div>

  {{-- Descrição --}}
  <div class="space-y-1">
    <label class="text-sm font-medium text-slate-200">Descrição *</label>
    <textarea name="descricao" rows="6"
              class="w-full resize-y rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100
                     placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600"
              required>{{ old('descricao', data_get($aviso,'descricao','')) }}</textarea>
    @error('descricao')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
  </div>

  {{-- Linha: WhatsApp + Status --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="space-y-1 md:col-span-2">
      <label class="text-sm font-medium text-slate-200">WhatsApp (apenas números, com DDI/DDD)</label>
      <input type="text" name="whatsapp" placeholder="5593999998888"
             value="{{ old('whatsapp', data_get($aviso,'whatsapp','')) }}"
             class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 placeholder:text-slate-400
                    focus:outline-none focus:ring-2 focus:ring-emerald-600">
      @error('whatsapp')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
      <p class="text-xs text-slate-400">Será usado no botão “Falar no WhatsApp”.</p>
    </div>

    <div class="space-y-1">
      <label class="text-sm font-medium text-slate-200">Status *</label>
      <select name="status"
              class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100
                     focus:outline-none focus:ring-2 focus:ring-emerald-600" required>
        @foreach($statuses as $k=>$v)
          <option value="{{ $k }}"
                  @selected(old('status', data_get($aviso,'status','publicado')) === $k)
                  class="bg-[#0B0F0D]">
            {{ $v }}
          </option>
        @endforeach
      </select>
      @error('status')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
    </div>
  </div>

  {{-- Linha: Janela de exibição --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="space-y-1">
      <label class="text-sm font-medium text-slate-200">Início da exibição</label>
      <input type="datetime-local" name="inicio_em"
             value="{{ old('inicio_em', optional(data_get($aviso,'inicio_em'))->format('Y-m-d\TH:i')) }}"
             class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100
                    focus:outline-none focus:ring-2 focus:ring-emerald-600">
      @error('inicio_em')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
    </div>
    <div class="space-y-1">
      <label class="text-sm font-medium text-slate-200">Fim da exibição</label>
      <input type="datetime-local" name="fim_em"
             value="{{ old('fim_em', optional(data_get($aviso,'fim_em'))->format('Y-m-d\TH:i')) }}"
             class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100
                    focus:outline-none focus:ring-2 focus:ring-emerald-600">
      @error('fim_em')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
    </div>
  </div>

  {{-- Imagem --}}
  <div class="space-y-2">
    <label class="text-sm font-medium text-slate-200">Imagem (opcional)</label>
    <input type="file" name="imagem" accept="image/*"
           class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-slate-100 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:text-white
                  hover:file:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600">
    @error('imagem')<p class="text-sm text-red-300">{{ $message }}</p>@enderror

    @if(($aviso->exists ?? false) && !empty($aviso->imagem_path))
      <div class="mt-2 flex items-center gap-3">
        <img src="{{ Storage::url($aviso->imagem_path) }}" alt="" class="h-24 w-40 rounded-lg border border-white/10 object-cover">
        <form action="{{ route('coordenador.avisos.imagem.remover',$aviso) }}" method="post"
              onsubmit="return confirm('Remover imagem atual?');">
          @csrf @method('DELETE')
          <button class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100 hover:bg-white/10">
            Remover imagem
          </button>
        </form>
      </div>
    @endif
  </div>

</div>

{{-- Ações --}}
<div class="pt-5 flex items-center justify-end gap-3">
  <a href="{{ route('coordenador.avisos.index') }}"
     class="rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-slate-100 hover:bg-white/10">
    Cancelar
  </a>
  <button type="submit"
          class="rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
    {{ ($mode ?? 'create') === 'edit' ? 'Salvar alterações' : 'Criar aviso' }}
  </button>
</div>
