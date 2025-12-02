@extends('console.layout')

@section('title','Editar: '.$empresa->nome)
@section('page.title','Editar empresa')

@section('content')

@if ($errors->any())
  <div class="mb-4 rounded-lg border border-rose-500/40 bg-rose-900/20 p-3 text-rose-100">
    <strong>Ops!</strong> Corrija os campos abaixo.
    <ul class="mt-2 mb-0 list-disc pl-5">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

{{-- ===== Barra superior: status + ações rápidas ===== --}}
<div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
  <div class="flex items-center gap-2">
    @php $st = $empresa->status ?? null; @endphp
    @if($st === 'publicado')
      <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-900/50 text-emerald-300 border border-emerald-700/40">
        Publicado
      </span>
    @elseif($st === 'arquivado')
      <span class="px-2 py-0.5 text-xs rounded-full bg-amber-900/30 text-amber-200 border border-amber-700/40">
        Arquivado
      </span>
    @else
      <span class="px-2 py-0.5 text-xs rounded-full bg-slate-700 text-slate-300 border border-slate-600">
        Rascunho
      </span>
    @endif>
  </div>

  <div class="flex flex-wrap gap-2">
    @if(Route::has('site.empresa'))
      <a href="{{ route('site.empresa', $empresa->slug ?? $empresa->id) }}" target="_blank" rel="noopener"
         class="px-3 py-2 rounded-lg bg-white/5 text-slate-200 hover:bg-white/10">Ver página</a>
    @endif
    <a href="{{ route('coordenador.empresas.index') }}"
       class="px-3 py-2 rounded-lg bg-white/5 text-slate-200 hover:bg-white/10">Voltar</a>
  </div>
</div>

{{-- ===== Painel de mídias (Capa / Perfil) ===== --}}
<div class="mb-6 grid gap-4 md:grid-cols-2">
  {{-- CAPA --}}
  <div class="rounded-2xl overflow-hidden border border-white/10 bg-slate-900/40">
    <div class="p-3 flex items-center justify-between">
      <div class="font-medium text-slate-100">Capa</div>
      @if(!empty($empresa->capa_url))
        <form method="POST" action="{{ route('coordenador.empresas.capa.remover', $empresa) }}"
              onsubmit="return confirm('Remover capa?');">
          @csrf @method('DELETE')
          <button class="text-rose-200 hover:underline text-sm">Remover</button>
        </form>
      @endif
    </div>
    <div class="h-40 bg-slate-800">
      @if(!empty($empresa->capa_url))
        <img src="{{ $empresa->capa_url }}" alt="Capa de {{ $empresa->nome }}" class="w-full h-full object-cover">
      @else
        <div class="h-full w-full grid place-items-center text-slate-400 text-sm">Sem capa</div>
      @endif
    </div>
    <div class="p-3 text-xs text-slate-400">Sugestão: 1600×600px (JPG/WEBP)</div>
  </div>

  {{-- PERFIL / LOGO --}}
  <div class="rounded-2xl overflow-hidden border border-white/10 bg-slate-900/40">
    <div class="p-3 flex items-center justify-between">
      <div class="font-medium text-slate-100">Perfil / Logo</div>
      @if(!empty($empresa->perfil_url))
        <form method="POST" action="{{ route('coordenador.empresas.perfil.remover', $empresa) }}"
              onsubmit="return confirm('Remover perfil/logo?');">
          @csrf @method('DELETE')
          <button class="text-rose-200 hover:underline text-sm">Remover</button>
        </form>
      @endif
    </div>
    <div class="h-40 bg-slate-800 grid place-items-center">
      @if(!empty($empresa->perfil_url))
        <img src="{{ $empresa->perfil_url }}" alt="Perfil de {{ $empresa->nome }}"
             class="h-28 w-28 rounded-full object-cover">
      @else
        <div class="h-28 w-28 rounded-full bg-slate-700 grid place-items-center text-slate-400 text-sm">
          Sem perfil
        </div>
      @endif
    </div>
    <div class="p-3 text-xs text-slate-400">Sugestão: 512×512px (PNG/JPG/WEBP)</div>
  </div>
</div>

{{-- ===== FORM ===== --}}
<form method="POST" action="{{ route('coordenador.empresas.update', $empresa) }}" enctype="multipart/form-data" class="space-y-6">
  @csrf @method('PUT')

  {{-- Se você já tem um partial com todos os campos, mantém; senão, dá pra usar os campos mínimos abaixo --}}
  @includeIf('coordenador.empresas._form', [
    'empresa'      => $empresa,
    'categorias'   => $categorias ?? collect(),
    'selecionadas' => $selecionadas ?? []
  ])

  {{-- Campos mínimos (fallback) --}}
  @unless (View::exists('coordenador.empresas._form'))
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-sm text-slate-300 mb-1">Nome *</label>
        <input type="text" name="nome" value="{{ old('nome', $empresa->nome) }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
      </div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $empresa->slug) }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm text-slate-300 mb-1">Descrição</label>
        <textarea name="descricao" rows="5"
          class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">{{ old('descricao', $empresa->descricao) }}</textarea>
      </div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">Cidade</label>
        <input type="text" name="cidade" value="{{ old('cidade', $empresa->cidade) }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
      </div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">Google Maps (URL)</label>
        <input type="url" name="maps_url" value="{{ old('maps_url', $empresa->maps_url) }}"
               class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100">
      </div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">Capa (arquivo)</label>
        <input type="file" name="capa" accept=".jpg,.jpeg,.png,.webp"
               class="block w-full text-slate-200">
      </div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">Perfil / Logo (arquivo)</label>
        <input type="file" name="perfil" accept=".jpg,.jpeg,.png,.webp"
               class="block w-full text-slate-200">
      </div>
      <div>
        <label class="block text-sm text-slate-300 mb-1">Status *</label>
        <select name="status" class="w-full rounded-lg bg-white/5 border border-white/10 px-3 py-2 text-slate-100" required>
          @php $cur = old('status', $empresa->status); @endphp
          <option value="rascunho"  @selected($cur==='rascunho')>Rascunho</option>
          <option value="publicado" @selected($cur==='publicado')>Publicado</option>
          <option value="arquivado" @selected($cur==='arquivado')>Arquivado</option>
        </select>
      </div>
    </div>
  @endunless

  {{-- Rodapé do formulário --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex flex-wrap gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">
        Salvar alterações
      </button>
      <a href="{{ route('coordenador.empresas.index') }}"
         class="px-4 py-2 rounded-lg bg-white/5 text-slate-200 hover:bg-white/10">Cancelar</a>
    </div>

    {{-- Ações rápidas de status --}}
    <div class="flex flex-wrap gap-2">
      <form method="POST" action="{{ route('coordenador.empresas.rascunho', $empresa) }}">
        @csrf @method('PATCH')
        <button class="px-3 py-2 rounded bg-white/10 hover:bg-white/20 text-sm">Marcar rascunho</button>
      </form>
      <form method="POST" action="{{ route('coordenador.empresas.publicar', $empresa) }}">
        @csrf @method('PATCH')
        <button class="px-3 py-2 rounded bg-white/10 hover:bg-white/20 text-sm">Publicar</button>
      </form>
      <form method="POST" action="{{ route('coordenador.empresas.arquivar', $empresa) }}">
        @csrf @method('PATCH')
        <button class="px-3 py-2 rounded bg-white/10 hover:bg-white/20 text-sm">Arquivar</button>
      </form>
    </div>
  </div>
</form>
@endsection
