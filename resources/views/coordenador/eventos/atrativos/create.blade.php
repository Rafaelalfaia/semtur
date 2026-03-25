@extends('console.layout')
@section('title', 'Novo atrativo')
@section('page.title', 'Novo atrativo')
@section('topbar.description', 'Cadastre um atrativo da edicao dentro do mesmo padrao visual e estrutural do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.eventos.index') }}" class="ui-console-topbar-tab">Eventos</a>
  <a href="{{ route('coordenador.eventos.edicoes.index', $edicao->evento) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.edicoes.atrativos.index', $edicao) }}" class="ui-console-topbar-tab">Atrativos</a>
  <span class="ui-console-topbar-tab is-active">Novo atrativo</span>
@endsection

@section('content')
<div class="ui-console-page">
  <x-dashboard.page-header
    title="Novo atrativo"
    subtitle="Cadastre nome, ordem, descricao, thumb e status do atrativo."
  >
    <a href="{{ route('coordenador.edicoes.atrativos.index', $edicao) }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  @if($errors->any())
    <div class="ui-alert ui-alert-danger mt-5">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.edicoes.atrativos.store', $edicao) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    <x-dashboard.section-card title="Dados do atrativo" subtitle="Preencha a estrutura basica do atrativo da edicao" class="ui-coord-dashboard-panel">
      <div class="space-y-4">
        <div>
          <label class="ui-form-label">Nome *</label>
          <input name="nome" value="{{ old('nome') }}" class="ui-form-control" required>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="ui-form-label">Slug (opcional)</label>
            <input name="slug" value="{{ old('slug') }}" class="ui-form-control">
          </div>
          <div>
            <label class="ui-form-label">Ordem</label>
            <input type="number" name="ordem" value="{{ old('ordem', $atrativo->ordem ?? 1) }}" class="ui-form-control" min="1">
          </div>
        </div>

        <div>
          <label class="ui-form-label">Descricao</label>
          <textarea name="descricao" rows="4" class="ui-form-control">{{ old('descricao') }}</textarea>
        </div>

        <div>
          <label class="ui-form-label">Thumb (imagem)</label>
          <input type="file" name="thumb" accept="image/*" class="ui-form-control">
          <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Recomendado: 800x600 ou proporcao semelhante.</p>
        </div>

        <div class="max-w-[240px]">
          <label class="ui-form-label">Status</label>
          <select name="status" class="ui-form-select">
            @foreach(['publicado','rascunho','arquivado'] as $st)
              <option value="{{ $st }}" @selected(old('status') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </x-dashboard.section-card>

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button class="ui-btn-primary">Salvar</button>
      <a href="{{ route('coordenador.edicoes.atrativos.index', $edicao) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
