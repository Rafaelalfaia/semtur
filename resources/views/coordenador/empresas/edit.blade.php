@extends('console.layout')

@section('title', 'Editar: '.$empresa->nome)
@section('page.title', 'Editar empresa')
@section('topbar.description', 'Atualize dados, midias e status editoriais da empresa mantendo compatibilidade total com o shell do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.empresas.index') }}" class="ui-console-topbar-tab">Empresas</a>
  <span class="ui-console-topbar-tab is-active">Editar</span>
@endsection

@section('content')
<div class="ui-console-page">
  @if ($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
      <strong>Ops!</strong> Corrija os campos abaixo.
      <ul class="mt-2 mb-0 list-disc pl-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <x-dashboard.page-header
    title="Editar empresa"
    subtitle="Painel editorial com status, midias e dados institucionais em um padrao consistente com o console."
  >
    <x-slot:actions>
      @if(Route::has('site.empresa'))
        <a href="{{ route('site.empresa', $empresa->slug ?? $empresa->id) }}" target="_blank" rel="noopener" class="ui-btn-secondary">
          Ver página
        </a>
      @endif
      <a href="{{ route('coordenador.empresas.index') }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <div class="mt-5 flex items-center gap-2">
    @php $st = $empresa->status ?? null; @endphp
    @if($st === 'publicado')
      <span class="ui-badge ui-badge-success">Publicado</span>
    @elseif($st === 'arquivado')
      <span class="ui-badge ui-badge-warning">Arquivado</span>
    @else
      <span class="ui-badge ui-badge-neutral">Rascunho</span>
    @endif
  </div>

  <div class="ui-coord-edit-media-grid mt-5">
    <x-dashboard.section-card title="Capa" subtitle="Imagem principal da apresentacao" class="ui-coord-dashboard-panel">
      <div class="ui-coord-media-stage">
        @if(!empty($empresa->capa_url))
          <img src="{{ $empresa->capa_url }}" alt="Capa de {{ $empresa->nome }}" class="h-full w-full object-cover">
        @else
          <div class="ui-coord-media-empty">Sem capa</div>
        @endif
      </div>

      <div class="mt-3 flex items-center justify-between gap-3">
        <span class="text-xs text-[var(--ui-text-soft)]">Sugestao: 1600x600px (JPG/WEBP)</span>
        @if(!empty($empresa->capa_url))
          <form method="POST" action="{{ route('coordenador.empresas.capa.remover', $empresa) }}" onsubmit="return confirm('Remover capa?');">
            @csrf @method('DELETE')
            <button class="ui-btn-danger !min-h-0 px-3 py-2 text-sm">Remover</button>
          </form>
        @endif
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Perfil / logo" subtitle="Imagem de identificacao" class="ui-coord-dashboard-panel">
      <div class="ui-coord-media-stage ui-coord-media-stage--avatar">
        @if(!empty($empresa->perfil_url))
          <img src="{{ $empresa->perfil_url }}" alt="Perfil de {{ $empresa->nome }}" class="h-28 w-28 rounded-full object-cover">
        @else
          <div class="ui-coord-media-empty ui-coord-media-empty--avatar">Sem perfil</div>
        @endif
      </div>

      <div class="mt-3 flex items-center justify-between gap-3">
        <span class="text-xs text-[var(--ui-text-soft)]">Sugestao: 512x512px (PNG/JPG/WEBP)</span>
        @if(!empty($empresa->perfil_url))
          <form method="POST" action="{{ route('coordenador.empresas.perfil.remover', $empresa) }}" onsubmit="return confirm('Remover perfil/logo?');">
            @csrf @method('DELETE')
            <button class="ui-btn-danger !min-h-0 px-3 py-2 text-sm">Remover</button>
          </form>
        @endif
      </div>
    </x-dashboard.section-card>
  </div>

  <form method="POST" action="{{ route('coordenador.empresas.update', $empresa) }}" enctype="multipart/form-data" class="mt-5 space-y-5">
    @csrf
    @method('PUT')

    <x-dashboard.section-card title="Dados da empresa" subtitle="Conteúdo principal e informações de contato" class="ui-coord-dashboard-panel">
      @includeIf('coordenador.empresas._form', [
        'empresa'      => $empresa,
        'categorias'   => $categorias ?? collect(),
        'selecionadas' => $selecionadas ?? []
      ])
    </x-dashboard.section-card>

    @unless (View::exists('coordenador.empresas._form'))
      <x-dashboard.section-card title="Dados básicos" subtitle="Fallback visual para campos essenciais" class="ui-coord-dashboard-panel">
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="ui-form-label">Nome *</label>
            <input type="text" name="nome" value="{{ old('nome', $empresa->nome) }}" class="ui-form-control" required>
          </div>
          <div>
            <label class="ui-form-label">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $empresa->slug) }}" class="ui-form-control">
          </div>
          <div class="sm:col-span-2">
            <label class="ui-form-label">Descrição</label>
            <textarea name="descricao" rows="5" class="ui-form-control">{{ old('descricao', $empresa->descricao) }}</textarea>
          </div>
          <div>
            <label class="ui-form-label">Cidade</label>
            <input type="text" name="cidade" value="{{ old('cidade', $empresa->cidade) }}" class="ui-form-control">
          </div>
          <div>
            <label class="ui-form-label">Google Maps (URL)</label>
            <input type="url" name="maps_url" value="{{ old('maps_url', $empresa->maps_url) }}" class="ui-form-control">
          </div>
        </div>
      </x-dashboard.section-card>
    @endunless

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-wrap gap-2">
        <button class="ui-btn-primary">Salvar alterações</button>
        <a href="{{ route('coordenador.empresas.index') }}" class="ui-btn-secondary">Cancelar</a>
      </div>

      <div class="ui-coord-inline-actions">
        <form method="POST" action="{{ route('coordenador.empresas.rascunho', $empresa) }}">
          @csrf @method('PATCH')
          <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Marcar rascunho</button>
        </form>
        <form method="POST" action="{{ route('coordenador.empresas.publicar', $empresa) }}">
          @csrf @method('PATCH')
          <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Publicar</button>
        </form>
        <form method="POST" action="{{ route('coordenador.empresas.arquivar', $empresa) }}">
          @csrf @method('PATCH')
          <button class="ui-btn-secondary !min-h-0 px-3 py-2 text-sm">Arquivar</button>
        </form>
      </div>
    </div>
  </form>
</div>
@endsection
