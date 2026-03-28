@extends('console.layout')
@section('title', 'Nova edição - '.$evento->nome)
@section('page.title', 'Nova edição')
@section('topbar.description', 'Cadastre uma nova edição do evento mantendo a consistência estrutural e visual do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.eventos.index') }}" class="ui-console-topbar-tab">Eventos</a>
  <a href="{{ route('coordenador.eventos.edicoes.index', $evento) }}" class="ui-console-topbar-tab">Edições</a>
  <span class="ui-console-topbar-tab is-active">Nova edição</span>
@endsection

@section('content')
<div class="ui-console-page">
  <x-dashboard.page-header
    title="Nova edição"
    subtitle="Cadastre ano, período, localização e status editorial da edição do evento."
  >
    <a href="{{ route('coordenador.eventos.edicoes.index', $evento) }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  @if($errors->any())
    <div class="ui-alert ui-alert-danger mt-5">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.eventos.edicoes.store', $evento) }}" class="mt-5 space-y-6">
    @csrf

    <x-dashboard.section-card title="Dados da edição" subtitle="Preencha as informações de calendário, local e resumo" class="ui-coord-dashboard-panel">
      <div class="grid gap-4 md:grid-cols-3">
        <div>
          <label class="ui-form-label">Ano *</label>
          <input type="number" name="ano" value="{{ old('ano', $edicao->ano) }}" class="ui-form-control" required>
        </div>
        <div>
          <label class="ui-form-label">Início</label>
          <input type="date" name="data_inicio" value="{{ old('data_inicio') }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Fim</label>
          <input type="date" name="data_fim" value="{{ old('data_fim') }}" class="ui-form-control">
        </div>
      </div>

      <div class="mt-4">
        <label class="ui-form-label">Local</label>
        <input name="local" value="{{ old('local') }}" class="ui-form-control">
      </div>

      <div class="mt-4">
        <label class="ui-form-label">Link do Google Maps</label>
        <input
          name="maps_url"
          value="{{ old('maps_url') }}"
          placeholder="Cole aqui o link do Google Maps"
          class="ui-form-control"
        >
        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Ex.: https://maps.app.goo.gl/... ou https://www.google.com/maps/...</p>
      </div>

      <div class="mt-4">
        <label class="ui-form-label">Resumo</label>
        <textarea name="resumo" rows="4" class="ui-form-control">{{ old('resumo') }}</textarea>
      </div>

      <div class="mt-4 min-w-[220px] max-w-[260px]">
        <label class="ui-form-label">Status</label>
        <select name="status" class="ui-form-select">
          @foreach(['publicado','rascunho','arquivado'] as $st)
            <option value="{{ $st }}" @selected(old('status') === $st)>{{ ucfirst($st) }}</option>
          @endforeach
        </select>
      </div>
    </x-dashboard.section-card>

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button class="ui-btn-primary">Salvar</button>
      <a href="{{ route('coordenador.eventos.edicoes.index', $evento) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
