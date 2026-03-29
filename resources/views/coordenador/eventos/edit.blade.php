@extends('console.layout')
@section('title', 'Editar evento')
@section('page.title', 'Editar evento')
@section('topbar.description', 'Atualize o evento principal mantendo o shell global e a heranca de light/dark do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.eventos.index') }}" class="ui-console-topbar-tab">Eventos</a>
  <span class="ui-console-topbar-tab is-active">Editar evento</span>
@endsection

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp

<div class="ui-console-page">
  <x-dashboard.page-header
    title="Editar evento"
    subtitle="Atualize dados, imagens e status editorial do evento sem sair do fluxo padrão do console."
  >
    <div class="flex flex-wrap gap-2">
      <a class="ui-btn-secondary" href="{{ route('coordenador.eventos.edicoes.index', $evento) }}">Gerenciar edições</a>
      <a class="ui-btn-secondary" href="{{ route('coordenador.eventos.index') }}">Voltar</a>
    </div>
  </x-dashboard.page-header>

  @if(session('ok'))
    <div class="ui-alert ui-alert-success mt-5">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="ui-alert ui-alert-danger mt-5">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.eventos.update', $evento) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    <x-dashboard.section-card title="Dados principais" subtitle="Edite as informacoes institucionais do evento" class="ui-coord-dashboard-panel">
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="ui-form-label">Nome *</label>
          <input name="nome" value="{{ old('nome', $evento->nome) }}" class="ui-form-control" required>
        </div>
        <div>
          <label class="ui-form-label">Slug</label>
          <input name="slug" value="{{ old('slug', $evento->slug) }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Cidade</label>
          <input name="cidade" value="{{ old('cidade', $evento->cidade) }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Regiao</label>
          <input name="regiao" value="{{ old('regiao', $evento->regiao) }}" class="ui-form-control">
        </div>
        <div class="md:col-span-2">
          <label class="ui-form-label">Descricao</label>
          <textarea name="descricao" rows="5" class="ui-form-control">{{ old('descricao', $evento->descricao) }}</textarea>
        </div>
      </div>
    </x-dashboard.section-card>

    <x-dashboard.section-card title="Midia e status" subtitle="Atualize imagens e controle o estado editorial" class="ui-coord-dashboard-panel">
      <div class="grid gap-4 lg:grid-cols-2">
        <div class="space-y-4">
          <div>
            <label class="ui-form-label">Capa</label>
            @if($evento->capa_path)
              <div class="ui-event-preview-card mb-3">
                <img src="{{ Storage::disk('public')->url($evento->capa_path) }}" class="ui-event-preview-image ui-event-preview-image-wide" alt="">
              </div>
              <label class="inline-flex items-center gap-2 text-sm text-[var(--ui-text-soft)]">
                <input type="checkbox" name="remove_capa" value="1">
                Remover capa
              </label>
            @endif
            <input type="file" name="capa" accept="image/*" class="ui-form-control mt-2">
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <label class="ui-form-label">Perfil</label>
            @if($evento->perfil_path)
              <div class="ui-event-preview-card mb-3">
                <img src="{{ Storage::disk('public')->url($evento->perfil_path) }}" class="ui-event-preview-image ui-event-preview-image-square" alt="">
              </div>
              <label class="inline-flex items-center gap-2 text-sm text-[var(--ui-text-soft)]">
                <input type="checkbox" name="remove_perfil" value="1">
                Remover perfil
              </label>
            @endif
            <input type="file" name="perfil" accept="image/*" class="ui-form-control mt-2">
          </div>

          <div>
            <label class="ui-form-label">Status</label>
            <select name="status" class="ui-form-select">
              @foreach(['publicado','rascunho','arquivado'] as $st)
                <option value="{{ $st }}" @selected(old('status', $evento->status) === $st)>{{ ucfirst($st) }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </x-dashboard.section-card>

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button class="ui-btn-primary">Salvar</button>
      <a href="{{ route('coordenador.eventos.index') }}" class="ui-btn-secondary">Voltar</a>
    </div>
  </form>
</div>
@endsection
