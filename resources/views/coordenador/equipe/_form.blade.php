@if($errors->any())
  <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200">
    <div class="font-semibold">Corrija os campos abaixo:</div>
    <ul class="mt-1 list-disc pl-5 text-sm">
      @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

@if(session('ok'))
  <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200">
    {{ session('ok') }}
  </div>
@endif

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3">
  @csrf
  @if($method!=='POST') @method($method) @endif

  {{-- Coluna A --}}
  <div class="space-y-4 lg:col-span-2">
    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 sm:p-5">
      <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="block text-sm text-slate-300 mb-1">Nome</label>
          <input name="nome" value="{{ old('nome', optional($membro)->nome) }}" required
                 class="w-full rounded-lg bg-white/5 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/50"/>
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1">Cargo</label>
          <input name="cargo" value="{{ old('cargo', optional($membro)->cargo) }}"
                 class="w-full rounded-lg bg-white/5 px-3 py-2"/>
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1">Slug (opcional)</label>
          <input name="slug" value="{{ old('slug', optional($membro)->slug) }}"
                 class="w-full rounded-lg bg-white/5 px-3 py-2"/>
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1">Status</label>
          @php $st = old('status', optional($membro)->status ?? 'publicado'); @endphp
          <select name="status" class="w-full rounded-lg bg-white/5 px-3 py-2">
            @foreach(['rascunho','publicado','arquivado'] as $opt)
              <option value="{{ $opt }}" @selected($st===$opt)>{{ ucfirst($opt) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1">Ordem</label>
          <input type="number" min="0" name="ordem" value="{{ old('ordem', optional($membro)->ordem) }}"
                 class="w-full rounded-lg bg-white/5 px-3 py-2"/>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm text-slate-300 mb-1">Resumo (curto)</label>
          <input name="resumo" value="{{ old('resumo', optional($membro)->resumo) }}"
                 class="w-full rounded-lg bg-white/5 px-3 py-2"/>
        </div>
      </div>
    </div>

    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 sm:p-5">
      <div class="grid gap-4 sm:grid-cols-2">
        @php $redes = old('redes', optional($membro)->redes ?? []); @endphp
        <div>
          <label class="block text-sm text-slate-300 mb-1">Instagram</label>
          <input name="redes[instagram]" value="{{ $redes['instagram'] ?? '' }}" class="w-full rounded-lg bg-white/5 px-3 py-2"/>
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1">LinkedIn</label>
          <input name="redes[linkedin]" value="{{ $redes['linkedin'] ?? '' }}" class="w-full rounded-lg bg-white/5 px-3 py-2"/>
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1">Facebook</label>
          <input name="redes[facebook]" value="{{ $redes['facebook'] ?? '' }}" class="w-full rounded-lg bg-white/5 px-3 py-2"/>
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-1">Site</label>
          <input name="redes[site]" value="{{ $redes['site'] ?? '' }}" class="w-full rounded-lg bg-white/5 px-3 py-2"/>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm text-slate-300 mb-1">WhatsApp</label>
          <input name="redes[whatsapp]" value="{{ $redes['whatsapp'] ?? '' }}" class="w-full rounded-lg bg-white/5 px-3 py-2"/>
        </div>
      </div>
    </div>
  </div>

  {{-- Coluna B --}}
  <div class="space-y-4">
    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 sm:p-5">
      <label class="block text-sm text-slate-300 mb-1">Foto</label>
      <input type="file" name="foto" accept="image/*"
             class="w-full rounded-lg bg-white/5 px-3 py-2 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:text-white"/>
      @if(optional($membro)->foto_url)
        <img src="{{ $membro->foto_url }}" class="mt-3 h-28 rounded-lg object-cover" alt="">
      @endif
    </div>

    <div class="flex gap-3">
      <button class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 font-medium hover:bg-emerald-500">
        Salvar
      </button>
      <a href="{{ route('coordenador.equipe.index') }}" class="inline-flex items-center rounded-lg bg-white/10 px-4 py-2 hover:bg-white/20">
        Voltar
      </a>
    </div>
  </div>
</form>
