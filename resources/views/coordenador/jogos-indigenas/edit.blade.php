@extends('console.layout')

@section('title', 'Editar jogo indígena')
@section('page.title', 'Editar jogo indígena')
@section('topbar.description', 'Atualize a estrutura principal dos Jogos Indígenas mantendo o mesmo shell e a herança global do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indígenas</a>
  <span class="ui-console-topbar-tab is-active">Editar jogo</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Editar Jogos Indígenas"
    subtitle="Ajuste conteúdo, publicação, ordem e imagens do jogo principal."
  >
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-btn-secondary">Ver edições</a>
      <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-btn-secondary">Voltar</a>
    </div>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.update', $jogo) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @method('PUT')

    @include('coordenador.jogos-indigenas._form', ['mode' => 'edit'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar alterações</button>
      <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
