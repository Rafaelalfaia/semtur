@extends('console.layout')

@section('title', 'Nova edicao - '.$jogo->titulo)
@section('page.title', 'Nova edicao')
@section('topbar.description', 'Cadastre o ano, a capa e os dados editoriais da edicao dos Jogos Indigenas dentro do padrao do console.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indigenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edicoes</a>
  <span class="ui-console-topbar-tab is-active">Nova edicao</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Criar edicao"
    subtitle="Defina o ano, a capa e a base editorial para a proxima fase de midia, videos e patrocinadores."
  >
    <x-slot:actions>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.store', $jogo) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf

    @include('coordenador.jogos-indigenas.edicoes._form', ['mode' => 'create'])

    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar edicao</button>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
