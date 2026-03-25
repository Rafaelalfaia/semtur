@extends('console.layout')
@section('title', 'Editar edicao - '.$evento->nome)
@section('page.title', 'Editar edicao')
@section('topbar.description', 'Atualize a edicao do evento mantendo o padrao de formularios e heranca global do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.eventos.index') }}" class="ui-console-topbar-tab">Eventos</a>
  <a href="{{ route('coordenador.eventos.edicoes.index', $evento) }}" class="ui-console-topbar-tab">Edicoes</a>
  <span class="ui-console-topbar-tab is-active">Editar edicao</span>
@endsection

@section('content')
@php
  $mapsFromCoords = ($edicao->lat && $edicao->lng)
      ? 'https://www.google.com/maps?q='.$edicao->lat.','.$edicao->lng
      : '';
@endphp

<div class="ui-console-page">
  <x-dashboard.page-header
    title="Editar edicao"
    subtitle="Ajuste calendario, localizacao e dados editoriais da edicao."
  >
    <a href="{{ route('coordenador.eventos.edicoes.index', $evento) }}" class="ui-btn-secondary">Voltar</a>
  </x-dashboard.page-header>

  @if(session('ok'))
    <div class="ui-alert ui-alert-success mt-5">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="ui-alert ui-alert-danger mt-5">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('coordenador.edicoes.update', $edicao) }}" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    <x-dashboard.section-card title="Dados da edicao" subtitle="Atualize datas, local, coordenadas e resumo" class="ui-coord-dashboard-panel">
      <div class="grid gap-4 md:grid-cols-3">
        <div>
          <label class="ui-form-label">Ano *</label>
          <input type="number" name="ano" value="{{ old('ano', $edicao->ano) }}" class="ui-form-control" required>
        </div>
        <div>
          <label class="ui-form-label">Inicio</label>
          <input type="date" name="data_inicio" value="{{ old('data_inicio', $edicao->data_inicio?->format('Y-m-d')) }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Fim</label>
          <input type="date" name="data_fim" value="{{ old('data_fim', $edicao->data_fim?->format('Y-m-d')) }}" class="ui-form-control">
        </div>
      </div>

      <div class="mt-4">
        <label class="ui-form-label">Local</label>
        <input name="local" value="{{ old('local', $edicao->local) }}" class="ui-form-control">
      </div>

      <div class="mt-4">
        <label class="ui-form-label">Link do Google Maps</label>
        <input
          name="maps_url"
          value="{{ old('maps_url', $mapsFromCoords) }}"
          placeholder="Cole aqui o link do Google Maps"
          class="ui-form-control"
        >
        <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Ao salvar, as coordenadas podem ser extraidas automaticamente do link.</p>
      </div>

      <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
          <label class="ui-form-label">Latitude</label>
          <input name="lat" value="{{ old('lat', $edicao->lat) }}" class="ui-form-control">
        </div>
        <div>
          <label class="ui-form-label">Longitude</label>
          <input name="lng" value="{{ old('lng', $edicao->lng) }}" class="ui-form-control">
        </div>
      </div>

      <div class="mt-4">
        <label class="ui-form-label">Resumo</label>
        <textarea name="resumo" rows="4" class="ui-form-control">{{ old('resumo', $edicao->resumo) }}</textarea>
      </div>

      <div class="mt-4 min-w-[220px] max-w-[260px]">
        <label class="ui-form-label">Status</label>
        <select name="status" class="ui-form-select">
          @foreach(['publicado','rascunho','arquivado'] as $st)
            <option value="{{ $st }}" @selected(old('status', $edicao->status) === $st)>{{ ucfirst($st) }}</option>
          @endforeach
        </select>
      </div>
    </x-dashboard.section-card>

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button class="ui-btn-primary">Salvar</button>
      <a href="{{ route('coordenador.eventos.edicoes.index', $evento) }}" class="ui-btn-secondary">Voltar</a>
    </div>
  </form>
</div>
@endsection
