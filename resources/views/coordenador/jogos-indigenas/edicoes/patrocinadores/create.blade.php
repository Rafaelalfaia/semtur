@extends('console.layout')

@section('title', 'Novo patrocinador - '.$edicao->titulo)
@section('page.title', 'Novo patrocinador da edicao')
@section('topbar.description', 'Cadastre um patrocinador com nome, logo opcional e URL.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indigenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edicoes</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogo, $edicao]) }}" class="ui-console-topbar-tab">Patrocinadores</a>
  <span class="ui-console-topbar-tab is-active">Novo patrocinador</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Cadastrar patrocinador" subtitle="Associe apoiadores a esta edicao com boa organizacao visual.">
    <x-slot:actions>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar</a>
    </x-slot:actions>
  </x-dashboard.page-header>

  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.store', [$jogo, $edicao]) }}" enctype="multipart/form-data" class="mt-5 space-y-6">
    @csrf
    @include('coordenador.jogos-indigenas.edicoes.patrocinadores._form', ['mode' => 'create'])
    <div class="flex flex-wrap items-center gap-3 border-t border-[var(--ui-border)] pt-5">
      <button type="submit" class="ui-btn-primary">Salvar patrocinador</button>
      <a href="{{ route('coordenador.jogos-indigenas.edicoes.patrocinadores.index', [$jogo, $edicao]) }}" class="ui-btn-secondary">Cancelar</a>
    </div>
  </form>
</div>
@endsection
