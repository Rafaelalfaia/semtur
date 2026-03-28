@extends('console.layout')

@section('title', 'Nova edição - '.$jogo->titulo)
@section('page.title', 'Nova edição')
@section('topbar.description', 'Cadastre o ano, a capa e os dados editoriais da edição dos Jogos Indígenas dentro do padrão do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indígenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edições</a>
  <span class="ui-console-topbar-tab is-active">Nova edição</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Criar edição"
    subtitle="Defina o ano, a capa e a base editorial para a próxima fase de mídia, vídeos e patrocinadores."
  >
    <x-slot:actions>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.store', $jogo) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.jogos-indigenas.edicoes._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar edição</button>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
