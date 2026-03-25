@extends('console.layout')
@section('title', 'Editar atrativo')
@section('page.title', 'Editar atrativo')
@section('topbar.description', 'Atualize o atrativo da edicao sem sair do fluxo estrutural do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.eventos.index') }}" class="ui-console-topbar-tab">Eventos</a>
  <a href="{{ route('coordenador.eventos.edicoes.index', $edicao->evento) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.edicoes.atrativos.index', $edicao) }}" class="ui-console-topbar-tab">Atrativos</a>
  <span class="ui-console-topbar-tab is-active">Editar atrativo</span>
@endsection

@section('content')
@php use Illuminate\Support\Facades\Storage; @endphp

<div class="ui-console-page">
  <x-dashboard.page-header
    title="Editar atrativo"
    subtitle="Atualize dados, thumb e status do atrativo relacionado a esta edicao."
  >
    <a href="{{ route('coordenador.edicoes.atrativos.index', $edicao) }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  @if(session('ok'))
    <div class="ui-alert ui-alert-success mt-5">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="ui-alert ui-alert-danger mt-5">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.atrativos.update', $atrativo) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    <x-dashboard.section-card title="Dados do atrativo" subtitle="Edite informacoes, thumb e estado editorial" class="ui-coord-dashboard-panel">
      <div class="space-y-4">
        <div>
          <label class="ui-form-label">Nome *</label>
          <input name="nome" value="{{ old('nome', $atrativo->nome) }}" class="ui-form-control" required>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="ui-form-label">Slug</label>
            <input name="slug" value="{{ old('slug', $atrativo->slug) }}" class="ui-form-control">
          </div>
          <div>
            <label class="ui-form-label">Ordem</label>
            <input type="number" name="ordem" value="{{ old('ordem', $atrativo->ordem) }}" class="ui-form-control">
          </div>
        </div>

        <div>
          <label class="ui-form-label">Descricao</label>
          <textarea name="descricao" rows="4" class="ui-form-control">{{ old('descricao', $atrativo->descricao) }}</textarea>
        </div>

        <div>
          <label class="ui-form-label">Thumb</label>
          @if($atrativo->thumb_path)
            <div class="ui-event-preview-card mb-3">
              <img src="{{ Storage::disk('public')->url($atrativo->thumb_path) }}" class="ui-event-preview-image ui-event-preview-image-square" alt="">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-[var(--ui-text-soft)]">
              <input type="checkbox" name="remove_thumb" value="1">
              Remover thumb
            </label>
          @endif
          <input type="file" name="thumb" accept="image/*" class="ui-form-control mt-2">
        </div>

        <div class="max-w-[240px]">
          <label class="ui-form-label">Status</label>
          <select name="status" class="ui-form-select">
            @foreach(['publicado','rascunho','arquivado'] as $st)
              <option value="{{ $st }}" @selected(old('status', $atrativo->status) === $st)>{{ ucfirst($st) }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </x-dashboard.section-card>

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button class="ui-btn-primary">Salvar</button>
      <a href="{{ route('coordenador.edicoes.atrativos.index', $edicao) }}" class="ui-btn-secondary">Voltar</a>
    </div>
  </form>
</div>
@endsection
