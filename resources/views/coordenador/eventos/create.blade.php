@extends('console.layout')
@section('title', 'Novo evento')
@section('page.title', 'Novo evento')
@section('topbar.description', 'Cadastre um novo evento mantendo o padrao estrutural do console e a heranca global de modo.')

@section('topbar.nav')
  <a href="{{ route('coordenador.eventos.index') }}" class="ui-console-topbar-tab">Eventos</a>
  <span class="ui-console-topbar-tab is-active">Novo evento</span>
@endsection

@section('content')
<div class="ui-console-page">
  <x-dashboard.page-header
    title="Novo evento"
    subtitle="Cadastre a base institucional do evento, com capa, perfil, localizacao e status editorial."
  >
    <a href="{{ route('coordenador.eventos.index') }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  @if($errors->any())
    <div class="ui-alert ui-alert-danger mt-5">
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.eventos.store') }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    <x-dashboard.section-card title="Dados principais" subtitle="Preencha as informacoes base do evento" class="ui-coord-dashboard-panel">
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="ui-form-label">Nome *</label>
          <input name="nome" value="{{ old('nome') }}" class="ui-form-control" required>
        </div>
        <div>
          <label class="ui-form-label">Slug (opcional)</label>
          <input name="slug" value="{{ old('slug') }}" placeholder="gerado do nome" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Cidade</label>
          <input name="cidade" value="{{ old('cidade') }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Regiao</label>
          <input name="regiao" value="{{ old('regiao') }}" class="ui-form-control">
        </div>
        <div class="md:col-span-2">
          <label class="ui-form-label">Descricao</label>
          <textarea name="descricao" rows="5" class="ui-form-control">{{ old('descricao') }}</textarea>
        </div>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Midia e status" subtitle="Controle a apresentacao visual e o estado editorial" class="ui-coord-dashboard-panel">
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="ui-form-label">Capa (1920x700 aprox.)</label>
          <input type="file" name="capa" accept="image/*" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Perfil (quadrada)</label>
          <input type="file" name="perfil" accept="image/*" class="ui-form-control">
        </div>
        <div>
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
      <a href="{{ route('coordenador.eventos.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
