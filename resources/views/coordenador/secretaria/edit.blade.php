@extends('console.layout')

@section('title','Secretaria')
@section('page.title','SEMTUR — Informações Institucionais')

@section('content')
  {{-- feedback --}}
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200">
      {{ session('ok') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200">
      <div class="font-semibold">Corrija os campos abaixo:</div>
      <ul class="mt-1 list-disc pl-5 text-sm">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.secretaria.update') }}" enctype="multipart/form-data"
        class="grid gap-6 lg:grid-cols-3">
    @csrf @method('PUT')

    {{-- Coluna A --}}
    <div class="space-y-4 lg:col-span-2">
      <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 sm:p-5">
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="block text-sm text-slate-300 mb-1">Nome</label>
            <input name="nome" value="{{ old('nome',$secretaria->nome) }}" required
                   class="w-full rounded-lg bg-white/5 px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500/50"/>
          </div>

          <div>
            <label class="block text-sm text-slate-300 mb-1">Slug (opcional)</label>
            <input name="slug" value="{{ old('slug',$secretaria->slug) }}"
                   class="w-full rounded-lg bg-white/5 px-3 py-2"/>
          </div>
          <div>
            <label class="block text-sm text-slate-300 mb-1">Status</label>
            <select name="status" class="w-full rounded-lg bg-white/5 px-3 py-2">
              @foreach(['rascunho','publicado','arquivado'] as $st)
                <option value="{{ $st }}" @selected(old('status',$secretaria->status)===$st)>{{ ucfirst($st) }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm text-slate-300 mb-1">Ordem</label>
            <input type="number" min="0" name="ordem" value="{{ old('ordem',$secretaria->ordem) }}"
                   class="w-full rounded-lg bg-white/5 px-3 py-2"/>
          </div>
        </div>

        <div class="mt-4">
          <label class="block text-sm text-slate-300 mb-1">Descrição</label>
          <textarea name="descricao" rows="6"
                    class="w-full rounded-lg bg-white/5 px-3 py-2">{{ old('descricao',$secretaria->descricao) }}</textarea>
        </div>
      </div>

      {{-- Redes --}}
      <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 sm:p-5">
        <div class="grid gap-4 sm:grid-cols-2">
          @php $redes = old('redes',$secretaria->redes ?? []); @endphp
          <div>
            <label class="block text-sm text-slate-300 mb-1">Instagram</label>
            <input name="redes[instagram]" value="{{ $redes['instagram'] ?? '' }}" class="w-full rounded-lg bg-white/5 px-3 py-2"/>
          </div>
          <div>
            <label class="block text-sm text-slate-300 mb-1">Facebook</label>
            <input name="redes[facebook]" value="{{ $redes['facebook'] ?? '' }}" class="w-full rounded-lg bg-white/5 px-3 py-2"/>
          </div>
          <div>
            <label class="block text-sm text-slate-300 mb-1">LinkedIn</label>
            <input name="redes[linkedin]" value="{{ $redes['linkedin'] ?? '' }}" class="w-full rounded-lg bg-white/5 px-3 py-2"/>
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

      {{-- Localização (somente URL do mapa, sem lat/lng) --}}
    <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 sm:p-5">
    <label class="block text-sm text-slate-300 mb-1">Link do mapa (opcional)</label>
    <input name="maps_url" value="{{ old('maps_url',$secretaria->maps_url) }}"
            placeholder="Cole aqui a URL do Google Maps/Bing Maps"
            class="w-full rounded-lg bg-white/5 px-3 py-2"/>
    <p class="mt-2 text-xs text-slate-400">
        Dica: pode ser um link do Google Maps ou Bing Maps. Não salvamos coordenadas; apenas o link.
    </p>
    </div>


    {{-- Coluna B --}}
    <div class="space-y-4">
      <div class="rounded-xl border border-white/10 bg-[#0F1412] p-4 sm:p-5">
        <div>
          <label class="block text-sm text-slate-300 mb-1">Foto (logo)</label>
          <input type="file" name="foto" accept="image/*" class="w-full rounded-lg bg-white/5 px-3 py-2 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:text-white"/>
          @if($secretaria->foto_url)
            <img src="{{ $secretaria->foto_url }}" class="mt-3 h-24 rounded-lg object-contain bg-white/5 p-2" alt="">
          @endif
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-300 mb-1">Foto de capa (opcional)</label>
          <input type="file" name="foto_capa" accept="image/*" class="w-full rounded-lg bg-white/5 px-3 py-2 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:text-white"/>
          @if($secretaria->foto_capa_url)
            <img src="{{ $secretaria->foto_capa_url }}" class="mt-3 h-24 rounded-lg object-cover" alt="">
          @endif
        </div>
      </div>

      <div class="flex gap-3">
        <button class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 font-medium hover:bg-emerald-500">
          Salvar
        </button>
        <a href="{{ url()->previous() }}" class="inline-flex items-center rounded-lg bg-white/10 px-4 py-2 hover:bg-white/20">
          Voltar
        </a>
      </div>
    </div>
  </form>
@endsection
